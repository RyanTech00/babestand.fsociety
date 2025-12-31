<?php
$title = "Nova Mensagem de Contacto";
$preheader = "Mensagem #" . $messageId . " de " . $name;
ob_start();
?>
<h2 class="greeting">Nova Mensagem de Contacto #<?= $messageId ?></h2>

<div class="info-box">
    <p class="box-title">👤 Dados do Remetente</p>
    <table class="data-table">
        <tr>
            <td class="label">Nome</td>
            <td class="value"><?= htmlspecialchars($name) ?></td>
        </tr>
        <tr>
            <td class="label">Email</td>
            <td class="value"><a href="mailto:<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></a></td>
        </tr>
        <tr>
            <td class="label">Telefone</td>
            <td class="value"><?= $phone ? htmlspecialchars($phone) : '<span style="color:#999;">Não indicado</span>' ?></td>
        </tr>
        <tr>
            <td class="label">Assunto</td>
            <td class="value"><?= htmlspecialchars($subject) ?></td>
        </tr>
        <?php if (!empty($vehicle_info)): ?>
        <tr>
            <td class="label">Veículo</td>
            <td class="value"><?= htmlspecialchars($vehicle_info) ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td class="label">Data</td>
            <td class="value"><?= date('d/m/Y H:i') ?></td>
        </tr>
        <tr>
            <td class="label">IP</td>
            <td class="value"><code><?= htmlspecialchars($ip_address) ?></code></td>
        </tr>
    </table>
</div>

<div style="background:#f8f9fa;border-radius:8px;padding:20px;margin:20px 0;">
    <p style="margin:0 0 10px 0;font-weight:600;color:#495057;">📝 Mensagem:</p>
    <p style="margin:0;color:#333;white-space:pre-wrap;"><?= nl2br(htmlspecialchars($message)) ?></p>
</div>

<div class="button-container">
    <a href="<?= SITE_URL ?>/admin/mensagem.php?id=<?= $messageId ?>" class="button">Ver no Painel Admin</a>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . "/base.php";
