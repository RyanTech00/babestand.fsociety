<?php
/**
 * BabeStand - Bootstrap
 * Inicialização da aplicação
 */

// Definir constante de segurança
define('BABESTAND', true);

// Carregar configuração
require_once __DIR__ . '/config/config.php';

// Autoloader simples
spl_autoload_register(function ($class) {
    // Converter namespace para path
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/src/';

    // Verificar se a classe usa o namespace App
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Obter nome relativo da classe
    $relativeClass = substr($class, $len);

    // Converter para path
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    // Carregar ficheiro se existir
    if (file_exists($file)) {
        require $file;
    }
});

// Carregar classes base
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/Session.php';
require_once __DIR__ . '/src/CSRF.php';
require_once __DIR__ . '/src/Validator.php';
require_once __DIR__ . '/src/Auth.php';

// Iniciar sessão
\App\Session::start();

// Headers de segurança HTTP
if (!headers_sent()) {
    header("X-Frame-Options: SAMEORIGIN");
    header("X-Content-Type-Options: nosniff");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("X-XSS-Protection: 1; mode=block");
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net; img-src 'self' data: blob: api.qrserver.com; font-src 'self' cdn.jsdelivr.net; connect-src 'self' cdn.jsdelivr.net; frame-ancestors 'self'");
}

// Verificar Remember Me token
if (!\App\Session::isAuthenticated() && isset($_COOKIE['remember_token'])) {
    $auth = new \App\Auth();
    $auth->loginWithRememberToken();
}

// Funções helper globais

/**
 * Escapa output HTML
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Gera URL absoluto
 */
function url(string $path = ''): string
{
    $path = ltrim($path, '/');

    // Remover extensão .php para URLs limpas (exceto para api/)
    if (!empty($path) && !str_starts_with($path, 'api/')) {
        $path = preg_replace('/\.php$/', '', $path);
    }

    return SITE_URL . '/' . $path;
}

/**
 * Redireciona para URL
 */
function redirect(string $path, int $status = 302): void
{
    header('Location: ' . url($path), true, $status);
    exit;
}

/**
 * Obtém valor de input (POST ou GET)
 */
function input(string $key, mixed $default = null): mixed
{
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

/**
 * Obtém valor antigo (para repopular formulários)
 */
function old(string $key, mixed $default = ''): mixed
{
    return \App\Session::flash('_old_input')[$key] ?? $default;
}

/**
 * Define inputs antigos para flash
 */
function flashOldInput(): void
{
    \App\Session::set('_old_input', $_POST);
}

/**
 * Verifica se é request POST
 */
function isPost(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Verifica se é request AJAX
 */
function isAjax(): bool
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Resposta JSON
 */
function json(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Obtém IP do cliente (considerando proxy reverso)
 */
function getClientIp(): string
{
    // Ordem de prioridade para headers de proxy
    $headers = [
        'HTTP_CF_CONNECTING_IP',     // Cloudflare
        'HTTP_X_REAL_IP',            // Nginx proxy
        'HTTP_X_FORWARDED_FOR',      // Proxy genérico
    ];
    
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = $_SERVER[$header];
            
            // X-Forwarded-For pode ter múltiplos IPs (client, proxy1, proxy2)
            // O primeiro é o IP original do cliente
            if (strpos($ip, ',') !== false) {
                $ips = explode(',', $ip);
                $ip = trim($ips[0]);
            }
            
            // Validar se é um IP válido
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    
    // Fallback para REMOTE_ADDR
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Formata preço em euros
 */
function formatPrice(float $price): string
{
    return number_format($price, 2, ',', '.') . ' €';
}

/**
 * Formata data para português
 */
function formatDate(string $date, string $format = 'd/m/Y'): string
{
    return date($format, strtotime($date));
}

/**
 * Formata quilómetros
 */
function formatMileage(int $mileage): string
{
    return number_format($mileage, 0, '', '.') . ' km';
}

/**
 * Gera slug a partir de texto
 */
function slug(string $text): string
{
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('/[^a-zA-Z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return strtolower(trim($text, '-'));
}

/**
 * Trunca texto
 */
function truncate(string $text, int $length = 100, string $suffix = '...'): string
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Verifica se utilizador está autenticado
 */
function auth(): bool
{
    return \App\Session::isAuthenticated();
}

/**
 * Verifica se utilizador é admin
 */
function isAdmin(): bool
{
    return \App\Session::isAdmin();
}

/**
 * Valida se URL de redirect é segura (interna)
 */
function isValidRedirectUrl(?string $url): bool
{
    if (empty($url)) {
        return false;
    }

    // Deve começar com / (relativo)
    if ($url[0] !== '/') {
        return false;
    }

    // Não pode ter // (evita //external.com)
    if (strpos($url, '//') !== false) {
        return false;
    }

    // Não pode ter caracteres perigosos
    if (preg_match('/[<>"\']/', $url)) {
        return false;
    }

    // Não pode ser página de login (loop infinito)
    $blockedPages = ['/login.php', '/registar.php', '/recuperar-password.php', '/logout.php'];
    foreach ($blockedPages as $blocked) {
        if (strpos($url, $blocked) === 0) {
            return false;
        }
    }

    return true;
}

/**
 * Requer autenticação (verifica também se conta está ativa)
 */
function requireAuth(): void
{
    if (!auth()) {
        // Guardar URL pretendida para redirect após login
        $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
        if (!empty($currentUrl) && isValidRedirectUrl($currentUrl)) {
            \App\Session::set('redirect_after_login', $currentUrl);
        }

        \App\Session::setFlash('error', 'Deve fazer login para aceder a esta página.');
        redirect('login.php');
    }

    // Verificar se a conta ainda está ativa na BD
    $db = \App\Database::getInstance();
    $userId = \App\Session::get('user_id');
    $user = $db->fetch("SELECT is_active, blocked_reason FROM users WHERE id = ?", [$userId]);

    if (!$user || !$user['is_active']) {
        // Conta foi bloqueada - forçar logout
        $auth = new \App\Auth();
        $auth->logout();

        $reason = $user['blocked_reason'] ? ' Motivo: ' . $user['blocked_reason'] : '';
        \App\Session::setFlash('error', 'A sua conta foi suspensa.' . $reason . ' Entre em contacto com o suporte.');
        redirect('login.php');
    }
}

/**
 * Requer admin
 */
function requireAdmin(): void
{
    requireAuth();
    if (!isAdmin()) {
        \App\Session::setFlash('error', 'Não tem permissões para aceder a esta página.');
        redirect('index.php');
    }
}

/**
 * Inclui view
 */
function view(string $name, array $data = []): void
{
    extract($data, EXTR_SKIP); // EXTR_SKIP previne sobrescrita de variáveis existentes
    $viewPath = __DIR__ . '/views/' . str_replace('.', '/', $name) . '.php';

    if (file_exists($viewPath)) {
        include $viewPath;
    } else {
        throw new \Exception("View not found: {$name}");
    }
}

/**
 * Carrega asset com cache busting
 */
function asset(string $path): string
{
    $fullPath = __DIR__ . '/public/' . ltrim($path, '/');
    $version = file_exists($fullPath) ? filemtime($fullPath) : time();
    return url($path) . '?v=' . $version;
}
