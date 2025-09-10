-- Sistema Financeiro - Schema Base
-- Database: dag_financeiro

-- Tabela de usuários
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('ativo', 'inativo', 'pendente') DEFAULT 'ativo',
    role ENUM('admin', 'financeiro', 'operador', 'leitor') DEFAULT 'operador',
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_deleted_at (deleted_at)
);

-- Tabela de organizações/empresas
CREATE TABLE organizations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cnpj VARCHAR(18) NULL,
    email VARCHAR(255) NULL,
    telefone VARCHAR(20) NULL,
    endereco TEXT NULL,
    status ENUM('ativa', 'inativa') DEFAULT 'ativa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_cnpj (cnpj),
    INDEX idx_status (status),
    INDEX idx_deleted_at (deleted_at)
);

-- Relacionamento usuário-organização com papéis
CREATE TABLE user_org_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    org_id INT NOT NULL,
    role ENUM('admin', 'financeiro', 'operador', 'leitor') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (org_id) REFERENCES organizations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_org (user_id, org_id),
    INDEX idx_user_id (user_id),
    INDEX idx_org_id (org_id),
    INDEX idx_role (role)
);

-- Tabela de auditoria/logs
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    org_id INT NULL,
    user_id INT NULL,
    entity VARCHAR(100) NOT NULL,
    entity_id INT NULL,
    action ENUM('create', 'update', 'delete', 'login', 'logout', 'view') NOT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    description TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (org_id) REFERENCES organizations(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_org_id (org_id),
    INDEX idx_entity (entity),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- Sessões de usuários
CREATE TABLE user_sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload TEXT NOT NULL,
    last_activity INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity)
);

-- Inserir usuário admin padrão
INSERT INTO users (nome, email, password, role, status, email_verified_at) VALUES 
('Administrador', 'admin@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'ativo', NOW());

-- Inserir organização padrão
INSERT INTO organizations (nome, status) VALUES 
('Organização Principal', 'ativa');

-- Associar admin à organização
INSERT INTO user_org_roles (user_id, org_id, role) VALUES 
(1, 1, 'admin');

-- Registrar criação inicial no audit log
INSERT INTO audit_logs (org_id, user_id, entity, action, description, ip_address) VALUES
(1, 1, 'system', 'create', 'Instalação inicial do sistema', '127.0.0.1');