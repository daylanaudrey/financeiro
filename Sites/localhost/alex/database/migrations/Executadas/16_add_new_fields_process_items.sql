-- Migration: Add new fields to process_items table
-- Sistema Aduaneiro - Novos campos de peso e FOB
-- Data: 2025-09-25

-- Adicionar novos campos à tabela process_items
ALTER TABLE `process_items`
ADD COLUMN `gross_weight` DECIMAL(12,3) DEFAULT 0.000 COMMENT 'Peso Bruto (KG)' AFTER `weight_kg`,
ADD COLUMN `weight_discount` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Percentual de desconto do peso' AFTER `gross_weight`,
ADD COLUMN `net_weight` DECIMAL(12,3) DEFAULT 0.000 COMMENT 'Peso Líquido (KG)' AFTER `weight_discount`,
ADD COLUMN `freight_ttl_kg` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Frete TTL/KG (USD)' AFTER `freight_usd`,
ADD COLUMN `total_fob_input` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Total FOB (USD) calculado' AFTER `unit_price_usd`;

-- Copiar dados existentes de weight_kg para gross_weight se houver
UPDATE `process_items`
SET `gross_weight` = `weight_kg`,
    `net_weight` = `weight_kg`
WHERE `weight_kg` > 0;