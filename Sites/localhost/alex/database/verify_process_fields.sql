-- Verificar se os novos campos foram adicionados à tabela processes
DESCRIBE processes;

-- Verificar estrutura específica dos novos campos
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'alex'
  AND TABLE_NAME = 'processes'
  AND COLUMN_NAME IN ('estimated_arrival_date', 'confirmed_arrival_date', 'free_time_days');

-- Mostrar alguns processos existentes
SELECT id, code, estimated_arrival_date, confirmed_arrival_date, free_time_days
FROM processes
WHERE deleted = 0
LIMIT 5;