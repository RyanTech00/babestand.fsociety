<?php
/**
 * BabeStand - Classe TOTP
 * Implementação do algoritmo TOTP (RFC 6238) para Google Authenticator
 * Compatível com Google Authenticator, Authy, Microsoft Authenticator, etc.
 */

namespace App;

class TOTP
{
    // Caracteres Base32 (RFC 4648)
    private const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    // Configurações padrão
    private const PERIOD = 30;      // Intervalo em segundos
    private const DIGITS = 6;       // Número de dígitos do código
    private const ALGORITHM = 'sha1'; // Algoritmo HMAC

    /**
     * Gera um novo secret em Base32
     * @param int $length Tamanho do secret (16 = 80 bits, recomendado)
     */
    public static function generateSecret(int $length = 16): string
    {
        $secret = '';
        $randomBytes = random_bytes($length);

        for ($i = 0; $i < $length; $i++) {
            $secret .= self::BASE32_CHARS[ord($randomBytes[$i]) % 32];
        }

        return $secret;
    }

    /**
     * Gera URL para QR Code (otpauth://)
     * Compatível com Google Authenticator
     */
    public static function getQRCodeUrl(string $email, string $secret, string $issuer = 'BabeStand'): string
    {
        $label = rawurlencode($issuer . ':' . $email);
        $params = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => self::DIGITS,
            'period' => self::PERIOD
        ]);

        return "otpauth://totp/{$label}?{$params}";
    }

    /**
     * Gera URL da imagem QR Code usando API do goqr.me (gratuita e fiável)
     */
    public static function getQRCodeImageUrl(string $email, string $secret, int $size = 200): string
    {
        $otpauthUrl = self::getQRCodeUrl($email, $secret);
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size .
               '&ecc=M&data=' . urlencode($otpauthUrl);
    }

    /**
     * Verifica se um código TOTP é válido
     * @param string $secret Secret em Base32
     * @param string $code Código de 6 dígitos
     * @param int $window Janela de tolerância (±1 = aceita código anterior/seguinte)
     */
    public static function verify(string $secret, string $code, int $window = 1): bool
    {
        // Remover espaços e garantir 6 dígitos
        $code = preg_replace('/\s+/', '', $code);

        if (!preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $timestamp = time();
        $binarySecret = self::base32Decode($secret);

        if ($binarySecret === false) {
            return false;
        }

        // Verificar código actual e adjacentes (window)
        for ($i = -$window; $i <= $window; $i++) {
            $timeSlice = floor(($timestamp + ($i * self::PERIOD)) / self::PERIOD);
            $expectedCode = self::generateCode($binarySecret, $timeSlice);

            if (hash_equals($expectedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gera código TOTP para um determinado time slice
     */
    private static function generateCode(string $binarySecret, int $timeSlice): string
    {
        // Converter time slice para bytes (64-bit big-endian)
        $time = pack('N*', 0, $timeSlice);

        // Calcular HMAC-SHA1
        $hmac = hash_hmac(self::ALGORITHM, $time, $binarySecret, true);

        // Truncamento dinâmico (RFC 4226)
        $offset = ord($hmac[strlen($hmac) - 1]) & 0x0F;

        $binary = (
            ((ord($hmac[$offset]) & 0x7F) << 24) |
            ((ord($hmac[$offset + 1]) & 0xFF) << 16) |
            ((ord($hmac[$offset + 2]) & 0xFF) << 8) |
            (ord($hmac[$offset + 3]) & 0xFF)
        );

        $otp = $binary % pow(10, self::DIGITS);

        return str_pad((string) $otp, self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Decodifica string Base32 para binário
     */
    private static function base32Decode(string $base32): string|false
    {
        $base32 = strtoupper(str_replace(' ', '', $base32));
        $base32 = rtrim($base32, '=');

        if (empty($base32)) {
            return false;
        }

        $binary = '';
        $buffer = 0;
        $bitsLeft = 0;

        for ($i = 0; $i < strlen($base32); $i++) {
            $char = $base32[$i];
            $value = strpos(self::BASE32_CHARS, $char);

            if ($value === false) {
                return false; // Caractere inválido
            }

            $buffer = ($buffer << 5) | $value;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $binary .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $binary;
    }

    /**
     * Gera código actual (para testes/debug)
     */
    public static function getCurrentCode(string $secret): string
    {
        $binarySecret = self::base32Decode($secret);
        $timeSlice = floor(time() / self::PERIOD);
        return self::generateCode($binarySecret, $timeSlice);
    }

    /**
     * Retorna segundos até o próximo código
     */
    public static function getSecondsRemaining(): int
    {
        return self::PERIOD - (time() % self::PERIOD);
    }
}
