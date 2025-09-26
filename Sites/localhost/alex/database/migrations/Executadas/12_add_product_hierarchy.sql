-- Migration: Add product hierarchy support
-- Sistema Aduaneiro - Suporte para produtos pai/filho
-- Data: 2025-09-23

-- 1. Remover índice único do NCM para permitir múltiplos produtos com mesmo NCM
ALTER TABLE `products`
DROP INDEX `idx_ncm`;

-- 2. Adicionar campo parent_id para hierarquia
ALTER TABLE `products`
ADD COLUMN `parent_id` INT NULL AFTER `id`,
ADD COLUMN `is_variant` BOOLEAN DEFAULT FALSE AFTER `parent_id`,
ADD COLUMN `variant_description` TEXT NULL AFTER `description`;

-- 3. Adicionar índice de foreign key
ALTER TABLE `products`
ADD CONSTRAINT `fk_product_parent`
FOREIGN KEY (`parent_id`) REFERENCES `products`(`id`)
ON DELETE CASCADE ON UPDATE CASCADE;

-- 4. Adicionar índices úteis
ALTER TABLE `products`
ADD INDEX `idx_parent_id` (`parent_id`),
ADD INDEX `idx_ncm` (`ncm`),
ADD INDEX `idx_is_variant` (`is_variant`);

-- 5. Criar view para produtos principais (sem variações)
CREATE OR REPLACE VIEW `v_main_products` AS
SELECT * FROM `products`
WHERE `parent_id` IS NULL
  AND `deleted` = FALSE;

-- 6. Criar view para produtos com suas variações
CREATE OR REPLACE VIEW `v_products_with_variants` AS
SELECT
    p.id,
    p.parent_id,
    p.is_variant,
    p.ncm,
    p.name,
    COALESCE(p.variant_description, p.description) as description,
    p.rfb_min,
    p.rfb_max,
    p.weight_kg,
    p.unit,
    p.ii_rate,
    p.ipi_rate,
    p.pis_rate,
    p.cofins_rate,
    p.icms_rate,
    p.is_active,
    p.created_at,
    p.updated_at,
    COALESCE(parent.name, p.name) as parent_name,
    COALESCE(parent.ncm, p.ncm) as parent_ncm
FROM `products` p
LEFT JOIN `products` parent ON p.parent_id = parent.id
WHERE p.deleted = FALSE;

-- 7. Adicionar comentários explicativos
ALTER TABLE `products`
COMMENT = 'Tabela de produtos com suporte a hierarquia pai/filho para variações de descrição';