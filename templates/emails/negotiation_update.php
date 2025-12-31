<?php
/**
 * Email: Atualização de Negociação
 * Enviado quando há nova oferta/contraproposta/decisão
 */

$typeLabel = $type === 'vender' ? 'Venda' : 'Troca';

// Configurar título e cabeçalho baseado na ação
if ($action === 'offer') {
    $title = "Nova Proposta de {$typeLabel}";
    $preheader = "Recebemos o seu pedido e temos uma proposta para si";
    $header_class = "";
} elseif ($action === 'counter_accepted' || $action === 'initial_accepted') {
    $title = "Proposta Aceite";
    $preheader = "A sua proposta foi aceite!";
    $header_class = "header-success";
} elseif ($action === 'deal_complete') {
    $title = "Negócio Concluído";
    $preheader = "O seu negócio foi concluído com sucesso";
    $header_class = "header-success";
} elseif ($action === 'rejected') {
    $title = "Pedido Recusado";
    $preheader = "Infelizmente o seu pedido foi recusado";
    $header_class = "header-danger";
} else {
    $title = "Atualização do Pedido";
    $preheader = "Há novidades sobre o seu pedido";
    $header_class = "";
}

ob_start();
?>
<h2 class="greeting">Olá, <?= htmlspecialchars($name) ?>!</h2>

<p class="text">Temos novidades sobre o seu pedido de <strong><?= strtolower($typeLabel) ?></strong> do veículo:</p>

<div class="info-box">
    <p class="box-title">🚗 <?= htmlspecialchars($vehicle_name) ?></p>
</div>

<?php if ($action === 'offer'): ?>
    <p class="text">Recebemos o seu pedido e temos uma <strong>proposta de avaliação</strong> para si:</p>

    <div class="success-box" style="text-align: center;">
        <p class="box-title">Valor da nossa oferta</p>
        <p style="font-size: 32px; font-weight: bold; color: #27ae60; margin: 10px 0;">
            <?= number_format($offer_value, 2, ',', '.') ?> €
        </p>
    </div>

    <?php if (!empty($admin_message)): ?>
        <div class="info-box">
            <p class="box-title">📝 Mensagem</p>
            <p class="box-text"><?= nl2br(htmlspecialchars($admin_message)) ?></p>
        </div>
    <?php endif; ?>

    <p class="text">Pode agora:</p>
    <table class="details-table">
        <tr><td class="label">✅ Aceitar</td><td class="value">Aceitar a nossa oferta e concluir o negócio</td></tr>
        <tr><td class="label">🔄 Contrapropor</td><td class="value">Fazer uma contraproposta com o valor que pretende</td></tr>
        <tr><td class="label">❌ Recusar</td><td class="value">Recusar se não estiver interessado</td></tr>
    </table>

    <div class="button-container">
        <a href="<?= SITE_URL ?>/conta/meu-veiculo.php?id=<?= $vehicle_id ?>&tab=sell" class="button">Ver Proposta</a>
    </div>

<?php elseif ($action === 'counter_accepted' || $action === 'initial_accepted'): ?>
    <p class="text">A sua proposta foi <strong>aceite</strong>!</p>

    <div class="success-box" style="text-align: center;">
        <p class="box-title">✅ Valor acordado</p>
        <p style="font-size: 32px; font-weight: bold; color: #27ae60; margin: 10px 0;">
            <?= number_format($final_price, 2, ',', '.') ?> €
        </p>
    </div>

    <p class="text">Entraremos em contacto brevemente para combinar os próximos passos.</p>

    <div class="button-container">
        <a href="<?= SITE_URL ?>/conta/meu-veiculo.php?id=<?= $vehicle_id ?>&tab=sell" class="button button-success">Ver Detalhes</a>
    </div>

<?php elseif ($action === 'deal_complete'): ?>
    <p class="text">O negócio foi <strong>concluído com sucesso</strong>! Parabéns!</p>

    <div class="success-box" style="text-align: center;">
        <p class="box-title">🎉 Valor final</p>
        <p style="font-size: 32px; font-weight: bold; color: #27ae60; margin: 10px 0;">
            <?= number_format($final_price, 2, ',', '.') ?> €
        </p>
    </div>

    <div class="info-box">
        <p class="box-title">📋 Próximos passos</p>
        <p class="box-text">
            O veículo será removido da sua conta e colocado novamente à venda no nosso stand.<br>
            Entraremos em contacto para combinar a entrega e o pagamento.
        </p>
    </div>

<?php elseif ($action === 'rejected'): ?>
    <p class="text">Lamentamos informar que o seu pedido foi <strong>recusado</strong>.</p>

    <?php if (!empty($admin_message)): ?>
        <div class="danger-box">
            <p class="box-title">📝 Motivo</p>
            <p class="box-text"><?= nl2br(htmlspecialchars($admin_message)) ?></p>
        </div>
    <?php endif; ?>

    <p class="text">Se tiver dúvidas ou quiser discutir outras opções, não hesite em contactar-nos.</p>

    <div class="button-container">
        <a href="<?= SITE_URL ?>/contacto.php" class="button">Contactar-nos</a>
    </div>

<?php else: ?>
    <p class="text">Há uma nova atualização no seu pedido.</p>

    <?php if (!empty($admin_message)): ?>
        <div class="info-box">
            <p class="box-title">📝 Mensagem</p>
            <p class="box-text"><?= nl2br(htmlspecialchars($admin_message)) ?></p>
        </div>
    <?php endif; ?>

    <div class="button-container">
        <a href="<?= SITE_URL ?>/conta/meu-veiculo.php?id=<?= $vehicle_id ?>&tab=sell" class="button">Ver Detalhes</a>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . "/base.php";
