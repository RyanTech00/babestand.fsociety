<?php
/**
 * BabeStand - Configuração Principal
 * 
 * INSTRUÇÕES:
 * 1. Copiar este ficheiro para config.php
 * 2. Criar ficheiro .env na raiz do projeto com as variáveis necessárias
 * 3. Nunca fazer commit do config.php ou .env com credenciais reais
 * 
 * @see .env.example para lista de variáveis de ambiente
 */

// Prevenir acesso direto
if (!defined('BABESTAND')) {
    die('Acesso negado');
}

// Carregar variáveis de ambiente
require_once __DIR__ . '/../src/Env.php';
\App\Env::load(__DIR__ . '/..');

// ============================================
// Configurações do Site
// ============================================
define('SITE_URL', \App\Env::get('SITE_URL', 'https://babestand.fsociety.pt'));
define('SITE_NAME', \App\Env::get('SITE_NAME', 'BabeStand'));
define('SITE_DOMAIN', \App\Env::get('SITE_DOMAIN', 'babestand.fsociety.pt'));

// ============================================
// Configurações da Base de Dados
// ============================================
define('DB_HOST', \App\Env::get('DB_HOST', 'localhost'));
define('DB_NAME', \App\Env::get('DB_NAME', 'babestand'));
define('DB_USER', \App\Env::get('DB_USER', 'babestand'));
define('DB_PASS', \App\Env::get('DB_PASS', ''));
define('DB_CHARSET', \App\Env::get('DB_CHARSET', 'utf8mb4'));

// ============================================
// Configurações de Email (SMTP)
// ============================================
define('MAIL_HOST', \App\Env::get('MAIL_HOST', 'localhost'));
define('MAIL_PORT', \App\Env::get('MAIL_PORT', 587));
define('MAIL_ENCRYPTION', \App\Env::get('MAIL_ENCRYPTION', 'tls'));
define('MAIL_USERNAME', \App\Env::get('MAIL_USERNAME', ''));
define('MAIL_PASSWORD', \App\Env::get('MAIL_PASSWORD', ''));
define('MAIL_FROM_ADDRESS', \App\Env::get('MAIL_FROM_ADDRESS', ''));
define('MAIL_FROM_NAME', \App\Env::get('MAIL_FROM_NAME', 'BabeStand'));
define('ADMIN_EMAIL', \App\Env::get('ADMIN_EMAIL', 'admin@babestand.fsociety.pt'));

// ============================================
// Configurações de Segurança
// ============================================
define('SESSION_LIFETIME', \App\Env::get('SESSION_LIFETIME', 3600));        // 1 hora
define('TOKEN_EXPIRY_MINUTES', \App\Env::get('TOKEN_EXPIRY_MINUTES', 15));  // 15 minutos
define('MAX_LOGIN_ATTEMPTS', \App\Env::get('MAX_LOGIN_ATTEMPTS', 5));       // 5 tentativas
define('LOCKOUT_DURATION', \App\Env::get('LOCKOUT_DURATION', 30));          // 30 minutos
define('REMEMBER_ME_DAYS', \App\Env::get('REMEMBER_ME_DAYS', 30));          // 30 dias

// ============================================
// Configurações de Upload
// ============================================
define('UPLOAD_MAX_SIZE', \App\Env::get('UPLOAD_MAX_SIZE', 5 * 1024 * 1024)); // 5MB
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// ============================================
// Timezone
// ============================================
date_default_timezone_set('Europe/Lisbon');

// ============================================
// Modo Debug (desativar em produção)
// ============================================
define('DEBUG_MODE', \App\Env::get('DEBUG_MODE', false));

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
