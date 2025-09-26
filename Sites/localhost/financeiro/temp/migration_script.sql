-- =================================================
-- SCRIPT DE MIGRAÇÃO: LOCAL → ONLINE
-- =================================================
-- Este script sincroniza o banco ONLINE com o LOCAL
-- IMPORTANTE: Faça backup antes de executar!
-- =================================================

-- Usar o banco correto
USE dagsol97_financeiro;

-- Criar tabela integration_configs
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4;

-- Adicionar colunas na tabela credit_cards

-- Modificar colunas na tabela credit_cards
-- LOCAL:  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nome do cartão (ex: Nubank, Santander)'
-- ONLINE: `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nome do cartão (ex: Nubank, Santander)'
-- LOCAL:  `observacoes` text COLLATE utf8mb4_unicode_ci
-- ONLINE: `observacoes` text
-- LOCAL:  `ativo` tinyint(1) NOT NULL DEFAULT '1'
-- ONLINE: `ativo` tinyint(1) DEFAULT '1'
-- LOCAL:  `dia_vencimento` int(2) NOT NULL COMMENT 'Dia do mês que vence (1-31)'
-- ONLINE: `dia_vencimento` int(2) NOT NULL
-- LOCAL:  `cor` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#6c757d' COMMENT 'Cor para identificação'
-- ONLINE: `cor` varchar(7) DEFAULT '#6c757d'
-- LOCAL:  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
-- ONLINE: `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
-- LOCAL:  `banco` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Banco emissor'
-- ONLINE: `banco` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Banco emissor'
-- LOCAL:  `dia_fechamento` int(2) NOT NULL COMMENT 'Dia que fecha a fatura (1-31)'
-- ONLINE: `dia_fechamento` int(2) NOT NULL
-- LOCAL:  `bandeira` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Visa, Mastercard, etc'
-- ONLINE: `bandeira` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Visa, Mastercard, etc'
-- LOCAL:  `ultimos_digitos` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Últimos 4 dígitos'
-- ONLINE: `ultimos_digitos` varchar(4) DEFAULT NULL
-- LOCAL:  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
-- ONLINE: `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
-- Adicionar índices na tabela credit_cards
-- Adicionar colunas na tabela notification_history
-- Modificar colunas na tabela notification_history
-- LOCAL:  `message` text COLLATE utf8mb4_unicode_ci
-- ONLINE: `message` text COLLATE utf8_unicode_ci
-- LOCAL:  `status` enum('sent','delivered','failed','clicked') COLLATE utf8mb4_unicode_ci DEFAULT 'sent'
-- ONLINE: `status` enum('sent','delivered','failed','clicked') COLLATE utf8_unicode_ci DEFAULT 'sent'
-- LOCAL:  `related_entity_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
-- ONLINE: `related_entity_type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL
-- LOCAL:  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
-- ONLINE: `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
-- LOCAL:  `notification_type` enum('new_transaction','upcoming_due','low_balance','goal_reached','overdue_transaction','weekly_summary','monthly_summary','system_alert') COLLATE utf8mb4_unicode_ci NOT NULL
-- ONLINE: `notification_type` enum('new_transaction','upcoming_due','low_balance','goal_reached','overdue_transaction','weekly_summary','monthly_summary','system_alert') COLLATE utf8_unicode_ci NOT NULL
-- LOCAL:  `error_message` text COLLATE utf8mb4_unicode_ci
-- ONLINE: `error_message` text COLLATE utf8_unicode_ci
-- LOCAL:  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
-- ONLINE: `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL
-- LOCAL:  `delivery_method` enum('desktop','email','sms','app','whatsapp') COLLATE utf8mb4_unicode_ci NOT NULL
-- ONLINE: `delivery_method` enum('desktop','email','sms','app') COLLATE utf8_unicode_ci NOT NULL
-- Adicionar colunas na tabela notification_preferences
-- Modificar colunas na tabela notification_preferences
-- LOCAL:  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
-- ONLINE: `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
-- LOCAL:  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
-- ONLINE: `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
-- Modificar colunas na tabela organization_invites
-- LOCAL:  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
-- ONLINE: `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
-- Modificar colunas na tabela organization_subscriptions
-- LOCAL:  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
-- ONLINE: `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
-- LOCAL:  `cancel_reason` text
-- ONLINE: `cancel_reason` mediumtext
-- LOCAL:  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
-- ONLINE: `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
-- Modificar colunas na tabela partial_payments
-- LOCAL:  `descricao` varchar(500) DEFAULT NULL COMMENT 'Observações sobre esta baixa'
-- ONLINE: `descricao` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Observações sobre esta baixa'
-- LOCAL:  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
-- ONLINE: `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
-- LOCAL:  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
-- ONLINE: `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
-- Modificar colunas na tabela subscription_payments
-- LOCAL:  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
-- ONLINE: `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
-- LOCAL:  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
-- ONLINE: `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
-- Modificar colunas na tabela subscription_plans
-- LOCAL:  `descricao` text
-- ONLINE: `descricao` mediumtext
-- LOCAL:  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
-- ONLINE: `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
-- LOCAL:  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
-- ONLINE: `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
-- Modificar colunas na tabela system_configs
-- LOCAL:  `key_value` text
-- ONLINE: `key_value` mediumtext
-- LOCAL:  `description` text
-- ONLINE: `description` mediumtext
-- LOCAL:  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
-- ONLINE: `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
-- LOCAL:  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
-- ONLINE: `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
-- Adicionar colunas na tabela transactions
-- Modificar colunas na tabela transactions
-- LOCAL:  `valor_pago` decimal(15,2) DEFAULT '0.00'
-- ONLINE: `valor_pago` decimal(15,2) DEFAULT '0.00' COMMENT 'Total já pago através de baixas parciais'
-- LOCAL:  `valor_original` decimal(15,2) DEFAULT NULL
-- ONLINE: `valor_original` decimal(15,2) DEFAULT NULL COMMENT 'Valor original da transação antes de baixas parciais'
-- Modificar colunas na tabela user_permissions
-- LOCAL:  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
-- ONLINE: `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
-- LOCAL:  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
-- ONLINE: `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
-- Adicionar colunas na tabela users
-- Modificar colunas na tabela vault_movements
-- LOCAL:  `descricao` text
-- ONLINE: `descricao` mediumtext
-- LOCAL:  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
-- ONLINE: `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
-- LOCAL:  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
-- ONLINE: `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
