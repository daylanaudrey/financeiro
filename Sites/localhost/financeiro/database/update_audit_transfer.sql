-- Adicionar 'transfer' ao ENUM da coluna action na tabela audit_logs
ALTER TABLE audit_logs 
MODIFY COLUMN action ENUM('create', 'update', 'delete', 'login', 'logout', 'view', 'launch', 'transfer') NOT NULL;