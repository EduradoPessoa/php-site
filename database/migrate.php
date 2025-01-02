<?php
require_once __DIR__ . '/../config/config.php';

try {
    $pdo = new PDO("sqlite:" . __DIR__ . "/../data/database.sqlite");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Criar tabela de usuários
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        name TEXT NOT NULL,
        role TEXT NOT NULL,
        status INTEGER DEFAULT 0,
        avatar TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Criar tabela de logs de atividade
    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        action TEXT NOT NULL,
        description TEXT,
        ip_address TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    
    // Criar tabela de redefinição de senha
    $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        token TEXT NOT NULL,
        expires_at DATETIME NOT NULL,
        used INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    
    // Criar tabela de notificações
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        message TEXT NOT NULL,
        type TEXT NOT NULL,
        read INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    
    // Criar índices
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_users_role ON users(role)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_activity_logs_user ON activity_logs(user_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_password_resets_token ON password_resets(token)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_notifications_user ON notifications(user_id)");
    
    // Criar usuário admin padrão se não existir
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
        
        echo "Usuário admin criado com sucesso!\n";
    }
    
    echo "Migrations executadas com sucesso!\n";
    
} catch (PDOException $e) {
    die("Erro na migração: " . $e->getMessage() . "\n");
}
