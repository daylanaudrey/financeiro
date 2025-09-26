-- Dados de demonstração para admin@sistema.com (org_id = 5)
-- Este script criará dados completos para todas as funcionalidades

SET @admin_user_id = 9;
SET @admin_org_id = 5;

-- 1. CONTAS BANCÁRIAS
INSERT INTO accounts (org_id, nome, tipo, saldo_inicial, saldo_atual, created_at, updated_at) VALUES
(@admin_org_id, 'Conta Corrente Itaú', 'corrente', 8000.00, 12750.00, NOW(), NOW()),
(@admin_org_id, 'Conta Poupança Bradesco', 'poupanca', 5000.00, 8200.00, NOW(), NOW()),
(@admin_org_id, 'Carteira Física', 'dinheiro', 300.00, 185.00, NOW(), NOW()),
(@admin_org_id, 'Conta Investimento Rico', 'investimento', 15000.00, 18500.00, NOW(), NOW()),
(@admin_org_id, 'Conta Salário Santander', 'corrente', 0.00, 2150.00, NOW(), NOW());

-- Obter IDs das contas
SET @conta_itau = (SELECT id FROM accounts WHERE org_id = @admin_org_id AND nome = 'Conta Corrente Itaú');
SET @conta_bradesco = (SELECT id FROM accounts WHERE org_id = @admin_org_id AND nome = 'Conta Poupança Bradesco');
SET @conta_carteira = (SELECT id FROM accounts WHERE org_id = @admin_org_id AND nome = 'Carteira Física');
SET @conta_rico = (SELECT id FROM accounts WHERE org_id = @admin_org_id AND nome = 'Conta Investimento Rico');
SET @conta_santander = (SELECT id FROM accounts WHERE org_id = @admin_org_id AND nome = 'Conta Salário Santander');

-- 2. CATEGORIAS
INSERT INTO categories (org_id, nome, tipo, status, created_at, updated_at) VALUES
-- Receitas
(@admin_org_id, 'Salário CLT', 'entrada', 'ativa', NOW(), NOW()),
(@admin_org_id, 'Freelances', 'entrada', 'ativa', NOW(), NOW()),
(@admin_org_id, 'Vendas Online', 'entrada', 'ativa', NOW(), NOW()),
(@admin_org_id, 'Dividendos', 'entrada', 'ativa', NOW(), NOW()),
(@admin_org_id, 'Rendimentos', 'entrada', 'ativa', NOW(), NOW()),
(@admin_org_id, 'Cashback', 'entrada', 'ativa', NOW(), NOW()),

-- Despesas
(@admin_org_id, 'Alimentação', 'saida', 'ativa', NOW(), NOW()),
(@admin_org_id, 'Transporte', 'saida', 'ativa', NOW(), NOW()),
(@admin_org_id, 'Moradia', 'saida', 'ativa', NOW(), NOW()),
(@admin_org_id, 'Entretenimento', 'saida', 'ativa', NOW(), NOW()),
(@admin_org_id, 'Saúde', 'saida', 'ativa', NOW(), NOW()),
(@admin_org_id, 'Educação', 'saida', 'ativa', NOW(), NOW()),
(@admin_org_id, 'Vestuário', 'saida', 'ativa', NOW(), NOW()),
(@admin_org_id, 'Tecnologia', 'saida', 'ativa', NOW(), NOW()),
(@admin_org_id, 'Impostos', 'saida', 'ativa', NOW(), NOW()),
(@admin_org_id, 'Seguros', 'saida', 'ativa', NOW(), NOW()),
(@admin_org_id, 'Pets', 'saida', 'ativa', NOW(), NOW()),
(@admin_org_id, 'Doações', 'saida', 'ativa', NOW(), NOW());

-- Obter IDs das categorias principais
SET @cat_salario = (SELECT id FROM categories WHERE org_id = @admin_org_id AND nome = 'Salário CLT');
SET @cat_freelance = (SELECT id FROM categories WHERE org_id = @admin_org_id AND nome = 'Freelances');
SET @cat_vendas = (SELECT id FROM categories WHERE org_id = @admin_org_id AND nome = 'Vendas Online');
SET @cat_dividendos = (SELECT id FROM categories WHERE org_id = @admin_org_id AND nome = 'Dividendos');
SET @cat_alimentacao = (SELECT id FROM categories WHERE org_id = @admin_org_id AND nome = 'Alimentação');
SET @cat_transporte = (SELECT id FROM categories WHERE org_id = @admin_org_id AND nome = 'Transporte');
SET @cat_moradia = (SELECT id FROM categories WHERE org_id = @admin_org_id AND nome = 'Moradia');
SET @cat_entretenimento = (SELECT id FROM categories WHERE org_id = @admin_org_id AND nome = 'Entretenimento');
SET @cat_saude = (SELECT id FROM categories WHERE org_id = @admin_org_id AND nome = 'Saúde');
SET @cat_educacao = (SELECT id FROM categories WHERE org_id = @admin_org_id AND nome = 'Educação');
SET @cat_tecnologia = (SELECT id FROM categories WHERE org_id = @admin_org_id AND nome = 'Tecnologia');

-- 3. CARTÕES DE CRÉDITO
INSERT INTO credit_cards (org_id, nome, bandeira, limite_total, limite_usado, vencimento_dia, fechamento_dia, status, created_at, updated_at) VALUES
(@admin_org_id, 'Itaú Click Visa', 'visa', 8000.00, 2450.00, 12, 5, 'ativo', NOW(), NOW()),
(@admin_org_id, 'Santander SX Master', 'mastercard', 6000.00, 1850.00, 18, 11, 'ativo', NOW(), NOW()),
(@admin_org_id, 'Bradesco Elo', 'elo', 4000.00, 980.00, 25, 18, 'ativo', NOW(), NOW()),
(@admin_org_id, 'XP Visa Infinite', 'visa', 15000.00, 4200.00, 8, 1, 'ativo', NOW(), NOW());

-- Obter IDs dos cartões
SET @card_itau = (SELECT id FROM credit_cards WHERE org_id = @admin_org_id AND nome = 'Itaú Click Visa');
SET @card_santander = (SELECT id FROM credit_cards WHERE org_id = @admin_org_id AND nome = 'Santander SX Master');
SET @card_bradesco = (SELECT id FROM credit_cards WHERE org_id = @admin_org_id AND nome = 'Bradesco Elo');
SET @card_xp = (SELECT id FROM credit_cards WHERE org_id = @admin_org_id AND nome = 'XP Visa Infinite');

-- 4. CONTATOS
INSERT INTO contacts (org_id, nome, email, telefone, tipo, status, created_at, updated_at) VALUES
(@admin_org_id, 'Empresa ABC Ltda', 'contato@abc.com.br', '11987654321', 'cliente', 'ativo', NOW(), NOW()),
(@admin_org_id, 'João Silva', 'joao@email.com', '11976543210', 'cliente', 'ativo', NOW(), NOW()),
(@admin_org_id, 'Supermercado São João', 'vendas@saojoao.com', '1134567890', 'fornecedor', 'ativo', NOW(), NOW()),
(@admin_org_id, 'Posto Ipiranga Centro', 'centro@ipiranga.com', '1123456789', 'fornecedor', 'ativo', NOW(), NOW()),
(@admin_org_id, 'Dr. Carlos Médico', 'dr.carlos@clinica.com', '1145678901', 'prestador', 'ativo', NOW(), NOW()),
(@admin_org_id, 'Maria Freelancer', 'maria@designer.com', '11956781234', 'parceiro', 'ativo', NOW(), NOW());

-- Obter IDs dos contatos
SET @contact_abc = (SELECT id FROM contacts WHERE org_id = @admin_org_id AND nome = 'Empresa ABC Ltda');
SET @contact_joao = (SELECT id FROM contacts WHERE org_id = @admin_org_id AND nome = 'João Silva');
SET @contact_supermercado = (SELECT id FROM contacts WHERE org_id = @admin_org_id AND nome = 'Supermercado São João');
SET @contact_posto = (SELECT id FROM contacts WHERE org_id = @admin_org_id AND nome = 'Posto Ipiranga Centro');
SET @contact_medico = (SELECT id FROM contacts WHERE org_id = @admin_org_id AND nome = 'Dr. Carlos Médico');

-- 5. CENTROS DE CUSTO
INSERT INTO cost_centers (org_id, nome, descricao, status, created_at, updated_at) VALUES
(@admin_org_id, 'Pessoal', 'Gastos pessoais e familiares', 'ativo', NOW(), NOW()),
(@admin_org_id, 'Profissional', 'Gastos relacionados ao trabalho', 'ativo', NOW(), NOW()),
(@admin_org_id, 'Investimentos', 'Aplicações e investimentos', 'ativo', NOW(), NOW()),
(@admin_org_id, 'Casa', 'Manutenção e melhorias da casa', 'ativo', NOW(), NOW());

-- 6. TRANSAÇÕES - MÊS ATUAL
-- RECEITAS
INSERT INTO transactions (org_id, account_id, category_id, contact_id, credit_card_id, kind, descricao, valor, data_competencia, data_pagamento, status, created_by, created_at, updated_at) VALUES
-- Salário
(@admin_org_id, @conta_santander, @cat_salario, NULL, NULL, 'entrada', 'Salário Janeiro 2025', 7500.00, '2025-01-05', '2025-01-05', 'confirmado', @admin_user_id, NOW(), NOW()),

-- Freelances
(@admin_org_id, @conta_itau, @cat_freelance, @contact_abc, NULL, 'entrada', 'Desenvolvimento Website ABC', 4500.00, '2025-01-08', '2025-01-08', 'confirmado', @admin_user_id, NOW(), NOW()),
(@admin_org_id, @conta_itau, @cat_freelance, @contact_joao, NULL, 'entrada', 'Consultoria João Silva', 1800.00, '2025-01-12', NULL, 'pendente', @admin_user_id, NOW(), NOW()),

-- Vendas online
(@admin_org_id, @conta_itau, @cat_vendas, NULL, NULL, 'entrada', 'Venda Curso Online', 850.00, '2025-01-03', '2025-01-03', 'confirmado', @admin_user_id, NOW(), NOW()),
(@admin_org_id, @conta_itau, @cat_vendas, NULL, NULL, 'entrada', 'Venda E-book', 120.00, '2025-01-07', '2025-01-07', 'confirmado', @admin_user_id, NOW(), NOW()),

-- Dividendos e rendimentos
(@admin_org_id, @conta_rico, @cat_dividendos, NULL, NULL, 'entrada', 'Dividendos ITUB4', 180.00, '2025-01-10', '2025-01-10', 'confirmado', @admin_user_id, NOW(), NOW()),
(@admin_org_id, @conta_rico, @cat_dividendos, NULL, NULL, 'entrada', 'Rendimento CDB', 450.00, '2025-01-15', '2025-01-15', 'confirmado', @admin_user_id, NOW(), NOW()),

-- DESPESAS EM CONTAS
-- Moradia
(@admin_org_id, @conta_itau, @cat_moradia, NULL, NULL, 'saida', 'Aluguel Janeiro', 2800.00, '2025-01-01', '2025-01-01', 'confirmado', @admin_user_id, NOW(), NOW()),
(@admin_org_id, @conta_itau, @cat_moradia, NULL, NULL, 'saida', 'Condomínio Janeiro', 480.00, '2025-01-05', '2025-01-05', 'confirmado', @admin_user_id, NOW(), NOW()),
(@admin_org_id, @conta_itau, @cat_moradia, NULL, NULL, 'saida', 'Conta de Luz', 165.00, '2025-01-08', '2025-01-08', 'confirmado', @admin_user_id, NOW(), NOW()),
(@admin_org_id, @conta_itau, @cat_moradia, NULL, NULL, 'saida', 'Conta de Água', 85.00, '2025-01-10', '2025-01-10', 'confirmado', @admin_user_id, NOW(), NOW()),
(@admin_org_id, @conta_itau, @cat_moradia, NULL, NULL, 'saida', 'Internet Fibra', 99.90, '2025-01-12', '2025-01-12', 'confirmado', @admin_user_id, NOW(), NOW()),

-- Transporte
(@admin_org_id, @conta_carteira, @cat_transporte, @contact_posto, NULL, 'saida', 'Combustível', 180.00, '2025-01-04', '2025-01-04', 'confirmado', @admin_user_id, NOW(), NOW()),
(@admin_org_id, @conta_carteira, @cat_transporte, @contact_posto, NULL, 'saida', 'Combustível', 175.00, '2025-01-11', '2025-01-11', 'confirmado', @admin_user_id, NOW(), NOW()),
(@admin_org_id, @conta_itau, @cat_transporte, NULL, NULL, 'saida', 'IPVA 2025', 420.00, '2025-01-15', '2025-01-15', 'confirmado', @admin_user_id, NOW(), NOW()),

-- Saúde
(@admin_org_id, @conta_itau, @cat_saude, NULL, NULL, 'saida', 'Plano de Saúde', 380.00, '2025-01-06', '2025-01-06', 'confirmado', @admin_user_id, NOW(), NOW()),
(@admin_org_id, @conta_carteira, @cat_saude, @contact_medico, NULL, 'saida', 'Consulta Dr. Carlos', 250.00, '2025-01-09', '2025-01-09', 'confirmado', @admin_user_id, NOW(), NOW()),

-- Alimentação
(@admin_org_id, @conta_carteira, @cat_alimentacao, @contact_supermercado, NULL, 'saida', 'Compras da Semana', 320.00, '2025-01-02', '2025-01-02', 'confirmado', @admin_user_id, NOW(), NOW()),
(@admin_org_id, @conta_carteira, @cat_alimentacao, @contact_supermercado, NULL, 'saida', 'Compras da Semana', 285.00, '2025-01-09', '2025-01-09', 'confirmado', @admin_user_id, NOW(), NOW()),

-- TRANSAÇÕES EM CARTÕES DE CRÉDITO
-- Itaú Click Visa
(@admin_org_id, NULL, @cat_alimentacao, NULL, @card_itau, 'saida', 'iFood - Almoço', 38.90, '2025-01-02', NULL, 'pendente', @admin_user_id, NOW(), NOW()),
(@admin_org_id, NULL, @cat_alimentacao, NULL, @card_itau, 'saida', 'Uber Eats - Jantar', 45.50, '2025-01-03', NULL, 'pendente', @admin_user_id, NOW(), NOW()),
(@admin_org_id, NULL, @cat_entretenimento, NULL, @card_itau, 'saida', 'Netflix Mensal', 55.90, '2025-01-05', NULL, 'pendente', @admin_user_id, NOW(), NOW()),
(@admin_org_id, NULL, @cat_entretenimento, NULL, @card_itau, 'saida', 'Spotify Premium', 21.90, '2025-01-05', NULL, 'pendente', @admin_user_id, NOW(), NOW()),
(@admin_org_id, NULL, @cat_tecnologia, NULL, @card_itau, 'saida', 'Amazon - Cabo USB-C', 89.90, '2025-01-07', NULL, 'pendente', @admin_user_id, NOW(), NOW()),
(@admin_org_id, NULL, @cat_alimentacao, NULL, @card_itau, 'saida', 'Padaria do Bairro', 25.80, '2025-01-08', NULL, 'pendente', @admin_user_id, NOW(), NOW()),
(@admin_org_id, NULL, @cat_transporte, NULL, @card_itau, 'saida', 'Uber - Centro', 28.50, '2025-01-10', NULL, 'pendente', @admin_user_id, NOW(), NOW()),
(@admin_org_id, NULL, @cat_alimentacao, NULL, @card_itau, 'saida', 'Restaurante Japonês', 185.00, '2025-01-12', NULL, 'pendente', @admin_user_id, NOW(), NOW()),
(@admin_org_id, NULL, @cat_tecnologia, NULL, @card_itau, 'saida', 'GitHub Pro', 45.00, '2025-01-13', NULL, 'pendente', @admin_user_id, NOW(), NOW()),

-- Santander SX Master
(@admin_org_id, NULL, @cat_alimentacao, NULL, @card_santander, 'saida', 'Mercado Extra', 450.00, '2025-01-04', NULL, 'pendente', @admin_user_id, NOW(), NOW()),
(@admin_org_id, NULL, @cat_educacao, NULL, @card_santander, 'saida', 'Curso Udemy', 199.90, '2025-01-06', NULL, 'pendente', @admin_user_id, NOW(), NOW()),
(@admin_org_id, NULL, @cat_entretenimento, NULL, @card_santander, 'saida', 'Cinema Multiplex', 85.00, '2025-01-08', NULL, 'pendente', @admin_user_id, NOW(), NOW()),
(@admin_org_id, NULL, @cat_tecnologia, NULL, @card_santander, 'saida', 'Microsoft 365', 35.90, '2025-01-10', NULL, 'pendente', @admin_user_id, NOW(), NOW()),
(@admin_org_id, NULL, @cat_alimentacao, NULL, @card_santander, 'saida', 'Açougue Premium', 180.00, '2025-01-11', NULL, 'pendente', @admin_user_id, NOW(), NOW()),
(@admin_org_id, NULL, @cat_transporte, NULL, @card_santander, 'saida', '99 Táxi', 42.30, '2025-01-14', NULL, 'pendente', @admin_user_id, NOW(), NOW()),

-- XP Visa Infinite (compras maiores)
(@admin_org_id, NULL, @cat_tecnologia, NULL, @card_xp, 'saida', 'MacBook Air M2', 8999.00, '2025-01-05', NULL, 'pendente', @admin_user_id, NOW(), NOW()),
(@admin_org_id, NULL, @cat_educacao, NULL, @card_xp, 'saida', 'MBA Online', 2400.00, '2025-01-08', NULL, 'pendente', @admin_user_id, NOW(), NOW()),
(@admin_org_id, NULL, @cat_entretenimento, NULL, @card_xp, 'saida', 'Show Bruno Mars', 850.00, '2025-01-12', NULL, 'pendente', @admin_user_id, NOW(), NOW()),

-- TRANSAÇÕES AGENDADAS (futuras)
(@admin_org_id, @conta_santander, @cat_salario, NULL, NULL, 'entrada', 'Salário Fevereiro 2025', 7500.00, '2025-02-05', NULL, 'agendado', @admin_user_id, NOW(), NOW()),
(@admin_org_id, @conta_itau, @cat_moradia, NULL, NULL, 'saida', 'Aluguel Fevereiro', 2800.00, '2025-02-01', NULL, 'agendado', @admin_user_id, NOW(), NOW()),
(@admin_org_id, @conta_itau, @cat_moradia, NULL, NULL, 'saida', 'Condomínio Fevereiro', 480.00, '2025-02-05', NULL, 'agendado', @admin_user_id, NOW(), NOW()),
(@admin_org_id, @conta_itau, @cat_saude, NULL, NULL, 'saida', 'Plano de Saúde Fev', 380.00, '2025-02-06', NULL, 'agendado', @admin_user_id, NOW(), NOW());

-- 7. CAIXINHAS/OBJETIVOS
INSERT INTO vault_goals (org_id, created_by, titulo, descricao, valor_meta, valor_atual, data_meta, categoria, cor, icone, prioridade, ativo, created_at, updated_at) VALUES
(@admin_org_id, @admin_user_id, 'Férias na Tailândia', 'Viagem de 15 dias para Tailândia em julho', 18000.00, 5200.00, '2025-07-01', 'viagem', '#FF9800', 'fas fa-plane', 'alta', 1, NOW(), NOW()),
(@admin_org_id, @admin_user_id, 'Reserva de Emergência', 'Fundo de emergência para 12 meses', 45000.00, 18500.00, '2025-12-31', 'emergencia', '#F44336', 'fas fa-shield-alt', 'alta', 1, NOW(), NOW()),
(@admin_org_id, @admin_user_id, 'Carro Novo', 'Troca do carro atual por um híbrido', 80000.00, 25000.00, '2025-11-01', 'veiculo', '#2196F3', 'fas fa-car', 'media', 1, NOW(), NOW()),
(@admin_org_id, @admin_user_id, 'Setup Gaming', 'PC Gamer completo para trabalho e jogos', 12000.00, 3800.00, '2025-06-01', 'outros', '#9C27B0', 'fas fa-gamepad', 'baixa', 1, NOW(), NOW()),
(@admin_org_id, @admin_user_id, 'Curso de Inglês', 'Intercâmbio no Canadá para fluência', 25000.00, 7200.00, '2026-01-01', 'educacao', '#4CAF50', 'fas fa-graduation-cap', 'media', 1, NOW(), NOW()),
(@admin_org_id, @admin_user_id, 'Investimento Imóvel', 'Entrada para apartamento de investimento', 150000.00, 42000.00, '2026-06-01', 'investimento', '#607D8B', 'fas fa-building', 'alta', 1, NOW(), NOW());

-- Obter IDs das caixinhas
SET @vault_tailandia = (SELECT id FROM vault_goals WHERE org_id = @admin_org_id AND titulo = 'Férias na Tailândia');
SET @vault_emergencia = (SELECT id FROM vault_goals WHERE org_id = @admin_org_id AND titulo = 'Reserva de Emergência');
SET @vault_carro = (SELECT id FROM vault_goals WHERE org_id = @admin_org_id AND titulo = 'Carro Novo');
SET @vault_setup = (SELECT id FROM vault_goals WHERE org_id = @admin_org_id AND titulo = 'Setup Gaming');

-- 8. MOVIMENTAÇÕES NAS CAIXINHAS
INSERT INTO vault_movements (vault_id, org_id, tipo, valor, descricao, data_movimento, created_at, updated_at) VALUES
-- Tailândia
(@vault_tailandia, @admin_org_id, 'deposito', 1000.00, 'Depósito inicial', DATE_SUB(CURDATE(), INTERVAL 120 DAY), NOW(), NOW()),
(@vault_tailandia, @admin_org_id, 'deposito', 800.00, 'Economia mensal Nov', DATE_SUB(CURDATE(), INTERVAL 90 DAY), NOW(), NOW()),
(@vault_tailandia, @admin_org_id, 'deposito', 800.00, 'Economia mensal Dez', DATE_SUB(CURDATE(), INTERVAL 60 DAY), NOW(), NOW()),
(@vault_tailandia, @admin_org_id, 'deposito', 1200.00, '13º salário', DATE_SUB(CURDATE(), INTERVAL 45 DAY), NOW(), NOW()),
(@vault_tailandia, @admin_org_id, 'deposito', 800.00, 'Economia mensal Jan', DATE_SUB(CURDATE(), INTERVAL 20 DAY), NOW(), NOW()),
(@vault_tailandia, @admin_org_id, 'deposito', 600.00, 'Freelance extra', DATE_SUB(CURDATE(), INTERVAL 10 DAY), NOW(), NOW()),

-- Emergência
(@vault_emergencia, @admin_org_id, 'deposito', 5000.00, 'Depósito inicial', DATE_SUB(CURDATE(), INTERVAL 150 DAY), NOW(), NOW()),
(@vault_emergencia, @admin_org_id, 'deposito', 2500.00, 'Economia Out', DATE_SUB(CURDATE(), INTERVAL 120 DAY), NOW(), NOW()),
(@vault_emergencia, @admin_org_id, 'deposito', 3000.00, 'Economia Nov', DATE_SUB(CURDATE(), INTERVAL 90 DAY), NOW(), NOW()),
(@vault_emergencia, @admin_org_id, 'deposito', 3000.00, 'Economia Dez', DATE_SUB(CURDATE(), INTERVAL 60 DAY), NOW(), NOW()),
(@vault_emergencia, @admin_org_id, 'deposito', 5000.00, 'Bônus anual', DATE_SUB(CURDATE(), INTERVAL 30 DAY), NOW(), NOW()),

-- Carro
(@vault_carro, @admin_org_id, 'deposito', 8000.00, 'Venda carro antigo', DATE_SUB(CURDATE(), INTERVAL 100 DAY), NOW(), NOW()),
(@vault_carro, @admin_org_id, 'deposito', 3000.00, 'Poupança Set', DATE_SUB(CURDATE(), INTERVAL 120 DAY), NOW(), NOW()),
(@vault_carro, @admin_org_id, 'deposito', 3500.00, 'Poupança Out', DATE_SUB(CURDATE(), INTERVAL 90 DAY), NOW(), NOW()),
(@vault_carro, @admin_org_id, 'deposito', 3500.00, 'Poupança Nov', DATE_SUB(CURDATE(), INTERVAL 60 DAY), NOW(), NOW()),
(@vault_carro, @admin_org_id, 'deposito', 4000.00, 'Poupança Dez', DATE_SUB(CURDATE(), INTERVAL 30 DAY), NOW(), NOW()),
(@vault_carro, @admin_org_id, 'deposito', 3000.00, 'Poupança Jan', DATE_SUB(CURDATE(), INTERVAL 15 DAY), NOW(), NOW()),

-- Setup Gaming
(@vault_setup, @admin_org_id, 'deposito', 1200.00, 'Início da economia', DATE_SUB(CURDATE(), INTERVAL 80 DAY), NOW(), NOW()),
(@vault_setup, @admin_org_id, 'deposito', 800.00, 'Economia Nov', DATE_SUB(CURDATE(), INTERVAL 60 DAY), NOW(), NOW()),
(@vault_setup, @admin_org_id, 'deposito', 900.00, 'Economia Dez', DATE_SUB(CURDATE(), INTERVAL 30 DAY), NOW(), NOW()),
(@vault_setup, @admin_org_id, 'deposito', 900.00, 'Economia Jan', DATE_SUB(CURDATE(), INTERVAL 10 DAY), NOW(), NOW());

-- 9. TRANSFERÊNCIAS ENTRE CONTAS
INSERT INTO transfers (org_id, conta_origem_id, conta_destino_id, valor, descricao, data_transferencia, status, created_at, updated_at) VALUES
(@admin_org_id, @conta_santander, @conta_itau, 2000.00, 'Centralizar na conta principal', '2025-01-06', 'confirmado', NOW(), NOW()),
(@admin_org_id, @conta_itau, @conta_bradesco, 3000.00, 'Poupança mensal', '2025-01-08', 'confirmado', NOW(), NOW()),
(@admin_org_id, @conta_itau, @conta_rico, 5000.00, 'Investimento CDB', '2025-01-10', 'confirmado', NOW(), NOW()),
(@admin_org_id, @conta_bradesco, @conta_itau, 1200.00, 'Resgate para pagamentos', '2025-01-12', 'confirmado', NOW(), NOW()),
(@admin_org_id, @conta_itau, @conta_carteira, 500.00, 'Dinheiro para gastos', '2025-01-13', 'confirmado', NOW(), NOW()),
(@admin_org_id, @conta_rico, @conta_itau, 800.00, 'Resgate dividendos', '2025-01-14', 'confirmado', NOW(), NOW());

-- Mensagem final
SELECT 'Dados de demonstração criados com sucesso!' AS status,
       'Admin agora tem dados completos em todas as tabelas' AS detalhes;