-- Tabela para armazenar configurações de integrações
CREATE TABLE IF NOT EXISTS integration_configs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organization_id INT NOT NULL DEFAULT 1,
    integration_type VARCHAR(50) NOT NULL,
    config_key VARCHAR(100) NOT NULL,
    config_value TEXT NOT NULL,
    is_encrypted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_org_integration_key (organization_id, integration_type, config_key),
    INDEX idx_org_integration (organization_id, integration_type)
);

-- Inserir configurações padrão se não existirem
INSERT IGNORE INTO integration_configs (organization_id, integration_type, config_key, config_value) VALUES
(1, 'whatsapp', 'token', ''),
(1, 'whatsapp', 'instance_id', ''),
(1, 'n8n', 'webhook_url', 'http://localhost/financeiro/webhook/n8n/transaction');