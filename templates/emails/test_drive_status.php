<?php
$title = "Atualização do Test Drive";
$preheader = "O estado do seu test drive foi atualizado";
$statusColors = [
    "aprovado" => "header-success",
    "confirmado" => "header-success",
    "concluido" => "header-success",
    "realizado" => "header-success",
    "cancelado" => "header-danger",
    "rejeitado" => "header-danger",
    "marcado como não compareceu" => ""
];
$header_class = $statusColors[strtolower($status ?? "")] ?? "";
$boxClass = strpos($header_class, "success") !== false ? "success-box" : (strpos($header_class, "danger") !== false ? "danger-box" : "info-box");
ob_start();
?>
<h2 class="greeting">Olá, <?= htmlspecialchars($name) ?>!</h2>
<p class="text">O estado do seu pedido de test drive foi atualizado.</p>
<div class="<?= $boxClass ?>">
    <p class="box-title">Estado: <?= htmlspecialchars(ucfirst($status)) ?></p>
    <table class="details-table">
        <tr><td class="label">Veículo</td><td class="value"><strong><?= htmlspecialchars($vehicle) ?></strong></td></tr>
        <tr><td class="label">Data</td><td class="value"><?= $date ?></td></tr>
        <tr><td class="label">Hora</td><td class="value"><?= $time ?></td></tr>
    </table>
</div>
<?php if (!empty($admin_notes)): ?>
<div class="info-box">
    <p class="box-title">📝 Notas</p>
    <p class="box-text"><?= htmlspecialchars($admin_notes) ?></p>
</div>
<?php endif; ?>
<p class="text">Se tiver alguma dúvida, não hesite em contactar-nos.</p>
<div class="button-container">
    <a href="<?= SITE_URL ?>/conta/test-drives.php" class="button">Ver Meus Test Drives</a>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . "/base.php";