<?php
/**
 * BabeStand - Classe Env
 * Carrega variáveis de ambiente do ficheiro .env
 */

namespace App;

class Env
{
    private static bool $loaded = false;
    private static array $cache = [];

    /**
     * Carrega o ficheiro .env
     */
    public static function load(string $path): void
    {
        if (self::$loaded) {
            return;
        }

        $envFile = rtrim($path, '/') . '/.env';
        
        if (!file_exists($envFile)) {
            throw new \Exception('.env file not found: ' . $envFile);
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Ignorar comentários
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Processar linha KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                // Remover aspas se existirem
                if (preg_match('/^(["\'])(.*)\1$/', $value, $matches)) {
                    $value = $matches[2];
                }

                // Guardar em cache e definir
                self::$cache[$name] = $value;
                
                if (!array_key_exists($name, $_ENV)) {
                    $_ENV[$name] = $value;
                }
                if (!array_key_exists($name, $_SERVER)) {
                    $_SERVER[$name] = $value;
                }
                putenv("$name=$value");
            }
        }

        self::$loaded = true;
    }

    /**
     * Obtém uma variável de ambiente
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        // Verificar cache primeiro
        if (isset(self::$cache[$key])) {
            return self::castValue(self::$cache[$key]);
        }

        // Verificar $_ENV
        if (isset($_ENV[$key])) {
            return self::castValue($_ENV[$key]);
        }

        // Verificar getenv()
        $value = getenv($key);
        if ($value !== false) {
            return self::castValue($value);
        }

        return $default;
    }

    /**
     * Converte valores especiais (true, false, null)
     */
    private static function castValue(string $value): mixed
    {
        $lower = strtolower($value);
        
        switch ($lower) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
            case 'empty':
            case '(empty)':
                return '';
        }

        // Se for numérico, converter
        if (is_numeric($value)) {
            return strpos($value, '.') !== false ? (float) $value : (int) $value;
        }

        return $value;
    }
}
