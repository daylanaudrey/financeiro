-- Alterações necessárias para sincronizar banco de produção com local
-- Execute estes comandos na ordem apresentada

-- 1. Adicionar campo whatsapp_number na tabela users (se não existir)
ALTER TABLE `users` ADD COLUMN `whatsapp_number` varchar(20) DEFAULT NULL AFTER `telefone`;

-- 2. Modificar campo due_date_reminder_days para suportar múltiplos valores
ALTER TABLE `notification_preferences` MODIFY COLUMN `due_date_reminder_days` varchar(50) DEFAULT '3';

-- 3. Remover campos extras que existem apenas na produção (se existirem)
-- Verificar se existem e remover:
-- ALTER TABLE `notification_preferences` DROP COLUMN IF EXISTS `due_date_reminder_days_multiple`;
-- ALTER TABLE `notification_preferences` DROP COLUMN IF EXISTS `remind_expenses`;
-- ALTER TABLE `notification_preferences` DROP COLUMN IF EXISTS `remind_income`;

-- 4. Remover tabela due_date_reminder_sent se existir
-- DROP TABLE IF EXISTS `due_date_reminder_sent`;

-- Observações:
-- - Execute com cuidado em produção
-- - Faça backup antes de executar
-- - Teste primeiro em ambiente de desenvolvimento se possível
-- - O campo due_date_reminder_days agora suporta valores como "3,7" para múltiplos lembretes