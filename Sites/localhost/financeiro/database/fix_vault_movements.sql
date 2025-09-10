-- Verificar e corrigir estrutura da tabela vault_movements
SHOW CREATE TABLE vault_movements;

-- Se necessário, adicionar AUTO_INCREMENT na coluna id
-- ALTER TABLE vault_movements MODIFY COLUMN id int(11) NOT NULL AUTO_INCREMENT;

-- Verificar se há movimentações inseridas
SELECT COUNT(*) as total_movements FROM vault_movements;