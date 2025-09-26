-- Migration: Create products table
-- Sistema Aduaneiro - Tabela de Produtos

CREATE TABLE IF NOT EXISTS `products` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(200) NOT NULL,
    `ncm` VARCHAR(8) NOT NULL,
    `description` TEXT NULL,

    -- Valores RFB
    `rfb_min` DECIMAL(12,2) DEFAULT 0.00,
    `rfb_max` DECIMAL(12,2) DEFAULT 0.00,

    -- Pesos e medidas
    `weight_kg` DECIMAL(10,3) DEFAULT 0.000,
    `unit` VARCHAR(10) DEFAULT 'UN',

    -- Alíquotas de impostos (em percentual)
    `ii_rate` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Imposto Importação %',
    `ipi_rate` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'IPI %',
    `pis_rate` DECIMAL(5,2) DEFAULT 2.10 COMMENT 'PIS %',
    `cofins_rate` DECIMAL(5,2) DEFAULT 9.65 COMMENT 'COFINS %',
    `icms_rate` DECIMAL(5,2) DEFAULT 18.00 COMMENT 'ICMS %',

    -- Controle
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted` BOOLEAN DEFAULT FALSE,
    `deleted_at` DATETIME NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_ncm` (`ncm`),
    INDEX `idx_name` (`name`),
    INDEX `idx_active` (`is_active`),
    INDEX `idx_deleted` (`deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;