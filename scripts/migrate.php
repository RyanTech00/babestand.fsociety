<?php
/**
 * BabeStand - Script de Migração
 * Executa migrações SQL pendentes
 */

define('BABESTAND', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';

use App\Database;

echo "BabeStand - Migração de Base de Dados\n";
echo "=====================================\n\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Verificar ficheiros de migração
    $sqlDir = __DIR__ . '/../sql/';
    $migrations = glob($sqlDir . 'migration_*.sql');
    
    if (empty($migrations)) {
        echo "Nenhuma migração encontrada.\n";
        exit(0);
    }
    
    sort($migrations);
    
    foreach ($migrations as $migration) {
        $filename = basename($migration);
        echo "Executando: $filename\n";
        
        $sql = file_get_contents($migration);
        
        // Executar statements individualmente
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            fn($s) => !empty($s) && !str_starts_with($s, '--')
        );
        
        foreach ($statements as $statement) {
            if (empty(trim($statement))) continue;
            
            try {
                $pdo->exec($statement);
                echo "  ✓ OK\n";
            } catch (PDOException $e) {
                // Ignorar erros de "já existe" ou "duplicado"
                if (strpos($e->getMessage(), 'already exists') !== false ||
                    strpos($e->getMessage(), 'Duplicate') !== false) {
                    echo "  - Já existe, ignorado\n";
                } else {
                    echo "  ✗ Erro: " . $e->getMessage() . "\n";
                }
            }
        }
        
        echo "\n";
    }
    
    echo "Migração concluída!\n";
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    exit(1);
}
