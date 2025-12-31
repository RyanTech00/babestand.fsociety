<?php
/**
 * BabeStand - Serviço de Geolocalização por IP
 * Usa ip-api.com (gratuito, até 45 req/min)
 */

namespace App\Services;

class GeoIP
{
    private static array $cache = [];
    private const API_URL = 'http://ip-api.com/json/';
    private const CACHE_TTL = 86400; // 24 horas

    /**
     * Consulta informações de localização para um IP
     */
    public static function lookup(string $ip): ?array
    {
        // Verificar cache em memória
        if (isset(self::$cache[$ip])) {
            return self::$cache[$ip];
        }

        // IPs privados/locais
        if (self::isPrivateIP($ip)) {
            $data = [
                'status' => 'success',
                'country' => 'Local',
                'regionName' => '',
                'city' => 'Rede Privada',
                'isp' => 'Local Network'
            ];
            self::$cache[$ip] = $data;
            return $data;
        }

        // Consultar API
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 3,
                    'ignore_errors' => true
                ]
            ]);
            
            $response = @file_get_contents(
                self::API_URL . urlencode($ip) . '?fields=status,country,regionName,city,isp',
                false,
                $context
            );
            
            if ($response) {
                $data = json_decode($response, true);
                if ($data && isset($data['status']) && $data['status'] === 'success') {
                    self::$cache[$ip] = $data;
                    return $data;
                }
            }
        } catch (\Exception $e) {
            // Silenciosamente falhar
        }

        return null;
    }

    /**
     * Retorna string formatada com localização
     * Exemplo: "Lisboa, Portugal" ou "Rede Privada/Local"
     */
    public static function getLocationString(string $ip): string
    {
        $data = self::lookup($ip);
        
        if (!$data) {
            return 'Localização desconhecida';
        }
        
        if ($data['country'] === 'Local') {
            return 'Rede Privada/Local';
        }

        $parts = [];
        if (!empty($data['city'])) {
            $parts[] = $data['city'];
        }
        if (!empty($data['regionName']) && $data['regionName'] !== $data['city']) {
            $parts[] = $data['regionName'];
        }
        if (!empty($data['country'])) {
            $parts[] = $data['country'];
        }

        return implode(', ', $parts) ?: 'Desconhecido';
    }

    /**
     * Retorna string curta (apenas cidade e país)
     */
    public static function getShortLocation(string $ip): string
    {
        $data = self::lookup($ip);
        
        if (!$data) {
            return '';
        }
        
        if ($data['country'] === 'Local') {
            return 'Local';
        }

        $parts = array_filter([
            $data['city'] ?? '',
            $data['country'] ?? ''
        ]);

        return implode(', ', $parts);
    }

    /**
     * Verifica se é IP privado/reservado
     */
    private static function isPrivateIP(string $ip): bool
    {
        // filter_var retorna false para IPs privados quando usamos estas flags
        return !filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    /**
     * Retorna o ISP do IP
     */
    public static function getISP(string $ip): string
    {
        $data = self::lookup($ip);
        return $data['isp'] ?? 'Desconhecido';
    }
}
