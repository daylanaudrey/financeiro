-- Sistema de Centro de Custo
-- Execute após financial_schema.sql

-- Tabela de centros de custo
CREATE TABLE cost_centers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    org_id INT NOT NULL,
    codigo VARCHAR(20) NOT NULL,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT NULL,
    parent_id INT NULL,
    nivel INT DEFAULT 1,
    caminho VARCHAR(500) NULL, -- Ex: "1.2.3" para hierarquia
    cor VARCHAR(7) DEFAULT '#6c757d',
    icone VARCHAR(50) DEFAULT 'fas fa-building',
    ativo BOOLEAN DEFAULT TRUE,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (org_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES cost_centers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_org_codigo (org_id, codigo),
    INDEX idx_org_id (org_id),
    INDEX idx_codigo (codigo),
    INDEX idx_parent_id (parent_id),
    INDEX idx_nivel (nivel),
    INDEX idx_ativo (ativo),
    INDEX idx_deleted_at (deleted_at)
);

-- Adicionar coluna cost_center_id na tabela transactions
ALTER TABLE transactions 
ADD COLUMN cost_center_id INT NULL AFTER contact_id,
ADD CONSTRAINT fk_transactions_cost_center 
    FOREIGN KEY (cost_center_id) REFERENCES cost_centers(id) ON DELETE SET NULL,
ADD INDEX idx_cost_center_id (cost_center_id);

-- Trigger para atualizar caminho hierárquico automaticamente
DELIMITER $$

CREATE TRIGGER update_cost_center_path_insert
    AFTER INSERT ON cost_centers
    FOR EACH ROW
BEGIN
    DECLARE parent_path VARCHAR(500);
    
    IF NEW.parent_id IS NOT NULL THEN
        SELECT caminho INTO parent_path 
        FROM cost_centers 
        WHERE id = NEW.parent_id;
        
        UPDATE cost_centers 
        SET caminho = CONCAT(IFNULL(parent_path, ''), '.', NEW.id),
            nivel = (LENGTH(IFNULL(parent_path, '')) - LENGTH(REPLACE(IFNULL(parent_path, ''), '.', '')) + 2)
        WHERE id = NEW.id;
    ELSE
        UPDATE cost_centers 
        SET caminho = CAST(NEW.id AS CHAR),
            nivel = 1
        WHERE id = NEW.id;
    END IF;
END$$

CREATE TRIGGER update_cost_center_path_update
    AFTER UPDATE ON cost_centers
    FOR EACH ROW
BEGIN
    DECLARE parent_path VARCHAR(500);
    
    IF NEW.parent_id != OLD.parent_id OR (NEW.parent_id IS NOT NULL AND OLD.parent_id IS NULL) OR (NEW.parent_id IS NULL AND OLD.parent_id IS NOT NULL) THEN
        
        IF NEW.parent_id IS NOT NULL THEN
            SELECT caminho INTO parent_path 
            FROM cost_centers 
            WHERE id = NEW.parent_id;
            
            UPDATE cost_centers 
            SET caminho = CONCAT(IFNULL(parent_path, ''), '.', NEW.id),
                nivel = (LENGTH(IFNULL(parent_path, '')) - LENGTH(REPLACE(IFNULL(parent_path, ''), '.', '')) + 2)
            WHERE id = NEW.id;
        ELSE
            UPDATE cost_centers 
            SET caminho = CAST(NEW.id AS CHAR),
                nivel = 1
            WHERE id = NEW.id;
        END IF;
        
        -- Atualizar todos os filhos recursivamente
        UPDATE cost_centers 
        SET caminho = CASE 
            WHEN NEW.parent_id IS NOT NULL THEN 
                CONCAT(CONCAT(IFNULL(parent_path, ''), '.', NEW.id), SUBSTRING(caminho, LENGTH(OLD.caminho) + 1))
            ELSE 
                CONCAT(CAST(NEW.id AS CHAR), SUBSTRING(caminho, LENGTH(OLD.caminho) + 1))
        END,
        nivel = nivel + (NEW.nivel - OLD.nivel)
        WHERE caminho LIKE CONCAT(OLD.caminho, '.%');
    END IF;
END$$

DELIMITER ;

-- Inserir centros de custo padrão para a organização 1
INSERT INTO cost_centers (org_id, codigo, nome, descricao, cor, icone, created_by) VALUES
-- Centros principais
(1, 'ADM', 'Administrativo', 'Despesas administrativas e gerais', '#007bff', 'fas fa-building', 1),
(1, 'VND', 'Vendas', 'Despesas relacionadas a vendas e marketing', '#28a745', 'fas fa-chart-line', 1),
(1, 'PRD', 'Produção', 'Custos diretos de produção', '#ffc107', 'fas fa-industry', 1),
(1, 'TI', 'Tecnologia da Informação', 'Investimentos e manutenção em TI', '#17a2b8', 'fas fa-laptop-code', 1),
(1, 'RH', 'Recursos Humanos', 'Despesas com pessoal e benefícios', '#e83e8c', 'fas fa-users', 1);

-- Aguardar para inserir subcentros após os triggers criarem os caminhos
-- Centros filhos do Administrativo (será inserido após o trigger processar)
INSERT INTO cost_centers (org_id, codigo, nome, descricao, parent_id, cor, icone, created_by) VALUES
(1, 'ADM.FIN', 'Financeiro', 'Despesas do departamento financeiro', 1, '#6f42c1', 'fas fa-calculator', 1),
(1, 'ADM.JUR', 'Jurídico', 'Despesas jurídicas e advocacia', 1, '#fd7e14', 'fas fa-gavel', 1);

-- Centros filhos de Vendas
INSERT INTO cost_centers (org_id, codigo, nome, descricao, parent_id, cor, icone, created_by) VALUES
(1, 'VND.MKT', 'Marketing', 'Campanhas e material de marketing', 2, '#20c997', 'fas fa-bullhorn', 1),
(1, 'VND.COM', 'Comercial', 'Equipe de vendas e comissões', 2, '#198754', 'fas fa-handshake', 1);