-- =====================================================
-- MIGRAÇÃO: Sistema de Propriedade de Veículos
-- BabeStand - 2025-12-29
-- =====================================================

-- 1. Adicionar campos à tabela vehicles para rastrear comprador
ALTER TABLE vehicles
ADD COLUMN buyer_id INT UNSIGNED NULL AFTER status_id,
ADD COLUMN sold_date DATETIME NULL AFTER buyer_id,
ADD COLUMN sold_price DECIMAL(12,2) NULL AFTER sold_date,
ADD COLUMN sold_mileage INT UNSIGNED NULL AFTER sold_price,
ADD INDEX idx_buyer (buyer_id),
ADD CONSTRAINT fk_vehicle_buyer FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE SET NULL;

-- 2. Adicionar intervalos de manutenção às marcas
ALTER TABLE brands
ADD COLUMN service_interval_km INT UNSIGNED DEFAULT 15000 AFTER is_active,
ADD COLUMN service_interval_months INT UNSIGNED DEFAULT 12 AFTER service_interval_km,
ADD COLUMN inspection_interval_km INT UNSIGNED DEFAULT 30000 AFTER service_interval_months,
ADD COLUMN inspection_interval_months INT UNSIGNED DEFAULT 24 AFTER inspection_interval_km;

-- 3. Atualizar intervalos por marca (baseado em pesquisa)
UPDATE brands SET service_interval_km = 15000, service_interval_months = 12, inspection_interval_km = 30000, inspection_interval_months = 24 WHERE name = 'Audi';
UPDATE brands SET service_interval_km = 16000, service_interval_months = 12, inspection_interval_km = 32000, inspection_interval_months = 24 WHERE name = 'Bentley';
UPDATE brands SET service_interval_km = 15000, service_interval_months = 12, inspection_interval_km = 30000, inspection_interval_months = 24 WHERE name = 'BMW';
UPDATE brands SET service_interval_km = 20000, service_interval_months = 12, inspection_interval_km = 40000, inspection_interval_months = 24 WHERE name = 'Citroën';
UPDATE brands SET service_interval_km = 15000, service_interval_months = 12, inspection_interval_km = 30000, inspection_interval_months = 24 WHERE name = 'Dacia';
UPDATE brands SET service_interval_km = 15000, service_interval_months = 12, inspection_interval_km = 30000, inspection_interval_months = 24 WHERE name = 'Ducati';
UPDATE brands SET service_interval_km = 20000, service_interval_months = 12, inspection_interval_km = 40000, inspection_interval_months = 24 WHERE name = 'Ferrari';
UPDATE brands SET service_interval_km = 15000, service_interval_months = 12, inspection_interval_km = 30000, inspection_interval_months = 24 WHERE name = 'Fiat';
UPDATE brands SET service_interval_km = 20000, service_interval_months = 12, inspection_interval_km = 40000, inspection_interval_months = 24 WHERE name = 'Ford';
UPDATE brands SET service_interval_km = 15000, service_interval_months = 12, inspection_interval_km = 30000, inspection_interval_months = 24 WHERE name = 'Honda';
UPDATE brands SET service_interval_km = 15000, service_interval_months = 12, inspection_interval_km = 30000, inspection_interval_months = 24 WHERE name = 'Hyundai';
UPDATE brands SET service_interval_km = 26000, service_interval_months = 12, inspection_interval_km = 52000, inspection_interval_months = 24 WHERE name = 'Jaguar';
UPDATE brands SET service_interval_km = 15000, service_interval_months = 12, inspection_interval_km = 30000, inspection_interval_months = 24 WHERE name = 'Kia';
UPDATE brands SET service_interval_km = 15000, service_interval_months = 12, inspection_interval_km = 30000, inspection_interval_months = 24 WHERE name = 'Lamborghini';
UPDATE brands SET service_interval_km = 20000, service_interval_months = 12, inspection_interval_km = 40000, inspection_interval_months = 24 WHERE name = 'Mazda';
UPDATE brands SET service_interval_km = 15000, service_interval_months = 12, inspection_interval_km = 30000, inspection_interval_months = 24 WHERE name = 'Mercedes-Benz';
UPDATE brands SET service_interval_km = 15000, service_interval_months = 12, inspection_interval_km = 30000, inspection_interval_months = 24 WHERE name = 'Nissan';
UPDATE brands SET service_interval_km = 15000, service_interval_months = 12, inspection_interval_km = 30000, inspection_interval_months = 24 WHERE name = 'Opel';
UPDATE brands SET service_interval_km = 20000, service_interval_months = 12, inspection_interval_km = 40000, inspection_interval_months = 24 WHERE name = 'Peugeot';
UPDATE brands SET service_interval_km = 20000, service_interval_months = 24, inspection_interval_km = 40000, inspection_interval_months = 24 WHERE name = 'Porsche';
UPDATE brands SET service_interval_km = 15000, service_interval_months = 12, inspection_interval_km = 30000, inspection_interval_months = 24 WHERE name = 'Renault';
UPDATE brands SET service_interval_km = 15000, service_interval_months = 12, inspection_interval_km = 30000, inspection_interval_months = 24 WHERE name = 'Seat';
UPDATE brands SET service_interval_km = 15000, service_interval_months = 12, inspection_interval_km = 30000, inspection_interval_months = 24 WHERE name = 'Škoda';
UPDATE brands SET service_interval_km = 15000, service_interval_months = 12, inspection_interval_km = 30000, inspection_interval_months = 24 WHERE name = 'Toyota';
UPDATE brands SET service_interval_km = 15000, service_interval_months = 12, inspection_interval_km = 30000, inspection_interval_months = 24 WHERE name = 'Volkswagen';
UPDATE brands SET service_interval_km = 20000, service_interval_months = 12, inspection_interval_km = 40000, inspection_interval_months = 24 WHERE name = 'Volvo';

-- 4. Tabela de documentos do veículo (fatura, contrato, etc.)
CREATE TABLE IF NOT EXISTS vehicle_documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    type ENUM('fatura', 'contrato', 'ficha_tecnica', 'seguro', 'iuc', 'inspecao', 'outro') NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_size INT UNSIGNED NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT NULL,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_vehicle_docs (vehicle_id),
    INDEX idx_user_docs (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabela de histórico de manutenções
CREATE TABLE IF NOT EXISTS vehicle_maintenance (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    type ENUM('revisao', 'inspecao', 'reparacao', 'pneus', 'travoes', 'outro') NOT NULL,
    description VARCHAR(255) NOT NULL,
    mileage INT UNSIGNED NULL,
    cost DECIMAL(10,2) NULL,
    service_date DATE NOT NULL,
    next_date DATE NULL,
    next_mileage INT UNSIGNED NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_vehicle_maint (vehicle_id),
    INDEX idx_next_date (next_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabela de lembretes de manutenção enviados
CREATE TABLE IF NOT EXISTS maintenance_reminders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    type ENUM('revisao', 'inspecao') NOT NULL,
    reminder_date DATE NOT NULL,
    sent_at TIMESTAMP NULL,
    opened_at TIMESTAMP NULL,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_pending_reminders (sent_at, reminder_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabela de pedidos de venda/troca
CREATE TABLE IF NOT EXISTS sell_trade_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    type ENUM('vender', 'trocar') NOT NULL,
    status ENUM('pendente', 'em_analise', 'aceite', 'recusado', 'concluido') DEFAULT 'pendente',
    message TEXT NULL,
    admin_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_user_requests (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
