<?php
$title = "Confirmar Alteração de Email";
$preheader = "Confirme o seu novo endereço de email";
ob_start();
?>
<h2 class="greeting">Olá, <?= htmlspecialchars($name) ?>!</h2>
<p class="text">Recebemos um pedido para alterar o endereço de email associado à sua conta no BabeStand.</p>
<p class="text">Para confirmar esta alteração, clique no botão abaixo:</p>
<div class="button-container">
    <a href="<?= $confirm_url ?>" class="button">Confirmar Novo Email</a>
</div>
<div class="warning-box">
    <p class="box-title">⚠️ Importante</p>
    <p class="box-text">Se não solicitou esta alteração, não clique no link e contacte-nos imediatamente.</p>
</div>
<div class="info-box">
    <p class="box-title">📍 Informações do pedido</p>
    <table class="details-table">
        <tr><td class="label">Data</td><td class="value"><?= $date ?></td></tr>
        <tr><td class="label">Endereço IP</td><td class="value"><?= $ip_address ?></td></tr>
        <tr><td class="label">Localização</td><td class="value"><?= \App\Services\GeoIP::getLocationString($ip_address) ?></td></tr>
    </table>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . "/base.php";