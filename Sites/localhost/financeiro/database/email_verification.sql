-- Adicionar colunas para validação de email
ALTER TABLE users 
ADD COLUMN email_verification_token VARCHAR(255) NULL AFTER email,
ADD COLUMN email_verified_at TIMESTAMP NULL AFTER email_verification_token,
ADD COLUMN email_verification_sent_at TIMESTAMP NULL AFTER email_verified_at;