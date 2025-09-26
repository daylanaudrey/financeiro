-- Migration: Create process_expenses table
-- Sistema Aduaneiro - Tabela de Despesas dos Processos

CREATE TABLE IF NOT EXISTS `process_expenses` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `process_id` INT NOT NULL,

    -- Tipo e descrição
    `type` VARCHAR(50) NOT NULL COMMENT 'Tipo de despesa',
    `description` VARCHAR(200) NOT NULL,

    -- Valores
    `amount_usd` DECIMAL(15,2) DEFAULT 0.00,
    `amount_brl` DECIMAL(15,2) DEFAULT 0.00,

    -- Controle
    `is_editable` BOOLEAN DEFAULT TRUE COMMENT 'Se pode ser editado pelo usuário',
    `notes` TEXT NULL,

    PRIMARY KEY (`id`),
    FOREIGN KEY (`process_id`) REFERENCES `processes`(`id`) ON DELETE CASCADE,
    INDEX `idx_process` (`process_id`),
    INDEX `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar tabela de tipos de despesas padrão
CREATE TABLE IF NOT EXISTS `expense_types` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `type` VARCHAR(50) NOT NULL UNIQUE,
    `description` VARCHAR(200) NOT NULL,
    `default_amount_brl` DECIMAL(15,2) DEFAULT 0.00,
    `is_editable` BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir tipos padrões de despesas
INSERT IGNORE INTO `expense_types` (`type`, `description`, `default_amount_brl`, `is_editable`) VALUES
('SISCOMEX', 'Taxa SISCOMEX', 185.00, FALSE),
('ARMAZEM', 'Armazenagem', 0.00, TRUE),
('DESPACHANTE', 'Honorários Despachante', 0.00, TRUE),
('TRANSPORTE', 'Transporte interno', 0.00, TRUE),
('OUTROS', 'Outras despesas', 0.00, TRUE);