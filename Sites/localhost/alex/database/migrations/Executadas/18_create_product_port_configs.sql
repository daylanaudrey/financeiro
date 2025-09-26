-- Criação da tabela product_port_configs
-- Configurações RFB específicas por produto e porto

CREATE TABLE IF NOT EXISTS product_port_configs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    port_id INT NOT NULL,
    rfb_min DECIMAL(15,2) NULL COMMENT 'RFB mínimo específico para este porto',
    rfb_max DECIMAL(15,2) NULL COMMENT 'RFB máximo específico para este porto',
    division_type ENUM('PC', 'KG') NOT NULL DEFAULT 'PC' COMMENT 'Forma de divisão: PC (quantidade) ou KG (peso)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_product_port (product_id, port_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (port_id) REFERENCES ports(id) ON DELETE CASCADE,

    INDEX idx_product_id (product_id),
    INDEX idx_port_id (port_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;