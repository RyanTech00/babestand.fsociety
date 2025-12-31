<?php
$title = "Veículo Disponível";
$preheader = "O veículo " . $vehicle . " que estava à espera já está disponível!";
$header_class = "header-success";

ob_start();
?>

<p class="greeting">Olá <?= htmlspecialchars($name) ?>!</p>

<div class="success-box">
    <p class="box-title">Boas notícias!</p>
    <p class="box-text">
        O veículo <strong><?= htmlspecialchars($vehicle) ?></strong> que estava na sua lista de espera
        já está <strong>disponível</strong>!
    </p>
</div>

<p class="text">
    Estava à espera deste veículo e temos o prazer de informar que agora está disponível
    para visualização e test drive. Não perca esta oportunidade!
</p>

<div class="button-container">
    <a href="<?= htmlspecialchars($vehicle_url) ?>" class="button button-success">Ver Veículo</a>
</div>

<div class="info-box">
    <p class="box-title">Agende um Test Drive</p>
    <p class="box-text">
        Não perca tempo! Agende já o seu test drive para conhecer o veículo pessoalmente.
    </p>
</div>

<div class="button-container">
    <a href="<?= htmlspecialchars($test_drive_url) ?>" class="button" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">Agendar Test Drive</a>
</div>

<p class="text text-small text-muted" style="margin-top: 30px;">
    Este veículo pode ser vendido a qualquer momento. Recomendamos que aja rapidamente se estiver interessado.
</p>

<?php
$content = ob_get_clean();
include __DIR__ . "/base.php";
?>
