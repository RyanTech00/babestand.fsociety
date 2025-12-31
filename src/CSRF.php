<?php
/**
 * BabeStand - Classe CSRF
 * Proteção contra Cross-Site Request Forgery
 */

namespace App;

class CSRF
{
    private const TOKEN_NAME = '_csrf_token';
    private const TOKEN_EXPIRY = 3600; // 1 hora

    /**
     * Gera um token CSRF
     */
    public static function generate(): string
    {
        $token = bin2hex(random_bytes(32));
        
        Session::set(self::TOKEN_NAME, [
            'token' => $token,
            'expires' => time() + self::TOKEN_EXPIRY
        ]);

        return $token;
    }

    /**
     * Obtém o token atual ou gera um novo
     */
    public static function getToken(): string
    {
        $data = Session::get(self::TOKEN_NAME);

        if (!$data || time() > $data['expires']) {
            return self::generate();
        }

        return $data['token'];
    }

    /**
     * Alias para getToken() - compatibilidade
     */
    public static function token(): string
    {
        return self::getToken();
    }

    /**
     * Valida um token CSRF
     */
    public static function validate(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        $data = Session::get(self::TOKEN_NAME);

        if (!$data) {
            return false;
        }

        // Verificar expiração
        if (time() > $data['expires']) {
            Session::remove(self::TOKEN_NAME);
            return false;
        }

        // Comparação timing-safe
        if (!hash_equals($data['token'], $token)) {
            return false;
        }

        // Nota: Não regeneramos o token após uso para permitir múltiplos
        // pedidos AJAX na mesma página. O token expira após 1 hora.

        return true;
    }

    /**
     * Gera campo HTML hidden com o token
     */
    public static function field(): string
    {
        $token = self::getToken();
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            self::TOKEN_NAME,
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Obtém o nome do campo
     */
    public static function getFieldName(): string
    {
        return self::TOKEN_NAME;
    }

    /**
     * Valida token do POST
     */
    public static function validateRequest(): bool
    {
        $token = $_POST[self::TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        return self::validate($token);
    }
}
