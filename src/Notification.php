<?php
/**
 * BabeStand - Sistema de Notificacoes
 */

namespace App;

class Notification
{
    private static ?Database $db = null;
    
    private static function getDb(): Database
    {
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }
    
    /**
     * Cria uma notificacao
     */
    public static function create(int $userId, string $type, string $title, string $message, ?string $link = null, ?array $data = null): int
    {
        $db = self::getDb();
        
        $db->query(
            "INSERT INTO notifications (user_id, type, title, message, link, data, is_read, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, 0, NOW())",
            [$userId, $type, $title, $message, $link, $data ? json_encode($data) : null]
        );
        
        return $db->lastInsertId();
    }
    
    /**
     * Obtem notificacoes de um utilizador
     */
    public static function getForUser(int $userId, bool $unreadOnly = false, int $limit = 20): array
    {
        $db = self::getDb();
        
        $where = "user_id = ?";
        if ($unreadOnly) {
            $where .= " AND is_read = 0";
        }
        
        return $db->fetchAll(
            "SELECT * FROM notifications WHERE $where ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }
    
    /**
     * Conta notificacoes nao lidas
     */
    public static function countUnread(int $userId): int
    {
        $db = self::getDb();
        return (int) $db->fetch(
            "SELECT COUNT(*) as total FROM notifications WHERE user_id = ? AND is_read = 0",
            [$userId]
        )['total'];
    }
    
    /**
     * Marca notificacao como lida
     */
    public static function markAsRead(int $notificationId, int $userId): bool
    {
        $db = self::getDb();
        return $db->query(
            "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ?",
            [$notificationId, $userId]
        );
    }
    
    /**
     * Marca todas como lidas
     */
    public static function markAllAsRead(int $userId): bool
    {
        $db = self::getDb();
        return $db->query(
            "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0",
            [$userId]
        );
    }
    
    /**
     * Elimina notificacao
     */
    public static function delete(int $notificationId, int $userId): bool
    {
        $db = self::getDb();
        return $db->query(
            "DELETE FROM notifications WHERE id = ? AND user_id = ?",
            [$notificationId, $userId]
        );
    }
    
    /**
     * Limpa notificacoes antigas (mais de 30 dias)
     */
    public static function cleanup(int $days = 30): int
    {
        $db = self::getDb();
        return $db->query(
            "DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY) AND is_read = 1",
            [$days]
        );
    }
    
    // === Metodos de conveniencia para tipos especificos ===
    
    /**
     * Notifica sobre test drive confirmado
     */
    public static function testDriveConfirmed(int $userId, string $vehicle, string $date): int
    {
        return self::create(
            $userId,
            'test_drive',
            'Test Drive Confirmado',
            "O seu test drive do $vehicle foi confirmado para $date.",
            'conta/test-drives.php'
        );
    }
    
    /**
     * Notifica sobre test drive cancelado
     */
    public static function testDriveCancelled(int $userId, string $vehicle): int
    {
        return self::create(
            $userId,
            'test_drive',
            'Test Drive Cancelado',
            "O test drive do $vehicle foi cancelado.",
            'conta/test-drives.php'
        );
    }
    
    /**
     * Notifica sobre resposta a mensagem
     */
    public static function messageReply(int $userId, int $messageId, string $subject): int
    {
        return self::create(
            $userId,
            'message',
            'Nova Resposta',
            "Recebeu uma resposta no ticket: $subject",
            'conta/mensagem.php?id=' . $messageId
        );
    }
    
    /**
     * Notifica admin sobre novo test drive
     */
    public static function newTestDriveForAdmin(string $userName, string $vehicle, string $date): void
    {
        $db = self::getDb();
        
        // Obter todos os admins
        $admins = $db->fetchAll(
            "SELECT u.id FROM users u JOIN roles r ON u.role_id = r.id WHERE r.slug = 'admin' AND u.is_active = 1"
        );
        
        foreach ($admins as $admin) {
            self::create(
                $admin['id'],
                'admin_alert',
                'Novo Test Drive',
                "$userName agendou test drive do $vehicle para $date.",
                'admin/test-drives.php'
            );
        }
    }
    
    /**
     * Notifica admin sobre nova mensagem de contacto
     */
    public static function newContactForAdmin(string $name, string $subject, int $messageId): void
    {
        $db = self::getDb();
        
        $admins = $db->fetchAll(
            "SELECT u.id FROM users u JOIN roles r ON u.role_id = r.id WHERE r.slug = 'admin' AND u.is_active = 1"
        );
        
        foreach ($admins as $admin) {
            self::create(
                $admin['id'],
                'admin_alert',
                'Nova Mensagem',
                "$name enviou: $subject",
                'admin/mensagem.php?id=' . $messageId
            );
        }
    }
}
