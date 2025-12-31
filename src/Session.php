<?php
/**
 * BabeStand - Classe Session
 * Gestão segura de sessões
 */

namespace App;

class Session
{
    private static bool $started = false;

    /**
     * Inicia a sessão com configurações seguras
     */
    public static function start(): void
    {
        if (self::$started) {
            return;
        }

        // Configurações de segurança
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'domain' => SITE_DOMAIN,
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        session_name('BABESTAND_SESSION');
        session_start();

        self::$started = true;

        // Regenerar ID periodicamente
        if (!isset($_SESSION['_created'])) {
            $_SESSION['_created'] = time();
        } elseif (time() - $_SESSION['_created'] > 1800) {
            self::regenerate();
        }

        // Verificar timeout
        if (isset($_SESSION['_last_activity']) && 
            (time() - $_SESSION['_last_activity'] > SESSION_LIFETIME)) {
            self::destroy();
            return;
        }
        $_SESSION['_last_activity'] = time();
    }

    /**
     * Regenera o ID da sessão
     */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
        $_SESSION['_created'] = time();
    }

    /**
     * Define um valor na sessão
     */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Obtém um valor da sessão
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Verifica se existe um valor
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove um valor da sessão
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Obtém e remove um valor (flash message)
     */
    public static function flash(string $key, mixed $default = null): mixed
    {
        $value = self::get($key, $default);
        self::remove($key);
        return $value;
    }

    /**
     * Define uma mensagem flash
     */
    public static function setFlash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type] = $message;
    }

    /**
     * Obtém mensagens flash
     */
    public static function getFlash(string $type): ?string
    {
        $message = $_SESSION['_flash'][$type] ?? null;
        unset($_SESSION['_flash'][$type]);
        return $message;
    }

    /**
     * Obtém todas as mensagens flash
     */
    public static function getAllFlash(): array
    {
        $messages = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $messages;
    }

    /**
     * Verifica se utilizador está autenticado
     */
    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
    }

    /**
     * Verifica se utilizador é admin
     */
    public static function isAdmin(): bool
    {
        return self::isAuthenticated() && (self::get('user_role') === 'admin');
    }

    /**
     * Obtém ID do utilizador
     */
    public static function getUserId(): ?int
    {
        return self::get('user_id');
    }

    /**
     * Destrói a sessão
     */
    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        self::$started = false;
    }
}
