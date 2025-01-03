-- Tabela de almoxarifados
CREATE TABLE IF NOT EXISTS warehouses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    code TEXT NOT NULL UNIQUE,
    type TEXT NOT NULL CHECK(type IN ('sales', 'inspection', 'damaged', 'import')),
    manager_id INTEGER NOT NULL,
    description TEXT,
    address TEXT,
    capacity REAL,
    temperature_control INTEGER DEFAULT 0,
    min_temperature REAL,
    max_temperature REAL,
    humidity_control INTEGER DEFAULT 0,
    min_humidity REAL,
    max_humidity REAL,
    notes TEXT,
    is_blocked INTEGER DEFAULT 0,
    is_default INTEGER DEFAULT 0,
    is_system INTEGER DEFAULT 0,
    active INTEGER DEFAULT 1,
    created_by INTEGER NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INTEGER NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (manager_id) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Tabela de produtos no almoxarifado
CREATE TABLE IF NOT EXISTS warehouse_products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    warehouse_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity REAL NOT NULL DEFAULT 0,
    location TEXT,
    notes TEXT,
    active INTEGER DEFAULT 1,
    created_by INTEGER NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INTEGER NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Tabela de movimentações de produtos entre almoxarifados
CREATE TABLE IF NOT EXISTS warehouse_movements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    source_warehouse_id INTEGER,
    target_warehouse_id INTEGER,
    product_id INTEGER NOT NULL,
    quantity REAL NOT NULL,
    movement_type TEXT NOT NULL CHECK(movement_type IN ('in', 'out', 'transfer')),
    reference_type TEXT,
    reference_id INTEGER,
    notes TEXT,
    created_by INTEGER NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (source_warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY (target_warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Índices para melhor performance
CREATE INDEX IF NOT EXISTS idx_warehouses_active ON warehouses(active);
CREATE INDEX IF NOT EXISTS idx_warehouses_type ON warehouses(type);
CREATE INDEX IF NOT EXISTS idx_warehouses_code ON warehouses(code);
CREATE INDEX IF NOT EXISTS idx_warehouses_default ON warehouses(is_default);
CREATE INDEX IF NOT EXISTS idx_warehouse_products_warehouse ON warehouse_products(warehouse_id, active);
CREATE INDEX IF NOT EXISTS idx_warehouse_products_product ON warehouse_products(product_id, active);
CREATE INDEX IF NOT EXISTS idx_warehouse_movements_source ON warehouse_movements(source_warehouse_id);
CREATE INDEX IF NOT EXISTS idx_warehouse_movements_target ON warehouse_movements(target_warehouse_id);
CREATE INDEX IF NOT EXISTS idx_warehouse_movements_product ON warehouse_movements(product_id);
CREATE INDEX IF NOT EXISTS idx_warehouse_movements_reference ON warehouse_movements(reference_type, reference_id);
