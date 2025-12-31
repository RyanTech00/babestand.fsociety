<?php
/**
 * Verifica e cria tabelas em falta
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Database;

$db = Database::getInstance();

echo "Verificando tabelas...\n\n";

// Verificar tabelas existentes
$tables = [];
$result = $db->getConnection()->query("SHOW TABLES");
while ($row = $result->fetch(\PDO::FETCH_NUM)) {
    $tables[] = $row[0];
}

echo "Tabelas existentes:\n";
foreach ($tables as $t) {
    echo "  - $t\n";
}

// Criar tabela features se não existir
if (!in_array('features', $tables)) {
    echo "\nCriando tabela 'features'...\n";
    $db->getConnection()->exec("
        CREATE TABLE features (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            category VARCHAR(50) NOT NULL,
            UNIQUE KEY unique_feature (name, category)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  Tabela 'features' criada.\n";
}

// Criar tabela vehicle_features se não existir
if (!in_array('vehicle_features', $tables)) {
    echo "\nCriando tabela 'vehicle_features'...\n";
    $db->getConnection()->exec("
        CREATE TABLE vehicle_features (
            id INT AUTO_INCREMENT PRIMARY KEY,
            vehicle_id INT UNSIGNED NOT NULL,
            feature_id INT NOT NULL,
            UNIQUE KEY unique_vehicle_feature (vehicle_id, feature_id),
            FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  Tabela 'vehicle_features' criada.\n";
}

// Criar tabela favorites se não existir
if (!in_array('favorites', $tables)) {
    echo "\nCriando tabela 'favorites'...\n";
    $db->getConnection()->exec("
        CREATE TABLE favorites (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            vehicle_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_favorite (user_id, vehicle_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  Tabela 'favorites' criada.\n";
}

// Criar tabela test_drives se não existir
if (!in_array('test_drives', $tables)) {
    echo "\nCriando tabela 'test_drives'...\n";
    $db->getConnection()->exec("
        CREATE TABLE test_drives (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            vehicle_id INT NOT NULL,
            scheduled_date DATE NOT NULL,
            scheduled_time TIME NOT NULL,
            status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  Tabela 'test_drives' criada.\n";
}

// Criar tabela login_logs se não existir
if (!in_array('login_logs', $tables)) {
    echo "\nCriando tabela 'login_logs'...\n";
    $db->getConnection()->exec("
        CREATE TABLE login_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED,
            email VARCHAR(255),
            ip_address VARCHAR(45),
            user_agent TEXT,
            success TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  Tabela 'login_logs' criada.\n";
}

echo "\nVerificação concluída!\n";
