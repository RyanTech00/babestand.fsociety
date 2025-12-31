<?php
$title = "Lembrete de Manutenção";
$preheader = "O seu veículo " . $vehicle . " precisa de manutenção em breve.";
$header_class = "";

ob_start();
?>

<p class="greeting">Olá <?= htmlspecialchars($name) ?>!</p>

<p class="text">
    Este é um lembrete amigável sobre a manutenção do seu veículo.
</p>

<div class="warning-box">
    <p class="box-title">Manutenção Próxima</p>
    <p class="box-text">
        O seu <strong><?= htmlspecialchars($vehicle) ?></strong> tem uma
        <strong><?= htmlspecialchars($maintenance_type) ?></strong> agendada para breve.
    </p>
</div>

<table class="details-table">
    <tr>
        <td class="label">Veículo</td>
        <td class="value"><?= htmlspecialchars($vehicle) ?></td>
    </tr>
    <tr>
        <td class="label">Tipo de Manutenção</td>
        <td class="value"><?= htmlspecialchars($maintenance_type) ?></td>
    </tr>
    <?php if (!empty($due_date)): ?>
    <tr>
        <td class="label">Data Prevista</td>
        <td class="value"><?= htmlspecialchars($due_date) ?></td>
    </tr>
    <?php endif; ?>
    <?php if (!empty($due_mileage)): ?>
    <tr>
        <td class="label">Quilometragem Prevista</td>
        <td class="value"><?= number_format($due_mileage, 0, ',', '.') ?> km</td>
    </tr>
    <?php endif; ?>
</table>

<div class="info-box">
    <p class="box-title">Por que é importante?</p>
    <p class="box-text">
        A manutenção regular do seu veículo garante segurança, eficiência e longevidade.
        Não deixe passar a data recomendada pelo fabricante.
    </p>
</div>

<div class="button-container">
    <a href="<?= htmlspecialchars($my_vehicles_url) ?>" class="button">Ver Meus Veículos</a>
</div>

<p class="text text-small text-muted" style="margin-top: 30px;">
    Pode registar as suas manutenções na sua área pessoal para manter um histórico completo.
</p>

<?php
$content = ob_get_clean();
include __DIR__ . "/base.php";
?>
