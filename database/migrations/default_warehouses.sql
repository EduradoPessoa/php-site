-- Criação dos almoxarifados padrão
INSERT INTO warehouses (
    id,
    name,
    type,
    manager_id,
    description,
    code,
    is_default,
    is_system,
    active,
    created_by,
    created_at,
    updated_by,
    updated_at
) VALUES
(98, 'Inspeção de Entrada', 'inspection', 1, 'Almoxarifado para inspeção de produtos recebidos', '098', 1, 1, 1, 1, CURRENT_TIMESTAMP, 1, CURRENT_TIMESTAMP),
(99, 'Vendas', 'sales', 1, 'Almoxarifado de produtos aprovados para venda', '099', 1, 1, 1, 1, CURRENT_TIMESTAMP, 1, CURRENT_TIMESTAMP);
