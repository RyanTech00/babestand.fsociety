<?php
/**
 * Script para atualizar administradores
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Database;

$db = Database::getInstance();

echo "=== Atualizando Administradores ===\n\n";

// Remover admin antigo
$db->query("DELETE FROM users WHERE email = 'admin@babestand.fsociety.pt'");
echo "Admin antigo removido.\n";

// Password hashes
$hugoPassword = password_hash('Hugo2025!', PASSWORD_ARGON2ID);
$ryanPassword = password_hash('Ryan2025!', PASSWORD_ARGON2ID);

// Verificar se Hugo existe
$hugo = $db->fetch("SELECT id FROM users WHERE email = ?", ['hugo.correia@fsociety.pt']);

if ($hugo) {
    // Atualizar Hugo para admin
    $db->query(
        "UPDATE users SET name = ?, password = ?, role_id = 1, is_active = 1 WHERE email = ?",
        ['Hugo Correia', $hugoPassword, 'hugo.correia@fsociety.pt']
    );
    echo "Hugo Correia atualizado para admin.\n";
} else {
    // Criar Hugo
    $db->query(
        "INSERT INTO users (name, email, password, role_id, is_active, created_at) VALUES (?, ?, ?, 1, 1, NOW())",
        ['Hugo Correia', 'hugo.correia@fsociety.pt', $hugoPassword]
    );
    echo "Hugo Correia criado como admin.\n";
}

// Verificar se Ryan existe
$ryan = $db->fetch("SELECT id FROM users WHERE email = ?", ['ryan.barbosa@fsociety.pt']);

if ($ryan) {
    // Atualizar Ryan para admin
    $db->query(
        "UPDATE users SET name = ?, password = ?, role_id = 1, is_active = 1 WHERE email = ?",
        ['Ryan Barbosa', $ryanPassword, 'ryan.barbosa@fsociety.pt']
    );
    echo "Ryan Barbosa atualizado para admin.\n";
} else {
    // Criar Ryan
    $db->query(
        "INSERT INTO users (name, email, password, role_id, is_active, created_at) VALUES (?, ?, ?, 1, 1, NOW())",
        ['Ryan Barbosa', 'ryan.barbosa@fsociety.pt', $ryanPassword]
    );
    echo "Ryan Barbosa criado como admin.\n";
}

// Verificar resultado
$admins = $db->fetchAll("SELECT id, name, email, role_id, is_active FROM users WHERE role_id = 1");
echo "\n=== Administradores Atuais ===\n";
foreach ($admins as $admin) {
    echo "- {$admin['name']} ({$admin['email']})\n";
}

echo "\n=== Concluído! ===\n";
