-- Atualizar tabela de portos - Remover campos FOB e adicionar código de recinto alfandegário
-- Remover campos de FOB mínimo e máximo
ALTER TABLE ports
DROP COLUMN IF EXISTS fob_min,
DROP COLUMN IF EXISTS fob_max;

-- Adicionar campo de código de recinto alfandegário
ALTER TABLE ports
ADD COLUMN customs_code VARCHAR(20) DEFAULT NULL COMMENT 'Código do recinto alfandegário' AFTER prefix;

-- Criar índice para o código de recinto alfandegário
SET @sql = 'CREATE INDEX idx_ports_customs_code ON ports(customs_code)';
SET @count = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'ports' AND index_name = 'idx_ports_customs_code');
SET @sql = IF(@count = 0, @sql, 'SELECT "Index idx_ports_customs_code already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Atualizar alguns portos com códigos de recinto alfandegário de exemplo
UPDATE ports SET customs_code = '7811501' WHERE prefix = 'SSZ'; -- Santos
UPDATE ports SET customs_code = '4104209' WHERE prefix = 'PNG'; -- Paranaguá
UPDATE ports SET customs_code = '3304557' WHERE prefix = 'RIO'; -- Rio de Janeiro
UPDATE ports SET customs_code = '3205002' WHERE prefix = 'VTR'; -- Vitória
UPDATE ports SET customs_code = '2611606' WHERE prefix = 'SPE'; -- Suape
UPDATE ports SET customs_code = '4208203' WHERE prefix = 'ITJ'; -- Itajaí
UPDATE ports SET customs_code = '4216602' WHERE prefix = 'SFS'; -- São Francisco do Sul
UPDATE ports SET customs_code = '2304400' WHERE prefix = 'FOR'; -- Fortaleza