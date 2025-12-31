<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BabeStand - O seu stand de automóveis de confiança. Veículos de qualidade com garantia.">
    <title><?= e($pageTitle ?? 'Início') ?> - <?= SITE_NAME ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= url('css/style.css') ?>?v=<?= filemtime(__DIR__ . '/../../public/css/style.css') ?>" rel="stylesheet">

    <!-- Theme Script (carrega antes do body para evitar flash) -->
    <script>
        (function() {
            const saved = localStorage.getItem('babestand-theme') || 'auto';
            let theme = saved;
            if (saved === 'auto') {
                theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= url('inicio.php') ?>">
                <i class="bi bi-car-front me-2"></i><?= SITE_NAME ?>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('inicio.php') ?>">
                            <i class="bi bi-house me-1"></i>Início
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('veiculos.php') ?>">
                            <i class="bi bi-grid me-1"></i>Veículos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('sobre.php') ?>">
                            <i class="bi bi-info-circle me-1"></i>Sobre Nós
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('contacto.php') ?>">
                            <i class="bi bi-envelope me-1"></i>Contacto
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav align-items-center">
                    <!-- Theme Toggle -->
                    <li class="nav-item me-2">
                        <button type="button" id="theme-toggle" class="theme-toggle" title="Alternar tema">
                            <i class="bi bi-circle-half"></i>
                        </button>
                    </li>

                    <?php if (auth()): ?>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= url('admin/dashboard.php') ?>">
                                    <i class="bi bi-speedometer2 me-1"></i>Admin
                                </a>
                            </li>
                        <?php endif; ?>
                        
                    <!-- Notificacoes -->
                    <li class="nav-item dropdown me-2">
                        <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" id="notification-bell">
                            <i class="bi bi-bell fs-5"></i>
                            <span class="notification-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notification-count" style="display: none;">0</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end notification-dropdown" style="width: 320px; max-height: 400px; overflow-y: auto;">
                            <li class="dropdown-header d-flex justify-content-between align-items-center">
                                <span>Notificacoes</span>
                                <a href="#" class="small text-primary" id="mark-all-read">Marcar todas lidas</a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <div id="notifications-list">
                                <li class="text-center py-3 text-muted">
                                    <i class="bi bi-bell-slash fs-4 d-block mb-1"></i>
                                    <small>Sem notificacoes</small>
                                </li>
                            </div>
                        </ul>
                    </li>
<li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <?php $userAvatar = \App\Session::get('user_avatar'); ?>
                                <?php if ($userAvatar): ?>
                                    <img src="<?= url('uploads/avatars/' . $userAvatar) ?>" class="rounded-circle me-1" style="width: 24px; height: 24px; object-fit: cover;" alt="">
                                <?php else: ?>
                                    <i class="bi bi-person-circle me-1"></i>
                                <?php endif; ?>
                                <?= e(\App\Session::get('user_name')) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="<?= url('conta/index.php') ?>">
                                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= url('conta/perfil.php') ?>">
                                        <i class="bi bi-person me-2"></i>Meu Perfil
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= url('conta/favoritos.php') ?>">
                                        <i class="bi bi-heart me-2"></i>Favoritos
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= url('conta/meus-veiculos.php') ?>">
                                        <i class="bi bi-car-front me-2"></i>Meus Veículos
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= url('conta/test-drives.php') ?>">
                                        <i class="bi bi-calendar-check me-2"></i>Test Drives
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= url('conta/mensagens.php') ?>">
                                        <i class="bi bi-envelope me-2"></i>Mensagens
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= url('conta/seguranca.php') ?>">
                                        <i class="bi bi-shield-lock me-2"></i>Segurança
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?= url('logout.php') ?>">
                                        <i class="bi bi-box-arrow-right me-2"></i>Sair
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('login.php') ?>">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Entrar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-light ms-2" href="<?= url('registar.php') ?>">
                                Criar Conta
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php
    $flashMessages = \App\Session::getAllFlash();
    if (!empty($flashMessages)):
    ?>
    <div class="container mt-3">
        <?php foreach ($flashMessages as $type => $message): ?>
            <?php
            $alertClass = match($type) {
                'success' => 'alert-success',
                'error' => 'alert-danger',
                'warning' => 'alert-warning',
                default => 'alert-info'
            };
            ?>
            <div class="alert <?= $alertClass ?> alert-dismissible fade show" role="alert">
                <?= e($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main>
