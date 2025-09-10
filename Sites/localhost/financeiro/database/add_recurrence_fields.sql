-- Adicionar campos de recorrência na tabela transactions
-- Execute este script no banco de dados para habilitar recorrência

ALTER TABLE transactions 
ADD COLUMN recurrence_type ENUM('weekly', 'monthly', 'quarterly', 'biannual', 'yearly') NULL AFTER observacoes,
ADD COLUMN recurrence_count INT NULL DEFAULT 1 AFTER recurrence_type,
ADD COLUMN recurrence_end_date DATE NULL AFTER recurrence_count,
ADD COLUMN parent_transaction_id INT NULL AFTER recurrence_end_date,
ADD COLUMN recurrence_sequence INT NULL DEFAULT 1 AFTER parent_transaction_id;

-- Adicionar índices para melhor performance
ALTER TABLE transactions 
ADD INDEX idx_recurrence_type (recurrence_type),
ADD INDEX idx_parent_transaction_id (parent_transaction_id);

-- Adicionar foreign key para parent_transaction_id
ALTER TABLE transactions 
ADD FOREIGN KEY (parent_transaction_id) REFERENCES transactions(id) ON DELETE CASCADE;