<?php
$title = "Resposta à sua Mensagem";
$preheader = "Respondemos à sua mensagem";
ob_start();
?>
<h2 class="greeting">Olá, <?= htmlspecialchars($name) ?>!</h2>
<p class="text">Recebemos a sua mensagem e aqui está a nossa resposta:</p>
<div style="background:#f0f7ff;border-left:4px solid #2980b9;padding:20px;margin:20px 0;border-radius:0 8px 8px 0;">
    <p class="box-title">📧 A sua mensagem</p>
    <p style="color:#666;font-style:italic;">"<?= htmlspecialchars($original_message) ?>"</p>
</div>
<div style="background:#e8f8f0;border-left:4px solid #27ae60;padding:20px;margin:20px 0;border-radius:0 8px 8px 0;">
    <p class="box-title">💬 A nossa resposta</p>
    <p style="color:#333;white-space:pre-wrap;"><?= nl2br(htmlspecialchars($reply)) ?></p>
</div>
<p class="text">Se precisar de mais esclarecimentos, pode responder directamente a esta mensagem ou aceder à sua área de cliente para ver o histórico completo.</p>
<div class="button-container">
    <a href="<?= SITE_URL ?>/conta/mensagens.php" class="button">Ver Minhas Mensagens</a>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . "/base.php";
