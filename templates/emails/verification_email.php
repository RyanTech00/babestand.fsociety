<?php
$title = "Ativação de Conta";
$preheader = "Ative a sua conta no BabeStand";
$header_class = "header-success";
ob_start();
?>
<h2 class="greeting">Bem-vindo, <?= htmlspecialchars($name) ?>!</h2>
<p class="text">Obrigado por se registar no BabeStand! Estamos muito felizes por tê-lo connosco.</p>
<p class="text">Para ativar a sua conta e começar a explorar os nossos veículos, clique no botão abaixo:</p>
<div class="button-container">
    <a href="<?= $verify_url ?>" class="button button-success">Ativar Minha Conta</a>
</div>
<div class="success-box">
    <p class="box-title">✨ O que pode fazer após a ativação</p>
    <p class="box-text">• Guardar veículos favoritos<br>• Agendar test drives<br>• Receber alertas de novos veículos<br>• Gerir o seu perfil</p>
</div>
<p class="text text-muted text-small">Se não criou esta conta, pode ignorar este email.</p>
<?php
$content = ob_get_clean();
include __DIR__ . "/base.php";