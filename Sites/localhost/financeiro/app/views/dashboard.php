<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-tachometer-alt me-3"></i>
        Dashboard
    </h1>
    <div class="quick-actions">
        <button class="btn btn-outline-secondary" id="toggleEditMode" onclick="toggleEditMode()" title="Personalizar Dashboard">
            <i class="fas fa-edit me-2"></i>
            <span id="editModeText">Editar Layout</span>
        </button>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#dashboardTransactionModal" onclick="setDashboardTransactionType('entrada')">
            <i class="fas fa-plus me-2"></i>
            Nova Receita
        </button>
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#dashboardTransactionModal" onclick="setDashboardTransactionType('saida')">
            <i class="fas fa-minus me-2"></i>
            Nova Despesa
        </button>
    </div>
</div>

<!-- Edit Mode Controls -->
<div id="editModeControls" class="alert alert-info" style="display: none;">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <i class="fas fa-info-circle me-2"></i>
            <strong>Modo de Edição Ativado:</strong> Arraste os widgets para reordená-los conforme sua preferência.
        </div>
        <div>
            <button class="btn btn-sm btn-outline-secondary me-2" onclick="resetLayoutToDefault()">
                <i class="fas fa-undo me-1"></i>
                Restaurar Padrão
            </button>
            <button class="btn btn-sm btn-success" onclick="saveLayoutAndExit()">
                <i class="fas fa-save me-1"></i>
                Salvar e Sair
            </button>
        </div>
    </div>
</div>

<!-- Dashboard Widgets Container -->
<div id="dashboardWidgets" class="dashboard-widgets">

<!-- Widget: Summary Cards -->
<div class="dashboard-widget" data-widget="summary-cards">
    <div class="widget-header" style="display: none;">
        <h5><i class="fas fa-chart-bar me-2"></i>Resumo Geral</h5>
        <div class="widget-controls">
            <i class="fas fa-grip-vertical drag-handle"></i>
        </div>
    </div>
    <div class="widget-content">
        <div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card success">
            <div class="position-relative">
                <h6 class="mb-2 opacity-90">Saldo Total</h6>
                <h4 class="mb-0">R$ <?= number_format($totalBalance, 2, ',', '.') ?></h4>
                <i class="fas fa-wallet stat-icon"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card info">
            <div class="position-relative">
                <h6 class="mb-2 opacity-90">Receitas (Mês)</h6>
                <h4 class="mb-0">R$ <?= number_format($monthlyIncome, 2, ',', '.') ?></h4>
                <i class="fas fa-trending-up stat-icon"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card danger">
            <div class="position-relative">
                <h6 class="mb-2 opacity-90">Despesas (Mês)</h6>
                <h4 class="mb-0">R$ <?= number_format($monthlyExpenses, 2, ',', '.') ?></h4>
                <i class="fas fa-trending-down stat-icon"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card warning">
            <div class="position-relative">
                <h6 class="mb-2 opacity-90">Resultado (Mês)</h6>
                <h4 class="mb-0">R$ <?= number_format(($monthlyIncome - $monthlyExpenses), 2, ',', '.') ?></h4>
                <i class="fas fa-chart-line stat-icon"></i>
            </div>
        </div>
        </div> <!-- End row -->
    </div> <!-- End widget-content -->
</div> <!-- End widget: summary-cards -->

<!-- Widget: Account Balances -->
<?php if (!empty($accountBalancesByType) && count($accountBalancesByType) > 1): ?>
<div class="dashboard-widget" data-widget="account-balances">
    <div class="widget-header" style="display: none;">
        <h5><i class="fas fa-users me-2"></i>Saldos por Tipo de Conta</h5>
        <div class="widget-controls">
            <i class="fas fa-grip-vertical drag-handle"></i>
        </div>
    </div>
    <div class="widget-content">
        <div class="row mb-4">
    <div class="col-12 mb-3">
        <h5 class="text-muted">Saldos por Tipo de Conta</h5>
    </div>
    <?php foreach ($accountBalancesByType as $tipo): ?>
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><?= $tipo['pessoa_tipo'] === 'PF' ? 'Pessoa Física' : 'Pessoa Jurídica' ?></h6>
                    <small class="text-muted"><?= $tipo['quantidade'] ?> conta<?= $tipo['quantidade'] > 1 ? 's' : '' ?></small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <h6 class="text-muted mb-1">Saldo Total</h6>
                            <h5 class="mb-0">R$ <?= number_format($tipo['total'], 2, ',', '.') ?></h5>
                        </div>
                        <?php if (isset($monthlyBalanceByType[$tipo['pessoa_tipo']])): ?>
                            <?php $monthly = $monthlyBalanceByType[$tipo['pessoa_tipo']]; ?>
                            <div class="col-3">
                                <h6 class="text-muted mb-1">Receitas</h6>
                                <small class="text-success fw-bold">+R$ <?= number_format($monthly['receitas'], 2, ',', '.') ?></small>
                            </div>
                            <div class="col-3">
                                <h6 class="text-muted mb-1">Despesas</h6>
                                <small class="text-danger fw-bold">-R$ <?= number_format($monthly['despesas'], 2, ',', '.') ?></small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
        </div> <!-- End row -->
    </div> <!-- End widget-content -->
</div> <!-- End widget: account-balances -->
<?php endif; ?>

<!-- Widget: Credit Cards -->
<?php if (!empty($creditCards)): ?>
<div class="dashboard-widget" data-widget="credit-cards">
    <div class="widget-header" style="display: none;">
        <h5><i class="fas fa-credit-card me-2"></i>Cartões de Crédito</h5>
        <div class="widget-controls">
            <i class="fas fa-grip-vertical drag-handle"></i>
        </div>
    </div>
    <div class="widget-content">
        <div class="row mb-4">
    <div class="col-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="text-muted mb-0">Cartões de Crédito</h5>
            <a href="<?= url('/credit-cards') ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-credit-card me-1"></i>
                Gerenciar
            </a>
        </div>
    </div>
    <?php 
    $activeCards = array_filter($creditCards, function($card) { return $card['ativo']; });
    $visibleCards = array_slice($activeCards, 0, 4); // Mostrar no máximo 4 cartões
    ?>
    <?php foreach ($visibleCards as $card): ?>
        <?php
        $percentualUso = $card['percentual_uso'] ?? 0;
        $limiteDisponivel = $card['limite_disponivel'] ?? 0;
        $corProgresso = $percentualUso < 50 ? 'success' : ($percentualUso < 80 ? 'warning' : 'danger');
        ?>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100" style="border-left: 4px solid <?= htmlspecialchars($card['cor']) ?>">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="mb-1 fw-bold"><?= htmlspecialchars($card['nome']) ?></h6>
                            <small class="text-muted">
                                <i class="fab fa-cc-<?= strtolower($card['bandeira']) ?> me-1"></i>
                                <?= ucfirst($card['bandeira']) ?>
                                <?php if ($card['ultimos_digitos']): ?>
                                    *<?= $card['ultimos_digitos'] ?>
                                <?php endif; ?>
                            </small>
                        </div>
                        <div class="text-end">
                            <small class="text-muted">Limite</small>
                            <div class="fw-bold">R$ <?= number_format($card['limite_total'], 0, ',', '.') ?></div>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Disponível</small>
                            <small class="fw-bold text-<?= $corProgresso ?>">
                                R$ <?= number_format($limiteDisponivel, 0, ',', '.') ?>
                            </small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-<?= $corProgresso ?>" style="width: <?= $percentualUso ?>%"></div>
                        </div>
                        <div class="mt-1 text-center">
                            <small class="text-muted"><?= number_format($percentualUso, 1) ?>% usado</small>
                        </div>
                    </div>
                    
                    <div class="row text-center mt-2 pt-2 border-top">
                        <div class="col-6">
                            <small class="text-muted d-block">Vencimento</small>
                            <small class="fw-bold">Dia <?= $card['dia_vencimento'] ?></small>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Fechamento</small>
                            <small class="fw-bold">Dia <?= $card['dia_fechamento'] ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php if (count($activeCards) > 4): ?>
        <div class="col-12 text-center">
            <small class="text-muted">
                E mais <?= count($activeCards) - 4 ?> cartão(ões). 
                <a href="<?= url('/credit-cards') ?>" class="text-decoration-none">Ver todos</a>
            </small>
        </div>
    <?php endif; ?>
        </div> <!-- End row -->
    </div> <!-- End widget-content -->
</div> <!-- End widget: credit-cards -->
<?php endif; ?>

<!-- Widget: Due Today -->
<?php
// Sempre mostrar o card, mesmo sem vencimentos
$showDueTodayCard = true;
if ($showDueTodayCard): ?>
<div class="dashboard-widget" data-widget="due-today">
    <div class="widget-header" style="display: none;">
        <h5><i class="fas fa-calendar-exclamation me-2"></i>Vencimentos de Hoje</h5>
        <div class="widget-controls">
            <i class="fas fa-grip-vertical drag-handle"></i>
        </div>
    </div>
    <div class="widget-content">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center bg-warning text-dark">
                <h5 class="mb-0 d-flex align-items-center">
                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                        <i class="fas fa-calendar-exclamation text-white"></i>
                    </div>
                    Vencimentos de Hoje
                </h5>
                <div class="d-flex align-items-center">
                    <?php $countDue = count($dueTodayTransactions ?? []); ?>
                    <span class="badge <?= $countDue > 0 ? 'bg-dark' : 'bg-secondary' ?> me-2">
                        <?= $countDue ?> item<?= $countDue != 1 ? 's' : '' ?>
                    </span>
                    <a href="<?= url('/transactions') ?>" class="btn btn-sm btn-outline-dark">Ver Todos</a>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($dueTodayTransactions)): ?>
                <div class="list-group list-group-flush">
                    <?php
                    $totalVencimentos = 0;
                    foreach ($dueTodayTransactions as $transaction):
                        $totalVencimentos += $transaction['valor'];
                    ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 border-warning">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <?php if ($transaction['kind'] === 'entrada'): ?>
                                        <div class="badge bg-success rounded-pill">
                                            <i class="fas fa-arrow-up"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="badge bg-danger rounded-pill">
                                            <i class="fas fa-arrow-down"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold"><?= htmlspecialchars($transaction['descricao']) ?></h6>
                                    <small class="text-muted">
                                        <?php if ($transaction['account_name']): ?>
                                            <i class="fas fa-university me-1"></i>
                                            <?= htmlspecialchars($transaction['account_name']) ?>
                                        <?php elseif ($transaction['credit_card_name']): ?>
                                            <i class="fab fa-cc-<?= strtolower($transaction['credit_card_bandeira']) ?> me-1"></i>
                                            <?= htmlspecialchars($transaction['credit_card_name']) ?>
                                        <?php endif; ?>

                                        <?php if (!empty($transaction['category_name'])): ?>
                                            • <span style="color: <?= $transaction['category_color'] ?>">
                                                <?= htmlspecialchars($transaction['category_name']) ?>
                                            </span>
                                        <?php endif; ?>

                                        • <i class="fas fa-clock me-1"></i>
                                        <span class="fw-bold text-warning">Vence hoje</span>
                                    </small>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold fs-5 <?= $transaction['kind'] === 'entrada' ? 'text-success' : 'text-danger' ?>">
                                    <?= $transaction['kind'] === 'entrada' ? '+' : '-' ?>R$ <?= number_format($transaction['valor'], 2, ',', '.') ?>
                                </div>
                                <div class="mt-1">
                                    <small class="badge bg-warning text-dark"><?= ucfirst($transaction['status']) ?></small>
                                    <button class="btn btn-sm btn-success ms-1" onclick="confirmTransactionDashboard(<?= $transaction['id'] ?>, '<?= htmlspecialchars($transaction['descricao']) ?>', <?= $transaction['valor'] ?>)" title="Confirmar hoje">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <!-- Estado vazio - sem vencimentos hoje -->
                <div class="text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-calendar-check text-success" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                    <h5 class="text-muted mb-2">Sem agendamentos para hoje</h5>
                    <p class="text-muted small mb-3">Nenhuma transação agendada vence hoje. Aproveite para organizar suas finanças!</p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="<?= url('/transactions') ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus me-1"></i>Novo Lançamento
                        </a>
                        <a href="<?= url('/transactions') ?>?status=agendado" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-calendar me-1"></i>Ver Agendados
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (count($dueTodayTransactions) > 0): ?>
                <div class="border-top pt-3 mt-3">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="fw-bold text-danger fs-5">
                                    <?php
                                    $totalReceitas = array_sum(array_map(function($t) { return $t['kind'] === 'entrada' ? $t['valor'] : 0; }, $dueTodayTransactions));
                                    $totalDespesas = array_sum(array_map(function($t) { return $t['kind'] === 'saida' ? $t['valor'] : 0; }, $dueTodayTransactions));
                                    ?>
                                    -R$ <?= number_format($totalDespesas, 2, ',', '.') ?>
                                </div>
                                <small class="text-muted">Total Despesas</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="fw-bold text-success fs-5">
                                    +R$ <?= number_format($totalReceitas, 2, ',', '.') ?>
                                </div>
                                <small class="text-muted">Total Receitas</small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Widget: Comparative Data -->
<div class="dashboard-widget" data-widget="comparative-data">
    <div class="widget-header" style="display: none;">
        <h5><i class="fas fa-chart-line me-2"></i>Comparativo de Meses</h5>
        <div class="widget-controls">
            <i class="fas fa-grip-vertical drag-handle"></i>
        </div>
    </div>
    <div class="widget-content">
        <div class="row mb-5">
    <div class="col-12 mb-3">
        <h5 class="text-muted">Comparativo de Meses</h5>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card border-info">
            <div class="card-header bg-light-info">
                <h6 class="mb-0 text-info">Mês Anterior</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Receitas:</span>
                    <span class="text-success">+R$ <?= number_format($previousMonthBalance['receitas'] ?? 0, 2, ',', '.') ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Despesas:</span>
                    <span class="text-danger">-R$ <?= number_format($previousMonthBalance['despesas'] ?? 0, 2, ',', '.') ?></span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between fw-bold">
                    <span>Resultado:</span>
                    <?php 
                    $prevResult = ($previousMonthBalance['receitas'] ?? 0) - ($previousMonthBalance['despesas'] ?? 0);
                    $prevClass = $prevResult >= 0 ? 'text-success' : 'text-danger';
                    ?>
                    <span class="<?= $prevClass ?>">R$ <?= number_format($prevResult, 2, ',', '.') ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card border-primary">
            <div class="card-header bg-light-primary">
                <h6 class="mb-0 text-primary">Mês Atual</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Receitas:</span>
                    <span class="text-success">+R$ <?= number_format($monthlyBalanceWithScheduled['receitas'] ?? 0, 2, ',', '.') ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Despesas:</span>
                    <span class="text-danger">-R$ <?= number_format($monthlyBalanceWithScheduled['despesas'] ?? 0, 2, ',', '.') ?></span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between fw-bold">
                    <span>Resultado:</span>
                    <?php 
                    $currentResult = ($monthlyBalanceWithScheduled['receitas'] ?? 0) - ($monthlyBalanceWithScheduled['despesas'] ?? 0);
                    $currentClass = $currentResult >= 0 ? 'text-success' : 'text-danger';
                    ?>
                    <span class="<?= $currentClass ?>">R$ <?= number_format($currentResult, 2, ',', '.') ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card border-warning">
            <div class="card-header bg-light-warning">
                <h6 class="mb-0 text-warning">Próximo Mês (Projeção)</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Receitas:</span>
                    <span class="text-success">+R$ <?= number_format($nextMonthBalance['receitas'] ?? 0, 2, ',', '.') ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Despesas:</span>
                    <span class="text-danger">-R$ <?= number_format($nextMonthBalance['despesas'] ?? 0, 2, ',', '.') ?></span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between fw-bold">
                    <span>Resultado:</span>
                    <?php 
                    $nextResult = ($nextMonthBalance['receitas'] ?? 0) - ($nextMonthBalance['despesas'] ?? 0);
                    $nextClass = $nextResult >= 0 ? 'text-success' : 'text-danger';
                    ?>
                    <span class="<?= $nextClass ?>">R$ <?= number_format($nextResult, 2, ',', '.') ?></span>
                </div>
            </div>
        </div>
        </div> <!-- End row -->
    </div> <!-- End widget-content -->
</div> <!-- End widget: comparative-data -->

<!-- Widget: Accounts and Scheduled -->
<div class="dashboard-widget" data-widget="accounts-scheduled">
    <div class="widget-header" style="display: none;">
        <h5><i class="fas fa-university me-2"></i>Contas e Agendamentos</h5>
        <div class="widget-controls">
            <i class="fas fa-grip-vertical drag-handle"></i>
        </div>
    </div>
    <div class="widget-content">
        <div class="row mb-5">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 d-flex align-items-center">
                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                        <i class="fas fa-university text-white"></i>
                    </div>
                    Contas Bancárias
                </h5>
                <a href="<?= url('/accounts') ?>" class="btn btn-sm btn-outline-primary">Ver Todas</a>
            </div>
            <div class="card-body">
                <?php if (!empty($accounts)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($accounts as $account): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($account['nome']) ?></h6>
                                    <small class="text-muted"><?= ucfirst($account['tipo']) ?> - <?= $account['pessoa_tipo'] ?></small>
                                </div>
                                <span class="badge bg-primary rounded-pill">
                                    R$ <?= number_format($account['saldo_atual'], 2, ',', '.') ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-plus-circle"></i>
                        <h5>Nenhuma conta cadastrada</h5>
                        <p>Comece criando sua primeira conta bancária para gerenciar suas finanças.</p>
                        <a href="<?= url('/accounts') ?>" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>
                            Criar Primeira Conta
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Widget: Due Dates Yesterday/Today/Tomorrow -->
<div class="dashboard-widget" data-widget="due-dates">
    <div class="widget-header" style="display: none;">
        <h5><i class="fas fa-calendar-exclamation me-2"></i>Vencimentos Próximos</h5>
        <div class="widget-controls">
            <i class="fas fa-grip-vertical drag-handle"></i>
        </div>
    </div>
    <div class="widget-content">
        <div class="row mb-4">
            <div class="col-12 mb-3">
                <h5 class="text-muted">Vencimentos Próximos</h5>
            </div>

            <!-- Vencidas Ontem -->
            <div class="col-lg-4 mb-3">
                <div class="card border-danger">
                    <div class="card-header bg-light text-danger">
                        <h6 class="mb-0 d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Vencidas Ontem
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($transactionsDueYesterday)): ?>
                            <?php
                            $totalYesterday = 0;
                            $countYesterday = count($transactionsDueYesterday);
                            ?>
                            <div class="mb-3">
                                <small class="text-muted"><?= $countYesterday ?> transaç<?= $countYesterday > 1 ? 'ões' : 'ão' ?></small>
                            </div>
                            <?php foreach (array_slice($transactionsDueYesterday, 0, 3) as $transaction): ?>
                                <?php $totalYesterday += $transaction['valor_pendente']; ?>
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <div>
                                        <div class="fw-bold small"><?= htmlspecialchars($transaction['descricao']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($transaction['account_name'] ?? $transaction['credit_card_name'] ?? '-') ?></small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-danger small">
                                            R$ <?= number_format($transaction['valor_pendente'], 2, ',', '.') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if ($countYesterday > 3): ?>
                                <small class="text-muted">E mais <?= $countYesterday - 3 ?> transaç<?= ($countYesterday - 3) > 1 ? 'ões' : 'ão' ?>...</small>
                            <?php endif; ?>
                            <div class="text-center mt-3 pt-2 border-top">
                                <strong class="text-danger">Total: R$ <?= number_format($totalYesterday, 2, ',', '.') ?></strong>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted">
                                <i class="fas fa-check-circle text-success mb-2" style="font-size: 2rem;"></i>
                                <div>Nenhuma pendência</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Vencem Hoje -->
            <div class="col-lg-4 mb-3">
                <div class="card border-warning">
                    <div class="card-header bg-light text-warning">
                        <h6 class="mb-0 d-flex align-items-center">
                            <i class="fas fa-calendar-day me-2"></i>
                            Vencem Hoje
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($transactionsDueToday)): ?>
                            <?php
                            $totalToday = 0;
                            $countToday = count($transactionsDueToday);
                            ?>
                            <div class="mb-3">
                                <small class="text-muted"><?= $countToday ?> transaç<?= $countToday > 1 ? 'ões' : 'ão' ?></small>
                            </div>
                            <?php foreach (array_slice($transactionsDueToday, 0, 3) as $transaction): ?>
                                <?php $totalToday += $transaction['valor_pendente']; ?>
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <div>
                                        <div class="fw-bold small"><?= htmlspecialchars($transaction['descricao']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($transaction['account_name'] ?? $transaction['credit_card_name'] ?? '-') ?></small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-warning small">
                                            R$ <?= number_format($transaction['valor_pendente'], 2, ',', '.') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if ($countToday > 3): ?>
                                <small class="text-muted">E mais <?= $countToday - 3 ?> transaç<?= ($countToday - 3) > 1 ? 'ões' : 'ão' ?>...</small>
                            <?php endif; ?>
                            <div class="text-center mt-3 pt-2 border-top">
                                <strong class="text-warning">Total: R$ <?= number_format($totalToday, 2, ',', '.') ?></strong>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted">
                                <i class="fas fa-check-circle text-success mb-2" style="font-size: 2rem;"></i>
                                <div>Nenhuma pendência</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Vencem Amanhã -->
            <div class="col-lg-4 mb-3">
                <div class="card border-info">
                    <div class="card-header bg-light text-info">
                        <h6 class="mb-0 d-flex align-items-center">
                            <i class="fas fa-calendar-plus me-2"></i>
                            Vencem Amanhã
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($transactionsDueTomorrow)): ?>
                            <?php
                            $totalTomorrow = 0;
                            $countTomorrow = count($transactionsDueTomorrow);
                            ?>
                            <div class="mb-3">
                                <small class="text-muted"><?= $countTomorrow ?> transaç<?= $countTomorrow > 1 ? 'ões' : 'ão' ?></small>
                            </div>
                            <?php foreach (array_slice($transactionsDueTomorrow, 0, 3) as $transaction): ?>
                                <?php $totalTomorrow += $transaction['valor_pendente']; ?>
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <div>
                                        <div class="fw-bold small"><?= htmlspecialchars($transaction['descricao']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($transaction['account_name'] ?? $transaction['credit_card_name'] ?? '-') ?></small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-info small">
                                            R$ <?= number_format($transaction['valor_pendente'], 2, ',', '.') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if ($countTomorrow > 3): ?>
                                <small class="text-muted">E mais <?= $countTomorrow - 3 ?> transaç<?= ($countTomorrow - 3) > 1 ? 'ões' : 'ão' ?>...</small>
                            <?php endif; ?>
                            <div class="text-center mt-3 pt-2 border-top">
                                <strong class="text-info">Total: R$ <?= number_format($totalTomorrow, 2, ',', '.') ?></strong>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted">
                                <i class="fas fa-check-circle text-success mb-2" style="font-size: 2rem;"></i>
                                <div>Nenhuma pendência</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-5">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 d-flex align-items-center">
                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                        <i class="fas fa-clock text-white"></i>
                    </div>
                    Próximos Agendamentos
                </h5>
                <a href="<?= url('/transactions') ?>" class="btn btn-sm btn-outline-primary">Ver Todos</a>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Inclui agendamentos vencidos e futuros</p>
                <?php if (!empty($upcomingTransactions)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($upcomingTransactions as $transaction): ?>
                            <?php
                            // Verificar se está vencida (data de competência passou e não está confirmada)
                            $isOverdue = (
                                strtotime($transaction['data_competencia']) < strtotime(date('Y-m-d')) &&
                                $transaction['status'] !== 'confirmado'
                            );
                            $itemClass = $isOverdue ? 'list-group-item-danger border-danger' : '';
                            ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center <?= $itemClass ?>"<?= $isOverdue ? ' title="Transação vencida - requer atenção"' : '' ?>>
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($transaction['descricao']) ?></h6>
                                    <small class="text-muted">
                                        <?php if ($transaction['account_name']): ?>
                                            <?= htmlspecialchars($transaction['account_name']) ?>
                                        <?php elseif ($transaction['credit_card_name']): ?>
                                            <i class="fab fa-cc-<?= strtolower($transaction['credit_card_bandeira']) ?> me-1"></i>
                                            <?= htmlspecialchars($transaction['credit_card_name']) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?> • 
                                        <?php if ($isOverdue): ?>
                                            <i class="fas fa-exclamation-triangle text-danger me-1" title="Vencida"></i>
                                        <?php endif; ?>
                                        
                                        <?php if ($transaction['status'] === 'confirmado' && $transaction['data_pagamento']): ?>
                                            <span class="text-success">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Pago em <?= date('d/m/Y', strtotime($transaction['data_pagamento'])) ?>
                                            </span>
                                            <?php if ($transaction['data_pagamento'] !== $transaction['data_competencia']): ?>
                                                <br><small class="text-muted">(Vencimento: <?= date('d/m/Y', strtotime($transaction['data_competencia'])) ?>)</small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            Vence em <?= date('d/m/Y', strtotime($transaction['data_competencia'])) ?>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($transaction['category_name'])): ?>
                                            • <?= htmlspecialchars($transaction['category_name']) ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold <?= $transaction['kind'] === 'entrada' ? 'text-success' : 'text-danger' ?>">
                                        <?= $transaction['kind'] === 'entrada' ? '+' : '-' ?>R$ <?= number_format($transaction['valor'], 2, ',', '.') ?>
                                    </span>
                                    <br>
                                    <div class="d-flex gap-2 justify-content-end align-items-center">
                                        <small class="badge bg-<?= $transaction['status'] === 'confirmado' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($transaction['status']) ?>
                                        </small>
                                        <?php if ($transaction['status'] !== 'confirmado'): ?>
                                            <?php if ($transaction['status'] === 'agendado'): ?>
                                                <button class="btn btn-sm btn-success" onclick="launchScheduledTransaction(<?= $transaction['id'] ?>)" title="Lançar agendamento">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-success" onclick="confirmTransactionDashboard(<?= $transaction['id'] ?>, '<?= htmlspecialchars($transaction['descricao']) ?>', <?= $transaction['valor'] ?>)" title="Confirmar lançamento">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-alt"></i>
                        <h5>Nenhum agendamento</h5>
                        <p>Configure lançamentos recorrentes ou agendados para automatizar sua gestão financeira.</p>
                        <a href="<?= url('/transactions') ?>" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>
                            Criar Lançamento
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        </div> <!-- End row -->
    </div> <!-- End widget-content -->
</div> <!-- End widget: accounts-scheduled -->

<!-- Widget: Category Charts -->
<div class="dashboard-widget" data-widget="category-charts">
    <div class="widget-header" style="display: none;">
        <h5><i class="fas fa-chart-pie me-2"></i>Gráficos de Categorias</h5>
        <div class="widget-controls">
            <i class="fas fa-grip-vertical drag-handle"></i>
        </div>
    </div>
    <div class="widget-content">
        <div class="row mb-5">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 d-flex align-items-center">
                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                        <i class="fas fa-chart-pie text-white"></i>
                    </div>
                    Gastos por Categoria (PF)
                </h5>
                <small class="text-muted"><?= date('Y') ?></small>
            </div>
            <div class="card-body">
                <?php if (!empty($categoryExpenses['PF'])): ?>
                    <div class="chart-container">
                        <canvas id="chartCategoriesPF"></canvas>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-chart-pie"></i>
                        <h5>Nenhuma despesa PF</h5>
                        <p>Não há despesas confirmadas em contas PF para exibir no gráfico.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 d-flex align-items-center">
                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                        <i class="fas fa-chart-pie text-white"></i>
                    </div>
                    Gastos por Categoria (PJ)
                </h5>
                <small class="text-muted"><?= date('Y') ?></small>
            </div>
            <div class="card-body">
                <?php if (!empty($categoryExpenses['PJ'])): ?>
                    <div class="chart-container">
                        <canvas id="chartCategoriesPJ"></canvas>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-chart-pie"></i>
                        <h5>Nenhuma despesa PJ</h5>
                        <p>Não há despesas confirmadas em contas PJ para exibir no gráfico.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        </div> <!-- End row -->
    </div> <!-- End widget-content -->
</div> <!-- End widget: category-charts -->

<!-- Widget: Recent Transactions -->
<div class="dashboard-widget" data-widget="recent-transactions">
    <div class="widget-header" style="display: none;">
        <h5><i class="fas fa-exchange-alt me-2"></i>Últimos Lançamentos</h5>
        <div class="widget-controls">
            <i class="fas fa-grip-vertical drag-handle"></i>
        </div>
    </div>
    <div class="widget-content">
        <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 d-flex align-items-center">
            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                <i class="fas fa-exchange-alt text-white"></i>
            </div>
            Últimos Lançamentos
        </h5>
        <a href="<?= url('/transactions') ?>" class="btn btn-sm btn-outline-primary">Ver Todos</a>
    </div>
    <div class="card-body">
        <?php if (!empty($recentTransactions)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <tbody>
                        <?php foreach ($recentTransactions as $transaction): ?>
                            <?php
                            // Verificar se a transação está vencida (data passou e não foi confirmada)
                            $isOverdue = (
                                strtotime($transaction['data_competencia']) < strtotime(date('Y-m-d')) &&
                                $transaction['status'] !== 'confirmado'
                            );
                            $rowClass = $isOverdue ? 'table-danger border-danger' : '';
                            ?>
                            <tr class="<?= $rowClass ?>"<?= $isOverdue ? ' title="Transação vencida - requer atenção"' : '' ?>>
                                <td style="width: 50px;">
                                    <?php if ($transaction['kind'] === 'entrada'): ?>
                                        <div class="badge bg-success rounded-pill">
                                            <i class="fas fa-arrow-up"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="badge bg-danger rounded-pill">
                                            <i class="fas fa-arrow-down"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div>
                                        <h6 class="mb-0"><?= htmlspecialchars($transaction['descricao']) ?></h6>
                                        <small class="text-muted">
                                            <?php if ($transaction['account_name']): ?>
                                                <?= htmlspecialchars($transaction['account_name']) ?>
                                            <?php elseif ($transaction['credit_card_name']): ?>
                                                <i class="fab fa-cc-<?= strtolower($transaction['credit_card_bandeira']) ?> me-1"></i>
                                                <?= htmlspecialchars($transaction['credit_card_name']) ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?> • 
                                            <?php if ($transaction['status'] === 'confirmado' && $transaction['data_pagamento']): ?>
                                                <span class="text-success">
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    Pago em <?= date('d/m/Y', strtotime($transaction['data_pagamento'])) ?>
                                                </span>
                                                <?php if ($transaction['data_pagamento'] !== $transaction['data_competencia']): ?>
                                                    <br><small class="text-muted ms-3">(Vencimento: <?= date('d/m/Y', strtotime($transaction['data_competencia'])) ?>)</small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?php if ($isOverdue): ?>
                                                    <span class="text-danger fw-bold">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        <?= date('d/m/Y', strtotime($transaction['data_competencia'])) ?> (Vencido)
                                                    </span>
                                                <?php else: ?>
                                                    <?= date('d/m/Y', strtotime($transaction['data_competencia'])) ?>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold <?= $transaction['kind'] === 'entrada' ? 'text-success' : 'text-danger' ?>">
                                        <?= $transaction['kind'] === 'entrada' ? '+' : '-' ?>R$ <?= number_format($transaction['valor'], 2, ',', '.') ?>
                                    </span>
                                    <br>
                                    <div class="d-flex gap-2 justify-content-end align-items-center">
                                        <small class="badge bg-<?= $transaction['status'] === 'confirmado' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($transaction['status']) ?>
                                        </small>
                                        <?php if ($transaction['status'] !== 'confirmado'): ?>
                                            <?php if ($transaction['status'] === 'agendado'): ?>
                                                <button class="btn btn-sm btn-success" onclick="launchScheduledTransaction(<?= $transaction['id'] ?>)" title="Lançar agendamento">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-success" onclick="confirmTransactionDashboard(<?= $transaction['id'] ?>, '<?= htmlspecialchars($transaction['descricao']) ?>', <?= $transaction['valor'] ?>)" title="Confirmar lançamento">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-receipt"></i>
                <h5>Nenhum lançamento cadastrado</h5>
                <p>Comece criando uma conta bancária e depois registre suas receitas e despesas para ter controle total das suas finanças.</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="<?= url('/accounts') ?>" class="btn btn-primary">
                        <i class="fas fa-university me-2"></i>
                        Criar Conta
                    </a>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#dashboardTransactionModal" onclick="setDashboardTransactionType('entrada')">
                        <i class="fas fa-plus me-2"></i>
                        Nova Receita
                    </button>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#dashboardTransactionModal" onclick="setDashboardTransactionType('saida')">
                        <i class="fas fa-minus me-2"></i>
                        Nova Despesa
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
        </div> <!-- End card -->
    </div> <!-- End widget-content -->
</div> <!-- End widget: recent-transactions -->

</div> <!-- End Dashboard Widgets Container -->

<!-- Modal para Nova Transação (Dashboard) -->
<div class="modal fade" id="dashboardTransactionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dashboardTransactionModalTitle">Novo Lançamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="dashboardTransactionForm">
                    <input type="hidden" id="dashboardTransactionId" name="id">
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="dashboardTransactionKind" name="kind" required onchange="updateDashboardCategoriesByType(this.value)">
                                    <option value="entrada">Receita</option>
                                    <option value="saida">Despesa</option>
                                </select>
                                <label for="dashboardTransactionKind">Tipo *</label>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="dashboardTransactionDescricao" name="descricao" required>
                                <label for="dashboardTransactionDescricao">Descrição *</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control currency-mask" id="dashboardTransactionValor" name="valor" placeholder="R$ 0,00" required>
                                <label for="dashboardTransactionValor">Valor *</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" id="dashboardTransactionDataCompetencia" name="data_competencia" required>
                                <label for="dashboardTransactionDataCompetencia">Data de Competência *</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" id="dashboardTransactionDataPagamento" name="data_pagamento">
                                <label for="dashboardTransactionDataPagamento">Data de Pagamento</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="dashboardPaymentMethod" name="payment_method" required onchange="toggleDashboardPaymentFields()">
                                    <option value="account">Conta Bancária</option>
                                    <option value="credit_card">Cartão de Crédito</option>
                                </select>
                                <label for="dashboardPaymentMethod">Método *</label>
                            </div>
                        </div>
                        <div class="col-md-3" id="dashboardAccountField">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="dashboardTransactionAccount" name="account_id">
                                    <option value="">Selecione uma conta...</option>
                                    <?php foreach ($accounts as $account): ?>
                                        <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="dashboardTransactionAccount">Conta *</label>
                            </div>
                        </div>
                        <div class="col-md-3" id="dashboardCreditCardField" style="display: none;">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="dashboardTransactionCreditCard" name="credit_card_id">
                                    <option value="">Selecione um cartão...</option>
                                    <?php foreach ($creditCards as $card): ?>
                                        <option value="<?= $card['id'] ?>"><?= htmlspecialchars($card['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="dashboardTransactionCreditCard">Cartão de Crédito *</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="dashboardTransactionCategory" name="category_id">
                                    <option value="">Selecione uma categoria...</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>">
                                            <?= htmlspecialchars($category['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="dashboardTransactionCategory">Categoria</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="dashboardTransactionContact" name="contact_id">
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
                                <label for="dashboardTransactionContact">Contato</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="dashboardTransactionStatus" name="status" required onchange="toggleDashboardRecurrenceFields()">
                                    <option value="confirmado">Confirmado</option>
                                    <option value="agendado">Agendado</option>
                                    <option value="rascunho">Rascunho</option>
                                </select>
                                <label for="dashboardTransactionStatus">Status *</label>
                            </div>
                        </div>
                    </div>
                    
                    
                    <!-- Campos de Recorrência (mostrar apenas quando status = agendado) -->
                    <div id="dashboardRecurrenceFields" style="display: none;">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Configure a recorrência para gerar automaticamente os próximos lançamentos
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="dashboardTransactionRecurrenceType" name="recurrence_type">
                                        <option value="">Não recorrente</option>
                                        <option value="weekly">Semanal</option>
                                        <option value="monthly">Mensal</option>
                                        <option value="quarterly">Trimestral</option>
                                        <option value="biannual">Semestral</option>
                                        <option value="yearly">Anual</option>
                                    </select>
                                    <label for="dashboardTransactionRecurrenceType">Ciclo de Recorrência</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="dashboardTransactionRecurrenceCount" name="recurrence_count" min="1" max="36" value="1">
                                    <label for="dashboardTransactionRecurrenceCount">Quantas repetições</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" id="dashboardTransactionRecurrenceEndDate" name="recurrence_end_date">
                            <label for="dashboardTransactionRecurrenceEndDate">Data limite (opcional)</label>
                        </div>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <textarea class="form-control" id="dashboardTransactionObservacoes" name="observacoes" style="height: 80px"></textarea>
                        <label for="dashboardTransactionObservacoes">Observações</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveDashboardTransaction()">
                    <i class="fas fa-save me-2"></i>
                    <span id="dashboardTransactionSaveButtonText">Salvar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Dashboard Transaction Modal Functions
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('dashboardTransactionDataCompetencia').value = today;
    
    // Apply currency mask to value field
    const valorField = document.getElementById('dashboardTransactionValor');
    valorField.value = 'R$ 0,00';
    
    // Aplicar máscara de moeda aos campos de valor
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
    
    valorField.addEventListener('input', function(e) {
        e.target.value = formatBrazilianCurrency(e.target.value);
    });
    
    // Reset form when modal is closed
    document.getElementById('dashboardTransactionModal').addEventListener('hidden.bs.modal', function() {
        resetDashboardTransactionForm();
    });
    
    // Add event listener for status change to control recurrence fields
    document.getElementById('dashboardTransactionStatus').addEventListener('change', toggleDashboardRecurrenceFields);
    
    // Initially hide recurrence fields (default status is 'confirmado')
    toggleDashboardRecurrenceFields();
});

function setDashboardTransactionType(type) {
    document.getElementById('dashboardTransactionKind').value = type;
    updateDashboardCategoriesByType(type);
    
    // Update modal title and button
    const title = type === 'entrada' ? 'Nova Receita' : 'Nova Despesa';
    document.getElementById('dashboardTransactionModalTitle').textContent = title;
}

function toggleDashboardPaymentFields() {
    const paymentMethod = document.getElementById('dashboardPaymentMethod').value;
    const accountField = document.getElementById('dashboardAccountField');
    const creditCardField = document.getElementById('dashboardCreditCardField');
    const accountSelect = document.getElementById('dashboardTransactionAccount');
    const creditCardSelect = document.getElementById('dashboardTransactionCreditCard');
    
    if (paymentMethod === 'credit_card') {
        accountField.style.display = 'none';
        creditCardField.style.display = 'block';
        accountSelect.removeAttribute('required');
        creditCardSelect.setAttribute('required', 'required');
        
        // Load credit cards when switching to credit card mode
        loadDashboardCreditCards();
        
        // For credit cards, force kind to be 'saida'
        document.getElementById('dashboardTransactionKind').value = 'saida';
        updateDashboardCategoriesByType('saida');
    } else {
        accountField.style.display = 'block';
        creditCardField.style.display = 'none';
        accountSelect.setAttribute('required', 'required');
        creditCardSelect.removeAttribute('required');
    }
}

function toggleDashboardRecurrenceFields() {
    const status = document.getElementById('dashboardTransactionStatus').value;
    const recurrenceFields = document.getElementById('dashboardRecurrenceFields');
    const paymentDateField = document.getElementById('dashboardTransactionDataPagamento');
    const competenceDateField = document.getElementById('dashboardTransactionDataCompetencia');
    
    if (status === 'agendado') {
        recurrenceFields.style.display = 'block';
        // Clear payment date for scheduled transactions
        paymentDateField.value = '';
    } else {
        recurrenceFields.style.display = 'none';
        // Clear recurrence fields when not scheduled
        document.getElementById('dashboardTransactionRecurrenceType').value = '';
        document.getElementById('dashboardTransactionRecurrenceCount').value = '1';
        document.getElementById('dashboardTransactionRecurrenceEndDate').value = '';
        
        // If confirmed and payment date is empty, set to competence date
        if (status === 'confirmado' && !paymentDateField.value && competenceDateField.value) {
            paymentDateField.value = competenceDateField.value;
        }
    }
}

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

// Função para atualizar categorias no dashboard baseado no tipo
function updateDashboardCategoriesByType(tipo) {
    if (!tipo) {
        return Promise.resolve();
    }
    
    return fetch(`<?= url('/api/transactions/categories') ?>?tipo=${tipo}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateDashboardCategorySelect(data.categories);
            } else {
                console.error('Erro ao carregar categorias:', data.message);
                loadAllDashboardCategories();
            }
        })
        .catch(error => {
            console.error('Erro na requisição de categorias:', error);
            loadAllDashboardCategories();
        });
}

function updateDashboardCategorySelect(categories) {
    const categorySelect = document.getElementById('dashboardTransactionCategory');
    const currentValue = categorySelect.value;
    
    // Clear existing options except the first one
    const firstOption = categorySelect.querySelector('option:first-child');
    categorySelect.innerHTML = '';
    categorySelect.appendChild(firstOption);
    
    // Add new categories
    categories.forEach(category => {
        const option = document.createElement('option');
        option.value = category.id;
        option.textContent = category.nome;
        categorySelect.appendChild(option);
    });
    
    // Try to maintain previous selection if it still exists
    if (currentValue && categorySelect.querySelector(`option[value="${currentValue}"]`)) {
        categorySelect.value = currentValue;
    } else {
        categorySelect.value = '';
    }
}

function loadAllDashboardCategories() {
    // Recarregar todas as categorias (fallback)
    const allCategories = <?= json_encode($categories) ?>;
    updateDashboardCategorySelect(allCategories);
}

function saveDashboardTransaction() {
    const form = document.getElementById('dashboardTransactionForm');
    const formData = new FormData(form);
    
    // Convert formatted value to decimal
    const valorField = document.getElementById('dashboardTransactionValor');
    const valorNumerico = parseBrazilianCurrency(valorField.value);
    formData.set('valor', valorNumerico);
    
    // Validate required fields
    const paymentMethod = formData.get('payment_method');
    if (paymentMethod === 'credit_card' && !formData.get('credit_card_id')) {
        Swal.fire('Atenção!', 'Selecione um cartão de crédito', 'warning');
        return;
    } else if (paymentMethod === 'account' && !formData.get('account_id')) {
        Swal.fire('Atenção!', 'Selecione uma conta bancária', 'warning');
        return;
    }
    
    if (!formData.get('descricao') || !valorNumerico || parseFloat(valorNumerico) <= 0) {
        Swal.fire('Atenção!', 'Preencha todos os campos obrigatórios', 'warning');
        return;
    }
    
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
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                bootstrap.Modal.getInstance(document.getElementById('dashboardTransactionModal')).hide();
                location.reload();
            });
        } else {
            Swal.fire('Erro!', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Erro!', 'Erro ao salvar transação', 'error');
    });
}

function loadDashboardCreditCards() {
    const creditCardSelect = document.getElementById('dashboardTransactionCreditCard');
    
    // Só carregar se ainda não foi carregado
    if (creditCardSelect.options.length === 1 && creditCardSelect.options[0].value === '') {
        fetch('<?= url('/api/credit-cards/active') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                creditCardSelect.innerHTML = '<option value="">Selecione um cartão...</option>';
                
                data.cards.forEach(card => {
                    const option = document.createElement('option');
                    option.value = card.id;
                    option.textContent = `${card.nome} (Limite: R$ ${parseFloat(card.limite_disponivel).toLocaleString('pt-BR', {minimumFractionDigits: 2})})`;
                    option.dataset.limiteDisponivel = card.limite_disponivel;
                    creditCardSelect.appendChild(option);
                });
            } else {
                creditCardSelect.innerHTML = '<option value="">Nenhum cartão encontrado</option>';
            }
        })
        .catch(error => {
            console.error('Error loading credit cards:', error);
            creditCardSelect.innerHTML = '<option value="">Erro ao carregar cartões</option>';
        });
    }
}

function resetDashboardTransactionForm() {
    document.getElementById('dashboardTransactionModalTitle').textContent = 'Novo Lançamento';
    document.getElementById('dashboardTransactionSaveButtonText').textContent = 'Salvar';
    document.getElementById('dashboardTransactionForm').reset();
    document.getElementById('dashboardTransactionId').value = '';
    
    // Reset default values
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('dashboardTransactionDataCompetencia').value = today;
    document.getElementById('dashboardTransactionDataPagamento').value = today; // Default payment date for confirmed transactions
    document.getElementById('dashboardTransactionValor').value = 'R$ 0,00';
    document.getElementById('dashboardTransactionKind').value = 'entrada';
    document.getElementById('dashboardTransactionStatus').value = 'confirmado';
    document.getElementById('dashboardPaymentMethod').value = 'account';
    
    // Reset field visibility
    toggleDashboardPaymentFields();
    
    // Load categories for default type (entrada)
    updateDashboardCategoriesByType('entrada');
    
    // Hide recurrence fields
    document.getElementById('dashboardRecurrenceFields').style.display = 'none';
}


// Função para lançar um agendamento
function launchScheduledTransaction(transactionId) {
    // Primeiro, buscar dados da transação
    fetch(`<?= url('/api/transactions/get') ?>?id=${transactionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const transaction = data.transaction;
                const valorOriginal = parseFloat(transaction.valor || 0);
                const valorFormatado = new Intl.NumberFormat('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                }).format(valorOriginal);
                
                Swal.fire({
                    title: 'Lançar Agendamento',
                    html: `
                        <div class="text-start mb-3">
                            <p><strong>Descrição:</strong> ${transaction.descricao}</p>
                            <p><strong>Valor Original:</strong> ${valorFormatado}</p>
                        </div>
                        <div class="form-group text-start">
                            <label for="launchValue" class="form-label">Valor Final (ajustar se necessário):</label>
                            <input type="text" id="launchValue" class="form-control currency-mask" 
                                   value="${valorFormatado}" placeholder="R$ 0,00">
                            <small class="text-muted">Ajuste o valor caso haja juros, multas ou descontos</small>
                        </div>
                        <div class="form-group text-start mt-3">
                            <label for="launchDate" class="form-label">Data de Pagamento:</label>
                            <input type="date" id="launchDate" class="form-control" value="${new Date().toISOString().split('T')[0]}">
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-check me-2"></i>Confirmar Lançamento',
                    cancelButtonText: 'Cancelar',
                    preConfirm: () => {
                        const valueInput = document.getElementById('launchValue');
                        const dateInput = document.getElementById('launchDate');
                        
                        const newValue = valueInput.value;
                        const paymentDate = dateInput.value;
                        
                        if (!newValue || newValue === 'R$ 0,00' || !paymentDate) {
                            Swal.showValidationMessage('Valor e data de pagamento são obrigatórios');
                            return false;
                        }
                        
                        return {
                            valor: newValue,
                            data_pagamento: paymentDate
                        };
                    },
                    didOpen: () => {
                        // Aplicar máscara de moeda
                        const valueInput = document.getElementById('launchValue');
                        
                        function formatCurrency(value) {
                            let numericValue = value.replace(/[^\d]/g, '');
                            if (numericValue === '') return 'R$ 0,00';
                            
                            let intValue = parseInt(numericValue);
                            let formattedValue = (intValue / 100).toLocaleString('pt-BR', {
                                style: 'currency',
                                currency: 'BRL'
                            });
                            
                            return formattedValue;
                        }
                        
                        valueInput.addEventListener('input', function(e) {
                            e.target.value = formatCurrency(e.target.value);
                        });
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('id', transactionId);
                        formData.append('valor', result.value.valor);
                        formData.append('data_pagamento', result.value.data_pagamento);
                        
                        fetch('<?= url('/api/transactions/launch') ?>', {
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
                            Swal.fire('Erro!', 'Erro ao lançar agendamento', 'error');
                        });
                    }
                });
            } else {
                Swal.fire('Erro!', 'Erro ao carregar dados da transação', 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            Swal.fire('Erro!', 'Erro ao carregar dados da transação', 'error');
        });
}
</script>

<!-- SortableJS CDN for drag and drop functionality -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<!-- Chart.js CDN - versão UMD para compatibilidade com script tags -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

<!-- Dashboard Widget Styles -->
<style>
.dashboard-widgets {
    min-height: 400px;
}

.dashboard-widget {
    margin-bottom: 1.5rem;
    transition: all 0.3s ease;
}

.dashboard-widget.edit-mode {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 10px;
    background: rgba(248, 249, 250, 0.5);
    position: relative;
    z-index: 1;
}

.dashboard-widget.edit-mode:hover {
    border-color: #007bff;
    background: rgba(0, 123, 255, 0.05);
}

.dashboard-widget.edit-mode .drag-handle {
    background: rgba(255, 255, 255, 0.15) !important;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.dashboard-widget.edit-mode .drag-handle:hover {
    background: rgba(255, 255, 255, 0.25) !important;
    border-color: rgba(255, 255, 255, 0.5);
    transform: scale(1.05);
}

/* Allow controlled behavior during drag */
body.dragging {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

.dashboard-widgets {
    position: relative;
    min-height: 200px;
}

.widget-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 15px;
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    border-radius: 6px;
    margin-bottom: 10px;
    font-size: 14px;
}

.widget-header h5 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    flex: 1;
}

.widget-controls {
    display: flex;
    gap: 8px;
    align-items: center;
}

.drag-handle {
    cursor: grab;
    padding: 8px;
    color: rgba(255, 255, 255, 0.8);
    transition: color 0.2s ease;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
    min-width: 24px;
    min-height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.drag-handle:hover {
    color: white;
    background: rgba(255, 255, 255, 0.2);
}

.drag-handle:active {
    cursor: grabbing;
    background: rgba(255, 255, 255, 0.3);
}

/* Ensure drag handle is always accessible */
.dashboard-widget.edit-mode .drag-handle {
    pointer-events: auto;
    z-index: 100;
    position: relative;
}

/* Sortable states */
.widget-ghost {
    opacity: 0.5;
    background: #f8f9fa;
}

.widget-chosen {
    background: rgba(0, 123, 255, 0.1);
    border-color: #007bff;
}

.widget-drag {
    transform: rotate(5deg);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.widget-dragging {
    z-index: 9999 !important;
    pointer-events: none;
}

.widget-content {
    position: relative;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .widget-header {
        padding: 6px 10px;
        font-size: 12px;
    }
    
    .dashboard-widget.edit-mode {
        padding: 5px;
    }
}
</style>

<script>
// Aguardar Chart.js carregar
window.addEventListener('load', function() {
    if (typeof Chart !== 'undefined') {
        initializeCategoryCharts();
    } else {
        console.error('Chart.js não foi carregado');
    }
});

function initializeCategoryCharts() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js não está disponível');
        return;
    }
    // Dados para gráfico PF
    <?php if (!empty($categoryExpenses['PF'])): ?>
    const dataPF = {
        labels: [<?php foreach ($categoryExpenses['PF'] as $expense): ?>
            '<?= addslashes($expense['category_name'] ?: 'Sem categoria') ?>',
        <?php endforeach; ?>],
        datasets: [{
            data: [<?php foreach ($categoryExpenses['PF'] as $expense): ?>
                <?= $expense['total_gasto'] ?>,
            <?php endforeach; ?>],
            backgroundColor: [<?php foreach ($categoryExpenses['PF'] as $expense): ?>
                '<?= $expense['category_color'] ?: '#6c757d' ?>',
            <?php endforeach; ?>],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    };
    
    const ctxPF = document.getElementById('chartCategoriesPF').getContext('2d');
    new Chart(ctxPF, {
        type: 'doughnut',
        data: dataPF,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return context.label + ': R$ ' + value.toLocaleString('pt-BR', {minimumFractionDigits: 2}) + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>
    
    // Dados para gráfico PJ
    <?php if (!empty($categoryExpenses['PJ'])): ?>
    const dataPJ = {
        labels: [<?php foreach ($categoryExpenses['PJ'] as $expense): ?>
            '<?= addslashes($expense['category_name'] ?: 'Sem categoria') ?>',
        <?php endforeach; ?>],
        datasets: [{
            data: [<?php foreach ($categoryExpenses['PJ'] as $expense): ?>
                <?= $expense['total_gasto'] ?>,
            <?php endforeach; ?>],
            backgroundColor: [<?php foreach ($categoryExpenses['PJ'] as $expense): ?>
                '<?= $expense['category_color'] ?: '#6c757d' ?>',
            <?php endforeach; ?>],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    };
    
    const ctxPJ = document.getElementById('chartCategoriesPJ').getContext('2d');
    new Chart(ctxPJ, {
        type: 'doughnut',
        data: dataPJ,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return context.label + ': R$ ' + value.toLocaleString('pt-BR', {minimumFractionDigits: 2}) + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>
}

// Função para confirmar lançamento no dashboard (permite editar data e valor)
function confirmTransactionDashboard(transactionId, description, originalValue) {
    const valorOriginal = parseFloat(originalValue || 0);
    const valorFormatado = new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(valorOriginal);
    
    Swal.fire({
        title: 'Confirmar Lançamento',
        html: `
            <div class="text-start mb-3">
                <p><strong>Descrição:</strong> ${description}</p>
                <p><strong>Valor Original:</strong> ${valorFormatado}</p>
                <p class="text-muted">Esta ação irá confirmar o lançamento e atualizar o saldo da conta.</p>
            </div>
            <div class="form-group text-start">
                <label for="confirmValue" class="form-label">Valor Final (ajustar se necessário):</label>
                <input type="text" id="confirmValue" class="form-control currency-mask" 
                       value="${valorFormatado}" placeholder="R$ 0,00">
                <small class="text-muted">Ajuste o valor caso haja juros, multas ou descontos</small>
            </div>
            <div class="form-group text-start mt-3">
                <label for="confirmDate" class="form-label">Data de Confirmação:</label>
                <input type="date" id="confirmDate" class="form-control" value="${new Date().toISOString().split('T')[0]}">
                <small class="text-muted">Pode ser diferente da data de competência original</small>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-check me-2"></i>Confirmar Lançamento',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const valueInput = document.getElementById('confirmValue');
            const dateInput = document.getElementById('confirmDate');
            
            const newValue = valueInput.value;
            const confirmDate = dateInput.value;
            
            if (!newValue || newValue === 'R$ 0,00' || !confirmDate) {
                Swal.showValidationMessage('Valor e data de confirmação são obrigatórios');
                return false;
            }
            
            return {
                valor: newValue,
                data_pagamento: confirmDate
            };
        },
        didOpen: () => {
            // Aplicar máscara de moeda
            const valueInput = document.getElementById('confirmValue');
            
            function formatCurrency(value) {
                let numericValue = value.replace(/[^\d]/g, '');
                if (numericValue === '') return 'R$ 0,00';
                
                let intValue = parseInt(numericValue);
                let formattedValue = (intValue / 100).toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                });
                
                return formattedValue;
            }
            
            valueInput.addEventListener('input', function(e) {
                e.target.value = formatCurrency(e.target.value);
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id', transactionId);
            formData.append('valor', result.value.valor);
            formData.append('data_pagamento', result.value.data_pagamento);
            
            fetch('<?= url('/api/transactions/confirm') ?>', {
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
                Swal.fire('Erro!', 'Erro ao confirmar lançamento', 'error');
            });
        }
    });
}

// Dashboard Widget Management System
let isEditMode = false;
let sortableInstance = null;

// Toggle edit mode on/off
function toggleEditMode() {
    isEditMode = !isEditMode;
    const editModeControls = document.getElementById('editModeControls');
    const editModeButton = document.getElementById('toggleEditMode');
    const editModeText = document.getElementById('editModeText');
    const widgets = document.querySelectorAll('.dashboard-widget');
    
    if (isEditMode) {
        // Enter edit mode
        editModeControls.style.display = 'block';
        editModeButton.classList.remove('btn-outline-secondary');
        editModeButton.classList.add('btn-warning');
        editModeText.textContent = 'Sair da Edição';
        
        // Show widget headers and enable sortable
        widgets.forEach(widget => {
            widget.classList.add('edit-mode');
            const header = widget.querySelector('.widget-header');
            if (header) header.style.display = 'flex';
        });
        
        // Initialize sortable
        initializeSortable();
        
    } else {
        // Exit edit mode
        editModeControls.style.display = 'none';
        editModeButton.classList.remove('btn-warning');
        editModeButton.classList.add('btn-outline-secondary');
        editModeText.textContent = 'Editar Layout';
        
        // Hide widget headers and disable sortable
        widgets.forEach(widget => {
            widget.classList.remove('edit-mode');
            const header = widget.querySelector('.widget-header');
            if (header) header.style.display = 'none';
        });
        
        // Destroy sortable
        if (sortableInstance) {
            sortableInstance.destroy();
            sortableInstance = null;
        }
    }
}

// Initialize sortable functionality using SortableJS
function initializeSortable() {
    const container = document.getElementById('dashboardWidgets');
    
    if (!container) {
        console.error('Dashboard widgets container not found');
        return;
    }
    
    if (typeof Sortable !== 'undefined') {
        sortableInstance = Sortable.create(container, {
            animation: 200,
            handle: '.drag-handle', // ONLY allow dragging by this handle
            ghostClass: 'widget-ghost',
            chosenClass: 'widget-chosen',
            dragClass: 'widget-drag',
            draggable: '.dashboard-widget', // ONLY these elements can be dragged
            filter: '.modal, .modal-backdrop, .swal2-container, .btn, button, input, select, textarea', // Allow normal widget content
            preventOnFilter: true,
            scroll: true, // Allow scrolling during drag
            scrollSensitivity: 30,
            scrollSpeed: 10,
            bubbleScroll: true,
            forceFallback: false,
            fallbackTolerance: 0,
            swapThreshold: 0.65,
            onStart: function(evt) {
                console.log('Drag started');
                document.body.classList.add('dragging');
                evt.item.classList.add('widget-dragging');
            },
            onEnd: function(evt) {
                console.log('Drag ended');
                document.body.classList.remove('dragging');
                evt.item.classList.remove('widget-dragging');
                console.log('Widget moved from', evt.oldIndex, 'to', evt.newIndex);
            }
        });
    } else {
        console.error('SortableJS library not found');
        Swal.fire('Erro', 'Biblioteca de arrastar e soltar não foi carregada. Recarregue a página.', 'error');
    }
}

// Get current widget order
function getCurrentWidgetOrder() {
    const widgets = document.querySelectorAll('.dashboard-widget');
    const order = [];
    
    widgets.forEach(widget => {
        const widgetId = widget.getAttribute('data-widget');
        if (widgetId) {
            order.push(widgetId);
        }
    });
    
    return order;
}

// Save layout and exit edit mode
function saveLayoutAndExit() {
    const order = getCurrentWidgetOrder();
    
    Swal.fire({
        title: 'Salvando layout...',
        text: 'Aguarde enquanto salvamos suas preferências',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch('<?= url('/api/dashboard/save-layout') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            widget_order: order
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Layout Salvo!',
                text: 'Suas preferências foram salvas com sucesso.',
                timer: 2000,
                showConfirmButton: false
            });
            toggleEditMode();
        } else {
            Swal.fire('Erro', data.message || 'Erro ao salvar layout', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Erro', 'Erro de conexão ao salvar layout', 'error');
    });
}

// Reset layout to default
function resetLayoutToDefault() {
    Swal.fire({
        title: 'Restaurar Layout Padrão?',
        text: 'Esta ação irá restaurar a ordem original dos widgets e não pode ser desfeita.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, Restaurar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Restaurando layout...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch('<?= url('/api/dashboard/reset-layout') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Layout Restaurado!',
                        text: 'A página será recarregada para aplicar as mudanças.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Erro', data.message || 'Erro ao restaurar layout', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Erro', 'Erro de conexão ao restaurar layout', 'error');
            });
        }
    });
}

// Apply user's saved layout order on page load
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($userLayout) && !empty($userLayout['widget_order'])): ?>
    const userWidgetOrder = <?= json_encode($userLayout['widget_order']) ?>;
    applyWidgetOrder(userWidgetOrder);
    <?php endif; ?>
});

// Apply widget order by reordering DOM elements
function applyWidgetOrder(order) {
    const container = document.getElementById('dashboardWidgets');
    const widgets = container.querySelectorAll('.dashboard-widget');
    
    // Create a map of current widgets
    const widgetMap = new Map();
    widgets.forEach(widget => {
        const widgetId = widget.getAttribute('data-widget');
        if (widgetId) {
            widgetMap.set(widgetId, widget);
        }
    });
    
    // Reorder widgets according to user preference
    order.forEach(widgetId => {
        const widget = widgetMap.get(widgetId);
        if (widget) {
            container.appendChild(widget);
        }
    });
    
    // Add any widgets that weren't in the user's order to the end
    widgetMap.forEach((widget, widgetId) => {
        if (!order.includes(widgetId)) {
            container.appendChild(widget);
        }
    });
}
</script>