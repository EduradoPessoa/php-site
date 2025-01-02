<?php
require_once __DIR__ . '/config.php';

try {
    // Criar diretório se não existir
    $dbDir = dirname(DB_PATH);
    if (!file_exists($dbDir)) {
        mkdir($dbDir, 0777, true);
    }

    // Conectar ao banco
    $pdo = new PDO("sqlite:" . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Remover tabelas existentes
    $pdo->exec("DROP TABLE IF EXISTS users");
    $pdo->exec("DROP TABLE IF EXISTS activity_logs");
    $pdo->exec("DROP TABLE IF EXISTS password_resets");
    
    // Criar tabelas
    $pdo->exec("CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        name TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'user',
        status INTEGER DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE activity_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        action TEXT NOT NULL,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    $pdo->exec("CREATE TABLE password_resets (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        token TEXT NOT NULL,
        expires_at DATETIME NOT NULL,
        used INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    // Criar usuário admin se não existir
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['eduardo@phoenyx.com.br']);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("
            INSERT INTO users (email, password, name, role, status)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'eduardo@phoenyx.com.br',
            password_hash('123456', PASSWORD_DEFAULT),
            'Eduardo',
            'admin',
            1
        ]);
    }

} catch(PDOException $e) {
    error_log("Erro no banco de dados: " . $e->getMessage());
    die("Erro na conexão com o banco de dados. Por favor, tente novamente mais tarde.");
}