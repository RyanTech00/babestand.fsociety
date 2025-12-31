<?php
$title = "Atualização do Pedido";
$preheader = "O seu pedido de " . $request_type . " foi atualizado.";

// Definir cor do header baseado no status
$header_class = "";
if ($status === 'aceite' || $status === 'concluido') {
    $header_class = "header-success";
} elseif ($status === 'recusado') {
    $header_class = "header-danger";
}

// Traduzir status
$statusText = [
    'pendente' => 'Pendente',
    'em_analise' => 'Em Análise',
    'aceite' => 'Aceite',
    'recusado' => 'Recusado',
    'concluido' => 'Concluído'
][$status] ?? $status;

// Traduzir tipo
$typeText = $request_type === 'vender' ? 'Venda' : 'Troca';

ob_start();
?>

<p class="greeting">Olá <?= htmlspecialchars($name) ?>!</p>

<p class="text">
    O seu pedido de <strong><?= htmlspecialchars($typeText) ?></strong> foi atualizado.
</p>

<?php if ($status === 'aceite' || $status === 'concluido'): ?>
<div class="success-box">
    <p class="box-title">Pedido <?= htmlspecialchars($statusText) ?></p>
    <p class="box-text">O seu pedido para o veículo <strong><?= htmlspecialchars($vehicle) ?></strong> foi <?= strtolower($statusText) ?>!</p>
</div>
<?php elseif ($status === 'recusado'): ?>
<div class="danger-box">
    <p class="box-title">Pedido <?= htmlspecialchars($statusText) ?></p>
    <p class="box-text">Infelizmente, o seu pedido para o veículo <strong><?= htmlspecialchars($vehicle) ?></strong> foi recusado.</p>
</div>
<?php elseif ($status === 'em_analise'): ?>
<div class="info-box">
    <p class="box-title">Pedido <?= htmlspecialchars($statusText) ?></p>
    <p class="box-text">O seu pedido para o veículo <strong><?= htmlspecialchars($vehicle) ?></strong> está a ser analisado pela nossa equipa.</p>
</div>
<?php else: ?>
<div class="warning-box">
    <p class="box-title">Pedido <?= htmlspecialchars($statusText) ?></p>
    <p class="box-text">O estado do seu pedido para o veículo <strong><?= htmlspecialchars($vehicle) ?></strong> foi atualizado.</p>
</div>
<?php endif; ?>

<table class="details-table">
    <tr>
        <td class="label">Veículo</td>
        <td class="value"><?= htmlspecialchars($vehicle) ?></td>
    </tr>
    <tr>
        <td class="label">Tipo de Pedido</td>
        <td class="value"><?= htmlspecialchars($typeText) ?></td>
    </tr>
    <tr>
        <td class="label">Estado</td>
        <td class="value"><strong><?= htmlspecialchars($statusText) ?></strong></td>
    </tr>
</table>

<?php if (!empty($admin_notes)): ?>
<div class="info-box">
    <p class="box-title">Notas da equipa</p>
    <p class="box-text"><?= nl2br(htmlspecialchars($admin_notes)) ?></p>
</div>
<?php endif; ?>

<?php if ($status === 'aceite'): ?>
<p class="text">
    Entraremos em contacto consigo em breve para dar seguimento ao processo.
    Se preferir, pode também contactar-nos diretamente.
</p>
<?php elseif ($status === 'recusado'): ?>
<p class="text">
    Se tiver dúvidas sobre esta decisão ou quiser discutir outras opções,
    não hesite em contactar-nos.
</p>
<?php endif; ?>

<div class="button-container">
    <a href="<?= htmlspecialchars($my_vehicles_url) ?>" class="button">Ver Meus Veículos</a>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . "/base.php";
?>
