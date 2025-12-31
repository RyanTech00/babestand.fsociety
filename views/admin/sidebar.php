<?php
/**
 * BabeStand - Admin Sidebar
 * Incluir em todas as páginas admin
 */

// Obter contagens para badges (se não existirem)
if (!isset($pendingTestDrives)) {
    $pendingTestDrives = $db->fetch("SELECT COUNT(*) as count FROM test_drives WHERE status_id = 1")['count'];
}
if (!isset($newMessages)) {
    $newMessages = $db->fetch("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'new'")['count'];
}
if (!isset($pendingReviews)) {
    $pendingReviews = $db->fetch("SELECT COUNT(*) as count FROM reviews WHERE status = 'pending'")['count'];
}
if (!isset($pendingSellRequests)) {
    $pendingSellRequests = $db->fetch("SELECT COUNT(*) as count FROM sell_trade_requests WHERE status IN ('pendente', 'em_analise')")['count'] ?? 0;
}

// Determinar página atual para highlight
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="col-lg-2 mb-4">
    <div class="card shadow-sm">
        <div class="card-body text-center py-4">
            <i class="bi bi-shield-lock-fill display-4 text-primary"></i>
            <h5 class="mt-2 mb-0">Admin</h5>
        </div>
        <div class="list-group list-group-flush">
            <a href="<?= url('admin/dashboard.php') ?>" class="list-group-item list-group-item-action <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
            <a href="<?= url('admin/veiculos.php') ?>" class="list-group-item list-group-item-action <?= $currentPage === 'veiculos.php' ? 'active' : '' ?>">
                <i class="bi bi-car-front me-2"></i>Veículos
            </a>
            <a href="<?= url('admin/vendas.php') ?>" class="list-group-item list-group-item-action <?= $currentPage === 'vendas.php' ? 'active' : '' ?>">
                <i class="bi bi-cash-coin me-2"></i>Vendas
                <?php if ($pendingSellRequests > 0): ?>
                    <span class="badge bg-warning float-end"><?= $pendingSellRequests ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= url('admin/utilizadores.php') ?>" class="list-group-item list-group-item-action <?= in_array($currentPage, ['utilizadores.php', 'utilizador.php']) ? 'active' : '' ?>">
                <i class="bi bi-people me-2"></i>Utilizadores
            </a>
            <a href="<?= url('admin/test-drives.php') ?>" class="list-group-item list-group-item-action <?= $currentPage === 'test-drives.php' ? 'active' : '' ?>">
                <i class="bi bi-calendar-check me-2"></i>Test Drives
                <?php if ($pendingTestDrives > 0): ?>
                    <span class="badge bg-warning float-end"><?= $pendingTestDrives ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= url('admin/reviews.php') ?>" class="list-group-item list-group-item-action <?= $currentPage === 'reviews.php' ? 'active' : '' ?>">
                <i class="bi bi-star me-2"></i>Reviews
                <?php if ($pendingReviews > 0): ?>
                    <span class="badge bg-warning float-end"><?= $pendingReviews ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= url('admin/mensagens.php') ?>" class="list-group-item list-group-item-action <?= in_array($currentPage, ['mensagens.php', 'mensagem.php']) ? 'active' : '' ?>">
                <i class="bi bi-envelope me-2"></i>Mensagens
                <?php if ($newMessages > 0): ?>
                    <span class="badge bg-warning float-end"><?= $newMessages ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= url('admin/marcas.php') ?>" class="list-group-item list-group-item-action <?= $currentPage === 'marcas.php' ? 'active' : '' ?>">
                <i class="bi bi-tags me-2"></i>Marcas
            </a>
            <a href="<?= url('admin/configuracoes.php') ?>" class="list-group-item list-group-item-action <?= $currentPage === 'configuracoes.php' ? 'active' : '' ?>">
                <i class="bi bi-gear me-2"></i>Configurações
            </a>
            <a href="<?= url('admin/logs.php') ?>" class="list-group-item list-group-item-action <?= $currentPage === 'logs.php' ? 'active' : '' ?>">
                <i class="bi bi-shield-exclamation me-2"></i>Logs Segurança
            </a>
            <hr class="my-0">
            <a href="<?= url('inicio.php') ?>" class="list-group-item list-group-item-action">
                <i class="bi bi-house me-2"></i>Ver Site
            </a>
            <a href="<?= url('logout.php') ?>" class="list-group-item list-group-item-action text-danger">
                <i class="bi bi-box-arrow-right me-2"></i>Sair
            </a>
        </div>
    </div>
</div>
