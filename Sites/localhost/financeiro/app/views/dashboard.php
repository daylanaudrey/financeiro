<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-tachometer-alt me-3"></i>
        Dashboard
    </h1>
    <div class="quick-actions">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#quickIncomeModal">
            <i class="fas fa-plus me-2"></i>
            Nova Receita
        </button>
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#quickExpenseModal">
            <i class="fas fa-minus me-2"></i>
            Nova Despesa
        </button>
    </div>
</div>

<!-- Cards de Resumo Geral -->
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
    </div>
</div>

<!-- Cards Separados por Tipo de Conta -->
<?php if (!empty($accountBalancesByType) && count($accountBalancesByType) > 1): ?>
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
</div>
<?php endif; ?>

<!-- Dados Comparativos -->
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
    </div>
</div>

<!-- Contas e Agendamentos -->
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
                                        <?= htmlspecialchars($transaction['account_name']) ?> • 
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
    </div>
</div>

<!-- Gráficos de Categorias -->
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
    </div>
</div>

<!-- Últimos Lançamentos -->
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
                                            <?= htmlspecialchars($transaction['account_name']) ?> • 
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
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#quickIncomeModal">
                        <i class="fas fa-plus me-2"></i>
                        Nova Receita
                    </button>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#quickExpenseModal">
                        <i class="fas fa-minus me-2"></i>
                        Nova Despesa
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para Nova Receita Rápida -->
<div class="modal fade" id="quickIncomeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nova Receita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickIncomeForm">
                    <div class="mb-3">
                        <label for="quickIncomeDescricao" class="form-label">Descrição *</label>
                        <input type="text" class="form-control" id="quickIncomeDescricao" name="descricao" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quickIncomeValor" class="form-label">Valor *</label>
                        <input type="text" class="form-control" id="quickIncomeValor" name="valor" placeholder="R$ 0,00" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="quickIncomeAccount" class="form-label">Conta *</label>
                                <select class="form-select" id="quickIncomeAccount" name="account_id" required>
                                    <option value="">Selecione uma conta...</option>
                                    <?php foreach ($accounts as $account): ?>
                                        <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="quickIncomeCategory" class="form-label">Categoria</label>
                                <select class="form-select" id="quickIncomeCategory" name="category_id">
                                    <option value="">Selecione uma categoria...</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>">
                                            <?= htmlspecialchars($category['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="quickIncomeData" class="form-label">Data *</label>
                                <input type="date" class="form-control" id="quickIncomeData" name="data_competencia" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quickIncomeObservacoes" class="form-label">Observações</label>
                        <textarea class="form-control" id="quickIncomeObservacoes" name="observacoes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="saveQuickIncome()">
                    <i class="fas fa-save me-2"></i>
                    Salvar Receita
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Nova Despesa Rápida -->
<div class="modal fade" id="quickExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nova Despesa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickExpenseForm">
                    <div class="mb-3">
                        <label for="quickExpenseDescricao" class="form-label">Descrição *</label>
                        <input type="text" class="form-control" id="quickExpenseDescricao" name="descricao" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quickExpenseValor" class="form-label">Valor *</label>
                        <input type="text" class="form-control" id="quickExpenseValor" name="valor" placeholder="R$ 0,00" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="quickExpenseAccount" class="form-label">Conta *</label>
                                <select class="form-select" id="quickExpenseAccount" name="account_id" required>
                                    <option value="">Selecione uma conta...</option>
                                    <?php foreach ($accounts as $account): ?>
                                        <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="quickExpenseCategory" class="form-label">Categoria</label>
                                <select class="form-select" id="quickExpenseCategory" name="category_id">
                                    <option value="">Selecione uma categoria...</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>">
                                            <?= htmlspecialchars($category['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="quickExpenseData" class="form-label">Data *</label>
                                <input type="date" class="form-control" id="quickExpenseData" name="data_competencia" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quickExpenseObservacoes" class="form-label">Observações</label>
                        <textarea class="form-control" id="quickExpenseObservacoes" name="observacoes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="saveQuickExpense()">
                    <i class="fas fa-save me-2"></i>
                    Salvar Despesa
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Configurar data de hoje como padrão ao abrir modais
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('quickIncomeData').value = today;
    document.getElementById('quickExpenseData').value = today;
    
    // Carregar categorias corretas ao abrir os modais
    document.getElementById('quickIncomeModal').addEventListener('show.bs.modal', function() {
        updateDashboardCategoriesByType('entrada', 'quickIncomeCategory');
    });
    
    document.getElementById('quickExpenseModal').addEventListener('show.bs.modal', function() {
        updateDashboardCategoriesByType('saida', 'quickExpenseCategory');
    });
    
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
    
    applyMask(document.getElementById('quickIncomeValor'));
    applyMask(document.getElementById('quickExpenseValor'));
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

// Função para atualizar categorias no dashboard baseado no tipo
function updateDashboardCategoriesByType(tipo, selectId) {
    if (!tipo) {
        return;
    }
    
    fetch(`<?= url('/api/transactions/categories') ?>?tipo=${tipo}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateDashboardCategorySelect(data.categories, selectId);
            } else {
                console.error('Erro ao carregar categorias:', data.message);
                // Em caso de erro, carregar todas as categorias
                loadAllDashboardCategories(selectId);
            }
        })
        .catch(error => {
            console.error('Erro na requisição de categorias:', error);
            // Em caso de erro, carregar todas as categorias
            loadAllDashboardCategories(selectId);
        });
}

function updateDashboardCategorySelect(categories, selectId) {
    const categorySelect = document.getElementById(selectId);
    const currentValue = categorySelect.value;
    
    // Limpar opções existentes (exceto a primeira)
    const firstOption = categorySelect.querySelector('option:first-child');
    categorySelect.innerHTML = '';
    categorySelect.appendChild(firstOption);
    
    // Adicionar novas categorias
    categories.forEach(category => {
        const option = document.createElement('option');
        option.value = category.id;
        option.textContent = category.nome;
        categorySelect.appendChild(option);
    });
    
    // Tentar manter a seleção anterior se ainda existir
    if (currentValue && categorySelect.querySelector(`option[value="${currentValue}"]`)) {
        categorySelect.value = currentValue;
    } else {
        categorySelect.value = '';
    }
}

function loadAllDashboardCategories(selectId) {
    // Recarregar todas as categorias (fallback)
    const allCategories = <?= json_encode($categories) ?>;
    updateDashboardCategorySelect(allCategories, selectId);
}

function saveQuickIncome() {
    const form = document.getElementById('quickIncomeForm');
    const formData = new FormData(form);
    
    // Converter valor formatado para número
    const valorField = document.getElementById('quickIncomeValor');
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

function saveQuickExpense() {
    const form = document.getElementById('quickExpenseForm');
    const formData = new FormData(form);
    
    // Converter valor formatado para número
    const valorField = document.getElementById('quickExpenseValor');
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

// Resetar formulários quando modais são fechados
document.getElementById('quickIncomeModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('quickIncomeForm').reset();
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('quickIncomeData').value = today;
    document.getElementById('quickIncomeValor').value = 'R$ 0,00';
    // Recarregar todas as categorias
    loadAllDashboardCategories('quickIncomeCategory');
});

document.getElementById('quickExpenseModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('quickExpenseForm').reset();
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('quickExpenseData').value = today;
    document.getElementById('quickExpenseValor').value = 'R$ 0,00';
    // Recarregar todas as categorias
    loadAllDashboardCategories('quickExpenseCategory');
});

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

<!-- Chart.js CDN - versão UMD para compatibilidade com script tags -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

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
</script>