-- Criar tabela de unidades de medida
CREATE TABLE IF NOT EXISTS units (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code VARCHAR(10) NOT NULL,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    active INTEGER DEFAULT 1,
    created_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_by INTEGER,
    updated_at DATETIME,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Criar índice único para o código
CREATE UNIQUE INDEX IF NOT EXISTS idx_units_code ON units(code) WHERE active = 1;

-- Criar coluna unit_id na tabela products
ALTER TABLE products ADD COLUMN unit_id INTEGER REFERENCES units(id);

-- Inserir algumas unidades padrão
INSERT INTO units (code, name, description, created_by) VALUES
('UN', 'Unidade', 'Unidade individual', 1),
('PC', 'Peça', 'Peça individual', 1),
('CX', 'Caixa', 'Caixa com múltiplas unidades', 1),
('KG', 'Quilograma', 'Medida de peso em quilogramas', 1),
('M', 'Metro', 'Medida de comprimento em metros', 1),
('L', 'Litro', 'Medida de volume em litros', 1);
