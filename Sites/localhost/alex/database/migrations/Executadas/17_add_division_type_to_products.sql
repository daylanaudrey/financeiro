-- Migration: Add division_type column to products table
-- Sistema Aduaneiro - Adicionar campo de divisão aos produtos

-- Adicionar coluna division_type na tabela products
ALTER TABLE products
ADD COLUMN division_type ENUM('KG','QUANTIDADE') DEFAULT 'KG'
COMMENT 'Tipo de divisão: por peso (KG) ou por quantidade'
AFTER unit;

-- Atualizar produtos existentes baseado na unidade atual
UPDATE products
SET division_type = CASE
    WHEN unit = 'KG' THEN 'KG'
    WHEN unit = 'UN' THEN 'QUANTIDADE'
    WHEN unit = 'PC' THEN 'QUANTIDADE'
    WHEN unit = 'PÇ' THEN 'QUANTIDADE'
    ELSE 'QUANTIDADE'
END
WHERE division_type IS NULL OR division_type = 'KG';

-- Verificar resultado
SELECT id, name, ncm, unit, division_type
FROM products
WHERE deleted = 0
ORDER BY id
LIMIT 10;