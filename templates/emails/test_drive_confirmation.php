<?php
$title = "Test Drive Confirmado";
$preheader = "O seu test drive foi agendado com sucesso";
$header_class = "header-success";
ob_start();
?>
<h2 class="greeting">Olá, <?= htmlspecialchars($name) ?>!</h2>
<p class="text">O seu test drive foi agendado com sucesso! Estamos ansiosos por recebê-lo.</p>
<div class="success-box">
    <p class="box-title">🚗 Detalhes do Test Drive</p>
    <table class="details-table">
        <tr><td class="label">Veículo</td><td class="value"><strong><?= htmlspecialchars($vehicle) ?></strong></td></tr>
        <tr><td class="label">Data</td><td class="value"><?= $date ?></td></tr>
        <tr><td class="label">Hora</td><td class="value"><?= $time ?></td></tr>
        <tr><td class="label">Local</td><td class="value"><?= htmlspecialchars($address) ?></td></tr>
    </table>
</div>
<div class="info-box">
    <p class="box-title">📋 O que trazer</p>
    <p class="box-text">• Carta de condução válida<br>• Documento de identificação<br>• Chegue 10 minutos antes</p>
</div>
<p class="text">Se precisar de remarcar ou cancelar, por favor contacte-nos com antecedência.</p>
<div class="button-container">
    <a href="<?= SITE_URL ?>/conta/test-drives.php" class="button button-success">Ver Meus Test Drives</a>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . "/base.php";