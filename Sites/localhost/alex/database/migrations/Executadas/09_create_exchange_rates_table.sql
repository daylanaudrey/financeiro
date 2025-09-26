-- Migration: Create exchange_rates table
-- Sistema Aduaneiro - Tabela de Taxas de Câmbio PTAX

CREATE TABLE IF NOT EXISTS `exchange_rates` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `date` DATE NOT NULL COMMENT 'Data da cotação',
    `currency` VARCHAR(3) NOT NULL DEFAULT 'USD' COMMENT 'Moeda',
    `rate` DECIMAL(10,4) NOT NULL COMMENT 'Taxa de câmbio',
    `source` VARCHAR(50) DEFAULT 'BACEN' COMMENT 'Fonte da cotação',
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_date_currency` (`date`, `currency`),
    INDEX `idx_date` (`date`),
    INDEX `idx_currency` (`currency`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir algumas taxas iniciais para teste
INSERT INTO `exchange_rates` (`date`, `currency`, `rate`, `source`) VALUES
(CURDATE() - INTERVAL 1 DAY, 'USD', 5.1234, 'BACEN'),
(CURDATE() - INTERVAL 2 DAY, 'USD', 5.0987, 'BACEN'),
(CURDATE() - INTERVAL 3 DAY, 'USD', 5.1456, 'BACEN')
ON DUPLICATE KEY UPDATE
    `rate` = VALUES(`rate`),
    `updated_at` = CURRENT_TIMESTAMP;