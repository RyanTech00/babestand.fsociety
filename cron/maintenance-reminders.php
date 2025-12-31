<?php
/**
 * BabeStand - Cron: Lembretes de Manutenção
 *
 * Executar diariamente (ex: às 09:00)
 * 0 9 * * * php /path/to/cron/maintenance-reminders.php
 *
 * Este script verifica:
 * 1. Veículos com revisão próxima (30 dias ou 1000km)
 * 2. Veículos com inspeção próxima (60 dias)
 * E envia emails de lembrete aos proprietários.
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

echo "[" . date('Y-m-d H:i:s') . "] Iniciando verificação de lembretes de manutenção...\n";

// 1. Buscar veículos comprados com dados de manutenção
$vehicles = $db->fetchAll(
    "SELECT v.*, v.mileage as current_mileage, v.sold_mileage,
     b.name as brand_name, b.service_interval_km, b.service_interval_months,
     u.id as user_id, u.name as user_name, u.email as user_email,
     (SELECT MAX(service_date) FROM vehicle_maintenance WHERE vehicle_id = v.id AND type = 'revisao') as last_service_date,
     (SELECT MAX(mileage) FROM vehicle_maintenance WHERE vehicle_id = v.id AND type = 'revisao') as last_service_mileage,
     (SELECT MAX(service_date) FROM vehicle_maintenance WHERE vehicle_id = v.id AND type = 'inspecao') as last_inspection_date
     FROM vehicles v
     JOIN brands b ON v.brand_id = b.id
     JOIN users u ON v.buyer_id = u.id
     WHERE v.buyer_id IS NOT NULL
     AND u.is_active = 1"
);

echo "Encontrados " . count($vehicles) . " veículos com proprietário.\n";

$remindersSent = 0;

foreach ($vehicles as $vehicle) {
    $vehicleName = $vehicle['brand_name'] . ' ' . $vehicle['model'];

    // Intervalos da marca
    $serviceIntervalKm = $vehicle['service_interval_km'] ?? 15000;
    $serviceIntervalMonths = $vehicle['service_interval_months'] ?? 12;

    // Verificar revisão por DATA
    $nextServiceDate = null;
    $serviceStatus = 'ok';

    if ($vehicle['last_service_date']) {
        // Tem histórico de manutenção - calcular a partir da última
        $lastService = new DateTime($vehicle['last_service_date']);
        $nextServiceDate = clone $lastService;
        $nextServiceDate->modify('+' . $serviceIntervalMonths . ' months');
    } elseif ($vehicle['sold_date']) {
        // Sem histórico - calcular a partir da data de venda
        $soldDate = new DateTime($vehicle['sold_date']);
        $nextServiceDate = clone $soldDate;
        $nextServiceDate->modify('+' . $serviceIntervalMonths . ' months');
    }

    // Verificar revisão por QUILOMETRAGEM
    $nextServiceMileage = null;
    $kmRemaining = null;

    if ($vehicle['last_service_mileage']) {
        // Tem histórico - próxima revisão é última + intervalo da marca
        $nextServiceMileage = $vehicle['last_service_mileage'] + $serviceIntervalKm;
    } elseif ($vehicle['sold_mileage']) {
        // Sem histórico - calcular a partir da quilometragem de venda
        // Encontrar o próximo múltiplo do intervalo
        $soldMileage = (int) $vehicle['sold_mileage'];
        $nextServiceMileage = ceil($soldMileage / $serviceIntervalKm) * $serviceIntervalKm;
        // Se já passou, adicionar mais um intervalo
        if ($nextServiceMileage <= $soldMileage) {
            $nextServiceMileage += $serviceIntervalKm;
        }
    }

    // Calcular km restantes (se tivermos quilometragem atual do veículo)
    // Nota: O utilizador pode atualizar a quilometragem na área "Meus Veículos"
    $currentMileage = $vehicle['current_mileage'] ?? $vehicle['sold_mileage'];
    if ($nextServiceMileage && $currentMileage) {
        $kmRemaining = $nextServiceMileage - $currentMileage;
    }

    // Determinar se precisa de lembrete (por DATA ou QUILOMETRAGEM)
    $needsReminder = false;
    $isUrgent = false;
    $reminderReason = '';

    // Verificar por DATA (faltam 30 dias ou menos)
    $daysUntil = null;
    if ($nextServiceDate) {
        $now = new DateTime();
        $daysUntil = (int) $now->diff($nextServiceDate)->format('%R%a');

        if ($daysUntil <= 30 && $daysUntil >= 0) {
            $needsReminder = true;
            $reminderReason = "faltam {$daysUntil} dias";
        } elseif ($daysUntil < 0) {
            $needsReminder = true;
            $isUrgent = true;
            $reminderReason = "atrasado " . abs($daysUntil) . " dias";
        }
    }

    // Verificar por QUILOMETRAGEM (faltam 1000km ou menos)
    if ($kmRemaining !== null) {
        if ($kmRemaining <= 1000 && $kmRemaining >= 0) {
            $needsReminder = true;
            $reminderReason .= ($reminderReason ? ' e ' : '') . "faltam {$kmRemaining} km";
        } elseif ($kmRemaining < 0) {
            $needsReminder = true;
            $isUrgent = true;
            $reminderReason .= ($reminderReason ? ' e ' : '') . "excedido " . abs($kmRemaining) . " km";
        }
    }

    if ($needsReminder) {
        // Verificar se já foi enviado lembrete recentemente (últimos 7 dias)
        $recentReminder = $db->fetch(
            "SELECT id FROM maintenance_reminders
             WHERE vehicle_id = ? AND user_id = ? AND type = 'revisao'
             AND sent_at > DATE_SUB(NOW(), INTERVAL 7 DAY)",
            [$vehicle['id'], $vehicle['user_id']]
        );

        if (!$recentReminder) {
            $maintenanceType = $isUrgent ? 'Revisão (URGENTE)' : 'Revisão';
            echo "  - Enviando lembrete de {$maintenanceType} para {$vehicleName} ({$vehicle['user_email']}) - {$reminderReason}\n";

            try {
                $mailer->sendMaintenanceReminder($vehicle['user_email'], $vehicle['user_name'], [
                    'vehicle' => $vehicleName,
                    'maintenance_type' => $maintenanceType,
                    'due_date' => $nextServiceDate ? $nextServiceDate->format('d/m/Y') : null,
                    'due_mileage' => $nextServiceMileage,
                    'km_remaining' => $kmRemaining,
                    'days_remaining' => $daysUntil
                ]);

                // Registar lembrete enviado
                $db->query(
                    "INSERT INTO maintenance_reminders (vehicle_id, user_id, type, reminder_date, sent_at)
                     VALUES (?, ?, 'revisao', ?, NOW())",
                    [$vehicle['id'], $vehicle['user_id'], $nextServiceDate ? $nextServiceDate->format('Y-m-d') : date('Y-m-d')]
                );

                $remindersSent++;
            } catch (Exception $e) {
                echo "    ERRO: " . $e->getMessage() . "\n";
            }
        }
    }

    // Verificar inspeção (veículos com 4+ anos)
    $vehicleAge = date('Y') - $vehicle['year'];
    if ($vehicleAge >= 4) {
        $intervalYears = $vehicleAge >= 8 ? 1 : 2;

        if ($vehicle['last_inspection_date']) {
            $lastInspection = new DateTime($vehicle['last_inspection_date']);
            $nextInspection = clone $lastInspection;
            $nextInspection->modify('+' . $intervalYears . ' years');

            $now = new DateTime();
            $daysUntil = (int) $now->diff($nextInspection)->format('%R%a');

            // Enviar lembrete se faltam 60 dias ou menos
            if ($daysUntil <= 60 && $daysUntil >= 0) {
                $recentReminder = $db->fetch(
                    "SELECT id FROM maintenance_reminders
                     WHERE vehicle_id = ? AND user_id = ? AND type = 'inspecao'
                     AND sent_at > DATE_SUB(NOW(), INTERVAL 14 DAY)",
                    [$vehicle['id'], $vehicle['user_id']]
                );

                if (!$recentReminder) {
                    echo "  - Enviando lembrete de inspeção para {$vehicleName} ({$vehicle['user_email']})\n";

                    try {
                        $mailer->sendMaintenanceReminder($vehicle['user_email'], $vehicle['user_name'], [
                            'vehicle' => $vehicleName,
                            'maintenance_type' => 'Inspeção',
                            'due_date' => $nextInspection->format('d/m/Y'),
                            'due_mileage' => null
                        ]);

                        $db->query(
                            "INSERT INTO maintenance_reminders (vehicle_id, user_id, type, reminder_date, sent_at)
                             VALUES (?, ?, 'inspecao', ?, NOW())",
                            [$vehicle['id'], $vehicle['user_id'], $nextInspection->format('Y-m-d')]
                        );

                        $remindersSent++;
                    } catch (Exception $e) {
                        echo "    ERRO: " . $e->getMessage() . "\n";
                    }
                }
            }
        }
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Concluído. {$remindersSent} lembrete(s) enviado(s).\n";
