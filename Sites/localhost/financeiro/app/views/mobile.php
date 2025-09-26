<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Financeiro Mobile</title>
    
    <!-- PWA Meta Tags -->
    <meta name="description" content="Sistema de gestão financeira otimizado para mobile">
    <meta name="theme-color" content="#007bff">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Financeiro">
    <meta name="msapplication-TileColor" content="#007bff">
    
    <!-- PWA Icons -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg width='32' height='32' xmlns='http://www.w3.org/2000/svg'%3E%3Crect width='32' height='32' rx='6' ry='6' fill='%23007bff'/%3E%3Ctext x='16' y='22' font-family='Arial' font-size='16' font-weight='bold' text-anchor='middle' fill='white'%3E$%3C/text%3E%3C/svg%3E">
    <link rel="apple-touch-icon" href="data:image/svg+xml,%3Csvg width='180' height='180' xmlns='http://www.w3.org/2000/svg'%3E%3Crect width='180' height='180' rx='32' ry='32' fill='%23007bff'/%3E%3Ccircle cx='90' cy='90' r='52' fill='white'/%3E%3Ctext x='90' y='108' font-family='Arial' font-size='60' font-weight='bold' text-anchor='middle' fill='%23007bff'%3E$%3C/text%3E%3C/svg%3E">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="<?= url('/mobile-manifest.json') ?>">
    
    <!-- Preload Critical Resources -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #007bff;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --light-gray: #f8f9fa;
            --border-color: #dee2e6;
        }
        
        body {
            background-color: var(--light-gray);
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .mobile-container {
            max-width: 100vw;
            margin: 0;
            padding: 10px;
        }
        
        .mobile-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #0056b3 100%);
            color: white;
            padding: 20px 15px;
            margin: -10px -10px 20px -10px;
            text-align: center;
            border-radius: 0 0 20px 20px;
        }
        
        .mobile-header h1 {
            font-size: 1.5rem;
            margin: 0 0 10px 0;
            font-weight: 600;
        }
        
        .balance-info {
            display: flex;
            justify-content: space-around;
            text-align: center;
            margin-top: 15px;
        }
        
        .balance-item {
            flex: 1;
        }
        
        .balance-item .label {
            font-size: 0.8rem;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        
        .balance-item .value {
            font-size: 1.1rem;
            font-weight: bold;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .action-btn {
            padding: 20px;
            border-radius: 15px;
            border: none;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: transform 0.2s;
        }
        
        .action-btn:hover, .action-btn:active {
            transform: translateY(-2px);
        }
        
        .action-btn.success {
            background: linear-gradient(135deg, var(--success-color) 0%, #1e7e34 100%);
        }
        
        .action-btn.danger {
            background: linear-gradient(135deg, var(--danger-color) 0%, #bd2130 100%);
        }
        
        .action-btn i {
            font-size: 1.5rem;
        }
        
        .accounts-section {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .account-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .account-item:last-child {
            border-bottom: none;
        }
        
        .account-info .name {
            font-weight: 600;
            color: #333;
        }
        
        .account-info .type {
            font-size: 0.8rem;
            color: #666;
        }
        
        .account-balance {
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .transactions-section {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .transaction-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .transaction-item:last-child {
            border-bottom: none;
        }
        
        .transaction-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
            font-size: 1.1rem;
        }
        
        .transaction-icon.income {
            background: var(--success-color);
        }
        
        .transaction-icon.expense {
            background: var(--danger-color);
        }
        
        .transaction-details {
            flex: 1;
        }
        
        .transaction-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 3px;
            font-size: 0.95rem;
        }
        
        .transaction-info {
            font-size: 0.8rem;
            color: #666;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .transaction-value {
            font-weight: bold;
            text-align: right;
        }
        
        .transaction-value.income {
            color: var(--success-color);
        }
        
        .transaction-value.expense {
            color: var(--danger-color);
        }
        
        .created-by {
            font-size: 0.7rem;
            color: #999;
            font-style: italic;
        }
        
        /* Modal styles */
        .modal-content {
            border-radius: 15px;
        }
        
        .form-floating label {
            font-size: 0.9rem;
        }
        
        .btn {
            border-radius: 10px;
            font-weight: 600;
        }
        
        /* PWA Specific Styles */
        .pwa-mode .mobile-header {
            padding-top: 40px; /* Safe area for status bar */
        }
        
        #install-button {
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }
        
        #install-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 123, 255, 0.4);
        }
        
        /* Safe area adjustments for PWA */
        @supports (padding: max(0px)) {
            .pwa-mode {
                padding-left: max(10px, env(safe-area-inset-left));
                padding-right: max(10px, env(safe-area-inset-right));
                padding-bottom: max(10px, env(safe-area-inset-bottom));
            }
        }
        
        @media (max-width: 576px) {
            .mobile-container {
                padding: 5px;
            }
            
            .mobile-header {
                margin: -5px -5px 15px -5px;
                padding: 15px 10px;
            }
            
            .pwa-mode .mobile-header {
                padding-top: 35px;
            }
            
            .action-btn {
                padding: 15px;
                font-size: 0.9rem;
            }
            
            .action-btn i {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="mobile-container">
        <!-- Header -->
        <div class="mobile-header">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h1 class="mb-0"><i class="fas fa-wallet me-2"></i>Financeiro</h1>
                <div class="header-actions">
                    <button class="btn btn-light btn-sm me-2" onclick="refreshPage()" title="Atualizar">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button class="btn btn-info btn-sm me-2" onclick="checkForUpdates()" title="Verificar Atualizações" id="updateBtn" style="display: none;">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="btn btn-outline-light btn-sm" onclick="logout()" title="Sair">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </div>
            </div>
            <div class="balance-info">
                <div class="balance-item">
                    <div class="label">Saldo Total</div>
                    <div class="value">R$ <?= number_format($totalBalance, 2, ',', '.') ?></div>
                </div>
                <div class="balance-item">
                    <div class="label">Receitas (Mês)</div>
                    <div class="value">R$ <?= number_format($monthlyBalance['receitas'] ?? 0, 2, ',', '.') ?></div>
                </div>
                <div class="balance-item">
                    <div class="label">Despesas (Mês)</div>
                    <div class="value">R$ <?= number_format($monthlyBalance['despesas'] ?? 0, 2, ',', '.') ?></div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <button class="action-btn success" data-bs-toggle="modal" data-bs-target="#incomeModal">
                <i class="fas fa-plus"></i>
                Nova Receita
            </button>
            <button class="action-btn danger" data-bs-toggle="modal" data-bs-target="#expenseModal">
                <i class="fas fa-minus"></i>
                Nova Despesa
            </button>
        </div>

        <!-- Menu de Navegação -->
        <div class="navigation-menu mb-4">
            <div class="d-grid gap-2">
                <button class="btn btn-outline-primary" onclick="showAllTransactions()">
                    <i class="fas fa-list me-2"></i>
                    Ver Todos os Lançamentos
                </button>
                <div class="row g-2">
                    <div class="col-6">
                        <button class="btn btn-outline-info w-100" data-bs-toggle="modal" data-bs-target="#transferModal">
                            <i class="fas fa-exchange-alt me-2"></i>
                            Transferência
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-outline-warning w-100" data-bs-toggle="modal" data-bs-target="#vaultModal">
                            <i class="fas fa-piggy-bank me-2"></i>
                            Cofre
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scheduled Transactions Section -->
        <div class="transactions-section mb-4">
            <div class="section-title">
                <i class="fas fa-calendar-alt"></i>
                Agendados (Ontem, Hoje, Amanhã)
            </div>
            <div id="scheduledTransactions">
                <!-- Agendados serão carregados via JavaScript -->
                <div class="text-center p-3">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p class="mb-0 mt-2">Carregando agendados...</p>
                </div>
            </div>
        </div>

        <!-- Accounts Section -->
        <div class="accounts-section">
            <div class="section-title">
                <i class="fas fa-university"></i>
                Saldo das Contas
            </div>
            <?php foreach ($accounts as $account): ?>
                <div class="account-item">
                    <div class="account-info">
                        <div class="name"><?= htmlspecialchars($account['nome']) ?></div>
                        <div class="type"><?= ucfirst($account['tipo']) ?> - <?= $account['pessoa_tipo'] ?></div>
                    </div>
                    <div class="account-balance">
                        R$ <?= number_format($account['saldo_atual'], 2, ',', '.') ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Recent Transactions -->
        <div class="transactions-section">
            <div class="section-title">
                <i class="fas fa-history"></i>
                Últimos Lançamentos
            </div>
            <?php if (!empty($recentTransactions)): ?>
                <?php foreach ($recentTransactions as $transaction): ?>
                    <div class="transaction-item">
                        <div class="transaction-icon <?= $transaction['kind'] === 'entrada' ? 'income' : 'expense' ?>">
                            <i class="fas fa-<?= $transaction['kind'] === 'entrada' ? 'arrow-up' : 'arrow-down' ?>"></i>
                        </div>
                        <div class="transaction-details">
                            <div class="transaction-title"><?= htmlspecialchars($transaction['descricao']) ?></div>
                            <div class="transaction-info">
                                <span><?= htmlspecialchars($transaction['account_name']) ?></span>
                                <span>•</span>
                                <span><?= date('d/m/Y', strtotime($transaction['data_competencia'])) ?></span>
                                <?php if ($transaction['category_name']): ?>
                                    <span>•</span>
                                    <span><?= htmlspecialchars($transaction['category_name']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($transaction['created_by_name']): ?>
                                <div class="created-by">por <?= htmlspecialchars($transaction['created_by_name']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="transaction-value <?= $transaction['kind'] === 'entrada' ? 'income' : 'expense' ?>">
                            <?= $transaction['kind'] === 'entrada' ? '+' : '-' ?>R$ <?= number_format($transaction['valor'], 2, ',', '.') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; color: #666; padding: 20px;">
                    <i class="fas fa-receipt" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.5;"></i>
                    <p>Nenhum lançamento encontrado</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal Nova Receita -->
    <div class="modal fade" id="incomeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Receita</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="incomeForm">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="incomeDescricao" name="descricao" required>
                            <label for="incomeDescricao">Descrição *</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="incomeValor" name="valor" placeholder="R$ 0,00" required>
                            <label for="incomeValor">Valor *</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <select class="form-select" id="incomeAccount" name="account_id" required>
                                <option value="">Selecione uma conta...</option>
                                <?php foreach ($accounts as $account): ?>
                                    <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label for="incomeAccount">Conta *</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <select class="form-select" id="incomeCategory" name="category_id">
                                <option value="">Selecione uma categoria...</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>">
                                        <?= htmlspecialchars($category['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="incomeCategory">Categoria</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" id="incomeData" name="data_competencia" required>
                            <label for="incomeData">Data *</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="saveIncome()">
                        <i class="fas fa-save me-2"></i>Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Nova Despesa -->
    <div class="modal fade" id="expenseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Despesa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="expenseForm">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="expenseDescricao" name="descricao" required>
                            <label for="expenseDescricao">Descrição *</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="expenseValor" name="valor" placeholder="R$ 0,00" required>
                            <label for="expenseValor">Valor *</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <select class="form-select" id="expenseAccount" name="account_id" required>
                                <option value="">Selecione uma conta...</option>
                                <?php foreach ($accounts as $account): ?>
                                    <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label for="expenseAccount">Conta *</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <select class="form-select" id="expenseCategory" name="category_id">
                                <option value="">Selecione uma categoria...</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>">
                                        <?= htmlspecialchars($category['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="expenseCategory">Categoria</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" id="expenseData" name="data_competencia" required>
                            <label for="expenseData">Data *</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" onclick="saveExpense()">
                        <i class="fas fa-save me-2"></i>Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Todos os Lançamentos -->
    <div class="modal fade" id="allTransactionsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Todos os Lançamentos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="date" class="form-control" id="filterStartDate">
                                <label for="filterStartDate">Data Inicial</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="date" class="form-control" id="filterEndDate">
                                <label for="filterEndDate">Data Final</label>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" id="filterAccount">
                                    <option value="">Todas as contas</option>
                                    <?php foreach ($accounts as $account): ?>
                                        <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="filterAccount">Conta</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" id="filterType">
                                    <option value="">Todos os tipos</option>
                                    <option value="entrada">Receitas</option>
                                    <option value="saida">Despesas</option>
                                </select>
                                <label for="filterType">Tipo</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <button class="btn btn-primary" onclick="applyFilters()">
                            <i class="fas fa-filter me-2"></i>Aplicar Filtros
                        </button>
                        <button class="btn btn-secondary" onclick="clearFilters()">
                            <i class="fas fa-times me-2"></i>Limpar
                        </button>
                    </div>
                    <!-- Lista de Transações -->
                    <div id="filteredTransactions">
                        <div class="text-center p-3">
                            <i class="fas fa-search"></i>
                            <p class="mb-0 mt-2">Use os filtros para buscar lançamentos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Transferência -->
    <div class="modal fade" id="transferModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Transferência</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="transferForm">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="transferDescricao" name="descricao" required>
                            <label for="transferDescricao">Descrição *</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="transferValor" name="valor" placeholder="R$ 0,00" required>
                            <label for="transferValor">Valor *</label>
                        </div>

                        <div class="form-floating mb-3">
                            <select class="form-select" id="transferFromAccount" name="from_account_id" required>
                                <option value="">Conta de origem...</option>
                                <?php foreach ($accounts as $account): ?>
                                    <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label for="transferFromAccount">Conta de Origem *</label>
                        </div>

                        <div class="form-floating mb-3">
                            <select class="form-select" id="transferToAccount" name="to_account_id" required>
                                <option value="">Conta de destino...</option>
                                <?php foreach ($accounts as $account): ?>
                                    <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label for="transferToAccount">Conta de Destino *</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" id="transferData" name="data_competencia" required>
                            <label for="transferData">Data *</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-info" onclick="saveTransfer()">
                        <i class="fas fa-exchange-alt me-2"></i>Transferir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cofre -->
    <div class="modal fade" id="vaultModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Movimentação de Cofre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="vaultForm">
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Ação:</strong> <span id="actionDescription">Selecione uma ação primeiro</span>
                        </div>

                        <div class="form-floating mb-3">
                            <select class="form-select" id="vaultAction" name="action" required onchange="updateActionDescription()">
                                <option value="">Selecione a ação...</option>
                                <option value="add">💰 Adicionar ao Cofre</option>
                                <option value="withdraw">💸 Resgatar do Cofre</option>
                            </select>
                            <label for="vaultAction">Ação *</label>
                        </div>

                        <div class="form-floating mb-3">
                            <select class="form-select" id="vaultGoal" name="vault_goal_id" required>
                                <option value="">Carregando objetivos...</option>
                            </select>
                            <label for="vaultGoal">Objetivo do Cofre *</label>
                        </div>

                        <div class="form-floating mb-3">
                            <select class="form-select" id="vaultAccount" name="account_id" required>
                                <option value="">Selecione a conta...</option>
                                <?php foreach ($accounts as $account): ?>
                                    <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['nome']) ?> (R$ <?= number_format($account['saldo_atual'], 2, ',', '.') ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <label for="vaultAccount" id="vaultAccountLabel">Conta *</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="vaultValor" name="valor" placeholder="R$ 0,00" required>
                            <label for="vaultValor">Valor *</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="vaultDescricao" name="descricao" required>
                            <label for="vaultDescricao">Descrição *</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" id="vaultData" name="data_competencia" required>
                            <label for="vaultData">Data *</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" onclick="saveVaultMovement()">
                        <i class="fas fa-piggy-bank me-2"></i>Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.js"></script>
    
    <script>
        // Função para atualizar página
        function refreshPage() {
            const refreshBtn = document.querySelector('button[onclick="refreshPage()"] i');
            refreshBtn.classList.add('fa-spin');
            setTimeout(() => {
                location.reload();
            }, 500);
        }

        // Função para logout
        function logout() {
            Swal.fire({
                title: 'Confirmar Logout',
                text: 'Deseja realmente sair do sistema?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sim, sair',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '<?= url('/logout') ?>';
                }
            });
        }

        // Função para carregar transações agendadas
        function loadScheduledTransactions() {
            fetch('<?= url('/api/transactions/scheduled') ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderScheduledTransactions(data.transactions);
                    } else {
                        showScheduledError();
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar agendados:', error);
                    showScheduledError();
                });
        }

        function renderScheduledTransactions(transactions) {
            const container = document.getElementById('scheduledTransactions');

            if (transactions.length === 0) {
                container.innerHTML = `
                    <div class="text-center p-3" style="color: #666;">
                        <i class="fas fa-calendar-check" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.5;"></i>
                        <p class="mb-0">Nenhum agendamento para os próximos dias</p>
                    </div>
                `;
                return;
            }

            let html = '';
            let currentDate = '';

            transactions.forEach(transaction => {
                const transDate = new Date(transaction.data_competencia);
                const today = new Date();
                const yesterday = new Date(today);
                yesterday.setDate(today.getDate() - 1);
                const tomorrow = new Date(today);
                tomorrow.setDate(today.getDate() + 1);

                let dateLabel = '';
                if (transDate.toDateString() === yesterday.toDateString()) {
                    dateLabel = 'Ontem';
                } else if (transDate.toDateString() === today.toDateString()) {
                    dateLabel = 'Hoje';
                } else if (transDate.toDateString() === tomorrow.toDateString()) {
                    dateLabel = 'Amanhã';
                }

                if (dateLabel !== currentDate) {
                    currentDate = dateLabel;
                    html += `<div class="date-separator mt-3 mb-2" style="font-weight: bold; color: #666; font-size: 0.9rem;">${dateLabel}</div>`;
                }

                const isOverdue = transDate < today && dateLabel === 'Ontem';
                const statusClass = isOverdue ? 'text-danger' : '';

                html += `
                    <div class="transaction-item ${statusClass}">
                        <div class="transaction-icon ${transaction.kind === 'entrada' ? 'income' : 'expense'}">
                            <i class="fas fa-${transaction.kind === 'entrada' ? 'arrow-up' : 'arrow-down'}"></i>
                        </div>
                        <div class="transaction-details">
                            <div class="transaction-title">${transaction.descricao}</div>
                            <div class="transaction-info">
                                <span>${transaction.account_name || 'Conta não definida'}</span>
                                <span>•</span>
                                <span>${transaction.category_name || 'Sem categoria'}</span>
                                ${isOverdue ? '<span class="text-danger ms-2"><i class="fas fa-exclamation-triangle"></i> Vencido</span>' : ''}
                            </div>
                        </div>
                        <div class="transaction-value ${transaction.kind === 'entrada' ? 'income' : 'expense'}">
                            ${transaction.kind === 'entrada' ? '+' : '-'}R$ ${parseFloat(transaction.valor).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        function showScheduledError() {
            document.getElementById('scheduledTransactions').innerHTML = `
                <div class="text-center p-3" style="color: #dc3545;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p class="mb-0 mt-2">Erro ao carregar agendados</p>
                </div>
            `;
        }

        // Configurar data de hoje como padrão
        document.addEventListener('DOMContentLoaded', function() {
            // Carregar agendados
            loadScheduledTransactions();
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('incomeData').value = today;
            document.getElementById('expenseData').value = today;
            document.getElementById('transferData').value = today;
            document.getElementById('vaultData').value = today;
            
            // Aplicar máscara de moeda
            function formatBrazilianCurrency(value) {
                let numbers = value.replace(/\D/g, '');
                if (numbers === '') return 'R$ ';
                let amount = parseInt(numbers) / 100;
                return 'R$ ' + amount.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
            
            function applyMask(field) {
                field.addEventListener('input', function(e) {
                    e.target.value = formatBrazilianCurrency(e.target.value);
                });
            }
            
            applyMask(document.getElementById('incomeValor'));
            applyMask(document.getElementById('expenseValor'));
            applyMask(document.getElementById('transferValor'));
            applyMask(document.getElementById('vaultValor'));
            
            // Carregar categorias corretas ao abrir modais
            document.getElementById('incomeModal').addEventListener('show.bs.modal', function() {
                updateCategoriesByType('entrada', 'incomeCategory');
            });
            
            document.getElementById('expenseModal').addEventListener('show.bs.modal', function() {
                updateCategoriesByType('saida', 'expenseCategory');
            });

            document.getElementById('vaultModal').addEventListener('show.bs.modal', function() {
                loadVaultGoals();
            });
        });
        
        // Função para converter moeda brasileira para decimal
        function parseBrazilianCurrency(value) {
            if (!value || value === 'R$ ') return '0';
            let cleaned = value.replace(/^R\$\s*/, '');
            if (cleaned.includes(',')) {
                cleaned = cleaned.replace(/\./g, '').replace(',', '.');
                return cleaned;
            }
            if (cleaned.includes('.')) {
                const parts = cleaned.split('.');
                if (parts[parts.length - 1].length > 2) {
                    return cleaned.replace(/\./g, '');
                }
                if (parts.length === 2 && parts[1].length <= 2) {
                    return cleaned;
                }
            }
            return cleaned;
        }
        
        // Função para atualizar categorias baseado no tipo
        function updateCategoriesByType(tipo, selectId) {
            if (!tipo) return;
            
            fetch(`<?= url('/api/transactions/categories') ?>?tipo=${tipo}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCategorySelect(data.categories, selectId);
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar categorias:', error);
                });
        }
        
        function updateCategorySelect(categories, selectId) {
            const categorySelect = document.getElementById(selectId);
            const firstOption = categorySelect.querySelector('option:first-child');
            categorySelect.innerHTML = '';
            categorySelect.appendChild(firstOption);
            
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.nome;
                categorySelect.appendChild(option);
            });
        }
        
        function saveIncome() {
            const form = document.getElementById('incomeForm');
            const formData = new FormData(form);
            
            const valorField = document.getElementById('incomeValor');
            const valorNumerico = parseBrazilianCurrency(valorField.value);
            formData.set('valor', valorNumerico);
            formData.set('kind', 'entrada');
            formData.set('status', 'confirmado');
            
            fetch('<?= url('/api/transactions/create') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Erro!', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire('Erro!', 'Erro ao salvar receita', 'error');
            });
        }
        
        function saveExpense() {
            const form = document.getElementById('expenseForm');
            const formData = new FormData(form);
            
            const valorField = document.getElementById('expenseValor');
            const valorNumerico = parseBrazilianCurrency(valorField.value);
            formData.set('valor', valorNumerico);
            formData.set('kind', 'saida');
            formData.set('status', 'confirmado');
            
            fetch('<?= url('/api/transactions/create') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Erro!', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire('Erro!', 'Erro ao salvar despesa', 'error');
            });
        }
        
        // Resetar formulários quando modais fecham
        document.getElementById('incomeModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('incomeForm').reset();
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('incomeData').value = today;
            document.getElementById('incomeValor').value = 'R$ 0,00';
        });

        document.getElementById('expenseModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('expenseForm').reset();
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('expenseData').value = today;
            document.getElementById('expenseValor').value = 'R$ 0,00';
        });

        document.getElementById('transferModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('transferForm').reset();
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('transferData').value = today;
            document.getElementById('transferValor').value = 'R$ 0,00';
        });

        document.getElementById('vaultModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('vaultForm').reset();
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('vaultData').value = today;
            document.getElementById('vaultValor').value = 'R$ 0,00';
        });

        // Função para verificar atualizações manualmente
        function checkForUpdates() {
            if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
                navigator.serviceWorker.getRegistration().then(registration => {
                    if (registration) {
                        Swal.fire({
                            title: 'Verificando atualizações...',
                            text: 'Aguarde enquanto verificamos se há uma nova versão.',
                            icon: 'info',
                            showConfirmButton: false,
                            timer: 2000
                        });

                        registration.update().then(() => {
                            console.log('Verificação de atualização solicitada');

                            // Se não houver atualização após 3 segundos, mostrar mensagem
                            setTimeout(() => {
                                Swal.fire({
                                    title: '✅ App atualizado!',
                                    text: 'Você está usando a versão mais recente.',
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }, 3000);
                        });
                    }
                });
            } else {
                Swal.fire('Erro!', 'Service Worker não disponível', 'error');
            }
        }

        // Mostrar botão de atualização se PWA estiver ativo
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
            document.getElementById('updateBtn').style.display = 'block';
        }

        // Função para atualizar descrição da ação
        function updateActionDescription() {
            const action = document.getElementById('vaultAction').value;
            const description = document.getElementById('actionDescription');
            const accountLabel = document.getElementById('vaultAccountLabel');

            if (action === 'add') {
                description.textContent = 'Transferir dinheiro DA CONTA selecionada PARA o cofre';
                accountLabel.textContent = 'Conta de Origem *';
            } else if (action === 'withdraw') {
                description.textContent = 'Transferir dinheiro DO COFRE para a conta selecionada';
                accountLabel.textContent = 'Conta de Destino *';
            } else {
                description.textContent = 'Selecione uma ação primeiro';
                accountLabel.textContent = 'Conta *';
            }
        }

        // Função para carregar objetivos de vault
        function loadVaultGoals() {
            fetch('<?= url('/api/vaults/goals') ?>')
                .then(response => response.json())
                .then(data => {
                    console.log('Dados dos vaults recebidos:', data);
                    if (data.success) {
                        console.log('Vaults:', data.vaults);
                        updateVaultGoalsSelect(data.vaults);
                    } else {
                        console.error('Erro ao carregar objetivos:', data.message);
                        const vaultGoalSelect = document.getElementById('vaultGoal');
                        vaultGoalSelect.innerHTML = '<option value="">Erro ao carregar objetivos</option>';
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar objetivos:', error);
                    const vaultGoalSelect = document.getElementById('vaultGoal');
                    vaultGoalSelect.innerHTML = '<option value="">Erro de conexão</option>';
                });
        }

        function updateVaultGoalsSelect(vaults) {
            const vaultGoalSelect = document.getElementById('vaultGoal');
            vaultGoalSelect.innerHTML = '<option value="">Selecione um objetivo...</option>';

            if (!vaults || vaults.length === 0) {
                vaultGoalSelect.innerHTML = '<option value="">Nenhum objetivo encontrado</option>';
                return;
            }

            vaults.forEach((vault, index) => {
                console.log(`Vault ${index}:`, vault);

                const option = document.createElement('option');
                option.value = vault.id || '';

                // Tratar valores undefined/null - usando o campo correto 'titulo'
                console.log('Título recebido:', vault.titulo, 'Tipo:', typeof vault.titulo);
                const nome = vault.titulo || 'Objetivo sem nome';
                const valorAtual = parseFloat(vault.valor_atual) || 0;
                const valorMeta = parseFloat(vault.valor_meta) || 0;
                const percentual = valorMeta > 0 ? (valorAtual / valorMeta * 100).toFixed(1) : 0;

                // Verificar se os valores são válidos
                if (isNaN(valorAtual)) {
                    console.warn('valor_atual inválido:', vault.valor_atual);
                }
                if (isNaN(valorMeta)) {
                    console.warn('valor_meta inválido:', vault.valor_meta);
                }

                // Formatar valor atual de forma mais segura
                let valorFormatado;
                try {
                    valorFormatado = valorAtual.toLocaleString('pt-BR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                } catch (e) {
                    console.error('Erro ao formatar valor:', e);
                    valorFormatado = '0,00';
                }

                const textoOpcao = `${nome} (${percentual}% - R$ ${valorFormatado})`;
                console.log('Texto da opção:', textoOpcao);

                option.textContent = textoOpcao;
                vaultGoalSelect.appendChild(option);
            });
        }

        // Função para mostrar modal de todos os lançamentos
        function showAllTransactions() {
            const modal = new bootstrap.Modal(document.getElementById('allTransactionsModal'));
            modal.show();
        }

        // Função para aplicar filtros de transações
        function applyFilters() {
            const startDate = document.getElementById('filterStartDate').value;
            const endDate = document.getElementById('filterEndDate').value;
            const accountId = document.getElementById('filterAccount').value;
            const type = document.getElementById('filterType').value;

            if (!startDate || !endDate) {
                Swal.fire('Atenção!', 'Por favor, informe as datas inicial e final', 'warning');
                return;
            }

            const params = new URLSearchParams({
                start_date: startDate,
                end_date: endDate
            });

            if (accountId) params.append('account_id', accountId);
            if (type) params.append('type', type);

            fetch(`<?= url('/api/transactions/filter') ?>?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderFilteredTransactions(data.transactions);
                    } else {
                        Swal.fire('Erro!', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro ao filtrar transações:', error);
                    Swal.fire('Erro!', 'Erro ao carregar transações', 'error');
                });
        }

        // Função para renderizar transações filtradas
        function renderFilteredTransactions(transactions) {
            const container = document.getElementById('filteredTransactions');

            if (transactions.length === 0) {
                container.innerHTML = `
                    <div class="text-center p-3" style="color: #666;">
                        <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.5;"></i>
                        <p class="mb-0">Nenhum lançamento encontrado</p>
                    </div>
                `;
                return;
            }

            let html = '';
            transactions.forEach(transaction => {
                html += `
                    <div class="transaction-item">
                        <div class="transaction-icon ${transaction.kind === 'entrada' ? 'income' : 'expense'}">
                            <i class="fas fa-${transaction.kind === 'entrada' ? 'arrow-up' : 'arrow-down'}"></i>
                        </div>
                        <div class="transaction-details">
                            <div class="transaction-title">${transaction.descricao}</div>
                            <div class="transaction-info">
                                <span>${transaction.account_name || 'Conta não definida'}</span>
                                <span>•</span>
                                <span>${new Date(transaction.data_competencia).toLocaleDateString('pt-BR')}</span>
                                ${transaction.category_name ? `<span>•</span><span>${transaction.category_name}</span>` : ''}
                            </div>
                        </div>
                        <div class="transaction-value ${transaction.kind === 'entrada' ? 'income' : 'expense'}">
                            ${transaction.kind === 'entrada' ? '+' : '-'}R$ ${parseFloat(transaction.valor).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Função para limpar filtros
        function clearFilters() {
            document.getElementById('filterStartDate').value = '';
            document.getElementById('filterEndDate').value = '';
            document.getElementById('filterAccount').value = '';
            document.getElementById('filterType').value = '';

            document.getElementById('filteredTransactions').innerHTML = `
                <div class="text-center p-3">
                    <i class="fas fa-search"></i>
                    <p class="mb-0 mt-2">Use os filtros para buscar lançamentos</p>
                </div>
            `;
        }

        // Função para salvar transferência
        function saveTransfer() {
            const form = document.getElementById('transferForm');
            const formData = new FormData(form);

            const valorField = document.getElementById('transferValor');
            const valorNumerico = parseBrazilianCurrency(valorField.value);
            const fromAccountId = document.getElementById('transferFromAccount').value;
            const toAccountId = document.getElementById('transferToAccount').value;

            if (fromAccountId === toAccountId) {
                Swal.fire('Erro!', 'A conta de origem deve ser diferente da conta de destino', 'error');
                return;
            }

            formData.set('valor', valorNumerico);
            formData.set('account_from', fromAccountId);
            formData.set('account_to', toAccountId);

            fetch('<?= url('/api/transactions/transfer') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('transferModal'));
                        modal.hide();
                        location.reload();
                    });
                } else {
                    Swal.fire('Erro!', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire('Erro!', 'Erro ao realizar transferência', 'error');
            });
        }

        // Função para movimentação do cofre
        function saveVaultMovement() {
            const action = document.getElementById('vaultAction').value;
            const accountId = document.getElementById('vaultAccount').value;
            const vaultGoalId = document.getElementById('vaultGoal').value;
            const valorField = document.getElementById('vaultValor');
            const valorNumerico = parseBrazilianCurrency(valorField.value);
            const descricao = document.getElementById('vaultDescricao').value;
            const dataCompetencia = document.getElementById('vaultData').value;

            console.log('Dados do formulário:', {
                action, accountId, vaultGoalId, valorNumerico, descricao, dataCompetencia
            });

            // Validações detalhadas
            if (!action) {
                Swal.fire('Erro!', 'Selecione uma ação (Adicionar ou Resgatar)', 'error');
                return;
            }

            if (!vaultGoalId) {
                Swal.fire('Erro!', 'Selecione um objetivo do cofre', 'error');
                return;
            }

            if (!accountId) {
                Swal.fire('Erro!', 'Selecione uma conta', 'error');
                return;
            }

            if (!valorNumerico || valorNumerico <= 0) {
                Swal.fire('Erro!', 'Informe um valor maior que zero', 'error');
                return;
            }

            if (!descricao.trim()) {
                Swal.fire('Erro!', 'Informe uma descrição', 'error');
                return;
            }

            if (!dataCompetencia) {
                Swal.fire('Erro!', 'Selecione uma data', 'error');
                return;
            }

            // Determinar endpoint baseado na ação
            let endpoint = '';
            let fieldName = '';

            if (action === 'add') {
                endpoint = '<?= url('/api/vaults/deposit') ?>';
                fieldName = 'account_from';
            } else if (action === 'withdraw') {
                endpoint = '<?= url('/api/vaults/withdraw') ?>';
                fieldName = 'account_to';
            } else {
                Swal.fire('Erro!', 'Ação inválida selecionada', 'error');
                return;
            }

            const newFormData = new FormData();
            newFormData.set('valor', valorNumerico);
            newFormData.set(fieldName, accountId);
            newFormData.set('vault_goal_id', vaultGoalId);
            newFormData.set('descricao', descricao);
            newFormData.set('data_competencia', dataCompetencia);

            console.log('Enviando para:', endpoint);
            console.log('Dados enviados:', {
                valor: valorNumerico,
                [fieldName]: accountId,
                vault_goal_id: vaultGoalId,
                descricao: descricao,
                data_competencia: dataCompetencia
            });

            fetch(endpoint, {
                method: 'POST',
                body: newFormData
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('vaultModal'));
                        modal.hide();
                        location.reload();
                    });
                } else {
                    Swal.fire('Erro!', data.message || 'Erro desconhecido', 'error');
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                Swal.fire('Erro!', 'Erro ao realizar movimentação do cofre: ' + error.message, 'error');
            });
        }
        
        // PWA - Registrar Service Worker com estratégia de atualização
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('<?= url('/mobile-sw.js') ?>')
                    .then(function(registration) {
                        console.log('SW registered successfully: ', registration.scope);

                        // Forçar verificação de atualização
                        registration.addEventListener('updatefound', () => {
                            const newWorker = registration.installing;
                            console.log('Nova versão do SW encontrada');

                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // Nova versão disponível, notificar usuário com SweetAlert2
                                    Swal.fire({
                                        title: '🎉 Nova versão disponível!',
                                        text: 'Uma nova versão do app foi encontrada. Recarregar para atualizar?',
                                        icon: 'info',
                                        showCancelButton: true,
                                        confirmButtonText: '✨ Atualizar Agora',
                                        cancelButtonText: 'Depois',
                                        confirmButtonColor: '#007bff'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            newWorker.postMessage({ action: 'skipWaiting' });
                                            Swal.fire({
                                                title: 'Atualizando...',
                                                text: 'Por favor, aguarde enquanto atualizamos o app.',
                                                icon: 'info',
                                                showConfirmButton: false,
                                                timer: 2000
                                            });
                                        }
                                    });
                                }
                            });
                        });

                        // Verificar atualizações periodicamente
                        setInterval(() => {
                            registration.update();
                        }, 60000); // Verificar a cada minuto

                        return registration.update();
                    })
                    .then(function() {
                        console.log('SW updated successfully');
                    })
                    .catch(function(registrationError) {
                        console.error('SW registration failed: ', registrationError);
                    });

                // Escutar mudanças no service worker
                navigator.serviceWorker.addEventListener('controllerchange', () => {
                    console.log('Service Worker controllerchange event');
                    window.location.reload();
                });
            });
        } else {
            console.log('Service Worker not supported');
        }
        
        // PWA - Detectar instalação
        let deferredPrompt;
        
        window.addEventListener('beforeinstallprompt', (e) => {
            console.log('PWA install prompt available');
            e.preventDefault();
            deferredPrompt = e;
            
            // Mostrar botão de instalação customizado
            showInstallButton();
        });
        
        // PWA - Detectar se PWA é instalável
        if ('getInstalledRelatedApps' in navigator) {
            navigator.getInstalledRelatedApps().then((relatedApps) => {
                console.log('Related apps:', relatedApps);
            });
        }
        
        // PWA - Debug manifest
        if ('serviceWorker' in navigator) {
            fetch('<?= url('/mobile-manifest.json') ?>')
                .then(response => response.json())
                .then(manifest => {
                    console.log('Manifest loaded successfully:', manifest);
                })
                .catch(error => {
                    console.error('Failed to load manifest:', error);
                });
        }
        
        window.addEventListener('appinstalled', (evt) => {
            console.log('PWA was installed');
            hideInstallButton();
        });
        
        function showInstallButton() {
            // Criar botão de instalação se não existir
            if (!document.getElementById('install-button')) {
                const installButton = document.createElement('button');
                installButton.id = 'install-button';
                installButton.innerHTML = '<i class="fas fa-download me-2"></i>Instalar App';
                installButton.className = 'btn btn-primary position-fixed';
                installButton.style.cssText = 'bottom: 20px; right: 20px; z-index: 1000; border-radius: 25px;';
                
                installButton.addEventListener('click', async () => {
                    if (deferredPrompt) {
                        deferredPrompt.prompt();
                        const { outcome } = await deferredPrompt.userChoice;
                        console.log(`User response to the install prompt: ${outcome}`);
                        deferredPrompt = null;
                        hideInstallButton();
                    }
                });
                
                document.body.appendChild(installButton);
            }
        }
        
        function hideInstallButton() {
            const installButton = document.getElementById('install-button');
            if (installButton) {
                installButton.remove();
            }
        }
        
        // PWA - Detectar se está rodando como app instalado
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
            console.log('Running as installed PWA');
            document.body.classList.add('pwa-mode');
        }
        
        // PWA - Lidar com shortcuts do manifest
        const urlParams = new URLSearchParams(window.location.search);
        const action = urlParams.get('action');
        
        if (action === 'income') {
            // Abrir modal de receita automaticamente
            setTimeout(() => {
                const incomeModal = new bootstrap.Modal(document.getElementById('incomeModal'));
                incomeModal.show();
            }, 500);
        } else if (action === 'expense') {
            // Abrir modal de despesa automaticamente
            setTimeout(() => {
                const expenseModal = new bootstrap.Modal(document.getElementById('expenseModal'));
                expenseModal.show();
            }, 500);
        }
    </script>
</body>
</html>