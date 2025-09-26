-- Migration: Create processes table
-- Sistema Aduaneiro - Tabela de Processos de Importação

CREATE TABLE IF NOT EXISTS `processes` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Código do processo',
    `client_id` INT NOT NULL,
    `type` ENUM('NUMERARIO', 'MAPA') DEFAULT 'NUMERARIO',
    `status` ENUM('DRAFT', 'PROCESSING', 'COMPLETED', 'CANCELLED') DEFAULT 'DRAFT',

    -- Datas
    `process_date` DATE NOT NULL,
    `arrival_date` DATE NULL,
    `clearance_date` DATE NULL,

    -- Informações de transporte
    `modal` ENUM('MARITIME', 'AIR', 'ROAD', 'RAIL') DEFAULT 'MARITIME',
    `container_number` VARCHAR(50) NULL,
    `bl_number` VARCHAR(50) NULL COMMENT 'Bill of Lading',
    `incoterm` VARCHAR(10) DEFAULT 'FOB',

    -- Valores totais (calculados)
    `total_fob_usd` DECIMAL(15,2) DEFAULT 0.00,
    `total_freight_usd` DECIMAL(15,2) DEFAULT 0.00,
    `total_insurance_usd` DECIMAL(15,2) DEFAULT 0.00,
    `total_cif_usd` DECIMAL(15,2) DEFAULT 0.00,

    -- Taxa de câmbio
    `exchange_rate` DECIMAL(10,4) NOT NULL COMMENT 'USD to BRL',

    -- Valores em BRL
    `total_cif_brl` DECIMAL(15,2) DEFAULT 0.00,
    `total_taxes_brl` DECIMAL(15,2) DEFAULT 0.00,
    `total_expenses_brl` DECIMAL(15,2) DEFAULT 0.00,
    `total_cost_brl` DECIMAL(15,2) DEFAULT 0.00,

    -- Observações
    `notes` TEXT NULL,

    -- Controle
    `created_by` INT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted` BOOLEAN DEFAULT FALSE,
    `deleted_at` DATETIME NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_code` (`code`),
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_status` (`status`),
    INDEX `idx_process_date` (`process_date`),
    INDEX `idx_deleted` (`deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;