-- Script para criar usuário de teste e popular banco de dados
-- Email: admin@sistema.com
-- Senha: password

-- 1. Criar usuário admin (senha: password)
INSERT INTO users (nome, email, password, role, status, email_verified_at, created_at, updated_at) 
VALUES (
    'Admin Sistema',
    'admin@sistema.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password
    'admin',
    'ativo',
    NOW(),
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE 
    password = VALUES(password),
    role = VALUES(role),
    status = VALUES(status);

-- Obter ID do usuário
SET @user_id = (SELECT id FROM users WHERE email = 'admin@sistema.com');

-- 2. Criar organização para o usuário
INSERT INTO organizations (nome, pessoa_tipo, email, status, subscription_status, created_at, updated_at)
VALUES (
    'Empresa Teste',
    'PJ',
    'admin@sistema.com',
    'ativa',
    'active',
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE id = id;

-- Obter ID da organização
SET @org_id = LAST_INSERT_ID();

-- 3. Vincular usuário à organização
INSERT INTO organization_users (org_id, user_id, role, created_at)
VALUES (@org_id, @user_id, 'admin', NOW())
ON DUPLICATE KEY UPDATE role = VALUES(role);

-- 4. Criar contas bancárias
INSERT INTO accounts (org_id, user_id, nome, tipo, saldo_inicial, saldo_atual, created_at, updated_at) VALUES
(@org_id, @user_id, 'Conta Corrente Banco do Brasil', 'corrente', 5000.00, 8750.00, NOW(), NOW()),
(@org_id, @user_id, 'Conta Poupança Caixa', 'poupanca', 2000.00, 3500.00, NOW(), NOW()),
(@org_id, @user_id, 'Carteira', 'dinheiro', 500.00, 350.00, NOW(), NOW()),
(@org_id, @user_id, 'Conta Investimento XP', 'investimento', 10000.00, 12500.00, NOW(), NOW());

-- Obter IDs das contas
SET @conta_corrente = (SELECT id FROM accounts WHERE org_id = @org_id AND nome = 'Conta Corrente Banco do Brasil');
SET @conta_poupanca = (SELECT id FROM accounts WHERE org_id = @org_id AND nome = 'Conta Poupança Caixa');
SET @conta_carteira = (SELECT id FROM accounts WHERE org_id = @org_id AND nome = 'Carteira');
SET @conta_investimento = (SELECT id FROM accounts WHERE org_id = @org_id AND nome = 'Conta Investimento XP');

-- 5. Criar categorias
INSERT INTO categories (org_id, user_id, nome, tipo, status, created_at, updated_at) VALUES
(@org_id, @user_id, 'Salário', 'entrada', 'ativa', NOW(), NOW()),
(@org_id, @user_id, 'Freelance', 'entrada', 'ativa', NOW(), NOW()),
(@org_id, @user_id, 'Investimentos', 'entrada', 'ativa', NOW(), NOW()),
(@org_id, @user_id, 'Alimentação', 'saida', 'ativa', NOW(), NOW()),
(@org_id, @user_id, 'Transporte', 'saida', 'ativa', NOW(), NOW()),
(@org_id, @user_id, 'Moradia', 'saida', 'ativa', NOW(), NOW()),
(@org_id, @user_id, 'Lazer', 'saida', 'ativa', NOW(), NOW()),
(@org_id, @user_id, 'Saúde', 'saida', 'ativa', NOW(), NOW()),
(@org_id, @user_id, 'Educação', 'saida', 'ativa', NOW(), NOW()),
(@org_id, @user_id, 'Compras', 'saida', 'ativa', NOW(), NOW());

-- Obter IDs das categorias
SET @cat_salario = (SELECT id FROM categories WHERE org_id = @org_id AND nome = 'Salário');
SET @cat_freelance = (SELECT id FROM categories WHERE org_id = @org_id AND nome = 'Freelance');
SET @cat_investimentos = (SELECT id FROM categories WHERE org_id = @org_id AND nome = 'Investimentos');
SET @cat_alimentacao = (SELECT id FROM categories WHERE org_id = @org_id AND nome = 'Alimentação');
SET @cat_transporte = (SELECT id FROM categories WHERE org_id = @org_id AND nome = 'Transporte');
SET @cat_moradia = (SELECT id FROM categories WHERE org_id = @org_id AND nome = 'Moradia');
SET @cat_lazer = (SELECT id FROM categories WHERE org_id = @org_id AND nome = 'Lazer');
SET @cat_saude = (SELECT id FROM categories WHERE org_id = @org_id AND nome = 'Saúde');
SET @cat_educacao = (SELECT id FROM categories WHERE org_id = @org_id AND nome = 'Educação');
SET @cat_compras = (SELECT id FROM categories WHERE org_id = @org_id AND nome = 'Compras');

-- 6. Criar cartões de crédito
INSERT INTO credit_cards (org_id, user_id, nome, bandeira, limite_total, limite_usado, vencimento_dia, fechamento_dia, status, created_at, updated_at) VALUES
(@org_id, @user_id, 'Nubank', 'mastercard', 5000.00, 1250.00, 10, 3, 'ativo', NOW(), NOW()),
(@org_id, @user_id, 'Inter Visa', 'visa', 3000.00, 750.00, 15, 8, 'ativo', NOW(), NOW()),
(@org_id, @user_id, 'C6 Bank', 'mastercard', 4000.00, 2100.00, 5, 25, 'ativo', NOW(), NOW());

-- Obter IDs dos cartões
SET @card_nubank = (SELECT id FROM credit_cards WHERE org_id = @org_id AND nome = 'Nubank');
SET @card_inter = (SELECT id FROM credit_cards WHERE org_id = @org_id AND nome = 'Inter Visa');
SET @card_c6 = (SELECT id FROM credit_cards WHERE org_id = @org_id AND nome = 'C6 Bank');

-- 7. Criar contatos
INSERT INTO contacts (org_id, nome, email, telefone, tipo, status, created_at, updated_at) VALUES
(@org_id, 'Supermercado Extra', 'contato@extra.com.br', '11999999999', 'fornecedor', 'ativo', NOW(), NOW()),
(@org_id, 'Posto Shell', 'posto@shell.com', '11888888888', 'fornecedor', 'ativo', NOW(), NOW()),
(@org_id, 'Cliente ABC', 'abc@cliente.com', '11777777777', 'cliente', 'ativo', NOW(), NOW()),
(@org_id, 'Empresa XYZ', 'xyz@empresa.com', '11666666666', 'cliente', 'ativo', NOW(), NOW());

-- Obter IDs dos contatos
SET @contact_extra = (SELECT id FROM contacts WHERE org_id = @org_id AND nome = 'Supermercado Extra');
SET @contact_shell = (SELECT id FROM contacts WHERE org_id = @org_id AND nome = 'Posto Shell');
SET @contact_abc = (SELECT id FROM contacts WHERE org_id = @org_id AND nome = 'Cliente ABC');
SET @contact_xyz = (SELECT id FROM contacts WHERE org_id = @org_id AND nome = 'Empresa XYZ');

-- 8. Criar transações do mês atual
INSERT INTO transactions (org_id, user_id, account_id, category_id, contact_id, credit_card_id, kind, descricao, valor, data_competencia, data_pagamento, status, created_at, updated_at) VALUES
-- Entradas
(@org_id, @user_id, @conta_corrente, @cat_salario, NULL, NULL, 'entrada', 'Salário Mensal', 8500.00, DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'confirmado', NOW(), NOW()),
(@org_id, @user_id, @conta_corrente, @cat_freelance, @contact_abc, NULL, 'entrada', 'Projeto Website Cliente ABC', 3500.00, DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_SUB(CURDATE(), INTERVAL 10 DAY), 'confirmado', NOW(), NOW()),
(@org_id, @user_id, @conta_investimento, @cat_investimentos, NULL, NULL, 'entrada', 'Rendimento CDB', 250.00, DATE_SUB(CURDATE(), INTERVAL 3 DAY), DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'confirmado', NOW(), NOW()),
(@org_id, @user_id, @conta_corrente, @cat_freelance, @contact_xyz, NULL, 'entrada', 'Consultoria Empresa XYZ', 2000.00, CURDATE(), NULL, 'pendente', NOW(), NOW()),

-- Saídas em contas
(@org_id, @user_id, @conta_corrente, @cat_moradia, NULL, NULL, 'saida', 'Aluguel', 2500.00, DATE_SUB(CURDATE(), INTERVAL 8 DAY), DATE_SUB(CURDATE(), INTERVAL 8 DAY), 'confirmado', NOW(), NOW()),
(@org_id, @user_id, @conta_corrente, @cat_moradia, NULL, NULL, 'saida', 'Condomínio', 650.00, DATE_SUB(CURDATE(), INTERVAL 7 DAY), DATE_SUB(CURDATE(), INTERVAL 7 DAY), 'confirmado', NOW(), NOW()),
(@org_id, @user_id, @conta_corrente, @cat_moradia, NULL, NULL, 'saida', 'Conta de Luz', 180.00, DATE_SUB(CURDATE(), INTERVAL 6 DAY), DATE_SUB(CURDATE(), INTERVAL 6 DAY), 'confirmado', NOW(), NOW()),
(@org_id, @user_id, @conta_corrente, @cat_moradia, NULL, NULL, 'saida', 'Internet', 120.00, DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'confirmado', NOW(), NOW()),
(@org_id, @user_id, @conta_carteira, @cat_alimentacao, @contact_extra, NULL, 'saida', 'Compras do mês', 450.00, DATE_SUB(CURDATE(), INTERVAL 4 DAY), DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'confirmado', NOW(), NOW()),
(@org_id, @user_id, @conta_carteira, @cat_transporte, @contact_shell, NULL, 'saida', 'Combustível', 200.00, DATE_SUB(CURDATE(), INTERVAL 2 DAY), DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'confirmado', NOW(), NOW()),
(@org_id, @user_id, @conta_corrente, @cat_educacao, NULL, NULL, 'saida', 'Curso Online', 197.00, DATE_ADD(CURDATE(), INTERVAL 5 DAY), NULL, 'agendado', NOW(), NOW()),
(@org_id, @user_id, @conta_corrente, @cat_saude, NULL, NULL, 'saida', 'Plano de Saúde', 450.00, DATE_ADD(CURDATE(), INTERVAL 10 DAY), NULL, 'agendado', NOW(), NOW()),

-- Transações em cartões de crédito
(@org_id, @user_id, NULL, @cat_alimentacao, NULL, @card_nubank, 'saida', 'iFood - Jantar', 45.00, DATE_SUB(CURDATE(), INTERVAL 12 DAY), NULL, 'pendente', NOW(), NOW()),
(@org_id, @user_id, NULL, @cat_alimentacao, NULL, @card_nubank, 'saida', 'Padaria', 25.00, DATE_SUB(CURDATE(), INTERVAL 11 DAY), NULL, 'pendente', NOW(), NOW()),
(@org_id, @user_id, NULL, @cat_compras, NULL, @card_nubank, 'saida', 'Amazon - Livros', 120.00, DATE_SUB(CURDATE(), INTERVAL 10 DAY), NULL, 'pendente', NOW(), NOW()),
(@org_id, @user_id, NULL, @cat_lazer, NULL, @card_nubank, 'saida', 'Netflix', 39.90, DATE_SUB(CURDATE(), INTERVAL 9 DAY), NULL, 'pendente', NOW(), NOW()),
(@org_id, @user_id, NULL, @cat_lazer, NULL, @card_nubank, 'saida', 'Spotify', 19.90, DATE_SUB(CURDATE(), INTERVAL 8 DAY), NULL, 'pendente', NOW(), NOW()),
(@org_id, @user_id, NULL, @cat_compras, NULL, @card_nubank, 'saida', 'Mercado Livre - Eletrônicos', 350.00, DATE_SUB(CURDATE(), INTERVAL 7 DAY), NULL, 'pendente', NOW(), NOW()),
(@org_id, @user_id, NULL, @cat_transporte, NULL, @card_nubank, 'saida', 'Uber', 35.00, DATE_SUB(CURDATE(), INTERVAL 6 DAY), NULL, 'pendente', NOW(), NOW()),
(@org_id, @user_id, NULL, @cat_alimentacao, NULL, @card_nubank, 'saida', 'Restaurante', 180.00, DATE_SUB(CURDATE(), INTERVAL 5 DAY), NULL, 'pendente', NOW(), NOW()),
(@org_id, @user_id, NULL, @cat_compras, NULL, @card_nubank, 'saida', 'Roupas', 450.00, DATE_SUB(CURDATE(), INTERVAL 4 DAY), NULL, 'pendente', NOW(), NOW()),

(@org_id, @user_id, NULL, @cat_alimentacao, NULL, @card_inter, 'saida', 'Supermercado', 320.00, DATE_SUB(CURDATE(), INTERVAL 8 DAY), NULL, 'pendente', NOW(), NOW()),
(@org_id, @user_id, NULL, @cat_lazer, NULL, @card_inter, 'saida', 'Cinema', 60.00, DATE_SUB(CURDATE(), INTERVAL 6 DAY), NULL, 'pendente', NOW(), NOW()),
(@org_id, @user_id, NULL, @cat_compras, NULL, @card_inter, 'saida', 'Farmácia', 85.00, DATE_SUB(CURDATE(), INTERVAL 4 DAY), NULL, 'pendente', NOW(), NOW()),
(@org_id, @user_id, NULL, @cat_alimentacao, NULL, @card_inter, 'saida', 'Açougue', 120.00, DATE_SUB(CURDATE(), INTERVAL 3 DAY), NULL, 'pendente', NOW(), NOW()),
(@org_id, @user_id, NULL, @cat_transporte, NULL, @card_inter, 'saida', '99 Taxi', 45.00, DATE_SUB(CURDATE(), INTERVAL 2 DAY), NULL, 'pendente', NOW(), NOW()),
(@org_id, @user_id, NULL, @cat_compras, NULL, @card_inter, 'saida', 'Papelaria', 120.00, DATE_SUB(CURDATE(), INTERVAL 1 DAY), NULL, 'pendente', NOW(), NOW()),

(@org_id, @user_id, NULL, @cat_compras, NULL, @card_c6, 'saida', 'Notebook Dell', 3500.00, DATE_SUB(CURDATE(), INTERVAL 15 DAY), NULL, 'pendente', NOW(), NOW()),
(@org_id, @user_id, NULL, @cat_educacao, NULL, @card_c6, 'saida', 'Udemy - Cursos', 150.00, DATE_SUB(CURDATE(), INTERVAL 10 DAY), NULL, 'pendente', NOW(), NOW()),
(@org_id, @user_id, NULL, @cat_lazer, NULL, @card_c6, 'saida', 'Steam Games', 200.00, DATE_SUB(CURDATE(), INTERVAL 5 DAY), NULL, 'pendente', NOW(), NOW());

-- Parcelar compra do notebook (criar parcelas futuras)
INSERT INTO transactions (org_id, user_id, account_id, category_id, contact_id, credit_card_id, kind, descricao, valor, data_competencia, data_pagamento, status, parcela_numero, parcela_total, created_at, updated_at) VALUES
(@org_id, @user_id, NULL, @cat_compras, NULL, @card_c6, 'saida', 'Notebook Dell - Parcela 1/12', 291.67, DATE_SUB(CURDATE(), INTERVAL 15 DAY), NULL, 'pendente', 1, 12, NOW(), NOW()),
(@org_id, @user_id, NULL, @cat_compras, NULL, @card_c6, 'saida', 'Notebook Dell - Parcela 2/12', 291.67, DATE_ADD(CURDATE(), INTERVAL 15 DAY), NULL, 'agendado', 2, 12, NOW(), NOW()),
(@org_id, @user_id, NULL, @cat_compras, NULL, @card_c6, 'saida', 'Notebook Dell - Parcela 3/12', 291.67, DATE_ADD(CURDATE(), INTERVAL 45 DAY), NULL, 'agendado', 3, 12, NOW(), NOW());

-- 9. Criar caixinhas/objetivos
INSERT INTO vault_goals (org_id, created_by, titulo, descricao, valor_meta, valor_atual, data_meta, categoria, cor, icone, prioridade, ativo, created_at, updated_at) VALUES
(@org_id, @user_id, 'Viagem Europa', 'Viagem de férias para Europa em 2025', 15000.00, 3500.00, '2025-07-01', 'viagem', '#4CAF50', 'fas fa-plane', 'media', 1, NOW(), NOW()),
(@org_id, @user_id, 'Reserva de Emergência', 'Fundo para emergências - 6 meses de despesas', 30000.00, 12000.00, '2024-12-31', 'emergencia', '#FF5722', 'fas fa-shield-alt', 'alta', 1, NOW(), NOW()),
(@org_id, @user_id, 'Troca do Carro', 'Entrada para trocar o carro', 20000.00, 8500.00, '2024-10-01', 'veiculo', '#2196F3', 'fas fa-car', 'media', 1, NOW(), NOW()),
(@org_id, @user_id, 'Reforma Casa', 'Reforma da cozinha e banheiro', 10000.00, 2100.00, '2024-08-01', 'casa', '#9C27B0', 'fas fa-home', 'baixa', 1, NOW(), NOW()),
(@org_id, @user_id, 'Presente de Natal', 'Presentes para a família', 2000.00, 500.00, '2024-12-20', 'outros', '#FFC107', 'fas fa-gift', 'baixa', 1, NOW(), NOW());

-- Obter IDs das caixinhas
SET @vault_viagem = (SELECT id FROM vault_goals WHERE org_id = @org_id AND titulo = 'Viagem Europa');
SET @vault_emergencia = (SELECT id FROM vault_goals WHERE org_id = @org_id AND titulo = 'Reserva de Emergência');
SET @vault_carro = (SELECT id FROM vault_goals WHERE org_id = @org_id AND titulo = 'Troca do Carro');

-- 10. Criar movimentações nas caixinhas
INSERT INTO vault_movements (vault_id, org_id, user_id, tipo, valor, descricao, data_movimento, created_at, updated_at) VALUES
(@vault_viagem, @org_id, @user_id, 'deposito', 500.00, 'Depósito mensal', DATE_SUB(CURDATE(), INTERVAL 30 DAY), NOW(), NOW()),
(@vault_viagem, @org_id, @user_id, 'deposito', 500.00, 'Depósito mensal', DATE_SUB(CURDATE(), INTERVAL 60 DAY), NOW(), NOW()),
(@vault_viagem, @org_id, @user_id, 'deposito', 1000.00, 'Bônus trabalho', DATE_SUB(CURDATE(), INTERVAL 45 DAY), NOW(), NOW()),
(@vault_viagem, @org_id, @user_id, 'deposito', 1500.00, '13º salário', DATE_SUB(CURDATE(), INTERVAL 15 DAY), NOW(), NOW()),

(@vault_emergencia, @org_id, @user_id, 'deposito', 1000.00, 'Depósito inicial', DATE_SUB(CURDATE(), INTERVAL 90 DAY), NOW(), NOW()),
(@vault_emergencia, @org_id, @user_id, 'deposito', 2000.00, 'Depósito mensal', DATE_SUB(CURDATE(), INTERVAL 60 DAY), NOW(), NOW()),
(@vault_emergencia, @org_id, @user_id, 'deposito', 2000.00, 'Depósito mensal', DATE_SUB(CURDATE(), INTERVAL 30 DAY), NOW(), NOW()),
(@vault_emergencia, @org_id, @user_id, 'deposito', 3000.00, 'Bônus anual', DATE_SUB(CURDATE(), INTERVAL 20 DAY), NOW(), NOW()),
(@vault_emergencia, @org_id, @user_id, 'deposito', 4000.00, 'Venda de item', DATE_SUB(CURDATE(), INTERVAL 10 DAY), NOW(), NOW()),

(@vault_carro, @org_id, @user_id, 'deposito', 1500.00, 'Poupança mensal', DATE_SUB(CURDATE(), INTERVAL 60 DAY), NOW(), NOW()),
(@vault_carro, @org_id, @user_id, 'deposito', 1500.00, 'Poupança mensal', DATE_SUB(CURDATE(), INTERVAL 30 DAY), NOW(), NOW()),
(@vault_carro, @org_id, @user_id, 'deposito', 2500.00, 'Venda equipamento antigo', DATE_SUB(CURDATE(), INTERVAL 25 DAY), NOW(), NOW()),
(@vault_carro, @org_id, @user_id, 'deposito', 3000.00, 'Freelance extra', DATE_SUB(CURDATE(), INTERVAL 5 DAY), NOW(), NOW());

-- 11. Criar transferências entre contas
INSERT INTO transfers (org_id, user_id, conta_origem_id, conta_destino_id, valor, descricao, data_transferencia, status, created_at, updated_at) VALUES
(@org_id, @user_id, @conta_corrente, @conta_poupanca, 1000.00, 'Poupança mensal', DATE_SUB(CURDATE(), INTERVAL 10 DAY), 'confirmado', NOW(), NOW()),
(@org_id, @user_id, @conta_corrente, @conta_investimento, 2000.00, 'Investimento CDB', DATE_SUB(CURDATE(), INTERVAL 8 DAY), 'confirmado', NOW(), NOW()),
(@org_id, @user_id, @conta_poupanca, @conta_corrente, 500.00, 'Resgate para despesas', DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'confirmado', NOW(), NOW()),
(@org_id, @user_id, @conta_corrente, @conta_carteira, 300.00, 'Dinheiro para despesas diárias', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'confirmado', NOW(), NOW());

-- 12. Criar centros de custo
INSERT INTO cost_centers (org_id, user_id, nome, descricao, status, created_at, updated_at) VALUES
(@org_id, @user_id, 'Casa', 'Despesas relacionadas à moradia', 'ativo', NOW(), NOW()),
(@org_id, @user_id, 'Pessoal', 'Despesas pessoais', 'ativo', NOW(), NOW()),
(@org_id, @user_id, 'Trabalho', 'Despesas relacionadas ao trabalho', 'ativo', NOW(), NOW()),
(@org_id, @user_id, 'Família', 'Despesas familiares', 'ativo', NOW(), NOW());

-- Mensagem de conclusão
SELECT 'Dados de teste criados com sucesso!' AS mensagem,
       'Email: admin@sistema.com' AS usuario,
       'Senha: password' AS senha;