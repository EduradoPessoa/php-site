<?php
require_once __DIR__ . '/../../config/config.php';

try {
    $pdo = new PDO("sqlite:" . __DIR__ . "/../../data/database.sqlite");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Desabilitar verificação de chave estrangeira temporariamente
    $pdo->exec('PRAGMA foreign_keys = OFF');
    
    // Limpar todas as tabelas
    $tables = [
        'warehouse_products',
        'purchase_order_items',
        'purchase_orders',
        'supplier_products',
        'products',
        'suppliers',
        'manufacturers',
        'warehouses',
        'units',
        'activity_logs'
    ];
    
    foreach ($tables as $table) {
        $pdo->exec("DELETE FROM $table");
        $pdo->exec("DELETE FROM sqlite_sequence WHERE name='$table'");
    }
    
    // Reabilitar verificação de chave estrangeira
    $pdo->exec('PRAGMA foreign_keys = ON');
    
    echo "Banco de dados limpo com sucesso!\n";
    
} catch (PDOException $e) {
    die("Erro ao limpar o banco de dados: " . $e->getMessage() . "\n");
}
