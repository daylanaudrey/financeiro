-- Schema para sistema de baixas parciais (pagamentos parciais)
-- Este sistema permite registrar pagamentos parciais de transações
-- e impacta automaticamente os saldos das contas

-- 1. Adicionar campos na tabela transactions para controlar baixas parciais
ALTER TABLE transactions
ADD COLUMN valor_original DECIMAL(15,2) NULL COMMENT 'Valor original da transação antes de baixas parciais',
ADD COLUMN valor_pago DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Total já pago através de baixas parciais',
ADD COLUMN valor_pendente DECIMAL(15,2) NULL COMMENT 'Valor ainda pendente (calculado)',
ADD COLUMN permite_baixa_parcial BOOLEAN DEFAULT FALSE COMMENT 'Se permite pagamento parcial',
ADD COLUMN status_pagamento ENUM('pendente', 'parcial', 'quitado') DEFAULT 'pendente' COMMENT 'Status do pagamento';

-- Atualizar transações existentes
UPDATE transactions
SET valor_original = valor,
    valor_pendente = valor,
    status_pagamento = CASE
        WHEN status = 'confirmado' THEN 'quitado'
        ELSE 'pendente'
    END
WHERE valor_original IS NULL;

-- 2. Tabela para registrar baixas parciais
CREATE TABLE IF NOT EXISTS partial_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    org_id INT NOT NULL,
    transaction_id INT NOT NULL,
    account_id INT NOT NULL COMMENT 'Conta de onde sai/entra o dinheiro',
    valor DECIMAL(15,2) NOT NULL COMMENT 'Valor da baixa parcial',
    data_pagamento DATE NOT NULL,
    descricao VARCHAR(500) NULL COMMENT 'Observações sobre esta baixa',

    -- Metadados
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    -- Foreign Keys
    FOREIGN KEY (org_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,

    -- Indexes
    INDEX idx_org_id (org_id),
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_account_id (account_id),
    INDEX idx_data_pagamento (data_pagamento),
    INDEX idx_deleted_at (deleted_at)
);

-- 3. View para facilitar visualização de transações com baixas parciais
CREATE OR REPLACE VIEW v_transactions_with_payments AS
SELECT
    t.*,
    COALESCE(t.valor_original, t.valor) as valor_total,
    COALESCE(t.valor_pago, 0) as total_pago,
    COALESCE(t.valor_original - COALESCE(t.valor_pago, 0), t.valor) as saldo_pendente,
    CASE
        WHEN t.valor_pago >= t.valor_original THEN 'quitado'
        WHEN t.valor_pago > 0 THEN 'parcial'
        ELSE 'pendente'
    END as situacao_pagamento,
    COUNT(pp.id) as qtd_pagamentos,
    GROUP_CONCAT(
        CONCAT(
            DATE_FORMAT(pp.data_pagamento, '%d/%m/%Y'),
            ': R$ ',
            FORMAT(pp.valor, 2, 'pt_BR')
        ) SEPARATOR ' | '
    ) as historico_pagamentos
FROM transactions t
LEFT JOIN partial_payments pp ON t.id = pp.transaction_id AND pp.deleted_at IS NULL
WHERE t.deleted_at IS NULL
GROUP BY t.id;

-- 4. Trigger para atualizar valores na transação quando houver baixa parcial
DELIMITER $$

CREATE TRIGGER after_partial_payment_insert
AFTER INSERT ON partial_payments
FOR EACH ROW
BEGIN
    DECLARE v_total_pago DECIMAL(15,2);
    DECLARE v_valor_original DECIMAL(15,2);
    DECLARE v_kind VARCHAR(20);

    -- Calcular total pago
    SELECT COALESCE(SUM(valor), 0) INTO v_total_pago
    FROM partial_payments
    WHERE transaction_id = NEW.transaction_id
      AND deleted_at IS NULL;

    -- Obter valor original e tipo da transação
    SELECT COALESCE(valor_original, valor), kind INTO v_valor_original, v_kind
    FROM transactions
    WHERE id = NEW.transaction_id;

    -- Atualizar transação
    UPDATE transactions
    SET valor_pago = v_total_pago,
        valor_pendente = v_valor_original - v_total_pago,
        status_pagamento = CASE
            WHEN v_total_pago >= v_valor_original THEN 'quitado'
            WHEN v_total_pago > 0 THEN 'parcial'
            ELSE 'pendente'
        END,
        status = CASE
            WHEN v_total_pago >= v_valor_original THEN 'confirmado'
            ELSE status
        END
    WHERE id = NEW.transaction_id;

    -- Atualizar saldo da conta (entrada aumenta, saída diminui)
    IF v_kind IN ('entrada', 'transfer_in') THEN
        UPDATE accounts
        SET saldo_atual = saldo_atual + NEW.valor
        WHERE id = NEW.account_id;
    ELSE
        UPDATE accounts
        SET saldo_atual = saldo_atual - NEW.valor
        WHERE id = NEW.account_id;
    END IF;
END$$

-- Trigger para quando uma baixa parcial é deletada
CREATE TRIGGER after_partial_payment_delete
AFTER UPDATE ON partial_payments
FOR EACH ROW
BEGIN
    DECLARE v_total_pago DECIMAL(15,2);
    DECLARE v_valor_original DECIMAL(15,2);
    DECLARE v_kind VARCHAR(20);

    -- Se foi soft delete
    IF NEW.deleted_at IS NOT NULL AND OLD.deleted_at IS NULL THEN
        -- Recalcular total pago
        SELECT COALESCE(SUM(valor), 0) INTO v_total_pago
        FROM partial_payments
        WHERE transaction_id = NEW.transaction_id
          AND deleted_at IS NULL;

        -- Obter valor original e tipo
        SELECT COALESCE(valor_original, valor), kind INTO v_valor_original, v_kind
        FROM transactions
        WHERE id = NEW.transaction_id;

        -- Atualizar transação
        UPDATE transactions
        SET valor_pago = v_total_pago,
            valor_pendente = v_valor_original - v_total_pago,
            status_pagamento = CASE
                WHEN v_total_pago >= v_valor_original THEN 'quitado'
                WHEN v_total_pago > 0 THEN 'parcial'
                ELSE 'pendente'
            END,
            status = CASE
                WHEN v_total_pago = 0 THEN 'agendado'
                ELSE status
            END
        WHERE id = NEW.transaction_id;

        -- Reverter saldo da conta
        IF v_kind IN ('entrada', 'transfer_in') THEN
            UPDATE accounts
            SET saldo_atual = saldo_atual - OLD.valor
            WHERE id = OLD.account_id;
        ELSE
            UPDATE accounts
            SET saldo_atual = saldo_atual + OLD.valor
            WHERE id = OLD.account_id;
        END IF;
    END IF;
END$$

DELIMITER ;

-- 5. Índices adicionais para performance
CREATE INDEX idx_transactions_status_pagamento ON transactions(status_pagamento);
CREATE INDEX idx_transactions_permite_baixa ON transactions(permite_baixa_parcial);

-- 6. Função helper para registrar baixa parcial (para facilitar uso)
DELIMITER $$

CREATE FUNCTION register_partial_payment(
    p_org_id INT,
    p_transaction_id INT,
    p_account_id INT,
    p_valor DECIMAL(15,2),
    p_data_pagamento DATE,
    p_descricao VARCHAR(500),
    p_created_by INT
) RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE v_payment_id INT;

    INSERT INTO partial_payments (
        org_id,
        transaction_id,
        account_id,
        valor,
        data_pagamento,
        descricao,
        created_by
    ) VALUES (
        p_org_id,
        p_transaction_id,
        p_account_id,
        p_valor,
        p_data_pagamento,
        p_descricao,
        p_created_by
    );

    SET v_payment_id = LAST_INSERT_ID();

    RETURN v_payment_id;
END$$

DELIMITER ;

-- Exemplos de uso:
-- SELECT register_partial_payment(1, 123, 1, 500.00, CURDATE(), 'Pagamento parcial 1/3', 1);