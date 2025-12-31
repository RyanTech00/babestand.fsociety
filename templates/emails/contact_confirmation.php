<?php
$title = "Recebemos a sua Mensagem";
$preheader = "Obrigado pelo seu contacto";
ob_start();
?>
<h2 class="greeting">Olá, <?= htmlspecialchars($name) ?>!</h2>
<p class="text">Recebemos a sua mensagem e entraremos em contacto brevemente.</p>

<div class="info-box">
    <p class="box-title">Resumo da sua mensagem</p>
    <table class="data-table">
        <tr>
            <td class="label">Assunto</td>
            <td class="value"><?= htmlspecialchars($subject) ?></td>
        </tr>
        <tr>
            <td class="label">Data</td>
            <td class="value"><?= $date ?></td>
        </tr>
    </table>
</div>

<p class="text">Normalmente respondemos dentro de 24 a 48 horas úteis. Se o assunto for urgente, pode contactar-nos directamente pelo telefone ou email indicados no nosso site.</p>

<div class="button-container">
    <a href="<?= SITE_URL ?>/conta/mensagens.php" class="button">Ver Minhas Mensagens</a>
</div>

<p class="text">Obrigado por nos contactar!</p>
<?php
$content = ob_get_clean();
include __DIR__ . "/base.php";
