-- Migration: Add destination port field to processes table
-- Sistema Aduaneiro - Campo de porto de destino (usando tabela ports existente)
-- Data: 2025-09-24

-- Adicionar campo porto de destino à tabela processes
ALTER TABLE `processes`
ADD COLUMN `destination_port_id` int(11) NULL COMMENT 'Porto de destino do processo';

-- Adicionar chave estrangeira
ALTER TABLE `processes`
ADD CONSTRAINT `fk_processes_destination_port`
    FOREIGN KEY (`destination_port_id`) REFERENCES `ports` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Índice para performance
CREATE INDEX `idx_processes_destination_port` ON `processes` (`destination_port_id`);