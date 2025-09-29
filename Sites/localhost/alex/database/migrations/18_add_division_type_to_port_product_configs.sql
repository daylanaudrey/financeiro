-- Migration: Add division_type column to port_product_configs table
-- Sistema Aduaneiro - Adicionar campo de divisão às configurações por porto

-- Adicionar coluna division_type na tabela port_product_configs
ALTER TABLE port_product_configs
ADD COLUMN division_type ENUM('PC', 'KG') NOT NULL DEFAULT 'PC'
COMMENT 'Forma de divisão: PC (quantidade) ou KG (peso)';

-- Verificar resultado
SELECT COUNT(*) as total_configs
FROM port_product_configs;