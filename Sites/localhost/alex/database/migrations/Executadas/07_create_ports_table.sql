-- Criar tabela de portos
CREATE TABLE IF NOT EXISTS ports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL COMMENT 'Nome do porto',
    prefix VARCHAR(10) NOT NULL UNIQUE COMMENT 'Prefixo do porto (ex: SSZ, SPO)',
    city VARCHAR(100) NOT NULL COMMENT 'Cidade do porto',
    state VARCHAR(50) DEFAULT NULL COMMENT 'Estado do porto',
    country VARCHAR(50) DEFAULT 'Brasil' COMMENT 'País do porto',
    fob_min DECIMAL(10,2) DEFAULT NULL COMMENT 'FOB mínimo',
    fob_max DECIMAL(10,2) DEFAULT NULL COMMENT 'FOB máximo',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Porto ativo',
    notes TEXT DEFAULT NULL COMMENT 'Observações sobre o porto',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted BOOLEAN DEFAULT FALSE,
    deleted_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices para otimização (ignorar se já existem)
SET @sql = 'CREATE INDEX idx_ports_prefix ON ports(prefix)';
SET @count = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'ports' AND index_name = 'idx_ports_prefix');
SET @sql = IF(@count = 0, @sql, 'SELECT "Index idx_ports_prefix already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = 'CREATE INDEX idx_ports_city ON ports(city)';
SET @count = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'ports' AND index_name = 'idx_ports_city');
SET @sql = IF(@count = 0, @sql, 'SELECT "Index idx_ports_city already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = 'CREATE INDEX idx_ports_active ON ports(is_active)';
SET @count = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'ports' AND index_name = 'idx_ports_active');
SET @sql = IF(@count = 0, @sql, 'SELECT "Index idx_ports_active already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = 'CREATE INDEX idx_ports_deleted ON ports(deleted)';
SET @count = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'ports' AND index_name = 'idx_ports_deleted');
SET @sql = IF(@count = 0, @sql, 'SELECT "Index idx_ports_deleted already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Inserir alguns portos de exemplo
INSERT IGNORE INTO ports (name, prefix, city, state, fob_min, fob_max, notes) VALUES
('Porto de Santos', 'SSZ', 'Santos', 'São Paulo', 100.00, 50000.00, 'Principal porto do Brasil'),
('Porto de Paranaguá', 'PNG', 'Paranaguá', 'Paraná', 150.00, 45000.00, 'Porto importante da região Sul'),
('Porto do Rio de Janeiro', 'RIO', 'Rio de Janeiro', 'Rio de Janeiro', 200.00, 40000.00, 'Porto histórico'),
('Porto de Vitória', 'VTR', 'Vitória', 'Espírito Santo', 120.00, 35000.00, 'Porto de minério e contêineres'),
('Porto de Suape', 'SPE', 'Suape', 'Pernambuco', 180.00, 30000.00, 'Porto industrial moderno'),
('Porto de Itajaí', 'ITJ', 'Itajaí', 'Santa Catarina', 110.00, 25000.00, 'Porto de contêineres'),
('Porto de São Francisco do Sul', 'SFS', 'São Francisco do Sul', 'Santa Catarina', 140.00, 28000.00, 'Porto graneleiro'),
('Porto de Fortaleza', 'FOR', 'Fortaleza', 'Ceará', 160.00, 20000.00, 'Porto do Nordeste');