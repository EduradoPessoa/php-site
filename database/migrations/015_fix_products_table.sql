-- Drop and recreate products table with correct structure
DROP TABLE IF EXISTS products;

CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code TEXT NOT NULL UNIQUE,
    name TEXT NOT NULL,
    description TEXT,
    manufacturer_id INTEGER,
    unit_id INTEGER,
    min_stock REAL DEFAULT 0,
    max_stock REAL DEFAULT 0,
    price REAL DEFAULT 0,
    default_warehouse_id INTEGER,
    active INTEGER DEFAULT 1,
    created_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_by INTEGER,
    updated_at DATETIME,
    FOREIGN KEY (manufacturer_id) REFERENCES manufacturers(id),
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (default_warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);
