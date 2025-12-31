<?php
$title = "Como foi o seu Test Drive?";
$preheader = "A sua opinião é muito importante para nós";
$header_class = "header-success";
ob_start();
?>
<h2 class="greeting">Olá, <?= htmlspecialchars($name) ?>!</h2>
<p class="text">Esperamos que tenha gostado do test drive do <strong><?= htmlspecialchars($vehicle) ?></strong>!</p>

<div class="info-box">
    <p class="box-title">⭐ A sua opinião conta!</p>
    <p class="box-text">A sua avaliação ajuda-nos a melhorar o nosso serviço e ajuda outros clientes a tomarem decisões informadas.</p>
</div>

<p class="text">Gostaríamos muito de saber a sua experiência. Reserve apenas 1 minuto para deixar a sua avaliação:</p>

<div class="button-container">
    <a href="<?= SITE_URL ?>/veiculo.php?id=<?= $vehicle_id ?>#reviews" class="button button-success">⭐ Deixar Avaliação</a>
</div>

<p class="text" style="color: #666; font-size: 14px; margin-top: 30px;">
    Este link estará disponível durante 7 dias após o seu test drive.
</p>

<p class="text">Obrigado por escolher a BabeStand!</p>
<?php
$content = ob_get_clean();
include __DIR__ . "/base.php";
