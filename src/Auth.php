<?php
/**
 * BabeStand - Classe Auth
 * Sistema de autenticação com 2FA por email e Remember Me
 */

namespace App;

use App\Services\Mailer;
use App\TOTP;

class Auth
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Tenta autenticar utilizador (passo 1 - verifica credenciais)
     */
    public function attempt(string $email, string $password, string $ip, string $userAgent, bool $remember = false): array
    {
        // Verificar rate limiting
        if ($this->isLockedOut($email, $ip)) {
            return [
                'success' => false,
                'error' => 'Demasiadas tentativas. Aguarde alguns minutos antes de tentar novamente.'
            ];
        }

        // Buscar utilizador (incluindo inativos para dar feedback adequado)
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );

        // Email não existe
        if (!$user) {
            $this->logAttempt($email, $ip, $userAgent, false);
            return [
                'success' => false,
                'error' => 'Credenciais inválidas.'
            ];
        }

        // Conta desativada pelo admin
        if (!$user['is_active']) {
            $this->logAttempt($email, $ip, $userAgent, false);
            $this->logLoginHistory($user['id'], $ip, $userAgent, false, 'Conta bloqueada pelo administrador');
            $this->logUserActivity($user['id'], 'login_blocked', 'Tentativa de login com conta bloqueada', $ip);

            $reason = $user['blocked_reason'] ? ' Motivo: ' . $user['blocked_reason'] : '';
            return [
                'success' => false,
                'error' => 'A sua conta foi suspensa.' . $reason . ' Entre em contacto com o suporte.'
            ];
        }

        // Conta bloqueada temporariamente por tentativas falhadas
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $minutesLeft = ceil((strtotime($user['locked_until']) - time()) / 60);
            $this->logLoginHistory($user['id'], $ip, $userAgent, false, 'Conta bloqueada temporariamente');
            return [
                'success' => false,
                'error' => "Conta temporariamente bloqueada devido a múltiplas tentativas falhadas. Tente novamente em {$minutesLeft} minuto(s)."
            ];
        }

        // Verificar password
        if (!password_verify($password, $user['password'])) {
            $this->logAttempt($email, $ip, $userAgent, false);
            $this->logLoginHistory($user['id'], $ip, $userAgent, false, 'Password incorreta');
            $this->incrementFailedAttempts($user['id']);

            // Verificar se foi bloqueado agora
            $updatedUser = $this->db->fetch("SELECT failed_login_attempts, locked_until FROM users WHERE id = ?", [$user['id']]);
            if ($updatedUser['locked_until']) {
                return [
                    'success' => false,
                    'error' => 'Demasiadas tentativas falhadas. A sua conta foi bloqueada temporariamente por ' . LOCKOUT_DURATION . ' minutos.'
                ];
            }

            $attemptsLeft = MAX_LOGIN_ATTEMPTS - $updatedUser['failed_login_attempts'];
            if ($attemptsLeft <= 3 && $attemptsLeft > 0) {
                return [
                    'success' => false,
                    'error' => "Credenciais inválidas. Restam {$attemptsLeft} tentativa(s) antes do bloqueio temporário."
                ];
            }

            return [
                'success' => false,
                'error' => 'Credenciais inválidas.'
            ];
        }

        // Reset tentativas falhadas (credenciais válidas)
        $this->resetFailedAttempts($user['id']);
        $this->logAttempt($email, $ip, $userAgent, true);

        // Guardar se quer remember me para usar depois da verificação
        Session::set('pending_remember_me', $remember);

        // Verificar se TOTP está activo - usar app em vez de email
        if (!empty($user['totp_enabled']) && !empty($user['totp_secret'])) {
            Session::set('pending_totp_user_id', $user['id']);
            Session::set('pending_totp_ip', $ip);
            return [
                'success' => true,
                'require_totp' => true,
                'user_id' => $user['id'],
                'email' => $user['email'],
                'message' => 'Insira o código do seu autenticador.'
            ];
        }

        // TOTP não activo - enviar email com link + código
        $tokenData = $this->generateLoginToken($user['id'], $ip, $userAgent);

        $mailer = new Mailer();
        $sent = $mailer->sendLoginToken($user['email'], $user['name'], $tokenData['token'], $tokenData['code']);

        if (!$sent) {
            return [
                'success' => false,
                'error' => 'Erro ao enviar email de verificação. Tente novamente.'
            ];
        }

        return [
            'success' => true,
            'require_totp' => false,
            'user_id' => $user['id'],
            'email' => $user['email'],
            'message' => 'Verifique o seu email para completar o login.'
        ];
    }

    /**
     * Verifica token de login (passo 2 - 2FA)
     */
    public function verifyLoginToken(string $token, string $ip): array
    {
        $hashedToken = hash('sha256', $token);

        $tokenData = $this->db->fetch(
            "SELECT lt.*, u.name, u.email, u.avatar, u.role_id, u.is_active, r.name as role_name
             FROM login_tokens lt
             JOIN users u ON lt.user_id = u.id
             JOIN roles r ON u.role_id = r.id
             WHERE lt.token = ?
             AND lt.is_used = 0
             AND lt.expires_at > NOW()",
            [$hashedToken]
        );

        if (!$tokenData) {
            return [
                'success' => false,
                'error' => 'Link inválido ou expirado.'
            ];
        }

        // Verificar se o link está a ser aberto no mesmo dispositivo (IP)
        // Se for dispositivo diferente, redirecionar para login (atacante teria de saber a password)
        if ($tokenData['ip_address'] !== $ip) {
            // Não marcar como usado - permite que o utilizador legítimo ainda use o link
            return [
                'success' => false,
                'error' => 'device_mismatch',
                'message' => 'Por razões de segurança, este link só pode ser usado no dispositivo onde iniciou o login.'
            ];
        }

        // Verificar se a conta não foi bloqueada enquanto esperava pelo email
        if (!$tokenData['is_active']) {
            $this->db->query(
                "UPDATE login_tokens SET is_used = 1, used_at = NOW() WHERE id = ?",
                [$tokenData['id']]
            );
            return [
                'success' => false,
                'error' => 'A sua conta foi suspensa. Entre em contacto com o suporte.'
            ];
        }

        // Marcar token como usado
        $this->db->query(
            "UPDATE login_tokens SET is_used = 1, used_at = NOW() WHERE id = ?",
            [$tokenData['id']]
        );

        // Atualizar último login e IP
        $this->db->query(
            "UPDATE users SET last_login = NOW(), last_ip = ? WHERE id = ?",
            [$ip, $tokenData['user_id']]
        );

        // Registar login bem sucedido no histórico
        $this->logLoginHistory($tokenData['user_id'], $ip, $tokenData['user_agent'] ?? '', true);

        // Criar sessão
        Session::regenerate();
        Session::set('user_id', $tokenData['user_id']);
        Session::set('user_name', $tokenData['name']);
        Session::set('user_email', $tokenData['email']);
        Session::set('user_role', $tokenData['role_name']);
        Session::set('user_avatar', $tokenData['avatar'] ?? '');

        // Verificar se foi pedido Remember Me
        if (Session::get('pending_remember_me')) {
            $this->setRememberToken($tokenData['user_id']);
            Session::remove('pending_remember_me');
        }

        // Log de atividade
        $this->logActivity($tokenData['user_id'], 'login', 'user', $tokenData['user_id']);
        $this->logUserActivity($tokenData['user_id'], 'login', 'Login efetuado com sucesso', $ip);

        return [
            'success' => true,
            'user' => [
                'id' => $tokenData['user_id'],
                'name' => $tokenData['name'],
                'email' => $tokenData['email'],
                'role' => $tokenData['role_name']
            ]
        ];
    }

    /**
     * Verifica código TOTP (Google Authenticator)
     */
    public function verifyTOTP(string $code, string $ip): array
    {
        $userId = Session::get('pending_totp_user_id');
        $pendingIp = Session::get('pending_totp_ip');

        if (!$userId) {
            return [
                'success' => false,
                'error' => 'Sessão expirada. Faça login novamente.'
            ];
        }

        // Buscar utilizador com secret TOTP
        $user = $this->db->fetch(
            "SELECT u.*, r.name as role_name FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE u.id = ? AND u.totp_enabled = 1 AND u.totp_secret IS NOT NULL",
            [$userId]
        );

        if (!$user) {
            Session::remove('pending_totp_user_id');
            Session::remove('pending_totp_ip');
            return [
                'success' => false,
                'error' => 'Utilizador não encontrado ou TOTP não activo.'
            ];
        }

        // Verificar se a conta não foi bloqueada
        if (!$user['is_active']) {
            Session::remove('pending_totp_user_id');
            Session::remove('pending_totp_ip');
            return [
                'success' => false,
                'error' => 'A sua conta foi suspensa. Entre em contacto com o suporte.'
            ];
        }

        // Verificar código TOTP
        if (!TOTP::verify($user['totp_secret'], $code)) {
            $this->logLoginHistory($userId, $ip, $_SERVER['HTTP_USER_AGENT'] ?? '', false, 'Código TOTP inválido');
            return [
                'success' => false,
                'error' => 'Código inválido. Verifique o código no seu autenticador.'
            ];
        }

        // Limpar sessão temporária
        Session::remove('pending_totp_user_id');
        Session::remove('pending_totp_ip');

        // Atualizar último login e IP
        $this->db->query(
            "UPDATE users SET last_login = NOW(), last_ip = ? WHERE id = ?",
            [$ip, $userId]
        );

        // Registar login bem sucedido no histórico
        $this->logLoginHistory($userId, $ip, $_SERVER['HTTP_USER_AGENT'] ?? '', true);

        // Criar sessão
        Session::regenerate();
        Session::set('user_id', $user['id']);
        Session::set('user_name', $user['name']);
        Session::set('user_email', $user['email']);
        Session::set('user_role', $user['role_name']);
        Session::set('user_avatar', $user['avatar'] ?? '');

        // Verificar se foi pedido Remember Me
        if (Session::get('pending_remember_me')) {
            $this->setRememberToken($user['id']);
            Session::remove('pending_remember_me');
        }

        // Log de atividade
        $this->logActivity($user['id'], 'login_totp', 'user', $user['id']);
        $this->logUserActivity($user['id'], 'login_totp', 'Login via TOTP (Google Authenticator)', $ip);

        return [
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role_name']
            ]
        ];
    }

    /**
     * Activa TOTP para um utilizador
     */
    public function enableTOTP(int $userId, string $secret, string $code): array
    {
        // Verificar se o código é válido antes de activar
        if (!TOTP::verify($secret, $code)) {
            return [
                'success' => false,
                'error' => 'Código inválido. Verifique se digitou corretamente.'
            ];
        }

        $this->db->query(
            "UPDATE users SET totp_secret = ?, totp_enabled = 1, totp_enabled_at = NOW() WHERE id = ?",
            [$secret, $userId]
        );

        $this->logUserActivity($userId, 'totp_enabled', 'TOTP (Google Authenticator) activado', getClientIp());

        return [
            'success' => true,
            'message' => 'Autenticação por app activada com sucesso!'
        ];
    }

    /**
     * Desactiva TOTP para um utilizador
     */
    public function disableTOTP(int $userId, string $password): array
    {
        // Verificar password antes de desactivar
        $user = $this->db->fetch("SELECT password FROM users WHERE id = ?", [$userId]);

        if (!$user || !password_verify($password, $user['password'])) {
            return [
                'success' => false,
                'error' => 'Password incorreta.'
            ];
        }

        $this->db->query(
            "UPDATE users SET totp_secret = NULL, totp_enabled = 0, totp_enabled_at = NULL WHERE id = ?",
            [$userId]
        );

        $this->logUserActivity($userId, 'totp_disabled', 'TOTP (Google Authenticator) desactivado', getClientIp());

        return [
            'success' => true,
            'message' => 'Autenticação por app desactivada. Voltará a receber códigos por email.'
        ];
    }

    /**
     * Cria token de Remember Me com User-Agent e País
     */
    public function setRememberToken(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + (REMEMBER_ME_DAYS * 24 * 60 * 60));

        // Hash do User-Agent para verificação posterior
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $userAgentHash = hash('sha256', $userAgent);

        // Obter país do IP (usando API gratuita)
        $country = $this->getCountryFromIp(getClientIp());

        $this->db->query(
            "UPDATE users SET remember_token = ?, remember_token_expires = ?, remember_user_agent_hash = ?, remember_country = ? WHERE id = ?",
            [$hashedToken, $expiresAt, $userAgentHash, $country, $userId]
        );

        // Definir cookie
        setcookie(
            'remember_token',
            $token,
            [
                'expires' => time() + (REMEMBER_ME_DAYS * 24 * 60 * 60),
                'path' => '/',
                'domain' => SITE_DOMAIN,
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );
    }

    /**
     * Obtém país a partir do IP usando API gratuita
     * IPs internos retornam "LOCAL" para distinguir de IPs públicos
     */
    private function getCountryFromIp(string $ip): ?string
    {
        // IPs locais retornam "LOCAL" - assim logins internos só funcionam internamente
        if ($this->isPrivateIp($ip)) {
            return 'LOCAL';
        }

        try {
            $context = stream_context_create([
                'http' => ['timeout' => 2] // Timeout curto para não atrasar login
            ]);
            $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=countryCode", false, $context);

            if ($response) {
                $data = json_decode($response, true);
                return $data['countryCode'] ?? null;
            }
        } catch (\Exception $e) {
            // Falha silenciosa - país será null
        }

        return null;
    }

    /**
     * Verifica se IP é privado/interno
     */
    private function isPrivateIp(string $ip): bool
    {
        // IPs especiais
        if (in_array($ip, ['127.0.0.1', '::1', '0.0.0.0'])) {
            return true;
        }

        // Ranges privados IPv4
        if (str_starts_with($ip, '192.168.') ||
            str_starts_with($ip, '10.') ||
            str_starts_with($ip, '172.16.') ||
            str_starts_with($ip, '172.17.') ||
            str_starts_with($ip, '172.18.') ||
            str_starts_with($ip, '172.19.') ||
            str_starts_with($ip, '172.20.') ||
            str_starts_with($ip, '172.21.') ||
            str_starts_with($ip, '172.22.') ||
            str_starts_with($ip, '172.23.') ||
            str_starts_with($ip, '172.24.') ||
            str_starts_with($ip, '172.25.') ||
            str_starts_with($ip, '172.26.') ||
            str_starts_with($ip, '172.27.') ||
            str_starts_with($ip, '172.28.') ||
            str_starts_with($ip, '172.29.') ||
            str_starts_with($ip, '172.30.') ||
            str_starts_with($ip, '172.31.')) {
            return true;
        }

        return false;
    }

    /**
     * Verifica e autentica via Remember Me token
     * Verifica também User-Agent e País
     */
    public function loginWithRememberToken(): bool
    {
        if (Session::isAuthenticated()) {
            return true;
        }

        $token = $_COOKIE['remember_token'] ?? null;
        if (!$token) {
            return false;
        }

        $hashedToken = hash('sha256', $token);

        $user = $this->db->fetch(
            "SELECT u.*, r.name as role_name FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE u.remember_token = ?
             AND u.remember_token_expires > NOW()
             AND u.is_active = 1",
            [$hashedToken]
        );

        if (!$user) {
            // Token inválido - limpar cookie
            $this->clearRememberToken();
            return false;
        }

        $ip = getClientIp();

        // Verificar User-Agent (se existir hash guardado)
        if (!empty($user['remember_user_agent_hash'])) {
            $currentUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $currentUserAgentHash = hash('sha256', $currentUserAgent);

            if ($user['remember_user_agent_hash'] !== $currentUserAgentHash) {
                // User-Agent diferente - exigir novo login
                $this->clearRememberToken();
                $this->logUserActivity($user['id'], 'remember_me_rejected', 'Remember Me rejeitado: User-Agent diferente', $ip);
                return false;
            }
        }

        // Verificar País (se existir país guardado)
        if (!empty($user['remember_country'])) {
            $currentCountry = $this->getCountryFromIp($ip);

            // Se conseguimos obter o país actual e é diferente do guardado
            if ($currentCountry !== null && $user['remember_country'] !== $currentCountry) {
                // País diferente - exigir novo login
                $this->clearRememberToken();
                $this->logUserActivity($user['id'], 'remember_me_rejected', "Remember Me rejeitado: País diferente ({$user['remember_country']} -> {$currentCountry})", $ip);
                return false;
            }
        }

        // Criar sessão
        Session::regenerate();
        Session::set('user_id', $user['id']);
        Session::set('user_name', $user['name']);
        Session::set('user_email', $user['email']);
        Session::set('user_role', $user['role_name']);
        Session::set('user_avatar', $user['avatar'] ?? '');

        // Renovar token (mantém User-Agent e País)
        $this->setRememberToken($user['id']);

        // Atualizar último login e IP
        $this->db->query(
            "UPDATE users SET last_login = NOW(), last_ip = ? WHERE id = ?",
            [$ip, $user['id']]
        );

        // Registar login via remember me
        $this->logLoginHistory($user['id'], $ip, $_SERVER['HTTP_USER_AGENT'] ?? '', true);
        $this->logActivity($user['id'], 'login_remember_me', 'user', $user['id']);
        $this->logUserActivity($user['id'], 'login_remember_me', 'Login automático via Remember Me', $ip);

        return true;
    }

    /**
     * Limpa token de Remember Me
     */
    public function clearRememberToken(): void
    {
        if (Session::isAuthenticated()) {
            $this->db->query(
                "UPDATE users SET remember_token = NULL, remember_token_expires = NULL WHERE id = ?",
                [Session::getUserId()]
            );
        }

        setcookie(
            'remember_token',
            '',
            [
                'expires' => time() - 3600,
                'path' => '/',
                'domain' => SITE_DOMAIN,
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );
    }

    /**
     * Gera token de login para 2FA
     * Retorna array com token e código de 6 dígitos
     */
    private function generateLoginToken(int $userId, string $ip, string $userAgent): array
    {
        // Invalidar tokens anteriores
        $this->db->query(
            "UPDATE login_tokens SET is_used = 1 WHERE user_id = ? AND is_used = 0",
            [$userId]
        );

        // Gerar novo token
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);

        // Gerar código de 6 dígitos para verificação alternativa
        $verificationCode = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $expiresAt = date('Y-m-d H:i:s', time() + (TOKEN_EXPIRY_MINUTES * 60));

        $this->db->query(
            "INSERT INTO login_tokens (user_id, token, verification_code, ip_address, user_agent, expires_at)
             VALUES (?, ?, ?, ?, ?, ?)",
            [$userId, $hashedToken, $verificationCode, $ip, $userAgent, $expiresAt]
        );

        return [
            'token' => $token,
            'code' => $verificationCode
        ];
    }

    /**
     * Verifica código de 6 dígitos (alternativa ao link)
     * Usado quando o link é aberto noutro dispositivo
     */
    public function verifyLoginCode(string $email, string $code, string $ip): array
    {
        // Buscar token mais recente para este utilizador
        $tokenData = $this->db->fetch(
            "SELECT lt.*, u.name, u.email, u.avatar, u.role_id, u.is_active, r.name as role_name
             FROM login_tokens lt
             JOIN users u ON lt.user_id = u.id
             JOIN roles r ON u.role_id = r.id
             WHERE u.email = ?
             AND lt.verification_code = ?
             AND lt.is_used = 0
             AND lt.expires_at > NOW()
             ORDER BY lt.created_at DESC
             LIMIT 1",
            [$email, $code]
        );

        if (!$tokenData) {
            return [
                'success' => false,
                'error' => 'Código inválido ou expirado.'
            ];
        }

        // Verificar se a conta não foi bloqueada
        if (!$tokenData['is_active']) {
            $this->db->query(
                "UPDATE login_tokens SET is_used = 1, used_at = NOW() WHERE id = ?",
                [$tokenData['id']]
            );
            return [
                'success' => false,
                'error' => 'A sua conta foi suspensa. Entre em contacto com o suporte.'
            ];
        }

        // Marcar token como usado
        $this->db->query(
            "UPDATE login_tokens SET is_used = 1, used_at = NOW() WHERE id = ?",
            [$tokenData['id']]
        );

        // Atualizar último login e IP
        $this->db->query(
            "UPDATE users SET last_login = NOW(), last_ip = ? WHERE id = ?",
            [$ip, $tokenData['user_id']]
        );

        // Registar login bem sucedido no histórico
        $this->logLoginHistory($tokenData['user_id'], $ip, $_SERVER['HTTP_USER_AGENT'] ?? '', true);

        // Criar sessão
        Session::regenerate();
        Session::set('user_id', $tokenData['user_id']);
        Session::set('user_name', $tokenData['name']);
        Session::set('user_email', $tokenData['email']);
        Session::set('user_role', $tokenData['role_name']);
        Session::set('user_avatar', $tokenData['avatar'] ?? '');

        // Verificar se foi pedido Remember Me
        if (Session::get('pending_remember_me')) {
            $this->setRememberToken($tokenData['user_id']);
            Session::remove('pending_remember_me');
        }

        // Log de atividade
        $this->logActivity($tokenData['user_id'], 'login', 'user', $tokenData['user_id']);
        $this->logUserActivity($tokenData['user_id'], 'login_code', 'Login via código de verificação', $ip);

        return [
            'success' => true,
            'user' => [
                'id' => $tokenData['user_id'],
                'name' => $tokenData['name'],
                'email' => $tokenData['email'],
                'role' => $tokenData['role_name']
            ]
        ];
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        if (Session::isAuthenticated()) {
            $userId = Session::getUserId();
            $user = $this->db->fetch("SELECT id FROM users WHERE id = ?", [$userId]);
            if ($user) {
                $this->logActivity($userId, 'logout', 'user', $userId);
                $this->logUserActivity($userId, 'logout', 'Logout efetuado', getClientIp());
            }
        }
        $this->clearRememberToken();
        Session::destroy();
    }

    /**
     * Regista novo utilizador
     */
    public function register(array $data): array
    {
        $hashedPassword = password_hash($data['password'], PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);

        $verificationToken = bin2hex(random_bytes(32));
        $ip = getClientIp();

        try {
            $this->db->query(
                "INSERT INTO users (name, email, password, phone, email_verification_token, is_active, registration_ip)
                 VALUES (?, ?, ?, ?, ?, 0, ?)",
                [
                    $data['name'],
                    $data['email'],
                    $hashedPassword,
                    $data['phone'] ?? null,
                    $verificationToken,
                    $ip
                ]
            );

            $userId = $this->db->lastInsertId();

            $mailer = new Mailer();
            $sent = $mailer->sendVerificationEmail($data['email'], $data['name'], $verificationToken);

            if (!$sent) {
                $this->db->query("DELETE FROM users WHERE id = ?", [$userId]);
                return [
                    'success' => false,
                    'error' => 'Erro ao enviar email de verificação. Tente novamente.'
                ];
            }

            $this->logActivity($userId, 'register', 'user', $userId);
            $this->logUserActivity($userId, 'register', 'Conta criada', $ip);

            return [
                'success' => true,
                'user_id' => $userId,
                'email' => $data['email'],
                'message' => 'Conta criada! Verifique o seu email para ativar a conta.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Erro ao criar conta. Tente novamente.'
            ];
        }
    }

    /**
     * Verifica email de ativação de conta (novas contas ou reenvio)
     */
    public function verifyEmail(string $token): array
    {
        // Procurar utilizador pelo token (tanto contas novas como existentes)
        $user = $this->db->fetch(
            "SELECT id, name, email, is_active, email_verified_at FROM users WHERE email_verification_token = ?",
            [$token]
        );

        if (!$user) {
            return [
                'success' => false,
                'error' => 'Link de verificação inválido ou já utilizado.'
            ];
        }

        // Verificar se já está verificado
        if ($user['email_verified_at']) {
            return [
                'success' => false,
                'error' => 'Este email já foi verificado anteriormente.'
            ];
        }

        // Atualizar: ativar conta (se não estiver) e marcar email como verificado
        $this->db->query(
            "UPDATE users SET is_active = 1, email_verified_at = NOW(), email_verification_token = NULL WHERE id = ?",
            [$user['id']]
        );

        $this->logActivity($user['id'], 'email_verified', 'user', $user['id']);
        $this->logUserActivity($user['id'], 'email_verified', 'Email verificado', getClientIp());

        return [
            'success' => true,
            'user' => $user,
            'message' => 'Email verificado com sucesso! Pode fazer login.'
        ];
    }

    /**
     * Inicia processo de recuperação de password
     */

    /**
     * Verifica rate limiting para password reset (máx 3 por hora)
     */
    private function isPasswordResetRateLimited(string $email, string $ip): bool
    {
        $count = $this->db->fetch(
            "SELECT COUNT(*) as c FROM security_logs
             WHERE (ip_address = ? OR (description LIKE ? AND event_type = 'password_reset_request'))
             AND event_type = 'password_reset_request'
             AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
            [$ip, "%$email%"]
        );
        return $count['c'] >= 3;
    }

    public function forgotPassword(string $email): array
    {
        // Rate limiting
        $ip = getClientIp();
        if ($this->isPasswordResetRateLimited($email, $ip)) {
            return ['success' => true]; // Não revelar se funcionou
        }

        $user = $this->db->fetch(
            "SELECT id, name, email FROM users WHERE email = ? AND is_active = 1",
            [$email]
        );

        if (!$user) {
            return ['success' => true];
        }

        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);

        $this->db->query(
            "UPDATE users SET reset_token = NULL, reset_token_expires = NULL WHERE id = ?",
            [$user['id']]
        );

        $this->db->query(
            "UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?",
            [$hashedToken, $expiresAt, $user['id']]
        );

        $mailer = new Mailer();
        $mailer->sendPasswordResetEmail($user['email'], $user['name'], $token);

        $this->logActivity($user['id'], 'password_reset_requested', 'user', $user['id']);
        $this->logUserActivity($user['id'], 'password_reset_requested', 'Recuperação de password solicitada', getClientIp());

        return ['success' => true];
    }

    /**
     * Valida token de reset
     */
    public function validateResetToken(string $token): ?array
    {
        $hashedToken = hash('sha256', $token);

        return $this->db->fetch(
            "SELECT id, name, email FROM users
             WHERE reset_token = ? AND reset_token_expires > NOW() AND is_active = 1",
            [$hashedToken]
        );
    }

    /**
     * Redefine a password
     */
    public function resetPassword(string $token, string $newPassword): array
    {
        $user = $this->validateResetToken($token);

        if (!$user) {
            return [
                'success' => false,
                'error' => 'Link inválido ou expirado.'
            ];
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);

        $this->db->query(
            "UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL,
             failed_login_attempts = 0, locked_until = NULL WHERE id = ?",
            [$hashedPassword, $user['id']]
        );

        $this->logActivity($user['id'], 'password_reset_completed', 'user', $user['id']);
        $this->logUserActivity($user['id'], 'password_reset', 'Password alterada', getClientIp());

        return [
            'success' => true,
            'message' => 'Password alterada com sucesso!'
        ];
    }

    /**
     * Bloquear utilizador (para admins)
     */
    public function blockUser(int $userId, int $adminId, string $reason = null): bool
    {
        $this->db->query(
            "UPDATE users SET is_active = 0, blocked_at = NOW(), blocked_reason = ?, blocked_by = ?,
             remember_token = NULL, remember_token_expires = NULL WHERE id = ?",
            [$reason, $adminId, $userId]
        );

        $this->logActivity($adminId, 'user_blocked', 'user', $userId);
        $this->logUserActivity($userId, 'account_blocked', 'Conta bloqueada: ' . ($reason ?: 'Sem motivo especificado'), getClientIp());

        return true;
    }

    /**
     * Desbloquear utilizador (para admins)
     */
    public function unblockUser(int $userId, int $adminId): bool
    {
        $this->db->query(
            "UPDATE users SET is_active = 1, blocked_at = NULL, blocked_reason = NULL, blocked_by = NULL,
             failed_login_attempts = 0, locked_until = NULL WHERE id = ?",
            [$userId]
        );

        $this->logActivity($adminId, 'user_unblocked', 'user', $userId);
        $this->logUserActivity($userId, 'account_unblocked', 'Conta desbloqueada', getClientIp());

        return true;
    }

    /**
     * Verifica se está bloqueado por rate limiting
     */
    private function isLockedOut(string $email, string $ip): bool
    {
        $minutes = LOCKOUT_DURATION;

        $attempts = $this->db->fetch(
            "SELECT COUNT(*) as count FROM login_attempts
             WHERE (email = ? OR ip_address = ?)
             AND success = 0
             AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)",
            [$email, $ip, $minutes]
        );

        return $attempts['count'] >= MAX_LOGIN_ATTEMPTS;
    }

    private function logAttempt(string $email, string $ip, string $userAgent, bool $success): void
    {
        $this->db->query(
            "INSERT INTO login_attempts (email, ip_address, user_agent, success) VALUES (?, ?, ?, ?)",
            [$email, $ip, $userAgent, $success ? 1 : 0]
        );
    }

    /**
     * Regista login no histórico (para admin ver)
     */
    private function logLoginHistory(int $userId, string $ip, string $userAgent, bool $success, string $failureReason = null): void
    {
        $this->db->query(
            "INSERT INTO login_history (user_id, ip_address, user_agent, success, failure_reason) VALUES (?, ?, ?, ?, ?)",
            [$userId, $ip, $userAgent, $success ? 1 : 0, $failureReason]
        );
    }

    /**
     * Regista atividade do utilizador (para admin ver)
     */
    private function logUserActivity(int $userId, string $action, string $description, string $ip): void
    {
        $this->db->query(
            "INSERT INTO user_activity (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)",
            [$userId, $action, $description, $ip, $_SERVER['HTTP_USER_AGENT'] ?? null]
        );
    }

    private function incrementFailedAttempts(int $userId): void
    {
        // Incrementar contador de tentativas
        $this->db->query(
            "UPDATE users SET login_attempts = login_attempts + 1, last_failed_attempt = NOW(), failed_login_attempts = failed_login_attempts + 1 WHERE id = ?",
            [$userId]
        );

        $user = $this->db->fetch(
            "SELECT id, name, email, login_attempts, failed_login_attempts FROM users WHERE id = ?",
            [$userId]
        );

        // Log no security_logs
        $logger = SecurityLogger::getInstance();
        $logger->loginFailed($user['email'], 'Tentativa ' . $user['login_attempts'] . ' de ' . MAX_LOGIN_ATTEMPTS);

        // Após 5 tentativas seguidas, bloquear conta e enviar email
        if ($user['login_attempts'] >= 5) {
            $this->lockAccountForSecurity($user);
        } elseif ($user['failed_login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
            // Bloqueio temporário (comportamento antigo)
            $lockedUntil = date('Y-m-d H:i:s', time() + (LOCKOUT_DURATION * 60));
            $this->db->query(
                "UPDATE users SET locked_until = ? WHERE id = ?",
                [$lockedUntil, $userId]
            );
        }
    }

    /**
     * Bloqueia conta por razões de segurança e envia email
     */
    private function lockAccountForSecurity(array $user): void
    {
        // Gerar token de desbloqueio (válido 24h)
        $unlockToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + (24 * 60 * 60));

        // Bloquear conta
        $this->db->query(
            "UPDATE users SET
                is_active = 0,
                account_locked_until = ?,
                unlock_token = ?,
                unlock_token_expires = ?,
                blocked_reason = 'Bloqueio automático: múltiplas tentativas de login falhadas',
                blocked_at = NOW()
            WHERE id = ?",
            [$expiresAt, hash('sha256', $unlockToken), $expiresAt, $user['id']]
        );

        // Registar no security_logs
        $logger = SecurityLogger::getInstance();
        $logger->accountLocked($user['id'], 'Conta bloqueada após 5 tentativas de login falhadas consecutivas');

        // Enviar email de alerta
        $mailer = new Services\Mailer();
        $mailer->sendAccountLockedEmail($user['email'], $user['name'], $unlockToken);
    }

    private function resetFailedAttempts(int $userId): void
    {
        $this->db->query(
            "UPDATE users SET failed_login_attempts = 0, login_attempts = 0, locked_until = NULL, last_failed_attempt = NULL WHERE id = ?",
            [$userId]
        );

        // Log de login bem sucedido
        $logger = SecurityLogger::getInstance();
        $logger->loginSuccess($userId);
    }

    private function logActivity(int $userId, string $action, string $entityType, int $entityId): void
    {
        $this->db->query(
            "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $userId,
                $action,
                $entityType,
                $entityId,
                getClientIp(),
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]
        );
    }

    public function user(): ?array
    {
        if (!Session::isAuthenticated()) {
            return null;
        }

        return $this->db->fetch(
            "SELECT u.*, r.name as role_name FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE u.id = ?",
            [Session::getUserId()]
        );
    }

    public function cleanExpiredTokens(): int
    {
        $stmt = $this->db->query(
            "DELETE FROM login_tokens WHERE expires_at < NOW() OR is_used = 1"
        );
        return $stmt->rowCount();
    }

    public function cleanOldAttempts(): int
    {
        $stmt = $this->db->query(
            "DELETE FROM login_attempts WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );
        return $stmt->rowCount();
    }
}
