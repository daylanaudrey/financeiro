-- Migration: Add RFB fields to process_items table
-- Sistema Aduaneiro - Campos RFB para itens do processo
-- Data: 2025-09-23

-- Adicionar campos RFB à tabela process_items
ALTER TABLE `process_items`
ADD COLUMN `rfb_used` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Valor RFB usado para este item',
ADD COLUMN `rfb_margin` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Margem percentual aplicada sobre RFB',
ADD COLUMN `inv_used` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Valor INV usado (RFB + Margem)',
ADD COLUMN `rfb_option` ENUM('min', 'max', 'custom') DEFAULT 'custom' COMMENT 'Opção RFB selecionada';