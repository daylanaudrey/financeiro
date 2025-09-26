-- Sistema de Vaults independente de contas
-- Substituir tabela vault_goals existente com nova estrutura

-- Primeiro, fazer backup das tabelas existentes (caso existam dados)
-- RENAME TABLE vault_goals TO vault_goals_backup;
-- RENAME TABLE vault_movements TO vault_movements_backup;

-- Nova estrutura - independente de contas
DROP TABLE IF EXISTS vault_goals;
CREATE TABLE vault_goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    org_id INT NOT NULL DEFAULT 1,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT NULL,
    valor_meta DECIMAL(15,2) NOT NULL,
    valor_atual DECIMAL(15,2) DEFAULT 0.00,
    data_meta DATE NULL,
    categoria ENUM('emergencia', 'viagem', 'compra', 'investimento', 'educacao', 'saude', 'casa', 'veiculo', 'aposentadoria', 'outros') DEFAULT 'outros',
    cor VARCHAR(7) DEFAULT '#007bff',
    icone VARCHAR(50) DEFAULT 'fas fa-bullseye',
    prioridade ENUM('baixa', 'media', 'alta') DEFAULT 'media',
    ativo BOOLEAN DEFAULT TRUE,
    concluido BOOLEAN DEFAULT FALSE,
    data_conclusao DATE NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_org_id (org_id),
    INDEX idx_categoria (categoria),
    INDEX idx_prioridade (prioridade),
    INDEX idx_ativo (ativo),
    INDEX idx_concluido (concluido),
    INDEX idx_deleted_at (deleted_at)
);

-- Tabela de movimentações dos vaults (depósitos/retiradas)
DROP TABLE IF EXISTS vault_movements;
CREATE TABLE vault_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vault_goal_id INT NOT NULL,
    transaction_id INT NOT NULL,
    tipo ENUM('deposito', 'retirada') NOT NULL,
    valor DECIMAL(15,2) NOT NULL,
    descricao TEXT NULL,
    data_movimento DATE NOT NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (vault_goal_id) REFERENCES vault_goals(id) ON DELETE CASCADE,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_vault_goal_id (vault_goal_id),
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_tipo (tipo),
    INDEX idx_data_movimento (data_movimento),
    INDEX idx_deleted_at (deleted_at)
);

-- Triggers para atualizar valor_atual do vault quando houver movimentação
DELIMITER $$

DROP TRIGGER IF EXISTS update_vault_goal_amount_after_insert$$
CREATE TRIGGER update_vault_goal_amount_after_insert
    AFTER INSERT ON vault_movements
    FOR EACH ROW
BEGIN
    UPDATE vault_goals 
    SET valor_atual = (
        SELECT COALESCE(SUM(
            CASE 
                WHEN vm.tipo = 'deposito' THEN vm.valor 
                WHEN vm.tipo = 'retirada' THEN -vm.valor 
                ELSE 0 
            END
        ), 0)
        FROM vault_movements vm 
        WHERE vm.vault_goal_id = NEW.vault_goal_id 
        AND vm.deleted_at IS NULL
    ),
    concluido = CASE 
        WHEN (
            SELECT COALESCE(SUM(
                CASE 
                    WHEN vm.tipo = 'deposito' THEN vm.valor 
                    WHEN vm.tipo = 'retirada' THEN -vm.valor 
                    ELSE 0 
                END
            ), 0)
            FROM vault_movements vm 
            WHERE vm.vault_goal_id = NEW.vault_goal_id 
            AND vm.deleted_at IS NULL
        ) >= valor_meta THEN TRUE 
        ELSE FALSE 
    END,
    data_conclusao = CASE 
        WHEN (
            SELECT COALESCE(SUM(
                CASE 
                    WHEN vm.tipo = 'deposito' THEN vm.valor 
                    WHEN vm.tipo = 'retirada' THEN -vm.valor 
                    ELSE 0 
                END
            ), 0)
            FROM vault_movements vm 
            WHERE vm.vault_goal_id = NEW.vault_goal_id 
            AND vm.deleted_at IS NULL
        ) >= valor_meta AND data_conclusao IS NULL THEN CURDATE()
        WHEN (
            SELECT COALESCE(SUM(
                CASE 
                    WHEN vm.tipo = 'deposito' THEN vm.valor 
                    WHEN vm.tipo = 'retirada' THEN -vm.valor 
                    ELSE 0 
                END
            ), 0)
            FROM vault_movements vm 
            WHERE vm.vault_goal_id = NEW.vault_goal_id 
            AND vm.deleted_at IS NULL
        ) < valor_meta THEN NULL
        ELSE data_conclusao
    END
    WHERE id = NEW.vault_goal_id;
END$$

DROP TRIGGER IF EXISTS update_vault_goal_amount_after_update$$
CREATE TRIGGER update_vault_goal_amount_after_update
    AFTER UPDATE ON vault_movements
    FOR EACH ROW
BEGIN
    UPDATE vault_goals 
    SET valor_atual = (
        SELECT COALESCE(SUM(
            CASE 
                WHEN vm.tipo = 'deposito' THEN vm.valor 
                WHEN vm.tipo = 'retirada' THEN -vm.valor 
                ELSE 0 
            END
        ), 0)
        FROM vault_movements vm 
        WHERE vm.vault_goal_id = NEW.vault_goal_id 
        AND vm.deleted_at IS NULL
    ),
    concluido = CASE 
        WHEN (
            SELECT COALESCE(SUM(
                CASE 
                    WHEN vm.tipo = 'deposito' THEN vm.valor 
                    WHEN vm.tipo = 'retirada' THEN -vm.valor 
                    ELSE 0 
                END
            ), 0)
            FROM vault_movements vm 
            WHERE vm.vault_goal_id = NEW.vault_goal_id 
            AND vm.deleted_at IS NULL
        ) >= valor_meta THEN TRUE 
        ELSE FALSE 
    END,
    data_conclusao = CASE 
        WHEN (
            SELECT COALESCE(SUM(
                CASE 
                    WHEN vm.tipo = 'deposito' THEN vm.valor 
                    WHEN vm.tipo = 'retirada' THEN -vm.valor 
                    ELSE 0 
                END
            ), 0)
            FROM vault_movements vm 
            WHERE vm.vault_goal_id = NEW.vault_goal_id 
            AND vm.deleted_at IS NULL
        ) >= valor_meta AND data_conclusao IS NULL THEN CURDATE()
        WHEN (
            SELECT COALESCE(SUM(
                CASE 
                    WHEN vm.tipo = 'deposito' THEN vm.valor 
                    WHEN vm.tipo = 'retirada' THEN -vm.valor 
                    ELSE 0 
                END
            ), 0)
            FROM vault_movements vm 
            WHERE vm.vault_goal_id = NEW.vault_goal_id 
            AND vm.deleted_at IS NULL
        ) < valor_meta THEN NULL
        ELSE data_conclusao
    END
    WHERE id = NEW.vault_goal_id;
END$$

DROP TRIGGER IF EXISTS update_vault_goal_amount_after_delete$$
CREATE TRIGGER update_vault_goal_amount_after_delete
    AFTER DELETE ON vault_movements
    FOR EACH ROW
BEGIN
    UPDATE vault_goals 
    SET valor_atual = (
        SELECT COALESCE(SUM(
            CASE 
                WHEN vm.tipo = 'deposito' THEN vm.valor 
                WHEN vm.tipo = 'retirada' THEN -vm.valor 
                ELSE 0 
            END
        ), 0)
        FROM vault_movements vm 
        WHERE vm.vault_goal_id = OLD.vault_goal_id 
        AND vm.deleted_at IS NULL
    ),
    concluido = CASE 
        WHEN (
            SELECT COALESCE(SUM(
                CASE 
                    WHEN vm.tipo = 'deposito' THEN vm.valor 
                    WHEN vm.tipo = 'retirada' THEN -vm.valor 
                    ELSE 0 
                END
            ), 0)
            FROM vault_movements vm 
            WHERE vm.vault_goal_id = OLD.vault_goal_id 
            AND vm.deleted_at IS NULL
        ) >= valor_meta THEN TRUE 
        ELSE FALSE 
    END,
    data_conclusao = CASE 
        WHEN (
            SELECT COALESCE(SUM(
                CASE 
                    WHEN vm.tipo = 'deposito' THEN vm.valor 
                    WHEN vm.tipo = 'retirada' THEN -vm.valor 
                    ELSE 0 
                END
            ), 0)
            FROM vault_movements vm 
            WHERE vm.vault_goal_id = OLD.vault_goal_id 
            AND vm.deleted_at IS NULL
        ) >= valor_meta AND data_conclusao IS NULL THEN CURDATE()
        WHEN (
            SELECT COALESCE(SUM(
                CASE 
                    WHEN vm.tipo = 'deposito' THEN vm.valor 
                    WHEN vm.tipo = 'retirada' THEN -vm.valor 
                    ELSE 0 
                END
            ), 0)
            FROM vault_movements vm 
            WHERE vm.vault_goal_id = OLD.vault_goal_id 
            AND vm.deleted_at IS NULL
        ) < valor_meta THEN NULL
        ELSE data_conclusao
    END
    WHERE id = OLD.vault_goal_id;
END$$

DELIMITER ;

-- Inserir dados de exemplo
INSERT INTO vault_goals (org_id, titulo, descricao, valor_meta, categoria, cor, icone, prioridade, created_by) VALUES
(1, 'Reserva de Emergência', 'Reserva para emergências equivalente a 6 meses de gastos', 25000.00, 'emergencia', '#dc3545', 'fas fa-shield-alt', 'alta', 1),
(1, 'Viagem para Europa', 'Economizar para uma viagem de 15 dias pela Europa', 15000.00, 'viagem', '#28a745', 'fas fa-plane', 'media', 1),
(1, 'Novo Notebook', 'Comprar notebook para trabalho', 5000.00, 'compra', '#007bff', 'fas fa-laptop', 'media', 1),
(1, 'Curso de Especialização', 'Investir em educação profissional', 3000.00, 'educacao', '#6f42c1', 'fas fa-graduation-cap', 'alta', 1);