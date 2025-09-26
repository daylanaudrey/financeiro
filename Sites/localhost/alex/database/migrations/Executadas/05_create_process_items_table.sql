-- Migration: Create process_items table
-- Sistema Aduaneiro - Tabela de Itens dos Processos

CREATE TABLE IF NOT EXISTS `process_items` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `process_id` INT NOT NULL,
    `product_id` INT NOT NULL,

    -- Quantidades
    `quantity` DECIMAL(15,3) NOT NULL,
    `unit` VARCHAR(10) DEFAULT 'UN',
    `weight_kg` DECIMAL(10,3) DEFAULT 0.000,

    -- Valores em USD
    `unit_price_usd` DECIMAL(15,4) NOT NULL,
    `total_fob_usd` DECIMAL(15,2) NOT NULL,
    `freight_usd` DECIMAL(15,2) DEFAULT 0.00,
    `insurance_usd` DECIMAL(15,2) DEFAULT 0.00,
    `cif_usd` DECIMAL(15,2) DEFAULT 0.00,

    -- Valores em BRL (calculados)
    `cif_brl` DECIMAL(15,2) DEFAULT 0.00,

    -- Impostos calculados em BRL
    `ii_value` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Imposto Importação',
    `ipi_value` DECIMAL(15,2) DEFAULT 0.00,
    `pis_value` DECIMAL(15,2) DEFAULT 0.00,
    `cofins_value` DECIMAL(15,2) DEFAULT 0.00,
    `icms_value` DECIMAL(15,2) DEFAULT 0.00,
    `total_taxes` DECIMAL(15,2) DEFAULT 0.00,

    -- Custo total
    `total_cost_brl` DECIMAL(15,2) DEFAULT 0.00,

    -- Observações
    `notes` TEXT NULL,

    PRIMARY KEY (`id`),
    FOREIGN KEY (`process_id`) REFERENCES `processes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT,
    INDEX `idx_process` (`process_id`),
    INDEX `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;