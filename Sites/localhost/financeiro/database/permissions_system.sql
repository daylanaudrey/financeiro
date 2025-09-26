-- Sistema de Permissões Granulares
-- Execute após multi_tenant_schema.sql

-- Função para definir permissões padrão por role
DELIMITER //
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

-- Trigger para criar permissões automaticamente quando usuário é associado à organização
DELIMITER //
CREATE TRIGGER after_user_org_role_insert
    AFTER INSERT ON user_org_roles
    FOR EACH ROW
BEGIN
    CALL SetDefaultPermissions(NEW.user_id, NEW.org_id, NEW.role);
END //
DELIMITER ;

-- Trigger para atualizar permissões quando role é alterado
DELIMITER //
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

-- View para facilitar consulta de permissões
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

-- View para resumo de permissões por usuário
CREATE VIEW user_permissions_summary AS
SELECT 
    u.id as user_id,
    u.nome as user_name,
    u.email,
    o.id as org_id,
    o.nome as org_name,
    uor.role,
    GROUP_CONCAT(
        CONCAT(up.module, ':', up.permission) 
        ORDER BY up.module, up.permission SEPARATOR '|'
    ) as permissions
FROM users u
JOIN user_org_roles uor ON u.id = uor.user_id
JOIN organizations o ON uor.org_id = o.id
LEFT JOIN user_permissions up ON u.id = up.user_id AND o.id = up.org_id AND up.granted = TRUE
WHERE u.deleted_at IS NULL AND o.deleted_at IS NULL
GROUP BY u.id, o.id;