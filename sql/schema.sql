-- ============================================
-- BabeStand - Schema da Base de Dados
-- Sistema de Gestão de Stand Automóvel
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------
-- Tabela: roles
-- --------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `description` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`name`, `description`) VALUES
('admin', 'Administrador do sistema'),
('user', 'Utilizador registado');

-- --------------------------------------------
-- Tabela: users
-- --------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `role_id` INT UNSIGNED NOT NULL DEFAULT 2,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) NULL,
    `address` TEXT NULL,
    `nif` VARCHAR(9) NULL,
    `avatar` VARCHAR(255) NULL,
    `remember_token` VARCHAR(64) NULL,
    `remember_token_expires` TIMESTAMP NULL,
    `reset_token` VARCHAR(64) NULL,
    `reset_token_expires` TIMESTAMP NULL,
    `email_verified_at` TIMESTAMP NULL,
    `email_verification_token` VARCHAR(64) NULL,
    `failed_login_attempts` INT UNSIGNED DEFAULT 0,
    `locked_until` TIMESTAMP NULL,
    `last_login` TIMESTAMP NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT,
    INDEX `idx_email` (`email`),
    INDEX `idx_remember_token` (`remember_token`),
    INDEX `idx_reset_token` (`reset_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Tabela: login_tokens (2FA por email)
-- --------------------------------------------
DROP TABLE IF EXISTS `login_tokens`;
CREATE TABLE `login_tokens` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `token` VARCHAR(64) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT NULL,
    `is_used` TINYINT(1) DEFAULT 0,
    `used_at` TIMESTAMP NULL,
    `expires_at` TIMESTAMP NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_token` (`token`),
    INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Tabela: login_attempts (rate limiting)
-- --------------------------------------------
DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT NULL,
    `success` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_ip` (`ip_address`),
    INDEX `idx_email` (`email`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Tabela: brands (marcas)
-- --------------------------------------------
DROP TABLE IF EXISTS `brands`;
CREATE TABLE `brands` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `logo` VARCHAR(255) NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `brands` (`name`) VALUES
('Audi'), ('BMW'), ('Citroën'), ('Dacia'), ('Fiat'),
('Ford'), ('Honda'), ('Hyundai'), ('Kia'), ('Mazda'),
('Mercedes-Benz'), ('Nissan'), ('Opel'), ('Peugeot'), ('Renault'),
('Seat'), ('Škoda'), ('Toyota'), ('Volkswagen'), ('Volvo');

-- --------------------------------------------
-- Tabela: fuel_types (combustíveis)
-- --------------------------------------------
DROP TABLE IF EXISTS `fuel_types`;
CREATE TABLE `fuel_types` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `fuel_types` (`name`) VALUES
('Gasolina'), ('Diesel'), ('Híbrido'), ('Elétrico'), ('GPL'), ('Híbrido Plug-in');

-- --------------------------------------------
-- Tabela: vehicle_status (estados)
-- --------------------------------------------
DROP TABLE IF EXISTS `vehicle_status`;
CREATE TABLE `vehicle_status` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `color` VARCHAR(7) DEFAULT '#6c757d',
    `show_public` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `vehicle_status` (`name`, `color`, `show_public`) VALUES
('Disponível', '#28a745', 1),
('Indisponível', '#dc3545', 0),
('Brevemente', '#ffc107', 1),
('Reservado', '#17a2b8', 0),
('Vendido', '#6c757d', 0);

-- --------------------------------------------
-- Tabela: vehicles
-- --------------------------------------------
DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE `vehicles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `brand_id` INT UNSIGNED NOT NULL,
    `model` VARCHAR(100) NOT NULL,
    `version` VARCHAR(100) NULL,
    `fuel_type_id` INT UNSIGNED NOT NULL,
    `status_id` INT UNSIGNED DEFAULT 1,
    `year` YEAR NOT NULL,
    `color` VARCHAR(50) NOT NULL,
    `mileage` INT UNSIGNED DEFAULT 0,
    `doors` TINYINT UNSIGNED DEFAULT 5,
    `seats` TINYINT UNSIGNED DEFAULT 5,
    `power_hp` SMALLINT UNSIGNED NULL,
    `engine_cc` SMALLINT UNSIGNED NULL,
    `transmission` ENUM('Manual', 'Automática') DEFAULT 'Manual',
    `price` DECIMAL(10,2) NOT NULL,
    `previous_price` DECIMAL(10,2) NULL,
    `description` TEXT NULL,
    `features` JSON NULL,
    `vin` VARCHAR(17) NULL,
    `license_plate` VARCHAR(10) NULL,
    `views` INT UNSIGNED DEFAULT 0,
    `is_featured` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`brand_id`) REFERENCES `brands`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`fuel_type_id`) REFERENCES `fuel_types`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`status_id`) REFERENCES `vehicle_status`(`id`) ON DELETE RESTRICT,
    INDEX `idx_brand` (`brand_id`),
    INDEX `idx_status` (`status_id`),
    INDEX `idx_price` (`price`),
    INDEX `idx_year` (`year`),
    INDEX `idx_featured` (`is_featured`),
    FULLTEXT `idx_search` (`model`, `description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Tabela: vehicle_images
-- --------------------------------------------
DROP TABLE IF EXISTS `vehicle_images`;
CREATE TABLE `vehicle_images` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `vehicle_id` INT UNSIGNED NOT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NULL,
    `is_primary` TINYINT(1) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE,
    INDEX `idx_vehicle` (`vehicle_id`),
    INDEX `idx_primary` (`is_primary`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Tabela: test_drive_status
-- --------------------------------------------
DROP TABLE IF EXISTS `test_drive_status`;
CREATE TABLE `test_drive_status` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `color` VARCHAR(7) DEFAULT '#6c757d'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `test_drive_status` (`name`, `color`) VALUES
('Pendente', '#ffc107'),
('Confirmado', '#28a745'),
('Concluído', '#17a2b8'),
('Cancelado', '#dc3545'),
('Não Compareceu', '#6c757d');

-- --------------------------------------------
-- Tabela: test_drives
-- --------------------------------------------
DROP TABLE IF EXISTS `test_drives`;
CREATE TABLE `test_drives` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `vehicle_id` INT UNSIGNED NOT NULL,
    `status_id` INT UNSIGNED DEFAULT 1,
    `scheduled_date` DATE NOT NULL,
    `scheduled_time` TIME NOT NULL,
    `notes` TEXT NULL,
    `admin_notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`status_id`) REFERENCES `test_drive_status`(`id`) ON DELETE RESTRICT,
    
    -- Apenas um test drive por slot (data/hora)
    UNIQUE KEY `unique_slot` (`scheduled_date`, `scheduled_time`),
    
    INDEX `idx_user` (`user_id`),
    INDEX `idx_vehicle` (`vehicle_id`),
    INDEX `idx_date` (`scheduled_date`),
    INDEX `idx_status` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Tabela: favorites
-- --------------------------------------------
DROP TABLE IF EXISTS `favorites`;
CREATE TABLE `favorites` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `vehicle_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_favorite` (`user_id`, `vehicle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Tabela: activity_logs
-- --------------------------------------------
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL,
    `action` VARCHAR(100) NOT NULL,
    `entity_type` VARCHAR(50) NULL,
    `entity_id` INT UNSIGNED NULL,
    `old_values` JSON NULL,
    `new_values` JSON NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Tabela: settings
-- --------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    `value` TEXT NULL,
    `type` ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    `description` VARCHAR(255) NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`key`, `value`, `type`, `description`) VALUES
('site_name', 'BabeStand', 'string', 'Nome do site'),
('site_email', 'info@babestand.fsociety.pt', 'string', 'Email de contacto'),
('site_phone', '+351 912 345 678', 'string', 'Telefone de contacto'),
('site_address', 'Rua Example, 123, Porto', 'string', 'Morada do stand'),
('test_drive_start_hour', '09:00', 'string', 'Hora de início dos test drives'),
('test_drive_end_hour', '18:00', 'string', 'Hora de fim dos test drives'),
('test_drive_duration', '30', 'integer', 'Duração do test drive em minutos'),
('test_drive_interval', '60', 'integer', 'Intervalo entre test drives em minutos'),
('max_login_attempts', '5', 'integer', 'Máximo de tentativas de login'),
('lockout_duration', '30', 'integer', 'Duração do bloqueio em minutos'),
('token_expiry_minutes', '15', 'integer', 'Expiração do token 2FA em minutos'),
('remember_me_days', '30', 'integer', 'Duração do remember me em dias');

-- --------------------------------------------
-- Tabela: contact_messages
-- --------------------------------------------
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) NULL,
    `subject` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `vehicle_id` INT UNSIGNED NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `read_at` TIMESTAMP NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE SET NULL,
    INDEX `idx_read` (`is_read`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- Fim do Schema
-- ============================================
