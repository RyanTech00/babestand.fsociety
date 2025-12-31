<?php
$title = "Novo Pedido de Test Drive";
$preheader = "Novo pedido de test drive recebido";
ob_start();
?>
<h2 class="greeting">Novo Pedido de Test Drive</h2>
<p class="text">Foi recebido um novo pedido de test drive que requer a sua atenção.</p>
<div class="info-box">
    <p class="box-title">🚗 Detalhes do Pedido</p>
    <table class="details-table">
        <tr><td class="label">Cliente</td><td class="value"><strong><?= htmlspecialchars($client_name) ?></strong></td></tr>
        <tr><td class="label">Email</td><td class="value"><a href="mailto:<?= htmlspecialchars($client_email) ?>"><?= htmlspecialchars($client_email) ?></a></td></tr>
        <tr><td class="label">Telefone</td><td class="value"><?= htmlspecialchars($client_phone ?? "Não indicado") ?></td></tr>
        <tr><td class="label">Veículo</td><td class="value"><strong><?= htmlspecialchars($vehicle) ?></strong></td></tr>
        <tr><td class="label">Data Pretendida</td><td class="value"><?= $date ?></td></tr>
        <tr><td class="label">Hora Pretendida</td><td class="value"><?= $time ?></td></tr>
    </table>
</div>
<?php if (!empty($notes)): ?>
<div style="background:#f8f9fa;padding:20px;border-radius:8px;margin:20px 0;">
    <p class="box-title">📝 Notas do Cliente</p>
    <p style="color:#333;"><?= htmlspecialchars($notes) ?></p>
</div>
<?php endif; ?>
<div class="button-container">
    <a href="<?= SITE_URL ?>/admin/test-drives.php" class="button">Gerir Test Drives</a>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . "/base.php";