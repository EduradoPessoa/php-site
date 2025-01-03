-- Criar tabela temporária com a nova estrutura
CREATE TABLE products_new (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    unit VARCHAR(20) NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'commodity',
    origin VARCHAR(50) NOT NULL DEFAULT 'national',
    manufacturer_id INTEGER,
    price DECIMAL(10,2) NOT NULL,
    last_purchase_date DATE,
    last_purchase_price DECIMAL(10,2),
    min_stock DECIMAL(10,2) DEFAULT 0,
    max_stock DECIMAL(10,2),
    current_stock DECIMAL(10,2) DEFAULT 0,
    status BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME,
    FOREIGN KEY (manufacturer_id) REFERENCES manufacturers(id)
);

-- Copiar dados da tabela antiga para a nova
INSERT INTO products_new (
    id, code, name, description, unit, type, origin, manufacturer_id,
    last_purchase_date, last_purchase_price, min_stock, max_stock,
    current_stock, status, created_at, updated_at
)
SELECT 
    id, code, name, description, unit, type, origin, manufacturer_id,
    last_purchase_date, last_purchase_price, min_stock, max_stock,
    current_stock, status, created_at, updated_at
FROM products;

-- Remover a tabela antiga
DROP TABLE products;

-- Renomear a nova tabela
ALTER TABLE products_new RENAME TO products;

-- Recriar os índices
CREATE INDEX idx_products_code ON products(code);
CREATE INDEX idx_products_name ON products(name);
CREATE INDEX idx_products_manufacturer ON products(manufacturer_id);
CREATE INDEX idx_products_type ON products(type);
CREATE INDEX idx_products_origin ON products(origin);
