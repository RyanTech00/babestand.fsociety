<?php
$title = "Veículo Favorito Vendido";
$preheader = "O veículo " . $vehicle . " dos seus favoritos foi vendido.";
$header_class = "header-danger";

ob_start();
?>

<p class="greeting">Olá <?= htmlspecialchars($name) ?>!</p>

<div class="danger-box">
    <p class="box-title">Veículo Vendido</p>
    <p class="box-text">O veículo <strong><?= htmlspecialchars($vehicle) ?></strong> que tinha nos seus favoritos foi vendido.</p>
</div>

<p class="text">
    Lamentamos que não tenha conseguido adquirir este veículo a tempo.
    No entanto, temos outros veículos semelhantes que podem ser do seu interesse!
</p>

<div class="button-container">
    <a href="<?= htmlspecialchars($similar_url) ?>" class="button">Ver Veículos Disponíveis</a>
</div>

<div class="info-box">
    <p class="box-title">Dica</p>
    <p class="box-text">
        Pode gerir os seus favoritos na sua conta para remover veículos que já não estão disponíveis.
    </p>
</div>

<p class="text text-small text-muted" style="margin-top: 30px;">
    <a href="<?= htmlspecialchars($favorites_url) ?>" style="color: #1a5276;">Gerir Favoritos</a>
</p>

<?php
$content = ob_get_clean();
include __DIR__ . "/base.php";
?>
