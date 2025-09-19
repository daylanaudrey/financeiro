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
            margin: 0;
            font-weight: 600;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
        }
        
        .header-actions .btn {
            border-color: rgba(255,255,255,0.3);
            color: white;
        }
        
        .header-actions .btn:hover {
            background-color: rgba(255,255,255,0.1);
            border-color: rgba(255,255,255,0.5);
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
        
        .balance-item.main {
            transform: scale(1.05);
        }
        
        .balance-item.main .value {
            font-size: 2rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .monthly-summary {
            display: flex;
            justify-content: space-around;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255,255,255,0.2);
        }
        
        .summary-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 10px;
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .summary-item i {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }
        
        .summary-item.income i {
            color: #90EE90;
        }
        
        .summary-item.expense i {
            color: #FFB6C1;
        }
        
        .summary-label {
            font-size: 0.8rem;
            opacity: 0.9;
        }
        
        .summary-value {
            font-size: 0.9rem;
            font-weight: 600;
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
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-icon {
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .btn-text {
            text-align: left;
            flex: 1;
        }
        
        .btn-title {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .btn-subtitle {
            font-size: 0.85rem;
            opacity: 0.9;
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
        
        .status-badge {
            font-size: 0.7rem;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-badge.confirmado {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-badge.agendado {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-badge.cancelado {
            background-color: #f8d7da;
            color: #721c24;
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
        
        /* FAB Styles */
        .fab-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .fab {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            color: white;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .main-fab {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            transform: scale(1);
        }
        
        .main-fab:hover {
            transform: scale(1.1);
        }
        
        .main-fab.active {
            transform: rotate(45deg);
        }
        
        .fab-menu {
            position: absolute;
            bottom: 70px;
            right: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s ease;
            pointer-events: none;
        }
        
        .fab-menu.active {
            opacity: 1;
            transform: translateY(0);
            pointer-events: all;
        }
        
        .income-fab {
            background: var(--success-color);
        }
        
        .expense-fab {
            background: var(--danger-color);
        }
        
        .fab-menu .fab {
            width: 50px;
            height: 50px;
            font-size: 1.2rem;
        }

        /* Estilos para vencimentos */
        .due-date-group {
            margin-bottom: 20px;
            padding: 15px;
            background: var(--light-gray);
            border-radius: 10px;
        }

        .due-date-header {
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }

        .transactions-list {
            margin-bottom: 10px;
        }

        .mini-transaction {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            font-size: 0.9rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .mini-transaction:last-child {
            border-bottom: none;
        }

        .mini-transaction .desc {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-right: 10px;
        }

        .mini-transaction .value {
            font-weight: 600;
            white-space: nowrap;
        }

        .due-date-total {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(0,0,0,0.1);
            font-weight: 600;
            text-align: right;
        }
        
        @media (max-width: 576px) {
            .mobile-container {
                padding: 5px;
                padding-bottom: 100px; /* Space for FAB */
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
            
            .btn-icon {
                width: 40px;
                height: 40px;
                font-size: 1.2rem;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="mobile-container">
        <!-- Header -->
        <div class="mobile-header">
            <div class="header-top">
                <h1><i class="fas fa-wallet me-2"></i>Financeiro</h1>
                <div class="header-actions">
                    <button class="btn btn-sm btn-outline-light me-2" onclick="refreshPage()" title="Atualizar">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-light" onclick="logout()" title="Sair">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </div>
            </div>
            <div class="balance-info">
                <div class="balance-item main">
                    <div class="label">💰 Saldo Total</div>
                    <div class="value" style="color: <?= $totalBalance >= 0 ? '#28a745' : '#dc3545' ?>">
                        R$ <?= number_format($totalBalance, 2, ',', '.') ?>
                    </div>
                </div>
            </div>
            
            <div class="monthly-summary">
                <div class="summary-item income">
                    <i class="fas fa-arrow-up"></i>
                    <div>
                        <div class="summary-label">Receitas</div>
                        <div class="summary-value">R$ <?= number_format($monthlyBalance['receitas'] ?? 0, 2, ',', '.') ?></div>
                    </div>
                </div>
                <div class="summary-item expense">
                    <i class="fas fa-arrow-down"></i>
                    <div>
                        <div class="summary-label">Despesas</div>
                        <div class="summary-value">R$ <?= number_format($monthlyBalance['despesas'] ?? 0, 2, ',', '.') ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <button class="action-btn success" data-bs-toggle="modal" data-bs-target="#incomeModal">
                <div class="btn-icon">
                    <i class="fas fa-plus"></i>
                </div>
                <div class="btn-text">
                    <div class="btn-title">Receita</div>
                    <div class="btn-subtitle">Adicionar entrada</div>
                </div>
            </button>
            <button class="action-btn danger" data-bs-toggle="modal" data-bs-target="#expenseModal">
                <div class="btn-icon">
                    <i class="fas fa-minus"></i>
                </div>
                <div class="btn-text">
                    <div class="btn-title">Despesa</div>
                    <div class="btn-subtitle">Adicionar saída</div>
                </div>
            </button>
        </div>
        
        <!-- FAB for Quick Add -->
        <div class="fab-container">
            <button class="fab main-fab" onclick="toggleFabMenu()">
                <i class="fas fa-plus"></i>
            </button>
            <div class="fab-menu" id="fabMenu">
                <button class="fab income-fab" data-bs-toggle="modal" data-bs-target="#incomeModal">
                    <i class="fas fa-arrow-up"></i>
                </button>
                <button class="fab expense-fab" data-bs-toggle="modal" data-bs-target="#expenseModal">
                    <i class="fas fa-arrow-down"></i>
                </button>
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

        <!-- Vencimentos: Ontem, Hoje e Amanhã -->
        <div class="accounts-section">
            <div class="section-title">
                <i class="fas fa-calendar-alt"></i>
                Vencimentos Próximos
            </div>

            <!-- Vencidas Ontem -->
            <?php if (!empty($transactionsDueYesterday)): ?>
            <div class="due-date-group">
                <div class="due-date-header text-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    Vencidas Ontem (<?= count($transactionsDueYesterday) ?>)
                    <small class="text-muted ms-2"><?= date('d/m', strtotime('-1 day')) ?></small>
                </div>
                <?php
                $totalYesterday = 0;
                foreach ($transactionsDueYesterday as $trans) {
                    $totalYesterday += ($trans['kind'] === 'entrada' ? $trans['valor_pendente'] : -$trans['valor_pendente']);
                }
                ?>
                <div class="transactions-list">
                    <?php foreach (array_slice($transactionsDueYesterday, 0, 3) as $transaction): ?>
                    <div class="mini-transaction">
                        <span class="desc"><?= htmlspecialchars(substr($transaction['descricao'], 0, 30)) ?></span>
                        <span class="value <?= $transaction['kind'] === 'entrada' ? 'text-success' : 'text-danger' ?>">
                            R$ <?= number_format($transaction['valor_pendente'], 2, ',', '.') ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                    <?php if (count($transactionsDueYesterday) > 3): ?>
                    <div class="text-muted small">+<?= count($transactionsDueYesterday) - 3 ?> mais...</div>
                    <?php endif; ?>
                </div>
                <div class="due-date-total">
                    Total: <span class="<?= $totalYesterday >= 0 ? 'text-success' : 'text-danger' ?>">
                        R$ <?= number_format(abs($totalYesterday), 2, ',', '.') ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Vencem Hoje -->
            <?php if (!empty($transactionsDueToday)): ?>
            <div class="due-date-group">
                <div class="due-date-header text-warning">
                    <i class="fas fa-clock"></i>
                    Vencem Hoje (<?= count($transactionsDueToday) ?>)
                    <small class="text-muted ms-2"><?= date('d/m') ?></small>
                </div>
                <?php
                $totalToday = 0;
                foreach ($transactionsDueToday as $trans) {
                    $totalToday += ($trans['kind'] === 'entrada' ? $trans['valor_pendente'] : -$trans['valor_pendente']);
                }
                ?>
                <div class="transactions-list">
                    <?php foreach (array_slice($transactionsDueToday, 0, 3) as $transaction): ?>
                    <div class="mini-transaction">
                        <span class="desc"><?= htmlspecialchars(substr($transaction['descricao'], 0, 30)) ?></span>
                        <span class="value <?= $transaction['kind'] === 'entrada' ? 'text-success' : 'text-danger' ?>">
                            R$ <?= number_format($transaction['valor_pendente'], 2, ',', '.') ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                    <?php if (count($transactionsDueToday) > 3): ?>
                    <div class="text-muted small">+<?= count($transactionsDueToday) - 3 ?> mais...</div>
                    <?php endif; ?>
                </div>
                <div class="due-date-total">
                    Total: <span class="<?= $totalToday >= 0 ? 'text-success' : 'text-danger' ?>">
                        R$ <?= number_format(abs($totalToday), 2, ',', '.') ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Vencem Amanhã -->
            <?php if (!empty($transactionsDueTomorrow)): ?>
            <div class="due-date-group">
                <div class="due-date-header text-info">
                    <i class="fas fa-calendar-check"></i>
                    Vencem Amanhã (<?= count($transactionsDueTomorrow) ?>)
                    <small class="text-muted ms-2"><?= date('d/m', strtotime('+1 day')) ?></small>
                </div>
                <?php
                $totalTomorrow = 0;
                foreach ($transactionsDueTomorrow as $trans) {
                    $totalTomorrow += ($trans['kind'] === 'entrada' ? $trans['valor_pendente'] : -$trans['valor_pendente']);
                }
                ?>
                <div class="transactions-list">
                    <?php foreach (array_slice($transactionsDueTomorrow, 0, 3) as $transaction): ?>
                    <div class="mini-transaction">
                        <span class="desc"><?= htmlspecialchars(substr($transaction['descricao'], 0, 30)) ?></span>
                        <span class="value <?= $transaction['kind'] === 'entrada' ? 'text-success' : 'text-danger' ?>">
                            R$ <?= number_format($transaction['valor_pendente'], 2, ',', '.') ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                    <?php if (count($transactionsDueTomorrow) > 3): ?>
                    <div class="text-muted small">+<?= count($transactionsDueTomorrow) - 3 ?> mais...</div>
                    <?php endif; ?>
                </div>
                <div class="due-date-total">
                    Total: <span class="<?= $totalTomorrow >= 0 ? 'text-success' : 'text-danger' ?>">
                        R$ <?= number_format(abs($totalTomorrow), 2, ',', '.') ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>

            <?php if (empty($transactionsDueYesterday) && empty($transactionsDueToday) && empty($transactionsDueTomorrow)): ?>
            <div class="text-center text-muted py-3">
                <i class="fas fa-check-circle"></i> Sem vencimentos próximos
            </div>
            <?php endif; ?>
        </div>

        <!-- Seção antiga de vencimentos hoje (remover se houver duplicação) -->
        <?php if (false && !empty($transactionsDueToday)): ?>
        <div class="accounts-section">
            <div class="section-title">
                <i class="fas fa-exclamation-triangle text-warning"></i>
                Vencem Hoje (<?= count($transactionsDueToday) ?>)
            </div>
            <?php foreach ($transactionsDueToday as $transaction): ?>
                <div class="transaction-item">
                    <div class="transaction-icon">
                        <i class="fas <?= $transaction['kind'] === 'entrada' ? 'fa-arrow-down text-success' : 'fa-arrow-up text-danger' ?>"></i>
                    </div>
                    <div class="transaction-info">
                        <div class="transaction-desc"><?= htmlspecialchars($transaction['descricao']) ?></div>
                        <div class="transaction-details">
                            <?php if ($transaction['account_name']): ?>
                                <small class="text-muted"><?= htmlspecialchars($transaction['account_name']) ?></small>
                            <?php elseif ($transaction['credit_card_name']): ?>
                                <small class="text-muted"><?= htmlspecialchars($transaction['credit_card_name']) ?></small>
                            <?php endif; ?>
                            <?php if ($transaction['category_name']): ?>
                                <span class="badge" style="background-color: <?= htmlspecialchars($transaction['category_color'] ?: '#6c757d') ?>; font-size: 0.7rem;">
                                    <?= htmlspecialchars($transaction['category_name']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="transaction-amount <?= $transaction['kind'] === 'entrada' ? 'text-success' : 'text-danger' ?>">
                        <?= $transaction['kind'] === 'entrada' ? '+' : '-' ?> R$ <?= number_format($transaction['valor'], 2, ',', '.') ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Search Section -->
        <div class="search-section" style="margin-bottom: 1.5rem;">
            <div class="search-container" style="position: relative;">
                <input type="text" 
                       id="searchInput" 
                       class="search-input" 
                       placeholder="Pesquisar lançamentos..." 
                       style="width: 100%; padding: 12px 16px 12px 45px; border: 1px solid #ddd; border-radius: 25px; font-size: 16px; background: white; outline: none;">
                <i class="fas fa-search" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #666; font-size: 16px;"></i>
                <button id="clearSearch" 
                        style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #999; cursor: pointer; display: none;"
                        onclick="clearSearch()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="transactions-section">
            <div class="section-title">
                <i class="fas fa-history"></i>
                <span id="transactionsTitle">Últimos Lançamentos</span>
                <span id="searchResultsCount" style="font-size: 0.9em; font-weight: normal; color: #666; display: none;"></span>
            </div>
            <?php if (!empty($recentTransactions)): ?>
                <div id="transactionsList">
                <?php foreach ($recentTransactions as $transaction): ?>
                    <div class="transaction-item" 
                         data-description="<?= htmlspecialchars(strtolower($transaction['descricao'])) ?>"
                         data-account="<?= htmlspecialchars(strtolower($transaction['account_name'])) ?>"
                         data-category="<?= htmlspecialchars(strtolower($transaction['category_name'] ?? '')) ?>"
                         data-date="<?= $transaction['data_competencia'] ?>"
                         data-value="<?= $transaction['valor'] ?>"
                         data-kind="<?= $transaction['kind'] ?>">
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
                                <span>•</span>
                                <span class="status-badge <?= $transaction['status'] ?>"><?= ucfirst($transaction['status']) ?></span>
                            </div>
                            <?php if ($transaction['created_by_name']): ?>
                                <div class="created-by">por <?= htmlspecialchars($transaction['created_by_name']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="transaction-value <?= $transaction['kind'] === 'entrada' ? 'income' : 'expense' ?>">
                            <?= $transaction['kind'] === 'entrada' ? '+' : '-' ?>R$ <?= number_format($transaction['valor'], 2, ',', '.') ?>
                            <?php if (!empty($transaction['is_partial']) && $transaction['is_partial']): ?>
                                <div style="font-size: 0.7rem; opacity: 0.8; margin-top: 2px;">
                                    <i class="fas fa-coins"></i> 
                                    R$ <?= number_format($transaction['valor_pago'], 2, ',', '.') ?> 
                                    de R$ <?= number_format($transaction['valor_original'], 2, ',', '.') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
                <div id="noTransactionsMessage" style="text-align: center; color: #666; padding: 20px; display: none;">
                    <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.5;"></i>
                    <p>Nenhum lançamento encontrado com esses critérios</p>
                </div>
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
                            <select class="form-select" id="incomeContact" name="contact_id">
                                <option value="">Selecione um contato...</option>
                                <?php foreach ($contacts as $contact): ?>
                                    <option value="<?= $contact['id'] ?>">
                                        <?= htmlspecialchars($contact['nome']) ?>
                                        <?php if ($contact['tipo']): ?>
                                            (<?= ucfirst($contact['tipo']) ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="incomeContact">Contato</label>
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
                            <select class="form-select" id="expenseContact" name="contact_id">
                                <option value="">Selecione um contato...</option>
                                <?php foreach ($contacts as $contact): ?>
                                    <option value="<?= $contact['id'] ?>">
                                        <?= htmlspecialchars($contact['nome']) ?>
                                        <?php if ($contact['tipo']): ?>
                                            (<?= ucfirst($contact['tipo']) ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="expenseContact">Contato</label>
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
    
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.js"></script>
    
    <script>
        // Configurar data de hoje como padrão
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('incomeData').value = today;
            document.getElementById('expenseData').value = today;
            
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
            
            // Carregar categorias corretas ao abrir modais
            document.getElementById('incomeModal').addEventListener('show.bs.modal', function() {
                updateCategoriesByType('entrada', 'incomeCategory');
            });
            
            document.getElementById('expenseModal').addEventListener('show.bs.modal', function() {
                updateCategoriesByType('saida', 'expenseCategory');
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
                        title: 'Lançamento Salvo!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    }).then(() => {
                        // Vibrar se disponível
                        vibrate([10, 50, 10]);
                        forceRefreshData();
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
                        title: 'Lançamento Salvo!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    }).then(() => {
                        // Vibrar se disponível
                        vibrate([10, 50, 10]);
                        forceRefreshData();
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
        
        // PWA - Registrar Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('<?= url('/mobile-sw.js') ?>')
                    .then(function(registration) {
                        console.log('SW registered successfully: ', registration.scope);
                        return registration.update();
                    })
                    .then(function() {
                        console.log('SW updated successfully');
                    })
                    .catch(function(registrationError) {
                        console.error('SW registration failed: ', registrationError);
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
        
        // FAB Menu Toggle
        function toggleFabMenu() {
            const fabMenu = document.getElementById('fabMenu');
            const mainFab = document.querySelector('.main-fab');
            
            fabMenu.classList.toggle('active');
            mainFab.classList.toggle('active');
        }
        
        // Fechar FAB menu ao clicar fora
        document.addEventListener('click', function(event) {
            const fabContainer = document.querySelector('.fab-container');
            const fabMenu = document.getElementById('fabMenu');
            const mainFab = document.querySelector('.main-fab');
            
            if (!fabContainer.contains(event.target) && fabMenu.classList.contains('active')) {
                fabMenu.classList.remove('active');
                mainFab.classList.remove('active');
            }
        });
        
        // Swipe gestures para navegação
        let startX = null;
        let startY = null;
        const minSwipeDistance = 100;
        
        document.addEventListener('touchstart', function(e) {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        }, { passive: true });
        
        document.addEventListener('touchmove', function(e) {
            if (!startX || !startY) return;
            
            // Prevenir scroll durante swipe horizontal
            const currentX = e.touches[0].clientX;
            const currentY = e.touches[0].clientY;
            const diffX = Math.abs(currentX - startX);
            const diffY = Math.abs(currentY - startY);
            
            if (diffX > diffY) {
                e.preventDefault();
            }
        }, { passive: false });
        
        document.addEventListener('touchend', function(e) {
            if (!startX || !startY) return;
            
            const endX = e.changedTouches[0].clientX;
            const endY = e.changedTouches[0].clientY;
            
            const diffX = startX - endX;
            const diffY = startY - endY;
            
            // Verificar se é swipe horizontal
            if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > minSwipeDistance) {
                if (diffX > 0) {
                    // Swipe para esquerda - abrir despesa
                    const expenseModal = new bootstrap.Modal(document.getElementById('expenseModal'));
                    expenseModal.show();
                } else {
                    // Swipe para direita - abrir receita
                    const incomeModal = new bootstrap.Modal(document.getElementById('incomeModal'));
                    incomeModal.show();
                }
            }
            
            // Verificar se é swipe vertical para baixo (refresh)
            if (diffY < -minSwipeDistance && Math.abs(diffY) > Math.abs(diffX)) {
                // Pull to refresh
                const header = document.querySelector('.mobile-header');
                if (window.scrollY === 0) {
                    header.style.transform = 'scale(1.05)';
                    setTimeout(() => {
                        header.style.transform = 'scale(1)';
                        refreshPage();
                    }, 200);
                }
            }
            
            startX = null;
            startY = null;
        }, { passive: true });
        
        // Haptic feedback para dispositivos que suportam
        function vibrate(pattern = 10) {
            if ('vibrate' in navigator) {
                navigator.vibrate(pattern);
            }
        }
        
        // Adicionar feedback tátil aos botões
        document.querySelectorAll('.action-btn, .fab').forEach(button => {
            button.addEventListener('click', () => vibrate(10));
        });
        
        // Função para forçar atualização dos dados
        function forceRefreshData() {
            // Mostrar indicador de loading
            showLoadingIndicator();
            
            // Para PWA, limpar todos os caches primeiro
            if ('caches' in window) {
                caches.keys().then(function(names) {
                    names.forEach(name => {
                        caches.delete(name);
                    });
                }).then(() => {
                    performRefresh();
                });
            } else {
                performRefresh();
            }
        }
        
        function performRefresh() {
            // Limpar service worker cache
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistrations().then(function(registrations) {
                    registrations.forEach(registration => {
                        if (registration.unregister) {
                            registration.unregister().then(() => {
                                // Re-registrar service worker
                                navigator.serviceWorker.register('<?= url('/mobile-sw.js') ?>');
                            });
                        }
                    });
                });
            }
            
            // Forçar reload sem alterar URL (evita cache mas mantém URL limpa)
            // Usar reload(true) para forçar bypass do cache
            window.location.reload(true);
        }
        
        // Função para atualizar a página (botão refresh)
        function refreshPage() {
            forceRefreshData();
        }
        
        function showLoadingIndicator() {
            // Criar overlay de loading se não existir
            let loadingOverlay = document.getElementById('loading-overlay');
            if (!loadingOverlay) {
                loadingOverlay = document.createElement('div');
                loadingOverlay.id = 'loading-overlay';
                loadingOverlay.innerHTML = `
                    <div style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0,0,0,0.9);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        z-index: 9999;
                        color: white;
                        font-size: 1.2rem;
                        backdrop-filter: blur(5px);
                    ">
                        <div style="text-align: center; padding: 30px; border-radius: 15px; background: rgba(0,123,255,0.2); border: 2px solid rgba(255,255,255,0.3);">
                            <i class="fas fa-sync-alt fa-spin fa-3x mb-3" style="color: #007bff;"></i>
                            <div style="font-weight: 600; margin-bottom: 10px;">Atualizando dados...</div>
                            <div style="font-size: 0.9rem; opacity: 0.8;">Aguarde um momento</div>
                        </div>
                    </div>
                `;
                document.body.appendChild(loadingOverlay);
            }
            
            // Auto-remover após 5 segundos como fallback
            setTimeout(() => {
                const overlay = document.getElementById('loading-overlay');
                if (overlay) {
                    overlay.remove();
                }
            }, 5000);
        }
        
        // Função de busca de transações
        function initializeSearch() {
            const searchInput = document.getElementById('searchInput');
            const clearButton = document.getElementById('clearSearch');
            const transactionsList = document.getElementById('transactionsList');
            const transactionsTitle = document.getElementById('transactionsTitle');
            const searchResultsCount = document.getElementById('searchResultsCount');
            const noTransactionsMessage = document.getElementById('noTransactionsMessage');
            
            if (!searchInput || !transactionsList) return;
            
            // Busca em tempo real
            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase().trim();
                
                if (searchTerm) {
                    clearButton.style.display = 'block';
                    performSearch(searchTerm);
                } else {
                    clearSearch();
                }
            });
            
            function performSearch(searchTerm) {
                // Mostrar indicador de carregamento
                transactionsTitle.textContent = 'Pesquisando...';
                searchResultsCount.style.display = 'none';
                noTransactionsMessage.style.display = 'none';
                
                // Fazer pesquisa no banco de dados via API
                const formData = new FormData();
                formData.append('search', searchTerm);
                
                fetch('<?= url('/api/mobile/search') ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displaySearchResults(data.data.transactions, data.data.count, searchTerm);
                    } else {
                        Swal.fire('Erro!', data.message, 'error');
                        clearSearch();
                    }
                })
                .catch(error => {
                    console.error('Erro na pesquisa:', error);
                    Swal.fire('Erro!', 'Erro ao pesquisar lançamentos', 'error');
                    clearSearch();
                });
            }
            
            function displaySearchResults(transactions, count, searchTerm) {
                // Limpar lista atual
                transactionsList.innerHTML = '';
                
                // Atualizar título
                transactionsTitle.textContent = 'Resultado da Busca';
                searchResultsCount.textContent = `(${count} encontrados)`;
                searchResultsCount.style.display = 'inline';
                
                if (count === 0) {
                    noTransactionsMessage.style.display = 'block';
                    noTransactionsMessage.innerHTML = `
                        <div class="no-transactions">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <p>Nenhum resultado encontrado para "<strong>${searchTerm}</strong>"</p>
                        </div>
                    `;
                } else {
                    noTransactionsMessage.style.display = 'none';
                    
                    // Renderizar transações encontradas
                    transactions.forEach(transaction => {
                        const transactionHtml = createTransactionItem(transaction);
                        transactionsList.appendChild(transactionHtml);
                    });
                }
            }
            
            function createTransactionItem(transaction) {
                const div = document.createElement('div');
                div.className = 'transaction-item';
                div.setAttribute('data-description', transaction.descricao.toLowerCase());
                div.setAttribute('data-account', (transaction.account_name || '').toLowerCase());
                div.setAttribute('data-category', (transaction.category_name || '').toLowerCase());
                
                const valor = parseFloat(transaction.valor);
                const valorFormatted = valor.toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                });
                
                const statusClass = transaction.status === 'confirmado' ? 'confirmed' : 
                                   transaction.status === 'agendado' ? 'scheduled' : 'partial';
                const statusText = transaction.status === 'confirmado' ? 'Confirmado' : 
                                  transaction.status === 'agendado' ? 'Agendado' : 'Parcial';
                const statusIcon = transaction.status === 'confirmado' ? 'check-circle' : 
                                  transaction.status === 'agendado' ? 'clock' : 'hourglass-half';
                
                const typeClass = transaction.kind === 'entrada' ? 'income' : 'expense';
                const typeIcon = transaction.kind === 'entrada' ? 'arrow-up' : 'arrow-down';
                
                const date = new Date(transaction.data_competencia);
                const formattedDate = date.toLocaleDateString('pt-BR');
                
                div.innerHTML = `
                    <div class="transaction-icon ${typeClass}">
                        <i class="fas fa-${typeIcon}"></i>
                    </div>
                    <div class="transaction-details">
                        <div class="transaction-description">
                            ${transaction.descricao}
                            <span class="badge ${statusClass} ms-2">
                                <i class="fas fa-${statusIcon} me-1"></i>
                                ${statusText}
                            </span>
                        </div>
                        <div class="transaction-meta">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>${formattedDate}
                                ${transaction.account_name ? `<i class="fas fa-university ms-2 me-1"></i>${transaction.account_name}` : ''}
                                ${transaction.category_name ? `<i class="fas fa-tag ms-2 me-1"></i>${transaction.category_name}` : ''}
                            </small>
                        </div>
                    </div>
                    <div class="transaction-value ${typeClass}">
                        ${valorFormatted}
                    </div>
                `;
                
                return div;
            }
        }
        
        // Função para limpar busca
        function clearSearch() {
            const searchInput = document.getElementById('searchInput');
            const clearButton = document.getElementById('clearSearch');
            const transactionsTitle = document.getElementById('transactionsTitle');
            const searchResultsCount = document.getElementById('searchResultsCount');
            const noTransactionsMessage = document.getElementById('noTransactionsMessage');
            
            searchInput.value = '';
            clearButton.style.display = 'none';
            
            // Restaurar título original
            transactionsTitle.textContent = 'Últimos Lançamentos';
            searchResultsCount.style.display = 'none';
            noTransactionsMessage.style.display = 'none';
            
            // Recarregar a página para restaurar as transações originais (últimas 50)
            window.location.reload();
        }
        
        // Inicializar busca quando página carrega
        document.addEventListener('DOMContentLoaded', function() {
            initializeSearch();
        });
        
        // Função para logout
        function logout() {
            if (confirm('Deseja realmente sair?')) {
                // Salvar informação de que estamos vindo do mobile
                sessionStorage.setItem('return_to_mobile', 'true');
                
                // Para PWA, forçar logout e redirecionamento
                if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
                    // Se é PWA, redirecionar diretamente para logout com parâmetro mobile
                    window.location.href = '<?= url('/logout') ?>?mobile=1';
                } else {
                    window.location.href = '<?= url('/logout') ?>';
                }
            }
        }
    </script>
    
    <!-- PWA Installation and URL Protection Script -->
    <script>
        // Prevent any unwanted redirects away from mobile
        document.addEventListener('DOMContentLoaded', function() {
            // Limpar parâmetro refresh da URL se existir
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('refresh')) {
                // Remover parâmetro refresh da URL sem recarregar a página
                const cleanUrl = window.location.pathname + window.location.hash;
                window.history.replaceState(null, '', cleanUrl);
                console.log('Parâmetro refresh removido da URL');
            }

            // Debug logging for production troubleshooting
            console.log('Mobile page loaded successfully');
            console.log('Current URL:', window.location.href);
            console.log('Pathname:', window.location.pathname);

            // Store current URL to prevent redirects
            const currentPath = window.location.pathname;
            if (currentPath.includes('/mobile')) {
                // Store in sessionStorage that we want to stay on mobile
                sessionStorage.setItem('mobile_intent', 'true');
                console.log('Mobile intent stored');
            }
            
            // Prevent navigation away from mobile if intended
            window.addEventListener('beforeunload', function() {
                if (sessionStorage.getItem('mobile_intent') === 'true') {
                    sessionStorage.setItem('return_to_mobile', 'true');
                }
            });
            
            // PWA Installation prompt handling
            let deferredPrompt;
            
            window.addEventListener('beforeinstallprompt', (e) => {
                // Prevent Chrome 67 and earlier from automatically showing the prompt
                e.preventDefault();
                // Stash the event so it can be triggered later
                deferredPrompt = e;
                
                // Show install banner if not already installed
                if (!window.matchMedia('(display-mode: standalone)').matches) {
                    showInstallBanner();
                }
            });
            
            function showInstallBanner() {
                const banner = document.createElement('div');
                banner.id = 'pwa-install-banner';
                banner.innerHTML = `
                    <div class="alert alert-info alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; max-width: 300px;">
                        <strong><i class="fas fa-mobile-alt me-2"></i>Instalar App</strong><br>
                        <small>Adicione à tela inicial para acesso rápido</small>
                        <button type="button" class="btn btn-sm btn-primary mt-2 d-block" onclick="installPWA()">
                            <i class="fas fa-download me-1"></i>Instalar
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                document.body.appendChild(banner);
                
                // Auto hide after 10 seconds
                setTimeout(() => {
                    const bannerElement = document.getElementById('pwa-install-banner');
                    if (bannerElement) {
                        bannerElement.remove();
                    }
                }, 10000);
            }
            
            // Global function for PWA installation
            window.installPWA = function() {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    deferredPrompt.userChoice.then((choiceResult) => {
                        if (choiceResult.outcome === 'accepted') {
                            console.log('User accepted the PWA install prompt');
                            Swal.fire({
                                icon: 'success',
                                title: 'App Instalado!',
                                text: 'O app foi adicionado à sua tela inicial',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                        deferredPrompt = null;
                    });
                    
                    // Hide banner
                    const banner = document.getElementById('pwa-install-banner');
                    if (banner) {
                        banner.remove();
                    }
                }
            };
        });
        
        // Detect if running as PWA
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
            document.body.classList.add('pwa-mode');
            console.log('Running as PWA');
            
            // Debug info para PWA
            console.log('PWA Debug Info:');
            console.log('- URL atual:', window.location.href);
            console.log('- Pathname:', window.location.pathname);
            console.log('- User Agent:', navigator.userAgent);
            console.log('- Display mode:', window.matchMedia('(display-mode: standalone)').matches ? 'standalone' : 'browser');
        }
    </script>
</body>
</html>