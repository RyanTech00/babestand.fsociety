<?php
$title = "Acesso à Conta";
$preheader = "Complete o seu login no BabeStand";
ob_start();
?>
<h2 class="greeting">Olá, <?= htmlspecialchars($name) ?>!</h2>
<p class="text">Recebemos um pedido de login na sua conta. Para completar o acesso, clique no botão abaixo:</p>
<div class="button-container">
    <a href="<?= $verify_url ?>" class="button">Aceder à Minha Conta</a>
</div>

<?php if (!empty($verification_code)): ?>
<div class="info-box" style="background: #f0f9ff; border: 2px solid #0ea5e9; text-align: center; padding: 20px;">
    <p class="box-title" style="color: #0369a1; font-size: 14px; margin-bottom: 10px;">🔐 Código de Verificação Alternativo</p>
    <p style="font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #0369a1; margin: 15px 0; font-family: monospace;"><?= $verification_code ?></p>
    <p class="box-text" style="color: #64748b; font-size: 13px;">Se o link não funcionar ou se está num dispositivo diferente, pode usar este código na página de login.</p>
</div>
<?php endif; ?>

<div class="warning-box">
    <p class="box-title">⏰ Link e código válidos por <?= $expiry_minutes ?> minutos</p>
    <p class="box-text">Por razões de segurança, este acesso expira em breve.</p>
</div>
<div class="info-box">
    <p class="box-title">📍 Informações do pedido</p>
    <table class="details-table">
        <tr><td class="label">Data</td><td class="value"><?= $date ?></td></tr>
        <tr><td class="label">Endereço IP</td><td class="value"><?= $ip_address ?></td></tr>
        <tr><td class="label">Localização</td><td class="value"><?= \App\Services\GeoIP::getLocationString($ip_address) ?></td></tr>
    </table>
</div>
<p class="text text-muted text-small">Se não foi você que solicitou este acesso, pode ignorar este email com segurança.</p>
<?php
$content = ob_get_clean();
include __DIR__ . "/base.php";