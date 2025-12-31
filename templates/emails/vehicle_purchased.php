<?php
/**
 * Email: Notificação de Compra de Veículo
 * Enviado ao cliente quando uma venda é registada
 */

$title = "Parabéns pela sua compra!";
$preheader = "O seu novo veículo está disponível na sua conta";
$header_class = "header-success";

ob_start();
?>
<h2 class="greeting">Parabéns, <?= htmlspecialchars($name) ?>!</h2>

<p class="text">É com grande satisfação que confirmamos a sua compra. Bem-vindo à família BabeStand!</p>

<div class="success-box">
    <p class="box-title">🚗 O seu novo veículo</p>
    <table class="details-table">
        <tr><td class="label">Veículo</td><td class="value"><strong><?= htmlspecialchars($vehicle_name) ?></strong></td></tr>
        <?php if (!empty($vehicle_year)): ?>
        <tr><td class="label">Ano</td><td class="value"><?= htmlspecialchars($vehicle_year) ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($mileage)): ?>
        <tr><td class="label">Quilometragem</td><td class="value"><?= number_format($mileage, 0, ',', '.') ?> km</td></tr>
        <?php endif; ?>
        <?php if (!empty($sold_price)): ?>
        <tr><td class="label">Valor</td><td class="value"><strong><?= number_format($sold_price, 2, ',', '.') ?> €</strong></td></tr>
        <?php endif; ?>
        <tr><td class="label">Data</td><td class="value"><?= date('d/m/Y') ?></td></tr>
    </table>
</div>

<p class="text">O seu veículo está agora disponível na sua área de cliente, onde pode:</p>

<div class="info-box">
    <p class="box-title">📋 O que pode fazer</p>
    <p class="box-text">
        • Consultar todas as informações do veículo<br>
        • Guardar documentos importantes (fatura, contrato, seguro, etc.)<br>
        • Registar manutenções e receber lembretes automáticos<br>
        • Solicitar avaliação para venda ou troca futura
    </p>
</div>

<div class="button-container">
    <a href="<?= SITE_URL ?>/conta/meus-veiculos.php" class="button button-success">Ver Meus Veículos</a>
</div>

<p class="text">Se tiver alguma dúvida sobre o seu veículo ou precisar de assistência, não hesite em contactar-nos.</p>

<p class="text"><strong>Obrigado por confiar na BabeStand!</strong></p>

<?php
$content = ob_get_clean();
include __DIR__ . "/base.php";
