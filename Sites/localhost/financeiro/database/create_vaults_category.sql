-- Criar categoria Vaults para depósitos em objetivos
INSERT IGNORE INTO categories (
    org_id, 
    nome, 
    tipo, 
    cor, 
    icone, 
    ativo, 
    created_by,
    created_at
) VALUES (
    1, 
    'Vaults', 
    'out', 
    '#6f42c1', 
    'fas fa-bullseye', 
    1, 
    1,
    NOW()
);