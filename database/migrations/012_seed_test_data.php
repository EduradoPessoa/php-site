<?php
require_once __DIR__ . '/../../config/config.php';

try {
    $pdo = new PDO("sqlite:" . __DIR__ . "/../../data/database.sqlite");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Inserir unidades
    $units = [
        ['PC', 'Peça'],
        ['CX', 'Caixa'],
        ['M', 'Metro'],
        ['KG', 'Quilograma'],
        ['L', 'Litro']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO units (code, name, active, created_by) VALUES (?, ?, 1, 1)");
    foreach ($units as $unit) {
        $stmt->execute($unit);
    }
    
    // Inserir fabricantes
    $manufacturers = [
        ['WEG', 'Brasil', 'www.weg.net'],
        ['Siemens', 'Alemanha', 'www.siemens.com'],
        ['ABB', 'Suíça', 'www.abb.com'],
        ['Schneider Electric', 'França', 'www.se.com'],
        ['Allen-Bradley', 'Estados Unidos', 'www.rockwellautomation.com']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO manufacturers (name, country, website, active, created_by) VALUES (?, ?, ?, 1, 1)");
    foreach ($manufacturers as $manufacturer) {
        $stmt->execute($manufacturer);
    }
    
    // Inserir almoxarifados
    $warehouses = [
        ['GERAL', 'Almoxarifado Geral', 'sales', 1, 1, 1, 1],
        ['INSP', 'Almoxarifado de Inspeção', 'inspection', 1, 1, 1, 0],
        ['IMP', 'Almoxarifado de Importação', 'import', 1, 0, 0, 0],
        ['AVAR', 'Almoxarifado de Avariados', 'damaged', 1, 0, 0, 0]
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO warehouses (
            code, name, type, manager_id, 
            temperature_control, humidity_control, is_system,
            active, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 1, 1)
    ");
    foreach ($warehouses as $warehouse) {
        $stmt->execute($warehouse);
    }
    
    // Inserir fornecedores
    $suppliers = [
        ['F001', 'Elétrica Luz', 'Elétrica Luz Ltda', '12.345.678/0001-90', 'national', 'Rua A, 123', 'São Paulo', 'SP', 'Brasil', '01234-567', '(11) 1234-5678', 'contato@eletrica.com', 'www.eletrica.com', 'João Silva', '(11) 98765-4321', 'joao@eletrica.com', 'Banco do Brasil', '1234-5', '12345-6', '30 dias', 'FOB'],
        ['F002', 'Global Tech', 'Global Tech Inc', '98.765.432/0001-10', 'international', '123 Main St', 'Miami', 'FL', 'Estados Unidos', '33101', '+1 305-123-4567', 'contact@globaltech.com', 'www.globaltech.com', 'John Doe', '+1 305-987-6543', 'john@globaltech.com', 'Bank of America', '9876-5', '98765-4', '45 dias', 'CIF'],
        ['F003', 'Automação Total', 'Automação Total SA', '45.678.901/0001-23', 'national', 'Av B, 456', 'Curitiba', 'PR', 'Brasil', '80000-000', '(41) 3333-4444', 'vendas@automacao.com', 'www.automacao.com', 'Maria Santos', '(41) 99999-8888', 'maria@automacao.com', 'Itaú', '3456-7', '34567-8', '15 dias', 'FOB']
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO suppliers (
            code, name, legal_name, tax_id, supplier_type, address, city, state, country,
            postal_code, phone, email, website, contact_name, contact_phone, contact_email,
            bank_name, bank_branch, bank_account, payment_terms, shipping_terms,
            status, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1)
    ");
    foreach ($suppliers as $supplier) {
        $stmt->execute($supplier);
    }
    
    // Inserir produtos
    $products = [
        ['P001', 'Motor Elétrico 1CV', 'Motor trifásico 220V/380V', 1, 1, 10, 100, 850.00, 1],
        ['P002', 'Inversor de Frequência', 'Inversor 2CV 220V', 2, 1, 5, 50, 1200.00, 1],
        ['P003', 'CLP S7-1200', 'CLP Siemens com 14 entradas e 10 saídas', 2, 1, 2, 20, 3500.00, 1],
        ['P004', 'Sensor Indutivo', 'Sensor M18 PNP NA', 4, 1, 20, 200, 120.00, 1],
        ['P005', 'Contator 9A', 'Contator tripolar 220V', 3, 1, 15, 150, 85.00, 1]
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO products (
            code, name, description, manufacturer_id, unit_id,
            min_stock, max_stock, price, default_warehouse_id,
            status, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1)
    ");
    foreach ($products as $product) {
        $stmt->execute($product);
    }
    
    // Inserir produtos nos almoxarifados
    $warehouse_products = [
        [1, 1, 50, 10, 100, 'A1-01'],
        [1, 2, 25, 5, 50, 'A1-02'],
        [1, 3, 10, 2, 20, 'A2-01'],
        [2, 4, 100, 20, 200, 'B1-01'],
        [2, 5, 75, 15, 150, 'B1-02']
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO warehouse_products (
            warehouse_id, product_id, quantity, min_stock,
            max_stock, location, active, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, 1, 1)
    ");
    foreach ($warehouse_products as $wp) {
        $stmt->execute($wp);
    }
    
    // Inserir produtos dos fornecedores
    $supplier_products = [
        [1, 1, 'ME-001', 'Motor 1CV WEG', 5, 1, 800.00, 'BRL', '2025-01-03'],
        [1, 2, 'INV-002', 'Inversor Siemens', 7, 1, 1150.00, 'BRL', '2025-01-03'],
        [2, 3, 'S7-1200', 'CLP Siemens', 10, 1, 3200.00, 'USD', '2025-01-03'],
        [3, 4, 'SI-18', 'Sensor Schneider', 3, 10, 110.00, 'BRL', '2025-01-03'],
        [3, 5, 'CT-9A', 'Contator ABB', 5, 5, 75.00, 'BRL', '2025-01-03']
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO supplier_products (
            supplier_id, product_id, supplier_code, supplier_name,
            lead_time, min_order_qty, price, currency, last_update,
            status, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1)
    ");
    foreach ($supplier_products as $sp) {
        $stmt->execute($sp);
    }
    
    echo "Dados de teste inseridos com sucesso!\n";
    
} catch (PDOException $e) {
    die("Erro ao inserir dados de teste: " . $e->getMessage() . "\n");
}
