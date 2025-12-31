<?php
/**
 * BabeStand - Script de Seed
 * Popula a base de dados com dados de teste
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
    'Skoda', 'Toyota', 'Volkswagen', 'Volvo'
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
    'Segurança' => ['ABS', 'Airbags', 'Controlo de tração', 'ESP', 'Sensor de estacionamento', 'Câmara traseira'],
    'Conforto' => ['Ar condicionado', 'Bancos aquecidos', 'Cruise Control', 'Volante multifunções', 'Keyless Entry'],
    'Entretenimento' => ['Bluetooth', 'GPS', 'Apple CarPlay', 'Android Auto', 'Ecrã tátil'],
    'Exterior' => ['Jantes de liga leve', 'Faróis LED', 'Tejadilho panorâmico', 'Vidros escurecidos']
];

foreach ($features as $category => $items) {
    foreach ($items as $item) {
        $exists = $db->fetch("SELECT id FROM features WHERE name = ? AND category = ?", [$item, $category]);
        if (!$exists) {
            $db->query("INSERT INTO features (name, category) VALUES (?, ?)", [$item, $category]);
        }
    }
}
echo "  Equipamentos adicionados.\n";

// 5. Adicionar veículos de exemplo
echo "\nAdicionando veículos de exemplo...\n";

// Obter IDs
$brandIds = $db->fetchAll("SELECT id, name FROM brands");
$brandMap = [];
foreach ($brandIds as $b) {
    $brandMap[$b['name']] = $b['id'];
}

$fuelIds = $db->fetchAll("SELECT id, name FROM fuel_types");
$fuelMap = [];
foreach ($fuelIds as $f) {
    $fuelMap[$f['name']] = $f['id'];
}

$statusId = $db->fetch("SELECT id FROM vehicle_status WHERE name = 'Disponível'")['id'];

$vehicles = [
    [
        'brand' => 'Volkswagen',
        'model' => 'Golf',
        'version' => '1.6 TDI Confortline',
        'year' => 2021,
        'price' => 24990,
        'mileage' => 45000,
        'fuel' => 'Gasóleo',
        'transmission' => 'Manual',
        'engine' => '1.6 TDI',
        'power' => 115,
        'doors' => 5,
        'color' => 'Cinza',
        'description' => 'Volkswagen Golf em excelente estado. Revisões feitas na marca. Único dono.'
    ],
    [
        'brand' => 'BMW',
        'model' => 'Série 3',
        'version' => '320d M Sport',
        'year' => 2020,
        'price' => 35990,
        'previous_price' => 38500,
        'mileage' => 62000,
        'fuel' => 'Gasóleo',
        'transmission' => 'Automática',
        'engine' => '2.0 TDI',
        'power' => 190,
        'doors' => 4,
        'color' => 'Preto',
        'description' => 'BMW Série 3 com pack M Sport. Interior em pele. GPS profissional.'
    ],
    [
        'brand' => 'Mercedes-Benz',
        'model' => 'Classe A',
        'version' => 'A180d AMG Line',
        'year' => 2022,
        'price' => 32500,
        'mileage' => 28000,
        'fuel' => 'Gasóleo',
        'transmission' => 'Automática',
        'engine' => '1.5 dCi',
        'power' => 116,
        'doors' => 5,
        'color' => 'Branco',
        'description' => 'Mercedes Classe A com linha AMG. MBUX com ecrã panorâmico. Garantia de fábrica.'
    ],
    [
        'brand' => 'Audi',
        'model' => 'A3',
        'version' => 'Sportback 30 TDI S Line',
        'year' => 2021,
        'price' => 29900,
        'mileage' => 35000,
        'fuel' => 'Gasóleo',
        'transmission' => 'Automática',
        'engine' => '2.0 TDI',
        'power' => 116,
        'doors' => 5,
        'color' => 'Azul',
        'description' => 'Audi A3 Sportback com linha S Line. Virtual Cockpit. LED Matrix.'
    ],
    [
        'brand' => 'Toyota',
        'model' => 'Corolla',
        'version' => '1.8 Hybrid Comfort',
        'year' => 2023,
        'price' => 27990,
        'mileage' => 12000,
        'fuel' => 'Híbrido',
        'transmission' => 'Automática',
        'engine' => '1.8 Hybrid',
        'power' => 122,
        'doors' => 5,
        'color' => 'Vermelho',
        'description' => 'Toyota Corolla Híbrido com apenas 12.000 km. Economia e fiabilidade.'
    ],
    [
        'brand' => 'Renault',
        'model' => 'Clio',
        'version' => '1.0 TCe Intens',
        'year' => 2022,
        'price' => 17990,
        'mileage' => 22000,
        'fuel' => 'Gasolina',
        'transmission' => 'Manual',
        'engine' => '1.0 TCe',
        'power' => 100,
        'doors' => 5,
        'color' => 'Laranja',
        'description' => 'Renault Clio nova geração. Ecrã multimédia 9.3". Câmara 360°.'
    ],
    [
        'brand' => 'Peugeot',
        'model' => '208',
        'version' => '1.2 PureTech GT',
        'year' => 2021,
        'price' => 19500,
        'previous_price' => 21000,
        'mileage' => 38000,
        'fuel' => 'Gasolina',
        'transmission' => 'Automática',
        'engine' => '1.2 PureTech',
        'power' => 130,
        'doors' => 5,
        'color' => 'Verde',
        'description' => 'Peugeot 208 GT com i-Cockpit 3D. Design premiado. Full LED.'
    ],
    [
        'brand' => 'Tesla',
        'model' => 'Model 3',
        'version' => 'Long Range',
        'year' => 2023,
        'price' => 45990,
        'mileage' => 8000,
        'fuel' => 'Elétrico',
        'transmission' => 'Automática',
        'engine' => 'Elétrico',
        'power' => 366,
        'doors' => 4,
        'color' => 'Branco',
        'description' => 'Tesla Model 3 Long Range. Autonomia 602 km. Autopilot. Supercharger gratuito.'
    ]
];

// Verificar se já existem veículos
$existingCount = $db->fetch("SELECT COUNT(*) as c FROM vehicles")['c'];
if ($existingCount > 0) {
    echo "  Já existem $existingCount veículos na base de dados.\n";
    echo "  Para adicionar novos veículos, limpe a tabela primeiro.\n";
} else {
    foreach ($vehicles as $v) {
        // Verificar se a marca existe
        if (!isset($brandMap[$v['brand']])) {
            // Criar marca se não existir
            $db->query("INSERT INTO brands (name, is_active) VALUES (?, 1)", [$v['brand']]);
            $brandMap[$v['brand']] = $db->lastInsertId();
        }

        $db->query(
            "INSERT INTO vehicles (brand_id, model, version, year, price, previous_price, mileage,
             fuel_type_id, transmission, engine, power, doors, color, description, status_id, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [
                $brandMap[$v['brand']],
                $v['model'],
                $v['version'],
                $v['year'],
                $v['price'],
                $v['previous_price'] ?? null,
                $v['mileage'],
                $fuelMap[$v['fuel']],
                $v['transmission'],
                $v['engine'],
                $v['power'],
                $v['doors'],
                $v['color'],
                $v['description'],
                $statusId
            ]
        );

        $vehicleId = $db->lastInsertId();

        // Adicionar algumas features aleatórias
        $allFeatures = $db->fetchAll("SELECT id FROM features ORDER BY RAND() LIMIT 8");
        foreach ($allFeatures as $f) {
            $db->query("INSERT INTO vehicle_features (vehicle_id, feature_id) VALUES (?, ?)", [$vehicleId, $f['id']]);
        }

        echo "  + {$v['brand']} {$v['model']}\n";
    }
}

// 6. Criar utilizador admin se não existir
echo "\nVerificando utilizador admin...\n";
$adminEmail = 'admin@babestand.pt';
$existingAdmin = $db->fetch("SELECT id FROM users WHERE email = ?", [$adminEmail]);

if (!$existingAdmin) {
    $password = password_hash('Admin123!', PASSWORD_ARGON2ID);
    // Verificar se a coluna role existe
    $columns = $db->fetchAll("SHOW COLUMNS FROM users LIKE 'role'");
    if (!empty($columns)) {
        $db->query(
            "INSERT INTO users (name, email, password, role, is_active, created_at)
             VALUES (?, ?, ?, 'admin', 1, NOW())",
            ['Administrador', $adminEmail, $password]
        );
    } else {
        // Usar role_id se existir
        $adminRoleId = $db->fetch("SELECT id FROM roles WHERE name = 'admin'");
        if ($adminRoleId) {
            $db->query(
                "INSERT INTO users (name, email, password, role_id, is_active, created_at)
                 VALUES (?, ?, ?, ?, 1, NOW())",
                ['Administrador', $adminEmail, $password, $adminRoleId['id']]
            );
        } else {
            $db->query(
                "INSERT INTO users (name, email, password, is_active, created_at)
                 VALUES (?, ?, ?, 1, NOW())",
                ['Administrador', $adminEmail, $password]
            );
        }
    }
    echo "  + Admin criado: admin@babestand.pt / Admin123!\n";
} else {
    echo "  Admin já existe.\n";
}

echo "\n=== Seed concluído com sucesso! ===\n";
