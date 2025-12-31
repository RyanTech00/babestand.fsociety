    </main>

    <?php
    // Carregar configurações do site para o footer
    $footerDb = \App\Database::getInstance();

    // Função para obter configuração
    $getFooterSetting = function($key, $default = '') use ($footerDb) {
        static $cache = [];
        if (!isset($cache[$key])) {
            $result = $footerDb->fetch("SELECT setting_value FROM site_settings WHERE setting_key = ?", [$key]);
            $cache[$key] = $result ? $result['setting_value'] : $default;
        }
        return $cache[$key];
    };

    // Obter configurações
    $footerSettings = [
        'phone' => $getFooterSetting('business_phone', '+351 912 345 678'),
        'email' => $getFooterSetting('business_email', 'info@babestand.fsociety.pt'),
        'address' => $getFooterSetting('business_address', 'Rua do Stand, 123'),
        'city' => $getFooterSetting('business_city', '4000-000 Porto'),
        'weekday_open' => $getFooterSetting('business_hours_weekday_open', '09:00'),
        'weekday_close' => $getFooterSetting('business_hours_weekday_close', '19:00'),
        'saturday_open' => $getFooterSetting('business_hours_saturday_open', '10:00'),
        'saturday_close' => $getFooterSetting('business_hours_saturday_close', '13:00'),
        'sunday_open' => $getFooterSetting('business_hours_sunday_open', ''),
        'sunday_close' => $getFooterSetting('business_hours_sunday_close', ''),
        'lunch_start' => $getFooterSetting('business_hours_lunch_start', ''),
        'lunch_end' => $getFooterSetting('business_hours_lunch_end', ''),
    ];

    // Função para formatar horário
    $formatFooterHours = function($open, $close, $lunchStart = '', $lunchEnd = '') {
        if (empty($open) || empty($close) || $open === $close || $open === '00:00' || $open === '01:01') {
            return 'Fechado';
        }
        $openStr = substr($open, 0, 5);
        $closeStr = substr($close, 0, 5);

        // Com pausa de almoço
        if (!empty($lunchStart) && !empty($lunchEnd)) {
            return $openStr . ' - ' . substr($lunchStart, 0, 5) . ' | ' . substr($lunchEnd, 0, 5) . ' - ' . $closeStr;
        }
        return $openStr . ' - ' . $closeStr;
    };
    ?>

    <!-- Footer -->
    <footer class="text-light py-5 mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="mb-3">
                        <i class="bi bi-car-front me-2"></i><?= SITE_NAME ?>
                    </h5>
                    <p class="text-muted">
                        O seu stand de automóveis de confiança. Oferecemos veículos de qualidade
                        com garantia e as melhores condições de financiamento.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-light fs-4"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-light fs-4"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-light fs-4"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>

                <div class="col-lg-2">
                    <h6 class="mb-3">Links Rápidos</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= url('veiculos.php') ?>" class="text-muted text-decoration-none">Veículos</a></li>
                        <li class="mb-2"><a href="<?= url('sobre.php') ?>" class="text-muted text-decoration-none">Sobre Nós</a></li>
                        <li class="mb-2"><a href="<?= url('contacto.php') ?>" class="text-muted text-decoration-none">Contacto</a></li>
                    </ul>
                </div>

                <div class="col-lg-3">
                    <h6 class="mb-3">Contactos</h6>
                    <ul class="list-unstyled text-muted">
                        <li class="mb-2">
                            <i class="bi bi-geo-alt me-2"></i><?= e($footerSettings['address']) ?>, <?= e($footerSettings['city']) ?>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-telephone me-2"></i>
                            <a href="tel:<?= e(preg_replace('/\s+/', '', $footerSettings['phone'])) ?>" class="text-muted text-decoration-none">
                                <?= e($footerSettings['phone']) ?>
                            </a>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-envelope me-2"></i>
                            <a href="mailto:<?= e($footerSettings['email']) ?>" class="text-muted text-decoration-none">
                                <?= e($footerSettings['email']) ?>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-3">
                    <h6 class="mb-3">Horário</h6>
                    <ul class="list-unstyled text-muted">
                        <li class="mb-2">
                            <i class="bi bi-clock me-2"></i>Seg-Sex: <?= $formatFooterHours($footerSettings['weekday_open'], $footerSettings['weekday_close'], $footerSettings['lunch_start'], $footerSettings['lunch_end']) ?>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-clock me-2"></i>Sábado: <?= $formatFooterHours($footerSettings['saturday_open'], $footerSettings['saturday_close']) ?>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-clock me-2"></i>Domingo: <?= $formatFooterHours($footerSettings['sunday_open'], $footerSettings['sunday_close']) ?>
                        </li>
                    </ul>
                </div>
            </div>

            <hr class="my-4 border-secondary">

            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 text-muted">
                        &copy; <?= date('Y') ?> <?= SITE_NAME ?>. Todos os direitos reservados.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="<?= url('termos.php') ?>" class="text-muted text-decoration-none me-3">Termos</a>
                    <a href="<?= url('privacidade.php') ?>" class="text-muted text-decoration-none">Privacidade</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Theme JS -->
    <script src="<?= url('js/theme.js') ?>?v=<?= filemtime(__DIR__ . '/../../public/js/theme.js') ?>"></script>
    <!-- Custom JS -->
    <script src="<?= url('js/app.js') ?>?v=<?= filemtime(__DIR__ . '/../../public/js/app.js') ?>"></script>
    <!-- Comparador JS -->
    <script src="<?= url('js/comparador.js') ?>?v=<?= @filemtime(__DIR__ . '/../../public/js/comparador.js') ?>"></script>
<?php if (auth()): ?>
    <script src="<?= url('js/notifications.js') ?>?v=<?= @filemtime(__DIR__ . '/../../public/js/notifications.js') ?>"></script>
    <?php endif; ?>

    <!-- Botao flutuante de comparacao -->
    <a href="<?= url('comparar.php') ?>" id="compare-floating-btn" class="btn btn-primary position-fixed d-none" style="bottom: 20px; right: 20px; z-index: 1050; border-radius: 50px; padding: 12px 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
        <i class="bi bi-columns-gap me-2"></i>Comparar <span id="compare-count" class="badge bg-light text-primary ms-1">0</span>
    </a>
</body>
</html>
