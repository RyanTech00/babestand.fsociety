<?php
/**
 * BabeStand - Cron: Notificar Favoritos Vendidos
 *
 * Executar a cada hora ou após alterações de estado de veículos
 * 30 * * * * php /path/to/cron/notify-favorite-sold.php
 *
 * Este script verifica veículos marcados como "Vendido"
 * e notifica utilizadores que tinham esse veículo nos favoritos.
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Database;
use App\Services\Mailer;
use App\Services\Notification;

// Verificar se está a correr via CLI
if (php_sapi_name() !== 'cli') {
    die('Este script deve ser executado via linha de comandos.');
}

$db = Database::getInstance();
$mailer = new Mailer();

echo "[" . date('Y-m-d H:i:s') . "] Verificando favoritos de veículos vendidos...\n";

// Buscar favoritos de veículos que foram vendidos mas ainda não notificados
$favorites = $db->fetchAll(
    "SELECT f.id as favorite_id, f.user_id, f.vehicle_id,
     u.name as user_name, u.email as user_email,
     v.model, b.name as brand_name
     FROM favorites f
     JOIN users u ON f.user_id = u.id
     JOIN vehicles v ON f.vehicle_id = v.id
     JOIN brands b ON v.brand_id = b.id
     JOIN vehicle_status s ON v.status_id = s.id
     WHERE s.name = 'Vendido'
     AND u.is_active = 1
     AND NOT EXISTS (
         SELECT 1 FROM favorite_sold_notifications fsn
         WHERE fsn.user_id = f.user_id AND fsn.vehicle_id = f.vehicle_id
     )"
);

echo "Encontrados " . count($favorites) . " utilizadores para notificar.\n";

$notificationsSent = 0;

foreach ($favorites as $fav) {
    $vehicleName = $fav['brand_name'] . ' ' . $fav['model'];

    echo "  - Notificando {$fav['user_email']} sobre {$vehicleName}\n";

    try {
        // Enviar email
        $mailer->sendFavoriteSold($fav['user_email'], $fav['user_name'], [
            'vehicle' => $vehicleName,
            'vehicle_id' => $fav['vehicle_id']
        ]);

        // Criar notificação in-app
        try {
            Notification::create(
                $fav['user_id'],
                'favorite_sold',
                "O veículo {$vehicleName} dos seus favoritos foi vendido.",
                '/conta/favoritos.php'
            );
        } catch (Exception $e) {
            // Notification class might not exist, continue
        }

        // Registar que foi notificado
        $db->query(
            "INSERT INTO favorite_sold_notifications (user_id, vehicle_id) VALUES (?, ?)",
            [$fav['user_id'], $fav['vehicle_id']]
        );

        $notificationsSent++;
    } catch (Exception $e) {
        echo "    ERRO: " . $e->getMessage() . "\n";
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Concluído. {$notificationsSent} notificação(ões) enviada(s).\n";
