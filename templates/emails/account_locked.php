<?php
$title = "Alerta de Segurança";
$preheader = "A sua conta foi temporariamente suspensa";
$header_class = "header-danger";
ob_start();
?>
<h2 class="greeting">Olá, <?= htmlspecialchars($name) ?>!</h2>
<div class="danger-box">
    <p class="box-title">⚠️ Conta Temporariamente Suspensa</p>
    <p class="box-text">A sua conta foi bloqueada devido a múltiplas tentativas de login falhadas. Isto pode indicar uma tentativa de acesso não autorizado.</p>
</div>
<div class="info-box">
    <p class="box-title">📍 Detalhes da atividade suspeita</p>
    <table class="details-table">
        <tr><td class="label">Data</td><td class="value"><?= $date ?></td></tr>
        <tr><td class="label">Endereço IP</td><td class="value"><?= $ip_address ?></td></tr>
        <tr><td class="label">Localização</td><td class="value"><?= \App\Services\GeoIP::getLocationString($ip_address) ?></td></tr>
    </table>
</div>
<p class="text"><strong>Se foi você que tentou fazer login</strong>, pode desbloquear a sua conta e definir uma nova password:</p>
<div class="button-container">
    <a href="<?= $unlock_url ?>" class="button button-danger">Desbloquear Conta</a>
</div>
<div class="warning-box">
    <p class="box-title">⏰ Link válido por 24 horas</p>
    <p class="box-text">Após este período, deverá contactar o suporte.</p>
</div>
<p class="text"><strong>Se não foi você:</strong></p>
<ul style="color:#555;"><li>Não clique no link acima</li><li>A sua conta permanece segura e bloqueada</li><li>Contacte-nos: <a href="mailto:<?= $support_email ?>"><?= $support_email ?></a></li></ul>
<?php
$content = ob_get_clean();
$footer_extra = "Esta é uma mensagem automática de segurança.";
include __DIR__ . "/base.php";