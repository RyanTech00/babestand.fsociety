<?php
$title = "Recuperação de Password";
$preheader = "Redefina a sua password no BabeStand";
ob_start();
?>
<h2 class="greeting">Olá, <?= htmlspecialchars($name) ?>!</h2>
<p class="text">Recebemos um pedido para redefinir a password da sua conta.</p>
<p class="text">Se foi você que fez este pedido, clique no botão abaixo para criar uma nova password:</p>
<div class="button-container">
    <a href="<?= $reset_url ?>" class="button">Redefinir Password</a>
</div>
<div class="warning-box">
    <p class="box-title">⏰ Link válido por <?= $expiry_hours ?> hora(s)</p>
    <p class="box-text">Por razões de segurança, este link expira em breve.</p>
</div>
<div class="info-box">
    <p class="box-title">📍 Informações do pedido</p>
    <table class="details-table">
        <tr><td class="label">Data</td><td class="value"><?= $date ?></td></tr>
        <tr><td class="label">Endereço IP</td><td class="value"><?= $ip_address ?></td></tr>
        <tr><td class="label">Localização</td><td class="value"><?= \App\Services\GeoIP::getLocationString($ip_address) ?></td></tr>
    </table>
</div>
<p class="text text-muted text-small">Se não solicitou esta alteração, ignore este email.</p>
<?php
$content = ob_get_clean();
include __DIR__ . "/base.php";