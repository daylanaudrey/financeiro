-- Tabela de permissões customizadas por role
CREATE TABLE IF NOT EXISTS role_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role VARCHAR(50) NOT NULL,
    module VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    allowed TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_role_permission (role, module, action),
    INDEX idx_role (role),
    INDEX idx_module_action (module, action)
);

-- Tabela de permissões específicas por usuário
CREATE TABLE IF NOT EXISTS user_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    module VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    allowed TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    UNIQUE KEY unique_user_permission (user_id, module, action),
    INDEX idx_user (user_id),
    INDEX idx_module_action (module, action),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Inserir permissões padrão para roles existentes
-- Admin tem todas as permissões
INSERT IGNORE INTO role_permissions (role, module, action, allowed) VALUES
-- Users
('admin', 'users', 'view', 1),
('admin', 'users', 'create', 1),
('admin', 'users', 'edit', 1),
('admin', 'users', 'delete', 1),
-- Products
('admin', 'products', 'view', 1),
('admin', 'products', 'create', 1),
('admin', 'products', 'edit', 1),
('admin', 'products', 'delete', 1),
-- Clients
('admin', 'clients', 'view', 1),
('admin', 'clients', 'create', 1),
('admin', 'clients', 'edit', 1),
('admin', 'clients', 'delete', 1),
-- Processes
('admin', 'processes', 'view', 1),
('admin', 'processes', 'create', 1),
('admin', 'processes', 'edit', 1),
('admin', 'processes', 'delete', 1),
-- Reports
('admin', 'reports', 'view', 1),
('admin', 'reports', 'export', 1),
-- Audit
('admin', 'audit', 'view', 1),
('admin', 'audit', 'export', 1),
('admin', 'audit', 'cleanup', 1),
-- System
('admin', 'system', 'admin', 1),
('admin', 'system', 'ports', 1),
('admin', 'system', 'exchange_rates', 1);

-- Operator tem permissões limitadas
INSERT IGNORE INTO role_permissions (role, module, action, allowed) VALUES
-- Products
('operator', 'products', 'view', 1),
('operator', 'products', 'create', 1),
('operator', 'products', 'edit', 1),
('operator', 'products', 'delete', 1),
-- Clients
('operator', 'clients', 'view', 1),
('operator', 'clients', 'create', 1),
('operator', 'clients', 'edit', 1),
('operator', 'clients', 'delete', 1),
-- Processes
('operator', 'processes', 'view', 1),
('operator', 'processes', 'create', 1),
('operator', 'processes', 'edit', 1),
('operator', 'processes', 'delete', 1),
-- Reports
('operator', 'reports', 'view', 1),
('operator', 'reports', 'export', 1),
-- Permissões negadas
('operator', 'users', 'view', 0),
('operator', 'users', 'create', 0),
('operator', 'users', 'edit', 0),
('operator', 'users', 'delete', 0),
('operator', 'audit', 'view', 0),
('operator', 'audit', 'export', 0),
('operator', 'audit', 'cleanup', 0);

-- Viewer tem apenas visualização
INSERT IGNORE INTO role_permissions (role, module, action, allowed) VALUES
-- Products
('viewer', 'products', 'view', 1),
('viewer', 'products', 'create', 0),
('viewer', 'products', 'edit', 0),
('viewer', 'products', 'delete', 0),
-- Clients
('viewer', 'clients', 'view', 1),
('viewer', 'clients', 'create', 0),
('viewer', 'clients', 'edit', 0),
('viewer', 'clients', 'delete', 0),
-- Processes
('viewer', 'processes', 'view', 1),
('viewer', 'processes', 'create', 0),
('viewer', 'processes', 'edit', 0),
('viewer', 'processes', 'delete', 0),
-- Reports
('viewer', 'reports', 'view', 1),
('viewer', 'reports', 'export', 0),
-- Todas as outras negadas
('viewer', 'users', 'view', 0),
('viewer', 'users', 'create', 0),
('viewer', 'users', 'edit', 0),
('viewer', 'users', 'delete', 0),
('viewer', 'audit', 'view', 0),
('viewer', 'audit', 'export', 0),
('viewer', 'audit', 'cleanup', 0);