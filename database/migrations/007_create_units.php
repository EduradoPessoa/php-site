<?php
require_once __DIR__ . '/../../config/config.php';

try {
    $pdo = new PDO("sqlite:" . __DIR__ . "/../../data/database.sqlite");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Criar tabela de unidades de medida
    $pdo->exec("CREATE TABLE IF NOT EXISTS units (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code VARCHAR(10) NOT NULL,
        name VARCHAR(50) NOT NULL,
        active INTEGER DEFAULT 1,
        created_by INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_by INTEGER,
        updated_at DATETIME,
        FOREIGN KEY (created_by) REFERENCES users(id),
        FOREIGN KEY (updated_by) REFERENCES users(id)
    )");
    
    // Criar índice único para o código
    $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_units_code ON units(code) WHERE active = 1");
    
    // Inserir unidades padrão
    $units = [
        ['UN', 'Unidade'],
        ['PC', 'Peça'],
        ['CX', 'Caixa'],
        ['KG', 'Quilograma'],
        ['M', 'Metro'],
        ['L', 'Litro']
    ];
    
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO units (code, name, created_by) VALUES (?, ?, 1)");
    foreach ($units as $unit) {
        $stmt->execute($unit);
    }
    
    echo "Tabela de unidades criada com sucesso!\n";
    
} catch (PDOException $e) {
    die("Erro na migração: " . $e->getMessage() . "\n");
}
