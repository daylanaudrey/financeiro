-- =================================================================
-- SCRIPT DE MIGRAÇÃO OTIMIZADO: LOCAL → ONLINE
-- =================================================================
-- Este script sincroniza o banco ONLINE com o LOCAL
-- IMPORTANTE: Faça backup completo antes de executar!
-- =================================================================

-- Usar o banco correto
USE dagsol97_financeiro;

-- =================================================================
-- 1. CRIAR TABELA INTEGRATION_CONFIGS (Nova tabela)
-- =================================================================

CREATE TABLE `integration_configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `organization_id` int(11) NOT NULL DEFAULT '1',
  `integration_type` varchar(50) NOT NULL,
  `config_key` varchar(100) NOT NULL,
  `config_value` text NOT NULL,
  `is_encrypted` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_org_integration_key` (`organization_id`,`integration_type`,`config_key`),
  KEY `idx_org_integration` (`organization_id`,`integration_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =================================================================
-- 2. ALTERAR TABELA CREDIT_CARDS
-- =================================================================

-- Adicionar coluna deleted_at para soft delete
ALTER TABLE `credit_cards` ADD COLUMN `deleted_at` timestamp NULL DEFAULT NULL;

-- Adicionar índices importantes
ALTER TABLE `credit_cards` ADD KEY `idx_deleted_at` (`deleted_at`);
ALTER TABLE `credit_cards` ADD KEY `idx_ativo` (`ativo`);
ALTER TABLE `credit_cards` ADD KEY `idx_org_id` (`org_id`);

-- =================================================================
-- 3. ALTERAR TABELA NOTIFICATION_HISTORY
-- =================================================================

-- Adicionar coluna created_at
ALTER TABLE `notification_history` ADD COLUMN `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP;

-- Atualizar ENUM para incluir whatsapp
ALTER TABLE `notification_history` MODIFY COLUMN `delivery_method`
  enum('desktop','email','sms','app','whatsapp') NOT NULL;

-- Permitir title como NULL (flexibilidade)
ALTER TABLE `notification_history` MODIFY COLUMN `title` varchar(255) DEFAULT NULL;

-- =================================================================
-- 4. ALTERAR TABELA NOTIFICATION_PREFERENCES
-- =================================================================

-- Adicionar suporte a notificações WhatsApp
ALTER TABLE `notification_preferences` ADD COLUMN `enable_whatsapp_notifications` tinyint(1) DEFAULT '0';

-- =================================================================
-- 5. ALTERAR TABELA TRANSACTIONS
-- =================================================================

-- Adicionar coluna is_partial para marcar transações com baixas parciais
ALTER TABLE `transactions` ADD COLUMN `is_partial` tinyint(1) DEFAULT '0' COMMENT 'Indica se possui baixas parciais';

-- =================================================================
-- 6. ALTERAR TABELA USERS
-- =================================================================

-- Adicionar campo WhatsApp
ALTER TABLE `users` ADD COLUMN `whatsapp_number` varchar(20) DEFAULT NULL COMMENT 'Número WhatsApp com código país';

-- =================================================================
-- 7. VERIFICAÇÕES FINAIS
-- =================================================================

-- Verificar se todas as alterações foram aplicadas
SELECT
    'integration_configs' as tabela,
    COUNT(*) as existe
FROM information_schema.tables
WHERE table_schema = 'dagsol97_financeiro'
AND table_name = 'integration_configs'

UNION ALL

SELECT
    'credit_cards.deleted_at' as tabela,
    COUNT(*) as existe
FROM information_schema.columns
WHERE table_schema = 'dagsol97_financeiro'
AND table_name = 'credit_cards'
AND column_name = 'deleted_at'

UNION ALL

SELECT
    'notification_preferences.enable_whatsapp_notifications' as tabela,
    COUNT(*) as existe
FROM information_schema.columns
WHERE table_schema = 'dagsol97_financeiro'
AND table_name = 'notification_preferences'
AND column_name = 'enable_whatsapp_notifications'

UNION ALL

SELECT
    'transactions.is_partial' as tabela,
    COUNT(*) as existe
FROM information_schema.columns
WHERE table_schema = 'dagsol97_financeiro'
AND table_name = 'transactions'
AND column_name = 'is_partial'

UNION ALL

SELECT
    'users.whatsapp_number' as tabela,
    COUNT(*) as existe
FROM information_schema.columns
WHERE table_schema = 'dagsol97_financeiro'
AND table_name = 'users'
AND column_name = 'whatsapp_number';

-- =================================================================
-- FINALIZADO
-- =================================================================
-- Execute as verificações acima para confirmar que tudo foi aplicado.
-- Se algum resultado for 0, a alteração não foi aplicada corretamente.
-- =================================================================