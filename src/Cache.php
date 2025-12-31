<?php
/**
 * BabeStand - Sistema de Cache
 * Cache simples baseado em ficheiros com suporte opcional para APCu
 */

namespace App;

class Cache
{
    private static ?string $cacheDir = null;
    private static bool $useAPCu;
    
    /**
     * Inicializa o sistema de cache
     */
    private static function init(): void
    {
        if (self::$cacheDir === null) {
            self::$cacheDir = dirname(__DIR__) . '/cache/';
            if (!is_dir(self::$cacheDir)) {
                mkdir(self::$cacheDir, 0755, true);
            }
            self::$useAPCu = extension_loaded('apcu') && apcu_enabled();
        }
    }
    
    /**
     * Obtem valor do cache ou executa callback e guarda
     */
    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        self::init();
        
        $cached = self::get($key);
        if ($cached !== null) {
            return $cached;
        }
        
        $value = $callback();
        self::set($key, $value, $ttl);
        
        return $value;
    }
    
    /**
     * Obtem valor do cache
     */
    public static function get(string $key): mixed
    {
        self::init();
        
        // Tentar APCu primeiro
        if (self::$useAPCu) {
            $success = false;
            $value = apcu_fetch(self::hashKey($key), $success);
            if ($success) {
                return $value;
            }
        }
        
        // Fallback para ficheiros
        $file = self::getFilePath($key);
        if (!file_exists($file)) {
            return null;
        }
        
        $content = file_get_contents($file);
        if ($content === false) {
            return null;
        }
        
        $data = json_decode($content, true);
        if ($data === null || !is_array($data)) {
            return null;
        }
        
        // Verificar expiracao
        if ($data['expires'] > 0 && $data['expires'] < time()) {
            unlink($file);
            return null;
        }
        
        return $data['value'];
    }
    
    /**
     * Guarda valor no cache
     */
    public static function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        self::init();
        
        // Guardar em APCu
        if (self::$useAPCu) {
            apcu_store(self::hashKey($key), $value, $ttl);
        }
        
        // Guardar em ficheiro
        $data = [
            'value' => $value,
            'expires' => $ttl > 0 ? time() + $ttl : 0,
            'created' => time()
        ];
        
        $file = self::getFilePath($key);
        return file_put_contents($file, json_encode($data), LOCK_EX) !== false;
    }
    
    /**
     * Remove valor do cache
     */
    public static function forget(string $key): bool
    {
        self::init();
        
        // Remover de APCu
        if (self::$useAPCu) {
            apcu_delete(self::hashKey($key));
        }
        
        // Remover ficheiro
        $file = self::getFilePath($key);
        if (file_exists($file)) {
            return unlink($file);
        }
        
        return true;
    }
    
    /**
     * Limpa todo o cache
     */
    public static function flush(): bool
    {
        self::init();
        
        // Limpar APCu
        if (self::$useAPCu) {
            apcu_clear_cache();
        }
        
        // Limpar ficheiros
        $files = glob(self::$cacheDir . '*.cache');
        if ($files) {
            foreach ($files as $file) {
                unlink($file);
            }
        }
        
        return true;
    }
    
    /**
     * Limpa cache expirado
     */
    public static function cleanup(): int
    {
        self::init();
        
        $deleted = 0;
        $files = glob(self::$cacheDir . '*.cache');
        
        if ($files) {
            foreach ($files as $file) {
                $content = file_get_contents($file);
                if ($content === false) {
                    continue;
                }
                
                $data = json_decode($content, true);
                if ($data && isset($data['expires']) && $data['expires'] > 0 && $data['expires'] < time()) {
                    unlink($file);
                    $deleted++;
                }
            }
        }
        
        return $deleted;
    }
    
    /**
     * Verifica se chave existe no cache
     */
    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }
    
    /**
     * Gera hash da chave
     */
    private static function hashKey(string $key): string
    {
        return 'babestand_' . md5($key);
    }
    
    /**
     * Obtem caminho do ficheiro de cache
     */
    private static function getFilePath(string $key): string
    {
        return self::$cacheDir . self::hashKey($key) . '.cache';
    }
}
