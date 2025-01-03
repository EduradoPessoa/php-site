<?php
require_once __DIR__ . '/../../config/config.php';

try {
    $pdo = new PDO("sqlite:" . __DIR__ . "/../../data/database.sqlite");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Criar tabela de almoxarifados
    $pdo->exec("CREATE TABLE IF NOT EXISTS warehouses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code VARCHAR(10) NOT NULL,
        name VARCHAR(100) NOT NULL,
        type VARCHAR(20) NOT NULL,
        manager_id INTEGER,
        address TEXT,
        temperature_control INTEGER DEFAULT 0,
        humidity_control INTEGER DEFAULT 0,
        is_system INTEGER DEFAULT 0,
        active INTEGER DEFAULT 1,
        created_by INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_by INTEGER,
        updated_at DATETIME,
        FOREIGN KEY (manager_id) REFERENCES users(id),
        FOREIGN KEY (created_by) REFERENCES users(id),
        FOREIGN KEY (updated_by) REFERENCES users(id)
    )");

    // Criar índice único para o código
    $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_warehouses_code ON warehouses(code) WHERE active = 1");

    // Criar tabela de produtos no almoxarifado
    $pdo->exec("CREATE TABLE IF NOT EXISTS warehouse_products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        warehouse_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        quantity REAL NOT NULL DEFAULT 0,
        min_stock REAL DEFAULT 0,
        max_stock REAL DEFAULT 0,
        location VARCHAR(50),
        active INTEGER DEFAULT 1,
        created_by INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_by INTEGER,
        updated_at DATETIME,
        FOREIGN KEY (warehouse_id) REFERENCES warehouses(id),
        FOREIGN KEY (product_id) REFERENCES products(id),
        FOREIGN KEY (created_by) REFERENCES users(id),
        FOREIGN KEY (updated_by) REFERENCES users(id),
        UNIQUE(warehouse_id, product_id, active)
    )");
    
    // Inserir almoxarifado padrão do sistema
    $stmt = $pdo->prepare("
        INSERT OR IGNORE INTO warehouses (
            code, 
            name, 
            type, 
            is_system, 
            created_by
        ) VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        'GERAL',
        'Almoxarifado Geral',
        'sales',
        1,
        1
    ]);
    
    echo "Tabelas de almoxarifado criadas com sucesso!\n";
    
} catch (PDOException $e) {
    die("Erro na migração: " . $e->getMessage() . "\n");
}
