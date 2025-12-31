<?php
/**
 * BabeStand - Cron: Notificar Lista de Espera
 *
 * Executar a cada hora ou após alterações de estado de veículos
 * 0 * * * * php /path/to/cron/notify-waiting-list.php
 *
 * Este script verifica veículos que mudaram para "Disponível"
 * e notifica todos na lista de espera.
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Database;
use App\Services\Mailer;

// Verificar se está a correr via CLI
if (php_sapi_name() !== 'cli') {
    die('Este script deve ser executado via linha de comandos.');
}

$db = Database::getInstance();
$mailer = new Mailer();

echo "[" . date('Y-m-d H:i:s') . "] Verificando lista de espera...\n";

// Buscar pessoas na lista de espera de veículos agora disponíveis
// que ainda não foram notificadas
$waitingList = $db->fetchAll(
    "SELECT wl.*, v.model, b.name as brand_name, s.name as status_name
     FROM vehicle_waiting_list wl
     JOIN vehicles v ON wl.vehicle_id = v.id
     JOIN brands b ON v.brand_id = b.id
     JOIN vehicle_status s ON v.status_id = s.id
     WHERE s.name = 'Disponível'
     AND wl.notified_at IS NULL
     ORDER BY wl.created_at ASC"
);

echo "Encontradas " . count($waitingList) . " pessoas para notificar.\n";

$notificationsSent = 0;

foreach ($waitingList as $entry) {
    $vehicleName = $entry['brand_name'] . ' ' . $entry['model'];
    $name = $entry['name'] ?: 'Cliente';

    echo "  - Notificando {$entry['email']} sobre {$vehicleName}\n";

    try {
        $mailer->sendWaitingListAvailable($entry['email'], $name, [
            'vehicle' => $vehicleName,
            'vehicle_id' => $entry['vehicle_id']
        ]);

        // Marcar como notificado
        $db->query(
            "UPDATE vehicle_waiting_list SET notified_at = NOW() WHERE id = ?",
            [$entry['id']]
        );

        $notificationsSent++;
    } catch (Exception $e) {
        echo "    ERRO: " . $e->getMessage() . "\n";
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Concluído. {$notificationsSent} notificação(ões) enviada(s).\n";
