-- Script de instalação do sistema multi-tenant
-- Para ser executado manualmente no banco de dados

-- 1. Executar em ordem:
-- SOURCE /caminho/para/schema.sql;
-- SOURCE /caminho/para/financial_schema.sql;
-- SOURCE /caminho/para/multi_tenant_schema.sql;
-- SOURCE /caminho/para/permissions_system.sql;
-- SOURCE /caminho/para/subscription_system.sql;

-- OU executar este arquivo completo:

-- ==========================================
-- TABELAS DO SISTEMA MULTI-TENANT
-- ==========================================

-- Tabela de planos de assinatura
CREATE TABLE IF NOT EXISTS subscription_plans (
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
CREATE TABLE IF NOT EXISTS organization_subscriptions (
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
CREATE TABLE IF NOT EXISTS subscription_payments (
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
CREATE TABLE IF NOT EXISTS user_permissions (
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
CREATE TABLE IF NOT EXISTS system_configs (
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
CREATE TABLE IF NOT EXISTS organization_invites (
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

-- ==========================================
-- INSERÇÃO DE DADOS INICIAIS
-- ==========================================

-- Inserir planos padrão
INSERT IGNORE INTO subscription_plans (nome, slug, descricao, preco, max_usuarios, max_transacoes, trial_days, features) VALUES
('Trial Gratuito', 'trial', 'Teste grátis por 7 dias', 0.00, NULL, 100, 7, '["dashboard", "transactions", "accounts", "categories", "contacts"]'),
('Starter', 'starter', 'Ideal para pequenos negócios', 29.00, 3, 500, 7, '["dashboard", "transactions", "accounts", "categories", "contacts", "reports"]'),
('Professional', 'professional', 'Para empresas em crescimento', 59.00, 10, 2000, 7, '["dashboard", "transactions", "accounts", "categories", "contacts", "reports", "advanced_reports", "api_access"]'),
('Enterprise', 'enterprise', 'Solução completa para grandes empresas', 99.00, NULL, NULL, 7, '["dashboard", "transactions", "accounts", "categories", "contacts", "reports", "advanced_reports", "api_access", "custom_integrations", "priority_support"]');

-- Inserir configurações do sistema
INSERT IGNORE INTO system_configs (key_name, key_value, description, type, is_public) VALUES
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
UPDATE users SET email = 'daylan@dagsolucaodigital.com.br' WHERE id = 1;

-- Criar assinatura trial para organização padrão (se não existir)
INSERT IGNORE INTO organization_subscriptions (org_id, plan_id, status, trial_ends_at, current_period_start, current_period_end) VALUES
(1, 1, 'trial', DATE_ADD(NOW(), INTERVAL 7 DAY), NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH));

-- ==========================================
-- FUNÇÕES E PROCEDURES
-- ==========================================

-- Função para verificar se organização pode criar novo usuário
DELIMITER //
DROP FUNCTION IF EXISTS CanCreateUser//
CREATE FUNCTION CanCreateUser(org_id INT) RETURNS BOOLEAN
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE user_count INT DEFAULT 0;
    DECLARE max_users INT DEFAULT NULL;
    DECLARE current_status VARCHAR(20);
    
    -- Buscar informações da assinatura atual
    SELECT 
        COUNT(DISTINCT u.id),
        sp.max_usuarios,
        os.status
    INTO user_count, max_users, current_status
    FROM organization_subscriptions os
    JOIN subscription_plans sp ON os.plan_id = sp.id
    LEFT JOIN user_org_roles uor ON os.org_id = uor.org_id
    LEFT JOIN users u ON uor.user_id = u.id AND u.deleted_at IS NULL
    WHERE os.org_id = org_id 
      AND os.status IN ('trial', 'active')
    GROUP BY sp.max_usuarios, os.status;
    
    -- Se não tem assinatura ativa, não pode criar
    IF current_status IS NULL OR current_status NOT IN ('trial', 'active') THEN
        RETURN FALSE;
    END IF;
    
    -- Se plano tem limite ilimitado (NULL), pode criar
    IF max_users IS NULL THEN
        RETURN TRUE;
    END IF;
    
    -- Verificar se está dentro do limite
    RETURN user_count < max_users;
END //
DELIMITER ;

-- Função para verificar se organização pode criar nova transação
DELIMITER //
DROP FUNCTION IF EXISTS CanCreateTransaction//
CREATE FUNCTION CanCreateTransaction(org_id INT) RETURNS BOOLEAN
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE transaction_count INT DEFAULT 0;
    DECLARE max_transactions INT DEFAULT NULL;
    DECLARE current_status VARCHAR(20);
    
    -- Buscar informações da assinatura atual
    SELECT 
        COUNT(t.id),
        sp.max_transacoes,
        os.status
    INTO transaction_count, max_transactions, current_status
    FROM organization_subscriptions os
    JOIN subscription_plans sp ON os.plan_id = sp.id
    LEFT JOIN transactions t ON os.org_id = t.org_id 
        AND t.deleted_at IS NULL 
        AND t.created_at >= os.current_period_start
    WHERE os.org_id = org_id 
      AND os.status IN ('trial', 'active')
    GROUP BY sp.max_transacoes, os.status;
    
    -- Se não tem assinatura ativa, não pode criar
    IF current_status IS NULL OR current_status NOT IN ('trial', 'active') THEN
        RETURN FALSE;
    END IF;
    
    -- Se plano tem limite ilimitado (NULL), pode criar
    IF max_transactions IS NULL THEN
        RETURN TRUE;
    END IF;
    
    -- Verificar se está dentro do limite
    RETURN transaction_count < max_transactions;
END //
DELIMITER ;

-- Procedure para definir permissões padrão por role
DELIMITER //
DROP PROCEDURE IF EXISTS SetDefaultPermissions//
CREATE PROCEDURE SetDefaultPermissions(IN user_id INT, IN org_id INT, IN user_role ENUM('admin', 'financeiro', 'operador', 'leitor'))
BEGIN
    -- Limpar permissões existentes
    DELETE FROM user_permissions WHERE user_id = user_id AND org_id = org_id;
    
    -- Permissões para Admin da Organização
    IF user_role = 'admin' THEN
        INSERT INTO user_permissions (user_id, org_id, module, permission, granted) VALUES
        (user_id, org_id, 'dashboard', 'view', TRUE),
        (user_id, org_id, 'transactions', 'view', TRUE),
        (user_id, org_id, 'transactions', 'create', TRUE),
        (user_id, org_id, 'transactions', 'edit', TRUE),
        (user_id, org_id, 'transactions', 'delete', TRUE),
        (user_id, org_id, 'transactions', 'export', TRUE),
        (user_id, org_id, 'accounts', 'view', TRUE),
        (user_id, org_id, 'accounts', 'create', TRUE),
        (user_id, org_id, 'accounts', 'edit', TRUE),
        (user_id, org_id, 'accounts', 'delete', TRUE),
        (user_id, org_id, 'categories', 'view', TRUE),
        (user_id, org_id, 'categories', 'create', TRUE),
        (user_id, org_id, 'categories', 'edit', TRUE),
        (user_id, org_id, 'categories', 'delete', TRUE),
        (user_id, org_id, 'contacts', 'view', TRUE),
        (user_id, org_id, 'contacts', 'create', TRUE),
        (user_id, org_id, 'contacts', 'edit', TRUE),
        (user_id, org_id, 'contacts', 'delete', TRUE),
        (user_id, org_id, 'reports', 'view', TRUE),
        (user_id, org_id, 'reports', 'export', TRUE),
        (user_id, org_id, 'settings', 'view', TRUE),
        (user_id, org_id, 'settings', 'edit', TRUE),
        (user_id, org_id, 'users', 'view', TRUE),
        (user_id, org_id, 'users', 'create', TRUE),
        (user_id, org_id, 'users', 'edit', TRUE),
        (user_id, org_id, 'users', 'delete', TRUE);
    END IF;
    
    -- Permissões para Financeiro
    IF user_role = 'financeiro' THEN
        INSERT INTO user_permissions (user_id, org_id, module, permission, granted) VALUES
        (user_id, org_id, 'dashboard', 'view', TRUE),
        (user_id, org_id, 'transactions', 'view', TRUE),
        (user_id, org_id, 'transactions', 'create', TRUE),
        (user_id, org_id, 'transactions', 'edit', TRUE),
        (user_id, org_id, 'transactions', 'delete', TRUE),
        (user_id, org_id, 'transactions', 'export', TRUE),
        (user_id, org_id, 'accounts', 'view', TRUE),
        (user_id, org_id, 'accounts', 'create', TRUE),
        (user_id, org_id, 'accounts', 'edit', TRUE),
        (user_id, org_id, 'categories', 'view', TRUE),
        (user_id, org_id, 'categories', 'create', TRUE),
        (user_id, org_id, 'categories', 'edit', TRUE),
        (user_id, org_id, 'contacts', 'view', TRUE),
        (user_id, org_id, 'contacts', 'create', TRUE),
        (user_id, org_id, 'contacts', 'edit', TRUE),
        (user_id, org_id, 'reports', 'view', TRUE),
        (user_id, org_id, 'reports', 'export', TRUE);
    END IF;
    
    -- Permissões para Operador
    IF user_role = 'operador' THEN
        INSERT INTO user_permissions (user_id, org_id, module, permission, granted) VALUES
        (user_id, org_id, 'dashboard', 'view', TRUE),
        (user_id, org_id, 'transactions', 'view', TRUE),
        (user_id, org_id, 'transactions', 'create', TRUE),
        (user_id, org_id, 'transactions', 'edit', TRUE),
        (user_id, org_id, 'accounts', 'view', TRUE),
        (user_id, org_id, 'categories', 'view', TRUE),
        (user_id, org_id, 'contacts', 'view', TRUE),
        (user_id, org_id, 'contacts', 'create', TRUE),
        (user_id, org_id, 'contacts', 'edit', TRUE),
        (user_id, org_id, 'reports', 'view', TRUE);
    END IF;
    
    -- Permissões para Leitor
    IF user_role = 'leitor' THEN
        INSERT INTO user_permissions (user_id, org_id, module, permission, granted) VALUES
        (user_id, org_id, 'dashboard', 'view', TRUE),
        (user_id, org_id, 'transactions', 'view', TRUE),
        (user_id, org_id, 'accounts', 'view', TRUE),
        (user_id, org_id, 'categories', 'view', TRUE),
        (user_id, org_id, 'contacts', 'view', TRUE),
        (user_id, org_id, 'reports', 'view', TRUE);
    END IF;
END //
DELIMITER ;

-- Criar triggers
DELIMITER //
DROP TRIGGER IF EXISTS after_user_org_role_insert//
CREATE TRIGGER after_user_org_role_insert
    AFTER INSERT ON user_org_roles
    FOR EACH ROW
BEGIN
    CALL SetDefaultPermissions(NEW.user_id, NEW.org_id, NEW.role);
END //
DELIMITER ;

DELIMITER //
DROP TRIGGER IF EXISTS after_user_org_role_update//
CREATE TRIGGER after_user_org_role_update
    AFTER UPDATE ON user_org_roles
    FOR EACH ROW
BEGIN
    IF NEW.role != OLD.role THEN
        CALL SetDefaultPermissions(NEW.user_id, NEW.org_id, NEW.role);
    END IF;
END //
DELIMITER ;

-- Criar permissões para o usuário admin existente
CALL SetDefaultPermissions(1, 1, 'admin');

-- ==========================================
-- VIEWS
-- ==========================================

-- View para relatório de uso por organização
DROP VIEW IF EXISTS organization_usage_report;
CREATE VIEW organization_usage_report AS
SELECT 
    o.id as org_id,
    o.nome as org_name,
    sp.nome as plan_name,
    sp.preco as plan_price,
    os.status as subscription_status,
    os.uso_usuarios,
    sp.max_usuarios,
    os.uso_transacoes,
    sp.max_transacoes,
    os.current_period_start,
    os.current_period_end,
    os.trial_ends_at,
    CASE 
        WHEN os.status = 'trial' THEN DATEDIFF(os.trial_ends_at, NOW())
        ELSE DATEDIFF(os.current_period_end, NOW())
    END as days_remaining,
    CASE 
        WHEN sp.max_usuarios IS NULL THEN 100.0
        ELSE (os.uso_usuarios / sp.max_usuarios) * 100
    END as users_usage_percent,
    CASE 
        WHEN sp.max_transacoes IS NULL THEN 100.0
        ELSE (os.uso_transacoes / sp.max_transacoes) * 100
    END as transactions_usage_percent
FROM organizations o
LEFT JOIN organization_subscriptions os ON o.id = os.org_id
LEFT JOIN subscription_plans sp ON os.plan_id = sp.id
WHERE o.deleted_at IS NULL
  AND (os.status IN ('trial', 'active', 'suspended') OR os.id IS NULL);

-- View para facilitar consulta de permissões
DROP VIEW IF EXISTS user_permissions_view;
CREATE VIEW user_permissions_view AS
SELECT 
    u.id as user_id,
    u.nome as user_name,
    u.email,
    o.id as org_id,
    o.nome as org_name,
    uor.role,
    up.module,
    up.permission,
    up.granted
FROM users u
JOIN user_org_roles uor ON u.id = uor.user_id
JOIN organizations o ON uor.org_id = o.id
LEFT JOIN user_permissions up ON u.id = up.user_id AND o.id = up.org_id
WHERE u.deleted_at IS NULL AND o.deleted_at IS NULL;

SELECT 'Multi-tenant system installed successfully!' as message;