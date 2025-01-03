-- Adiciona o campo de almoxarifado padrão na tabela de produtos
ALTER TABLE products ADD COLUMN default_warehouse_id INTEGER REFERENCES warehouses(id);
CREATE INDEX idx_products_default_warehouse ON products(default_warehouse_id);
