<?php
/**
 * BabeStand - Classe SecurityLogger
 * Sistema de registo de eventos de segurança
 */

namespace App;

class SecurityLogger
{
    private Database $db;
    private static ?self $instance = null;

    // Tipos de evento
    public const LOGIN_SUCCESS = 'login_success';
    public const LOGIN_FAILED = 'login_failed';
    public const LOGIN_BLOCKED = 'login_blocked';
    public const ACCOUNT_LOCKED = 'account_locked';
    public const ACCOUNT_UNLOCKED = 'account_unlocked';
    public const PASSWORD_RESET_REQUEST = 'password_reset_request';
    public const PASSWORD_RESET_SUCCESS = 'password_reset_success';
    public const PASSWORD_CHANGED = 'password_changed';
    public const EMAIL_CHANGED = 'email_changed';
    public const PROFILE_UPDATED = 'profile_updated';
    public const SUSPICIOUS_ACTIVITY = 'suspicious_activity';
    public const UPLOAD_REJECTED = 'upload_rejected';
    public const CSRF_VIOLATION = 'csrf_violation';
    public const RATE_LIMIT_EXCEEDED = 'rate_limit_exceeded';
    public const ADMIN_ACTION = 'admin_action';
    public const VEHICLE_CREATED = 'vehicle_created';
    public const VEHICLE_UPDATED = 'vehicle_updated';
    public const VEHICLE_DELETED = 'vehicle_deleted';
    public const TEST_DRIVE_REQUEST = 'test_drive_request';
    public const TEST_DRIVE_CONFIRMED = 'test_drive_confirmed';
    public const TEST_DRIVE_CANCELLED = 'test_drive_cancelled';
    public const CONTACT_MESSAGE = 'contact_message';
    public const OTHER = 'other';

    // Níveis de severidade
    public const INFO = 'info';
    public const WARNING = 'warning';
    public const ERROR = 'error';
    public const CRITICAL = 'critical';

    private function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Obtém instância singleton
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Regista um evento de segurança
     */
    public function log(
        string $eventType,
        string $severity = self::INFO,
        ?string $description = null,
        ?int $userId = null,
        ?array $metadata = null
    ): bool {
        try {
            $ip = $this->getClientIp();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

            // Se não foi passado userId, tentar obter da sessão
            if ($userId === null && Session::isAuthenticated()) {
                $userId = Session::getUserId();
            }

            $this->db->query(
                "INSERT INTO security_logs (user_id, event_type, severity, ip_address, user_agent, description, metadata)
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    $userId,
                    $eventType,
                    $severity,
                    $ip,
                    $userAgent,
                    $description,
                    $metadata ? json_encode($metadata) : null
                ]
            );

            return true;
        } catch (\Exception $e) {
            // Falha silenciosa para não interromper o fluxo
            error_log("SecurityLogger Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atalhos para logs comuns
     */
    public function loginSuccess(int $userId, ?string $description = null): bool
    {
        return $this->log(self::LOGIN_SUCCESS, self::INFO, $description ?? 'Login bem sucedido', $userId);
    }

    public function loginFailed(?string $email = null, ?string $reason = null): bool
    {
        return $this->log(
            self::LOGIN_FAILED,
            self::WARNING,
            $reason ?? 'Tentativa de login falhada',
            null,
            ['email' => $email]
        );
    }

    public function accountLocked(int $userId, string $reason = 'Múltiplas tentativas falhadas'): bool
    {
        return $this->log(self::ACCOUNT_LOCKED, self::CRITICAL, $reason, $userId);
    }

    public function accountUnlocked(int $userId, ?int $adminId = null): bool
    {
        return $this->log(
            self::ACCOUNT_UNLOCKED,
            self::INFO,
            'Conta desbloqueada',
            $userId,
            $adminId ? ['unlocked_by' => $adminId] : null
        );
    }

    public function suspiciousActivity(string $description, ?int $userId = null, ?array $metadata = null): bool
    {
        return $this->log(self::SUSPICIOUS_ACTIVITY, self::CRITICAL, $description, $userId, $metadata);
    }

    public function uploadRejected(string $filename, string $reason, ?int $userId = null): bool
    {
        return $this->log(
            self::UPLOAD_REJECTED,
            self::WARNING,
            "Upload rejeitado: $reason",
            $userId,
            ['filename' => $filename, 'reason' => $reason]
        );
    }

    public function adminAction(int $adminId, string $action, ?array $metadata = null): bool
    {
        return $this->log(self::ADMIN_ACTION, self::INFO, $action, $adminId, $metadata);
    }

    /**
     * Obtém logs com filtros
     */
    public function getLogs(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = 'sl.user_id = ?';
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['event_type'])) {
            $where[] = 'sl.event_type = ?';
            $params[] = $filters['event_type'];
        }

        if (!empty($filters['severity'])) {
            $where[] = 'sl.severity = ?';
            $params[] = $filters['severity'];
        }

        if (!empty($filters['ip_address'])) {
            $where[] = 'sl.ip_address LIKE ?';
            $params[] = '%' . $filters['ip_address'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'DATE(sl.created_at) >= ?';
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'DATE(sl.created_at) <= ?';
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(sl.description LIKE ? OR sl.ip_address LIKE ?)';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT sl.*, u.name as user_name, u.email as user_email
                FROM security_logs sl
                LEFT JOIN users u ON sl.user_id = u.id
                WHERE $whereClause
                ORDER BY sl.created_at DESC
                LIMIT ? OFFSET ?";

        $params[] = (int) $limit;
        $params[] = (int) $offset;

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Conta logs com filtros
     */
    public function countLogs(array $filters = []): int
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = 'user_id = ?';
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['event_type'])) {
            $where[] = 'event_type = ?';
            $params[] = $filters['event_type'];
        }

        if (!empty($filters['severity'])) {
            $where[] = 'severity = ?';
            $params[] = $filters['severity'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'DATE(created_at) >= ?';
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'DATE(created_at) <= ?';
            $params[] = $filters['date_to'];
        }

        $whereClause = implode(' AND ', $where);

        $result = $this->db->fetch("SELECT COUNT(*) as total FROM security_logs WHERE $whereClause", $params);
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Obtém estatísticas de segurança
     */
    public function getStats(string $period = 'day'): array
    {
        // Usar sl.created_at para evitar ambiguidade em JOINs
        $periodSql = match($period) {
            'week' => 'DATE(sl.created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)',
            'month' => 'DATE(sl.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)',
            'year' => 'DATE(sl.created_at) >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)',
            default => 'DATE(sl.created_at) = CURDATE()'
        };
        
        // Para queries sem JOIN (usar security_logs diretamente)
        $periodSqlNoAlias = match($period) {
            'week' => 'DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)',
            'month' => 'DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)',
            'year' => 'DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)',
            default => 'DATE(created_at) = CURDATE()'
        };

        // Contagem por tipo de evento
        $eventCounts = $this->db->fetchAll("
            SELECT event_type, COUNT(*) as count
            FROM security_logs
            WHERE $periodSqlNoAlias
            GROUP BY event_type
            ORDER BY count DESC
        ");

        // Contagem por severidade
        $severityCounts = $this->db->fetchAll("
            SELECT severity, COUNT(*) as count
            FROM security_logs
            WHERE $periodSqlNoAlias
            GROUP BY severity
        ");

        // Top IPs suspeitos (mais tentativas falhadas)
        $suspiciousIps = $this->db->fetchAll("
            SELECT ip_address, COUNT(*) as count
            FROM security_logs
            WHERE $periodSqlNoAlias AND event_type IN ('login_failed', 'suspicious_activity', 'upload_rejected')
            GROUP BY ip_address
            ORDER BY count DESC
            LIMIT 10
        ");

        // Últimos eventos críticos
        $criticalEvents = $this->db->fetchAll("
            SELECT sl.*, u.name as user_name
            FROM security_logs sl
            LEFT JOIN users u ON sl.user_id = u.id
            WHERE $periodSql AND sl.severity = 'critical'
            ORDER BY sl.created_at DESC
            LIMIT 5
        ");

        // Tentativas de login falhadas por hora (últimas 24h)
        $loginAttemptsByHour = $this->db->fetchAll("
            SELECT HOUR(created_at) as hour, COUNT(*) as count
            FROM security_logs
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
              AND event_type = 'login_failed'
            GROUP BY HOUR(created_at)
            ORDER BY hour
        ");

        return [
            'event_counts' => $eventCounts,
            'severity_counts' => $severityCounts,
            'suspicious_ips' => $suspiciousIps,
            'critical_events' => $criticalEvents,
            'login_attempts_by_hour' => $loginAttemptsByHour,
            'total' => $this->countLogs([
                'date_from' => match($period) {
                    'week' => date('Y-m-d', strtotime('-7 days')),
                    'month' => date('Y-m-d', strtotime('-30 days')),
                    'year' => date('Y-m-d', strtotime('-1 year')),
                    default => date('Y-m-d')
                }
            ])
        ];
    }

    /**
     * Obtém tipos de eventos disponíveis
     */
    public static function getEventTypes(): array
    {
        return [
            self::LOGIN_SUCCESS => 'Login bem sucedido',
            self::LOGIN_FAILED => 'Login falhado',
            self::LOGIN_BLOCKED => 'Login bloqueado',
            self::ACCOUNT_LOCKED => 'Conta bloqueada',
            self::ACCOUNT_UNLOCKED => 'Conta desbloqueada',
            self::PASSWORD_RESET_REQUEST => 'Pedido reset password',
            self::PASSWORD_RESET_SUCCESS => 'Password alterada',
            self::PASSWORD_CHANGED => 'Password alterada',
            self::EMAIL_CHANGED => 'Email alterado',
            self::PROFILE_UPDATED => 'Perfil actualizado',
            self::SUSPICIOUS_ACTIVITY => 'Actividade suspeita',
            self::UPLOAD_REJECTED => 'Upload rejeitado',
            self::CSRF_VIOLATION => 'Violação CSRF',
            self::RATE_LIMIT_EXCEEDED => 'Rate limit excedido',
            self::ADMIN_ACTION => 'Acção de admin',
            self::VEHICLE_CREATED => 'Veículo criado',
            self::VEHICLE_UPDATED => 'Veículo actualizado',
            self::VEHICLE_DELETED => 'Veículo eliminado',
            self::TEST_DRIVE_REQUEST => 'Pedido test drive',
            self::TEST_DRIVE_CONFIRMED => 'Test drive confirmado',
            self::TEST_DRIVE_CANCELLED => 'Test drive cancelado',
            self::CONTACT_MESSAGE => 'Mensagem de contacto',
            self::OTHER => 'Outro'
        ];
    }

    /**
     * Limpa logs antigos
     */
    public function cleanOldLogs(int $daysToKeep = 90): int
    {
        $stmt = $this->db->query(
            "DELETE FROM security_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$daysToKeep]
        );
        return $stmt->rowCount();
    }

    /**
     * Obtém IP do cliente
     */
    private function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (str_contains($ip, ',')) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }
}
