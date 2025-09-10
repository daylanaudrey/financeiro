-- Criar uma conta do tipo carteira para testar o depósito

INSERT INTO accounts (
    org_id, 
    nome, 
    tipo, 
    saldo_inicial, 
    saldo_atual, 
    ativo, 
    descricao, 
    cor, 
    created_by
) VALUES (
    1, 
    'Carteira Principal', 
    'carteira', 
    0.00, 
    0.00, 
    TRUE, 
    'Carteira para dinheiro em espécie', 
    '#ffc107', 
    1
);

-- Criar mais uma conta carteira para ter opções
INSERT INTO accounts (
    org_id, 
    nome, 
    tipo, 
    saldo_inicial, 
    saldo_atual, 
    ativo, 
    descricao, 
    cor, 
    created_by
) VALUES (
    1, 
    'Carteira Reserva', 
    'carteira', 
    0.00, 
    0.00, 
    TRUE, 
    'Carteira reserva para emergências', 
    '#28a745', 
    1
);