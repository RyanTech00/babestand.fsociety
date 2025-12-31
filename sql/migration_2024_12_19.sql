-- ============================================
-- BabeStand - Migração 2024-12-19
-- Adiciona tabelas e colunas em falta
-- ============================================

SET NAMES utf8mb4;

-- --------------------------------------------
-- Tabela: login_history
-- Histórico de logins para visualização no admin
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS `login_history` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT NULL,
    `success` TINYINT(1) DEFAULT 0,
    `failure_reason` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Tabela: user_activity
-- Atividade do utilizador para auditoria
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS `user_activity` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `action` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Tabela: vehicle_features
-- Características/extras dos veículos
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS `vehicle_features` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `vehicle_id` INT UNSIGNED NOT NULL,
    `feature` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE,
    INDEX `idx_vehicle` (`vehicle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Adicionar colunas em falta na tabela users
-- --------------------------------------------

-- Coluna para último IP
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `last_ip` VARCHAR(45) NULL AFTER `last_login`;

-- Colunas para bloqueio de conta
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `blocked_at` TIMESTAMP NULL AFTER `is_active`;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `blocked_reason` VARCHAR(255) NULL AFTER `blocked_at`;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `blocked_by` INT UNSIGNED NULL AFTER `blocked_reason`;

-- Colunas para alteração de email pendente
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `pending_email` VARCHAR(255) NULL AFTER `email`;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `pending_email_token` VARCHAR(64) NULL AFTER `pending_email`;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `pending_email_expires` TIMESTAMP NULL AFTER `pending_email_token`;

-- Coluna para IP de registo
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `registration_ip` VARCHAR(45) NULL AFTER `created_at`;

-- Colunas para morada
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `street` VARCHAR(255) NULL AFTER `address`;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `postal_code` VARCHAR(20) NULL AFTER `street`;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `city` VARCHAR(100) NULL AFTER `postal_code`;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `district` VARCHAR(100) NULL AFTER `city`;

-- ============================================
-- Fim da Migração
-- ============================================
