-- =====================================================
-- MIGRAÇÃO: Visibilidade de Veículos por Estado
-- BabeStand - 2025-12-29
-- =====================================================

-- 1. Atualizar vehicle_status para mostrar mais estados publicamente
-- Mostrar: Disponível, Brevemente, Reservado, Vendido, Em manutenção
-- Esconder: Indisponível
UPDATE vehicle_status SET show_public = 1 WHERE name IN ('Disponível', 'Brevemente', 'Reservado', 'Vendido');
UPDATE vehicle_status SET show_public = 0 WHERE name = 'Indisponível';

-- 2. Adicionar novo estado "Em manutenção" se não existir
INSERT IGNORE INTO vehicle_status (name, color, show_public) VALUES ('Em manutenção', '#fd7e14', 1);

-- 3. Tabela de notificações de favoritos vendidos
CREATE TABLE IF NOT EXISTS favorite_sold_notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    vehicle_id INT UNSIGNED NOT NULL,
    notified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_notification (user_id, vehicle_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Lista de espera para veículos "Brevemente" ou "Reservado"
CREATE TABLE IF NOT EXISTS vehicle_waiting_list (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NULL,
    email VARCHAR(255) NOT NULL,
    name VARCHAR(100) NULL,
    phone VARCHAR(20) NULL,
    notes TEXT NULL,
    notified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_waiting (vehicle_id, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
