<?php
/**
 * BabeStand - Script de Seed
 * Configura apenas dados base (marcas, combustíveis, estados, equipamentos)
 * Veículos e utilizadores devem ser criados pelo admin através do dashboard
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Database;

$db = Database::getInstance();

echo "=== BabeStand Seed Script ===\n\n";

// 1. Adicionar marcas
echo "Adicionando marcas...\n";
$brands = [
    'Audi', 'BMW', 'Citroen', 'Fiat', 'Ford', 'Honda', 'Hyundai', 'Kia',
    'Mercedes-Benz', 'Nissan', 'Opel', 'Peugeot', 'Renault', 'Seat',
    'Skoda', 'Toyota', 'Volkswagen', 'Volvo', 'Tesla', 'Mazda', 'Mitsubishi',
    'Dacia', 'Jeep', 'Land Rover', 'Porsche', 'Mini', 'Alfa Romeo', 'Lexus'
];

foreach ($brands as $brand) {
    $exists = $db->fetch("SELECT id FROM brands WHERE name = ?", [$brand]);
    if (!$exists) {
        $db->query("INSERT INTO brands (name, is_active) VALUES (?, 1)", [$brand]);
        echo "  + $brand\n";
    }
}

// 2. Adicionar tipos de combustível
echo "\nVerificando tipos de combustível...\n";
$fuels = ['Gasolina', 'Gasóleo', 'Híbrido', 'Elétrico', 'GPL'];
foreach ($fuels as $fuel) {
    $exists = $db->fetch("SELECT id FROM fuel_types WHERE name = ?", [$fuel]);
    if (!$exists) {
        $db->query("INSERT INTO fuel_types (name) VALUES (?)", [$fuel]);
        echo "  + $fuel\n";
    }
}

// 3. Adicionar estados de veículo
echo "\nVerificando estados de veículo...\n";
$statuses = [
    ['name' => 'Disponível', 'color' => '#28a745', 'show_public' => 1],
    ['name' => 'Reservado', 'color' => '#ffc107', 'show_public' => 1],
    ['name' => 'Vendido', 'color' => '#dc3545', 'show_public' => 0],
    ['name' => 'Em manutenção', 'color' => '#6c757d', 'show_public' => 0]
];
foreach ($statuses as $status) {
    $exists = $db->fetch("SELECT id FROM vehicle_status WHERE name = ?", [$status['name']]);
    if (!$exists) {
        $db->query(
            "INSERT INTO vehicle_status (name, color, show_public) VALUES (?, ?, ?)",
            [$status['name'], $status['color'], $status['show_public']]
        );
        echo "  + {$status['name']}\n";
    }
}

// 4. Adicionar features/equipamentos
echo "\nAdicionando equipamentos...\n";
$features = [
    'Segurança' => ['ABS', 'Airbags frontais', 'Airbags laterais', 'Controlo de tração', 'ESP', 'Sensor de estacionamento', 'Câmara traseira', 'Alerta de colisão', 'Assistente de faixa'],
    'Conforto' => ['Ar condicionado', 'Ar condicionado automático', 'Bancos aquecidos', 'Bancos ventilados', 'Cruise Control', 'Cruise Control adaptativo', 'Volante multifunções', 'Keyless Entry', 'Arranque por botão', 'Espelhos elétricos'],
    'Entretenimento' => ['Bluetooth', 'GPS', 'Apple CarPlay', 'Android Auto', 'Ecrã tátil', 'Sistema de som premium', 'DAB Radio', 'USB/AUX'],
    'Exterior' => ['Jantes de liga leve', 'Faróis LED', 'Faróis Xenon', 'Tejadilho panorâmico', 'Vidros escurecidos', 'Barras de tejadilho', 'Sensores de chuva', 'Sensores de luz']
];

$addedFeatures = 0;
foreach ($features as $category => $items) {
    foreach ($items as $item) {
        $exists = $db->fetch("SELECT id FROM features WHERE name = ? AND category = ?", [$item, $category]);
        if (!$exists) {
            $db->query("INSERT INTO features (name, category) VALUES (?, ?)", [$item, $category]);
            $addedFeatures++;
        }
    }
}
echo "  $addedFeatures equipamentos adicionados.\n";

// 5. Verificar/Criar utilizador admin
echo "\nVerificando utilizador admin...\n";
$adminEmail = 'admin@babestand.fsociety.pt';
$existingAdmin = $db->fetch("SELECT id FROM users WHERE email = ?", [$adminEmail]);

if (!$existingAdmin) {
    $password = password_hash('Admin123!', PASSWORD_ARGON2ID);

    // Verificar estrutura da tabela users
    $columns = $db->fetchAll("SHOW COLUMNS FROM users LIKE 'role'");

    if (!empty($columns)) {
        $db->query(
            "INSERT INTO users (name, email, password, role, is_active, created_at)
             VALUES (?, ?, ?, 'admin', 1, NOW())",
            ['Administrador', $adminEmail, $password]
        );
    } else {
        // Usar role_id
        $adminRoleId = $db->fetch("SELECT id FROM roles WHERE name = 'admin'");
        if ($adminRoleId) {
            $db->query(
                "INSERT INTO users (name, email, password, role_id, is_active, created_at)
                 VALUES (?, ?, ?, ?, 1, NOW())",
                ['Administrador', $adminEmail, $password, $adminRoleId['id']]
            );
        }
    }
    echo "  + Admin criado: $adminEmail / Admin123!\n";
} else {
    echo "  Admin já existe.\n";
}

echo "\n=== Seed concluído com sucesso! ===\n";
echo "\nPróximos passos:\n";
echo "  1. Aceda ao dashboard admin: /admin/dashboard.php\n";
echo "  2. Adicione veículos através de: /admin/veiculos.php\n";
echo "  3. Os utilizadores registam-se através de: /registar.php\n";
