# 🔧 Área Pós-Venda

A área pós-venda permite aos proprietários de veículos comprados no stand gerir documentos, registar manutenções e receber lembretes automáticos.

## Funcionalidades

### Gestão de Documentos
- **Upload seguro**: PDF, JPG, PNG, DOC
- **Tipos**: Seguro, Inspeção, Contrato, Fatura, Outro
- **Acesso controlado**: Apenas o proprietário pode ver
- **Download/Visualização**: Via API com validação

### Histórico de Manutenções
- **Registo**: Revisões, reparações, mudanças de óleo
- **Quilometragem**: Atualização automática do veículo
- **Custos**: Tracking de despesas
- **Notas**: Observações do mecânico

### Lembretes Automáticos
- **Revisão**: 30 dias antes ou 1000 km antes
- **Inspeção**: 60 dias antes da expiração
- **Email**: Notificação automática ao proprietário

## Upload Seguro de Documentos

O sistema valida ficheiros contra código malicioso:

```php
// api/vehicle-document.php
$fileContent = file_get_contents($file['tmp_name'], false, null, 0, 8192);
$dangerousPatterns = [
    '<?php', '<?=', '<script', 'javascript:', 
    'onload=', 'onerror=', 'eval(', 'base64_decode('
];

foreach ($dangerousPatterns as $pattern) {
    if (stripos($fileContent, $pattern) !== false) {
        Session::setFlash('error', 'Ficheiro com conteúdo malicioso.');
        redirect('conta/meu-veiculo.php?id=' . $vehicleId);
    }
}
```

## Acesso Controlado

Os documentos estão protegidos por:

1. **Pasta fora do webroot**: Ficheiros não acessíveis diretamente
2. **`.htaccess` restritivo**: Bloqueia acesso direto
3. **API com validação**: Verifica propriedade antes de servir

```php
// Validação de propriedade
$document = $db->fetch(
    "SELECT d.*, v.buyer_id FROM vehicle_documents d
     JOIN vehicles v ON d.vehicle_id = v.id
     WHERE d.id = ? AND d.user_id = ?",
    [$documentId, $userId]
);

if (!$document) {
    Session::setFlash('error', 'Documento não encontrado.');
    redirect('conta/meus-veiculos.php');
}
```

## Limpeza Automática na Venda

Quando um veículo é vendido/trocado, os documentos do anterior proprietário são **automaticamente removidos**:

```php
// Remover documentos do anterior proprietário
$documents = $db->fetchAll(
    "SELECT * FROM vehicle_documents WHERE vehicle_id = ?",
    [$vehicleId]
);

foreach ($documents as $doc) {
    unlink($uploadPath . $doc['filename']); // Remove ficheiro
}

$db->query("DELETE FROM vehicle_documents WHERE vehicle_id = ?", [$vehicleId]);
```

Isto garante que:
- ✅ Dados pessoais não são expostos ao novo dono
- ✅ Conformidade com RGPD
- ✅ Privacidade do anterior proprietário

## Registo de Manutenções

```sql
CREATE TABLE vehicle_maintenance (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    type ENUM('revisao', 'reparacao', 'oleo', 'pneus', 'travoes', 'outro'),
    description VARCHAR(255) NOT NULL,
    mileage INT UNSIGNED NULL,
    cost DECIMAL(10,2) NULL,
    service_date DATE NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Atualização de Quilometragem

Ao registar manutenção, a quilometragem do veículo é atualizada automaticamente:

```php
if ($mileage > $vehicleCurrentMileage) {
    $db->query(
        "UPDATE vehicles SET current_mileage = ?, current_mileage_updated_at = NOW() WHERE id = ?",
        [$mileage, $vehicleId]
    );
}
```

## Lembretes Automáticos (Cron)

O script `cron/maintenance-reminders.php` executa diariamente:

```php
// Verificar veículos com revisão próxima
$vehicles = $db->fetchAll("
    SELECT v.*, u.email, u.name, b.service_interval_km, b.service_interval_months
    FROM vehicles v
    JOIN users u ON v.buyer_id = u.id
    JOIN brands b ON v.brand_id = b.id
    WHERE v.buyer_id IS NOT NULL
");

foreach ($vehicles as $vehicle) {
    $kmRemaining = $nextServiceKm - $vehicle['current_mileage'];
    $daysRemaining = $nextServiceDate->diff(new DateTime())->days;
    
    // Enviar lembrete se < 30 dias OU < 1000 km
    if ($daysRemaining <= 30 || $kmRemaining <= 1000) {
        // Verificar se já foi enviado nos últimos 7 dias
        $recentReminder = $db->fetch(
            "SELECT id FROM maintenance_reminders 
             WHERE vehicle_id = ? AND sent_at > DATE_SUB(NOW(), INTERVAL 7 DAY)",
            [$vehicle['id']]
        );
        
        if (!$recentReminder) {
            $mailer->sendMaintenanceReminder($vehicle['email'], $vehicle['name'], [
                'vehicle' => $vehicleName,
                'due_date' => $nextServiceDate->format('d/m/Y'),
                'km_remaining' => $kmRemaining
            ]);
        }
    }
}
```

## Tipos de Documentos

| Tipo | Ícone | Descrição |
|------|-------|-----------|
| Seguro | 🛡️ | Apólice de seguro |
| Inspeção | 📋 | Certificado de inspeção |
| Contrato | 📄 | Contrato de compra |
| Fatura | 🧾 | Faturas de serviços |
| Outro | 📎 | Outros documentos |

## Interface do Utilizador

A área "Meus Veículos" apresenta:
- 📊 **Informações**: Detalhes do veículo
- 📄 **Documentos**: Upload e gestão
- 🔧 **Manutenções**: Histórico e registo
- 💰 **Vender/Trocar**: Iniciar negociação
