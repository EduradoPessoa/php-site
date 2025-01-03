<?php
// Configuração do banco de dados SQLite
$databasePath = __DIR__ . '/../data/database.sqlite';
$createTables = !file_exists($databasePath);

try {
    $pdo = new PDO("sqlite:$databasePath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($createTables) {
        // Tabelas do sistema
        $pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT 'user',
                status INTEGER NOT NULL DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $pdo->exec("
            CREATE TABLE activity_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                action TEXT NOT NULL,
                description TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");

        // Tabelas do módulo de compras
        $pdo->exec("
            CREATE TABLE manufacturers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                country TEXT NOT NULL,
                website TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $pdo->exec("
            CREATE TABLE products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT NOT NULL UNIQUE,
                name TEXT NOT NULL,
                description TEXT,
                manufacturer_id INTEGER,
                origin TEXT NOT NULL CHECK(origin IN ('national', 'imported')),
                type TEXT NOT NULL CHECK(type IN ('specialty', 'commodity')),
                min_stock REAL DEFAULT 0,
                max_stock REAL DEFAULT 0,
                current_stock REAL DEFAULT 0,
                unit TEXT NOT NULL,
                last_purchase_date DATE,
                last_purchase_price REAL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (manufacturer_id) REFERENCES manufacturers(id)
            )
        ");

        $pdo->exec("
            CREATE TABLE suppliers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT NOT NULL UNIQUE,
                name TEXT NOT NULL,
                legal_name TEXT,
                tax_id TEXT,
                supplier_type TEXT NOT NULL CHECK(supplier_type IN ('national', 'international')),
                address TEXT,
                city TEXT,
                state TEXT,
                country TEXT NOT NULL,
                postal_code TEXT,
                phone TEXT,
                email TEXT,
                website TEXT,
                contact_name TEXT,
                contact_phone TEXT,
                contact_email TEXT,
                bank_name TEXT,
                bank_account TEXT,
                bank_branch TEXT,
                payment_terms TEXT,
                shipping_terms TEXT,
                notes TEXT,
                status INTEGER NOT NULL DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $pdo->exec("
            CREATE TABLE supplier_products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                supplier_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                supplier_code TEXT,
                supplier_name TEXT,
                lead_time INTEGER,
                min_order_qty REAL,
                price REAL NOT NULL,
                currency TEXT NOT NULL DEFAULT 'BRL',
                last_update DATE,
                status INTEGER NOT NULL DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
                FOREIGN KEY (product_id) REFERENCES products(id),
                UNIQUE(supplier_id, product_id)
            )
        ");

        $pdo->exec("
            CREATE TABLE purchase_orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT NOT NULL UNIQUE,
                supplier_id INTEGER NOT NULL,
                order_date DATE NOT NULL,
                delivery_date DATE,
                status TEXT NOT NULL DEFAULT 'draft',
                currency TEXT NOT NULL DEFAULT 'BRL',
                subtotal REAL NOT NULL DEFAULT 0,
                tax_amount REAL NOT NULL DEFAULT 0,
                shipping_amount REAL NOT NULL DEFAULT 0,
                total_amount REAL NOT NULL DEFAULT 0,
                payment_terms TEXT,
                shipping_terms TEXT,
                notes TEXT,
                created_by INTEGER NOT NULL,
                approved_by INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
                FOREIGN KEY (created_by) REFERENCES users(id),
                FOREIGN KEY (approved_by) REFERENCES users(id)
            )
        ");

        $pdo->exec("
            CREATE TABLE purchase_order_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                purchase_order_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                quantity REAL NOT NULL,
                unit_price REAL NOT NULL,
                tax_rate REAL DEFAULT 0,
                tax_amount REAL DEFAULT 0,
                total_amount REAL NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id),
                FOREIGN KEY (product_id) REFERENCES products(id)
            )
        ");

        // Inserir usuário admin
        $adminPassword = password_hash('123456', PASSWORD_DEFAULT);
        $pdo->exec("
            INSERT INTO users (name, email, password, role, status)
            VALUES ('Eduardo Pessoa', 'eduardo@phoenyx.com.br', '$adminPassword', 'admin', 1)
        ");
    }

} catch(PDOException $e) {
    die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}