-- Migration: Create port_product_configs table
-- Sistema Aduaneiro - Configurações RFB específicas por porto e produto

-- Criar tabela se não existir
CREATE TABLE IF NOT EXISTS port_product_configs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    port_id INT NOT NULL,
    product_id INT NOT NULL,
    rfb_min_override DECIMAL(15,2) NULL COMMENT 'RFB mínimo específico para este porto',
    rfb_max_override DECIMAL(15,2) NULL COMMENT 'RFB máximo específico para este porto',
    division_type ENUM('PC', 'KG') NOT NULL DEFAULT 'PC' COMMENT 'Forma de divisão: PC (quantidade) ou KG (peso)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted BOOLEAN DEFAULT FALSE,
    deleted_at TIMESTAMP NULL,

    UNIQUE KEY unique_port_product (port_id, product_id),
    FOREIGN KEY (port_id) REFERENCES ports(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,

    INDEX idx_product_id (product_id),
    INDEX idx_port_id (port_id),
    INDEX idx_deleted (deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Se a tabela já existir mas não tiver a coluna division_type, adicionar
ALTER TABLE port_product_configs
ADD COLUMN IF NOT EXISTS division_type ENUM('PC', 'KG') NOT NULL DEFAULT 'PC' COMMENT 'Forma de divisão: PC (quantidade) ou KG (peso)';

-- Verificar estrutura final
DESCRIBE port_product_configs;