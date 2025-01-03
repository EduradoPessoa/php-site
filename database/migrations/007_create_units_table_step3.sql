-- Criar coluna unit_id na tabela products
ALTER TABLE products ADD COLUMN unit_id INTEGER REFERENCES units(id);
