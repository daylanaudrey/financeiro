-- Sistema Multi-Tenant - Novas Tabelas
-- Execute após schema.sql e financial_schema.sql

-- Tabela de planos de assinatura
CREATE TABLE subscription_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT NULL,
    preco DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    moeda VARCHAR(3) DEFAULT 'BRL',
    periodo ENUM('mensal', 'anual') DEFAULT 'mensal',
    max_usuarios INT NULL COMMENT 'NULL = ilimitado',
    max_transacoes INT NULL COMMENT 'NULL = ilimitado',
    max_organizacoes INT DEFAULT 1,
    trial_days INT DEFAULT 0,
    features JSON NULL COMMENT 'Recursos disponíveis no plano',
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_ativo (ativo)
);

-- Tabela de assinaturas das organizações
CREATE TABLE organization_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    org_id INT NOT NULL,
    plan_id INT NOT NULL,
    status ENUM('trial', 'active', 'suspended', 'cancelled', 'expired') DEFAULT 'trial',
    trial_ends_at DATETIME NULL,
    current_period_start DATETIME NOT NULL,
    current_period_end DATETIME NOT NULL,
    cancelled_at DATETIME NULL,
    cancel_reason TEXT NULL,
    uso_usuarios INT DEFAULT 0,
    uso_transacoes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (org_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id) ON DELETE RESTRICT,
    INDEX idx_org_id (org_id),
    INDEX idx_plan_id (plan_id),
    INDEX idx_status (status),
    INDEX idx_trial_ends_at (trial_ends_at),
    INDEX idx_current_period_end (current_period_end)
);

-- Tabela de histórico de pagamentos
CREATE TABLE subscription_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subscription_id INT NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    moeda VARCHAR(3) DEFAULT 'BRL',
    status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(100) NULL,
    external_id VARCHAR(255) NULL COMMENT 'ID do gateway de pagamento',
    paid_at DATETIME NULL,
    due_date DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subscription_id) REFERENCES organization_subscriptions(id) ON DELETE CASCADE,
    INDEX idx_subscription_id (subscription_id),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date),
    INDEX idx_external_id (external_id)
);

-- Tabela de permissões granulares
CREATE TABLE user_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    org_id INT NOT NULL,
    module VARCHAR(100) NOT NULL COMMENT 'dashboard, transactions, accounts, etc',
    permission ENUM('view', 'create', 'edit', 'delete', 'export') NOT NULL,
    granted BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (org_id) REFERENCES organizations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_org_module_permission (user_id, org_id, module, permission),
    INDEX idx_user_id (user_id),
    INDEX idx_org_id (org_id),
    INDEX idx_module (module)
);

-- Tabela de configurações do sistema
CREATE TABLE system_configs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(255) NOT NULL UNIQUE,
    key_value TEXT NULL,
    description TEXT NULL,
    type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    is_public BOOLEAN DEFAULT FALSE COMMENT 'Se pode ser acessado pelo frontend',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key_name (key_name),
    INDEX idx_is_public (is_public)
);

-- Tabela de convites para organizações
CREATE TABLE organization_invites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    org_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    role ENUM('admin', 'financeiro', 'operador', 'leitor') NOT NULL,
    token VARCHAR(100) NOT NULL UNIQUE,
    invited_by INT NOT NULL,
    accepted_at DATETIME NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (org_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_org_id (org_id),
    INDEX idx_email (email),
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at)
);

-- Inserir planos padrão
INSERT INTO subscription_plans (nome, slug, descricao, preco, max_usuarios, max_transacoes, trial_days, features) VALUES
('Trial Gratuito', 'trial', 'Teste grátis por 7 dias', 0.00, NULL, 100, 7, '["dashboard", "transactions", "accounts", "categories", "contacts"]'),
('Starter', 'starter', 'Ideal para pequenos negócios', 29.00, 3, 500, 7, '["dashboard", "transactions", "accounts", "categories", "contacts", "reports"]'),
('Professional', 'professional', 'Para empresas em crescimento', 59.00, 10, 2000, 7, '["dashboard", "transactions", "accounts", "categories", "contacts", "reports", "advanced_reports", "api_access"]'),
('Enterprise', 'enterprise', 'Solução completa para grandes empresas', 99.00, NULL, NULL, 7, '["dashboard", "transactions", "accounts", "categories", "contacts", "reports", "advanced_reports", "api_access", "custom_integrations", "priority_support"]');

-- Inserir configurações do sistema
INSERT INTO system_configs (key_name, key_value, description, type, is_public) VALUES
('app_name', 'Sistema Financeiro', 'Nome da aplicação', 'string', TRUE),
('app_version', '2.0.0', 'Versão da aplicação', 'string', TRUE),
('trial_days', '7', 'Dias de trial gratuito', 'integer', FALSE),
('smtp_host', '', 'Servidor SMTP', 'string', FALSE),
('smtp_port', '587', 'Porta SMTP', 'integer', FALSE),
('smtp_username', '', 'Usuário SMTP', 'string', FALSE),
('smtp_password', '', 'Senha SMTP', 'string', FALSE),
('smtp_encryption', 'tls', 'Criptografia SMTP', 'string', FALSE),
('default_timezone', 'America/Sao_Paulo', 'Fuso horário padrão', 'string', TRUE),
('currency_default', 'BRL', 'Moeda padrão', 'string', TRUE),
('max_upload_size', '10485760', 'Tamanho máximo de upload (bytes)', 'integer', TRUE);

-- Atualizar usuário admin existente para super admin
UPDATE users SET email = 'daylan@dagsolucaodigital.com.br', role = 'admin' WHERE id = 1;

-- Criar assinatura trial para organização padrão
INSERT INTO organization_subscriptions (org_id, plan_id, status, trial_ends_at, current_period_start, current_period_end) VALUES
(1, 1, 'trial', DATE_ADD(NOW(), INTERVAL 7 DAY), NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH));