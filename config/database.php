<?php
$dbPath = __DIR__ . '/../data/database.sqlite';
$dbDir = dirname($dbPath);

if (!file_exists($dbDir)) {
    mkdir($dbDir, 0777, true);
}

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Criar tabelas se não existirem
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        name TEXT NOT NULL,
        role TEXT NOT NULL,
        status INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Remover usuário admin existente para recriar
    $pdo->exec("DELETE FROM users WHERE email = 'eduardo@phoenyx.com.br'");
    
    // Criar usuário admin
    $stmt = $pdo->prepare("INSERT INTO users (email, password, name, role, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        'eduardo@phoenyx.com.br',
        password_hash('123456', PASSWORD_DEFAULT),
        'Eduardo',
        'admin',
        1
    ]);

} catch(PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}