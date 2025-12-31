-- Migration: Sistema de Segurança e Logs
-- Data: 2024-12-19

-- 1. Adicionar campo de tentativas na tabela users (se não existir)
ALTER TABLE users
ADD COLUMN IF NOT EXISTS login_attempts INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS last_failed_attempt DATETIME NULL,
ADD COLUMN IF NOT EXISTS account_locked_until DATETIME NULL,
ADD COLUMN IF NOT EXISTS unlock_token VARCHAR(64) NULL,
ADD COLUMN IF NOT EXISTS unlock_token_expires DATETIME NULL;

-- 2. Criar tabela de logs de segurança
CREATE TABLE IF NOT EXISTS security_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    event_type ENUM(
        'login_success',
        'login_failed',
        'login_blocked',
        'account_locked',
        'account_unlocked',
        'password_reset_request',
        'password_reset_success',
        'password_changed',
        'email_changed',
        'profile_updated',
        'suspicious_activity',
        'upload_rejected',
        'csrf_violation',
        'rate_limit_exceeded',
        'admin_action',
        'vehicle_created',
        'vehicle_updated',
        'vehicle_deleted',
        'test_drive_request',
        'test_drive_confirmed',
        'test_drive_cancelled',
        'contact_message',
        'other'
    ) NOT NULL,
    severity ENUM('info', 'warning', 'error', 'critical') DEFAULT 'info',
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    description TEXT NULL,
    metadata JSON NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_user_id (user_id),
    INDEX idx_event_type (event_type),
    INDEX idx_severity (severity),
    INDEX idx_created_at (created_at),
    INDEX idx_ip_address (ip_address),

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Criar índice composto para pesquisas frequentes
CREATE INDEX IF NOT EXISTS idx_security_logs_search
ON security_logs (event_type, created_at, user_id);

-- 4. Verificar e atualizar constraint se existir
-- (Para garantir compatibilidade)
