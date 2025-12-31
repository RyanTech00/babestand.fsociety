<?php
// Cron Job: Enviar pedidos de review apos test-drives concluidos
// Este script envia emails automaticos pedindo reviews aos clientes
// que acabaram de realizar um test-drive.
// Executar: php /var/www/babestand/cron/send-review-requests.php

// Nao executar via web
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Este script apenas pode ser executado via CLI.');
}

require_once __DIR__ . '/../bootstrap.php';

use App\Database;
use App\Services\Mailer;

$db = Database::getInstance();
$mailer = new Mailer();

echo "[" . date('Y-m-d H:i:s') . "] Iniciando envio de pedidos de review...\n";

// Buscar test-drives CONCLUIDOS que:
// 1. Ja terminaram (data+hora+1h < agora)
// 2. Ainda nao receberam email de review
// 3. Foram concluidos nos ultimos 7 dias (para nao enviar emails muito antigos)
$testDrives = $db->fetchAll("
    SELECT
        td.id,
        td.user_id,
        td.vehicle_id,
        td.scheduled_date,
        td.scheduled_time,
        u.name,
        u.email,
        v.model,
        b.name as brand_name
    FROM test_drives td
    JOIN users u ON td.user_id = u.id
    JOIN vehicles v ON td.vehicle_id = v.id
    JOIN brands b ON v.brand_id = b.id
    WHERE td.status_id = 3
    AND td.review_email_sent = 0
    AND td.scheduled_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    AND CONCAT(td.scheduled_date, ' ', ADDTIME(td.scheduled_time, '01:00:00')) <= NOW()
    ORDER BY td.scheduled_date ASC, td.scheduled_time ASC
");

$sent = 0;
$errors = 0;

if (empty($testDrives)) {
    echo "[" . date('Y-m-d H:i:s') . "] Nenhum test-drive pendente de envio de review.\n";
} else {
    echo "[" . date('Y-m-d H:i:s') . "] Encontrados " . count($testDrives) . " test-drives para enviar email.\n";

    foreach ($testDrives as $td) {
        try {
            // Verificar se utilizador ja deixou review para este veiculo
            $existingReview = $db->fetch(
                "SELECT id FROM reviews WHERE user_id = ? AND vehicle_id = ?",
                [$td['user_id'], $td['vehicle_id']]
            );

            if ($existingReview) {
                // Ja tem review, marcar como enviado e pular
                $db->query("UPDATE test_drives SET review_email_sent = 1 WHERE id = ?", [$td['id']]);
                echo "[" . date('Y-m-d H:i:s') . "] Test Drive #{$td['id']}: Utilizador ja deixou review, ignorado.\n";
                continue;
            }

            // Enviar email
            $result = $mailer->sendReviewRequest($td['email'], $td['name'], $td);

            if ($result) {
                // Marcar como enviado
                $db->query("UPDATE test_drives SET review_email_sent = 1 WHERE id = ?", [$td['id']]);
                $sent++;
                echo "[" . date('Y-m-d H:i:s') . "] OK: Email enviado para {$td['email']} (Test Drive #{$td['id']} - {$td['brand_name']} {$td['model']})\n";
            } else {
                $errors++;
                echo "[" . date('Y-m-d H:i:s') . "] ERRO: Falha ao enviar para {$td['email']} (Test Drive #{$td['id']})\n";
            }

        } catch (Exception $e) {
            $errors++;
            echo "[" . date('Y-m-d H:i:s') . "] ERRO: {$td['email']} (Test Drive #{$td['id']}): " . $e->getMessage() . "\n";
        }

        // Pequeno delay entre envios para nao sobrecarregar o servidor SMTP
        usleep(500000); // 0.5 segundos
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Concluido. Enviados: {$sent}, Erros: {$errors}\n";
echo "---\n";
