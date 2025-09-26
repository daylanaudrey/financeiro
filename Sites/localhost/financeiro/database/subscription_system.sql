-- Sistema de Assinaturas e Controle de Limites
-- Execute após permissions_system.sql

-- Função para verificar se organização pode criar novo usuário
DELIMITER //
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

-- Procedure para atualizar uso da assinatura
DELIMITER //
CREATE PROCEDURE UpdateSubscriptionUsage(IN org_id INT)
BEGIN
    DECLARE user_count INT DEFAULT 0;
    DECLARE transaction_count INT DEFAULT 0;
    DECLARE subscription_id INT;
    DECLARE period_start DATETIME;
    
    -- Buscar ID da assinatura e início do período
    SELECT os.id, os.current_period_start 
    INTO subscription_id, period_start
    FROM organization_subscriptions os
    WHERE os.org_id = org_id AND os.status IN ('trial', 'active')
    LIMIT 1;
    
    IF subscription_id IS NOT NULL THEN
        -- Contar usuários ativos
        SELECT COUNT(DISTINCT u.id)
        INTO user_count
        FROM user_org_roles uor
        JOIN users u ON uor.user_id = u.id
        WHERE uor.org_id = org_id AND u.deleted_at IS NULL;
        
        -- Contar transações do período atual
        SELECT COUNT(t.id)
        INTO transaction_count
        FROM transactions t
        WHERE t.org_id = org_id 
          AND t.deleted_at IS NULL 
          AND t.created_at >= period_start;
        
        -- Atualizar uso
        UPDATE organization_subscriptions 
        SET uso_usuarios = user_count,
            uso_transacoes = transaction_count,
            updated_at = NOW()
        WHERE id = subscription_id;
    END IF;
END //
DELIMITER ;

-- Procedure para expirar trials
DELIMITER //
CREATE PROCEDURE ExpireTrials()
BEGIN
    UPDATE organization_subscriptions 
    SET status = 'expired',
        updated_at = NOW()
    WHERE status = 'trial' 
      AND trial_ends_at <= NOW();
      
    -- Log das organizações que expiraram
    INSERT INTO audit_logs (org_id, entity, action, description)
    SELECT org_id, 'subscription', 'update', 'Trial expirado automaticamente'
    FROM organization_subscriptions 
    WHERE status = 'expired' 
      AND updated_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE);
END //
DELIMITER ;

-- Procedure para renovar assinaturas
DELIMITER //
CREATE PROCEDURE RenewSubscriptions()
BEGIN
    -- Renovar assinaturas pagas com pagamento aprovado
    UPDATE organization_subscriptions os
    JOIN subscription_payments sp ON os.id = sp.subscription_id
    SET os.current_period_start = os.current_period_end,
        os.current_period_end = CASE 
            WHEN (SELECT periodo FROM subscription_plans WHERE id = os.plan_id) = 'anual' THEN DATE_ADD(os.current_period_end, INTERVAL 1 YEAR)
            ELSE DATE_ADD(os.current_period_end, INTERVAL 1 MONTH)
        END,
        os.status = 'active',
        os.updated_at = NOW()
    WHERE os.current_period_end <= NOW()
      AND os.status = 'active'
      AND sp.status = 'paid'
      AND sp.due_date <= NOW()
      AND sp.due_date = (
          SELECT MAX(sp2.due_date) 
          FROM subscription_payments sp2 
          WHERE sp2.subscription_id = os.id 
            AND sp2.status = 'paid'
      );
      
    -- Suspender assinaturas sem pagamento
    UPDATE organization_subscriptions 
    SET status = 'suspended',
        updated_at = NOW()
    WHERE current_period_end <= DATE_SUB(NOW(), INTERVAL 3 DAY)
      AND status = 'active'
      AND id NOT IN (
          SELECT DISTINCT subscription_id 
          FROM subscription_payments 
          WHERE status = 'paid' 
            AND due_date >= DATE_SUB(NOW(), INTERVAL 5 DAY)
      );
END //
DELIMITER ;

-- Trigger para atualizar uso quando usuário é adicionado/removido
DELIMITER //
CREATE TRIGGER after_user_org_role_change
    AFTER INSERT ON user_org_roles
    FOR EACH ROW
BEGIN
    CALL UpdateSubscriptionUsage(NEW.org_id);
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER after_user_org_role_delete
    AFTER DELETE ON user_org_roles
    FOR EACH ROW
BEGIN
    CALL UpdateSubscriptionUsage(OLD.org_id);
END //
DELIMITER ;

-- Trigger para atualizar uso quando transação é criada
DELIMITER //
CREATE TRIGGER after_transaction_insert
    AFTER INSERT ON transactions
    FOR EACH ROW
BEGIN
    CALL UpdateSubscriptionUsage(NEW.org_id);
END //
DELIMITER ;

-- View para relatório de uso por organização
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

-- Atualizar uso inicial para organizações existentes
CALL UpdateSubscriptionUsage(1);