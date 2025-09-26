-- Inserir configurações padrão do MailerSend na tabela system_configs
INSERT IGNORE INTO system_configs (key_name, key_value, description) VALUES
('mailersend_api_key', '', 'API Key do MailerSend para envio de emails'),
('mailersend_from_email', 'noreply@dagsolucaodigital.com.br', 'Email remetente padrão'),
('mailersend_from_name', 'DAG Sistema Financeiro', 'Nome remetente padrão'),
('email_service', 'mailersend', 'Serviço de email utilizado (mailersend ou smtp)');