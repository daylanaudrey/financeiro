-- Adicionar diferenciação PF/PJ nas contas
-- Execute este SQL após o financial_schema.sql

-- Adicionar coluna pessoa_tipo na tabela accounts
ALTER TABLE accounts ADD COLUMN pessoa_tipo ENUM('PF', 'PJ') NOT NULL DEFAULT 'PF' AFTER org_id;

-- Adicionar campos específicos para PJ
ALTER TABLE accounts ADD COLUMN razao_social VARCHAR(255) NULL AFTER nome;
ALTER TABLE accounts ADD COLUMN cnpj VARCHAR(18) NULL AFTER razao_social;
ALTER TABLE accounts ADD COLUMN cpf VARCHAR(14) NULL AFTER cnpj;
ALTER TABLE accounts ADD COLUMN inscricao_estadual VARCHAR(20) NULL AFTER cnpj;

-- Adicionar índices
ALTER TABLE accounts ADD INDEX idx_pessoa_tipo (pessoa_tipo);
ALTER TABLE accounts ADD INDEX idx_cnpj (cnpj);
ALTER TABLE accounts ADD INDEX idx_cpf (cpf);

-- Atualizar as organizações para ter também o campo pessoa_tipo
ALTER TABLE organizations ADD COLUMN pessoa_tipo ENUM('PF', 'PJ') NOT NULL DEFAULT 'PJ' AFTER id;