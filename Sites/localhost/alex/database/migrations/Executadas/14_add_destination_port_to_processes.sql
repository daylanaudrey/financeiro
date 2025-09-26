-- Migration: Add destination port field to processes table
-- Sistema Aduaneiro - Campo de porto de destino
-- Data: 2025-09-24

-- Criar tabela de portos se não existir
CREATE TABLE IF NOT EXISTS `ports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL COMMENT 'Código do porto (ex: BRSSZ)',
  `name` varchar(255) NOT NULL COMMENT 'Nome do porto',
  `city` varchar(100) NOT NULL COMMENT 'Cidade do porto',
  `state` varchar(2) NOT NULL COMMENT 'Estado (sigla)',
  `country` varchar(2) NOT NULL COMMENT 'País (código)',
  `type` enum('SEA','AIR','DRY') NOT NULL DEFAULT 'SEA' COMMENT 'Tipo de porto',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ports_code` (`code`),
  KEY `idx_ports_type` (`type`),
  KEY `idx_ports_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Portos de destino';

-- Inserir alguns portos brasileiros principais
INSERT INTO `ports` (`code`, `name`, `city`, `state`, `country`, `type`) VALUES
('BRSSZ', 'Porto de Santos', 'Santos', 'SP', 'BR', 'SEA'),
('BRRIG', 'Porto do Rio Grande', 'Rio Grande', 'RS', 'SEA'),
('BRSFS', 'Porto de São Francisco do Sul', 'São Francisco do Sul', 'SC', 'SEA'),
('BRPNG', 'Porto de Paranaguá', 'Paranaguá', 'PR', 'SEA'),
('BRPEL', 'Porto de Pelotas', 'Pelotas', 'RS', 'SEA'),
('BRVIX', 'Porto de Vitória', 'Vitória', 'ES', 'SEA'),
('BRRIO', 'Porto do Rio de Janeiro', 'Rio de Janeiro', 'RJ', 'SEA'),
('BRFOR', 'Porto de Fortaleza', 'Fortaleza', 'CE', 'SEA'),
('BRSSA', 'Porto de Salvador', 'Salvador', 'BA', 'SEA'),
('BRRECF', 'Porto do Recife', 'Recife', 'PE', 'SEA'),
('BRGRU', 'Aeroporto de Guarulhos', 'Guarulhos', 'SP', 'BR', 'AIR'),
('BRGIG', 'Aeroporto do Galeão', 'Rio de Janeiro', 'RJ', 'BR', 'AIR'),
('BRCNF', 'Aeroporto de Confins', 'Confins', 'MG', 'BR', 'AIR'),
('BRPOA', 'Aeroporto de Porto Alegre', 'Porto Alegre', 'RS', 'BR', 'AIR');

-- Adicionar campo porto de destino à tabela processes
ALTER TABLE `processes`
ADD COLUMN `destination_port_id` int(11) NULL COMMENT 'Porto de destino do processo',
ADD CONSTRAINT `fk_processes_destination_port`
    FOREIGN KEY (`destination_port_id`) REFERENCES `ports` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Índice para performance
CREATE INDEX `idx_processes_destination_port` ON `processes` (`destination_port_id`);