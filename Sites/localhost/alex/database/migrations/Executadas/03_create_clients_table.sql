-- Migration: Create clients table
-- Sistema Aduaneiro - Tabela de Clientes

CREATE TABLE IF NOT EXISTS `clients` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `type` ENUM('PF', 'PJ') NOT NULL DEFAULT 'PJ',
    `name` VARCHAR(200) NOT NULL,
    `document` VARCHAR(20) NOT NULL COMMENT 'CPF ou CNPJ',
    `ie` VARCHAR(20) NULL COMMENT 'Inscrição Estadual',
    `im` VARCHAR(20) NULL COMMENT 'Inscrição Municipal',

    -- Endereço
    `address` VARCHAR(255) NULL,
    `number` VARCHAR(20) NULL,
    `complement` VARCHAR(100) NULL,
    `neighborhood` VARCHAR(100) NULL,
    `city` VARCHAR(100) NULL,
    `state` CHAR(2) NULL,
    `zip_code` VARCHAR(9) NULL,

    -- Contato
    `phone` VARCHAR(20) NULL,
    `mobile` VARCHAR(20) NULL,
    `email` VARCHAR(150) NULL,
    `contact_name` VARCHAR(100) NULL,

    -- Configurações padrão
    `incoterm` VARCHAR(10) DEFAULT 'FOB',
    `payment_terms` VARCHAR(100) NULL,

    -- Controle
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted` BOOLEAN DEFAULT FALSE,
    `deleted_at` DATETIME NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_document` (`document`),
    INDEX `idx_name` (`name`),
    INDEX `idx_type` (`type`),
    INDEX `idx_active` (`is_active`),
    INDEX `idx_deleted` (`deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;