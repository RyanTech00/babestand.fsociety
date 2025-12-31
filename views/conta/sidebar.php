<?php
/**
 * Sidebar da área de conta do utilizador
 * Requer: $user, $currentPage, $favoriteCount, $pendingTestDrives
 */
?>
<div class="col-lg-3 mb-4">
    <div class="card shadow-sm">
        <div class="card-body text-center">
            <div class="position-relative d-inline-block mb-3">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="<?= url('uploads/avatars/' . $user['avatar']) ?>"
                         class="rounded-circle"
                         style="width: 100px; height: 100px; object-fit: cover;"
                         alt="Avatar">
                <?php else: ?>
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto"
                         style="width: 100px; height: 100px;">
                        <i class="bi bi-person-fill display-4 text-primary"></i>
                    </div>
                <?php endif; ?>
            </div>
            <h5 class="mb-1"><?= e($user['name']) ?></h5>
            <p class="text-muted small mb-3"><?= e($user['email']) ?></p>
            <?php if ($user['email_verified_at']): ?>
                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Email Verificado</span>
            <?php else: ?>
                <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle me-1"></i>Email Não Verificado</span>
            <?php endif; ?>
        </div>
        <div class="list-group list-group-flush">
            <a href="<?= url('conta/index.php') ?>" class="list-group-item list-group-item-action <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-house-door me-2"></i>Dashboard
            </a>
            <a href="<?= url('conta/perfil.php') ?>" class="list-group-item list-group-item-action <?= ($currentPage ?? '') === 'perfil' ? 'active' : '' ?>">
                <i class="bi bi-person me-2"></i>Meu Perfil
            </a>
            <a href="<?= url('conta/favoritos.php') ?>" class="list-group-item list-group-item-action <?= ($currentPage ?? '') === 'favoritos' ? 'active' : '' ?>">
                <i class="bi bi-heart me-2"></i>Favoritos
                <?php if ($favoriteCount > 0): ?>
                    <span class="badge bg-primary float-end"><?= $favoriteCount ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= url('conta/test-drives.php') ?>" class="list-group-item list-group-item-action <?= ($currentPage ?? '') === 'test-drives' ? 'active' : '' ?>">
                <i class="bi bi-calendar-check me-2"></i>Test Drives
                <?php if ($pendingTestDrives > 0): ?>
                    <span class="badge bg-warning float-end"><?= $pendingTestDrives ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= url('conta/meus-veiculos.php') ?>" class="list-group-item list-group-item-action <?= ($currentPage ?? '') === 'meus-veiculos' ? 'active' : '' ?>">
                <i class="bi bi-car-front me-2"></i>Meus Veículos
                <?php if (isset($vehicleCount) && $vehicleCount > 0): ?>
                    <span class="badge bg-primary float-end"><?= $vehicleCount ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= url('conta/mensagens.php') ?>" class="list-group-item list-group-item-action <?= ($currentPage ?? '') === 'mensagens' ? 'active' : '' ?>">
                <i class="bi bi-envelope me-2"></i>Mensagens
            </a>
            <a href="<?= url('conta/seguranca.php') ?>" class="list-group-item list-group-item-action <?= ($currentPage ?? '') === 'seguranca' ? 'active' : '' ?>">
                <i class="bi bi-shield-lock me-2"></i>Segurança
            </a>
            <a href="<?= url('logout.php') ?>" class="list-group-item list-group-item-action text-danger">
                <i class="bi bi-box-arrow-right me-2"></i>Sair
            </a>
        </div>
    </div>
</div>
