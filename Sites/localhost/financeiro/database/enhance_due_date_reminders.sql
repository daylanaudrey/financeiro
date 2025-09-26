-- Enhanced due date reminder system with multiple options
-- Adicionar mais flexibilidade para lembretes de vencimento

-- 1. Adicionar suporte para múltiplos dias de lembrete
ALTER TABLE notification_preferences
ADD COLUMN due_date_reminder_days_multiple VARCHAR(50) DEFAULT '3,7' COMMENT 'Dias separados por vírgula para múltiplos lembretes';

-- 2. Adicionar configuração para tipos específicos de lembretes
ALTER TABLE notification_preferences
ADD COLUMN remind_expenses BOOLEAN DEFAULT TRUE COMMENT 'Lembrar despesas a vencer';

ALTER TABLE notification_preferences
ADD COLUMN remind_income BOOLEAN DEFAULT TRUE COMMENT 'Lembrar receitas a receber';

-- 3. Tabela para controlar quais lembretes já foram enviados (evitar spam)
CREATE TABLE IF NOT EXISTS due_date_reminder_sent (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    user_id INT NOT NULL,
    org_id INT NOT NULL,
    reminder_days INT NOT NULL COMMENT 'Quantos dias antes foi enviado',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (org_id) REFERENCES organizations(id) ON DELETE CASCADE,

    -- Evitar duplicados do mesmo lembrete
    UNIQUE KEY unique_reminder (transaction_id, user_id, reminder_days)
);

-- 4. Atualizar preferências existentes para usar múltiplos dias
UPDATE notification_preferences
SET due_date_reminder_days_multiple = CONCAT(due_date_reminder_days, ',7')
WHERE due_date_reminder_days_multiple IS NULL;

-- Exemplos de configurações comuns:
-- '1,3,7' = lembrar 1 dia, 3 dias e 7 dias antes
-- '1,2,5,10' = lembrar em múltiplos momentos
-- '7' = apenas 7 dias antes (padrão simples)