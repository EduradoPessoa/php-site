-- Criar unidades a partir dos valores únicos existentes (se necessário)
INSERT OR IGNORE INTO units (code, name, created_by)
SELECT DISTINCT 
    UPPER(unit) as code,
    UPPER(unit) as name,
    1 as created_by
FROM products 
WHERE unit IS NOT NULL 
AND unit != ''
AND status = 1;

-- Atualizar os produtos com os IDs das unidades correspondentes
UPDATE products 
SET unit_id = (
    SELECT id 
    FROM units 
    WHERE UPPER(code) = UPPER(products.unit)
    AND status = 1
)
WHERE unit IS NOT NULL 
AND unit != ''
AND status = 1;

-- Remover a coluna unit após a migração
ALTER TABLE products DROP COLUMN unit;
