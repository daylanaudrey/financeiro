-- Sistema Financeiro - Tabelas Financeiras
-- Execute após o schema.sql principal

-- Tabela de contas bancárias
CREATE TABLE accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    org_id INT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    tipo ENUM('corrente', 'poupanca', 'carteira', 'cartao_prepago', 'vault') NOT NULL,
    banco VARCHAR(255) NULL,
    agencia VARCHAR(20) NULL,
    conta VARCHAR(30) NULL,
    moeda VARCHAR(3) DEFAULT 'BRL',
    saldo_inicial DECIMAL(15,2) DEFAULT 0.00,
    saldo_atual DECIMAL(15,2) DEFAULT 0.00,
    ativo BOOLEAN DEFAULT TRUE,
    descricao TEXT NULL,
    cor VARCHAR(7) DEFAULT '#007bff',
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (org_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_org_id (org_id),
    INDEX idx_tipo (tipo),
    INDEX idx_ativo (ativo),
    INDEX idx_deleted_at (deleted_at)
);

-- Tabela de categorias
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    org_id INT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    tipo ENUM('receita', 'despesa') NOT NULL,
    parent_id INT NULL,
    cor VARCHAR(7) DEFAULT '#6c757d',
    icone VARCHAR(50) DEFAULT 'fas fa-tag',
    ativo BOOLEAN DEFAULT TRUE,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (org_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_org_id (org_id),
    INDEX idx_tipo (tipo),
    INDEX idx_parent_id (parent_id),
    INDEX idx_ativo (ativo),
    INDEX idx_deleted_at (deleted_at)
);

-- Tabela de contatos (fornecedores/clientes)
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    org_id INT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    tipo ENUM('fornecedor', 'cliente', 'ambos') NOT NULL,
    documento VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    telefone VARCHAR(20) NULL,
    endereco TEXT NULL,
    observacoes TEXT NULL,
    tags JSON NULL,
    ativo BOOLEAN DEFAULT TRUE,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (org_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_org_id (org_id),
    INDEX idx_tipo (tipo),
    INDEX idx_documento (documento),
    INDEX idx_ativo (ativo),
    INDEX idx_deleted_at (deleted_at)
);

-- Tabela de lançamentos/transações
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    org_id INT NOT NULL,
    account_id INT NOT NULL,
    kind ENUM('entrada', 'saida', 'transfer_out', 'transfer_in') NOT NULL,
    valor DECIMAL(15,2) NOT NULL,
    moeda VARCHAR(3) DEFAULT 'BRL',
    data_competencia DATE NOT NULL,
    data_pagamento DATE NULL,
    status ENUM('rascunho', 'agendado', 'confirmado', 'cancelado') DEFAULT 'confirmado',
    category_id INT NULL,
    contact_id INT NULL,
    descricao TEXT NOT NULL,
    observacoes TEXT NULL,
    attachment_url VARCHAR(500) NULL,
    recurrence_instance_id INT NULL,
    transfer_pair_id INT NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (org_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_org_id (org_id),
    INDEX idx_account_id (account_id),
    INDEX idx_kind (kind),
    INDEX idx_status (status),
    INDEX idx_data_competencia (data_competencia),
    INDEX idx_data_pagamento (data_pagamento),
    INDEX idx_category_id (category_id),
    INDEX idx_contact_id (contact_id),
    INDEX idx_transfer_pair_id (transfer_pair_id),
    INDEX idx_deleted_at (deleted_at)
);

-- Inserir categorias padrão
INSERT INTO categories (org_id, nome, tipo, cor, icone, created_by) VALUES
-- Receitas
(1, 'Salário', 'receita', '#28a745', 'fas fa-coins', 1),
(1, 'Freelance', 'receita', '#17a2b8', 'fas fa-laptop-code', 1),
(1, 'Investimentos', 'receita', '#6f42c1', 'fas fa-chart-line', 1),
(1, 'Vendas', 'receita', '#fd7e14', 'fas fa-shopping-cart', 1),
(1, 'Outros Recebimentos', 'receita', '#6c757d', 'fas fa-plus-circle', 1),

-- Despesas
(1, 'Alimentação', 'despesa', '#dc3545', 'fas fa-utensils', 1),
(1, 'Transporte', 'despesa', '#ffc107', 'fas fa-car', 1),
(1, 'Moradia', 'despesa', '#007bff', 'fas fa-home', 1),
(1, 'Saúde', 'despesa', '#e83e8c', 'fas fa-heartbeat', 1),
(1, 'Educação', 'despesa', '#6610f2', 'fas fa-graduation-cap', 1),
(1, 'Lazer', 'despesa', '#20c997', 'fas fa-gamepad', 1),
(1, 'Compras', 'despesa', '#fd7e14', 'fas fa-shopping-bag', 1),
(1, 'Serviços', 'despesa', '#6c757d', 'fas fa-tools', 1),
(1, 'Impostos', 'despesa', '#343a40', 'fas fa-file-invoice-dollar', 1),
(1, 'Outras Despesas', 'despesa', '#adb5bd', 'fas fa-minus-circle', 1);