-- Migration: Add arrival dates and free time fields to processes table
-- Sistema Aduaneiro - Campos de chegada prevista, confirmada e free time

-- Adicionar novos campos à tabela processes
ALTER TABLE processes
ADD COLUMN estimated_arrival_date DATE NULL COMMENT 'Data de chegada prevista',
ADD COLUMN confirmed_arrival_date DATE NULL COMMENT 'Data de chegada confirmada',
ADD COLUMN free_time_days INT DEFAULT 7 COMMENT 'Dias de free time (armazenagem sem custo)';

-- Atualizar processos existentes com free time padrão
UPDATE processes
SET free_time_days = 7
WHERE free_time_days IS NULL OR free_time_days = 0;

-- Verificar estrutura
DESCRIBE processes;