<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-exchange-alt me-3"></i>
        Lançamentos
    </h1>
    <div class="quick-actions">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#transactionModal" onclick="setTransactionType('entrada')">
            <i class="fas fa-plus me-2"></i>
            Nova Receita
        </button>
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#transactionModal" onclick="setTransactionType('saida')">
            <i class="fas fa-minus me-2"></i>
            Nova Despesa
        </button>
    </div>
</div>

<!-- Cards de Resumo do Mês -->
<div class="row mb-5">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card success">
            <div class="position-relative">
                <h6 class="mb-2 opacity-90">Receitas (Mês)</h6>
                <h4 class="mb-0">R$ <?= number_format($monthlyBalance['receitas'] ?? 0, 2, ',', '.') ?></h4>
                <i class="fas fa-arrow-up stat-icon"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card danger">
            <div class="position-relative">
                <h6 class="mb-2 opacity-90">Despesas (Mês)</h6>
                <h4 class="mb-0">R$ <?= number_format($monthlyBalance['despesas'] ?? 0, 2, ',', '.') ?></h4>
                <i class="fas fa-arrow-down stat-icon"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card info">
            <div class="position-relative">
                <h6 class="mb-2 opacity-90">Resultado (Mês)</h6>
                <?php 
                $resultado = ($monthlyBalance['receitas'] ?? 0) - ($monthlyBalance['despesas'] ?? 0);
                ?>
                <h4 class="mb-0">R$ <?= number_format($resultado, 2, ',', '.') ?></h4>
                <i class="fas fa-chart-line stat-icon"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card warning">
            <div class="position-relative">
                <h6 class="mb-2 opacity-90">Total de Lançamentos</h6>
                <h4 class="mb-0"><?= $monthlyBalance['total_transactions'] ?? 0 ?></h4>
                <i class="fas fa-receipt stat-icon"></i>
            </div>
        </div>
    </div>
</div>

<?php if (empty($transactions)): ?>
    <!-- Estado Vazio -->
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <i class="fas fa-receipt"></i>
                <h5>Nenhum lançamento encontrado</h5>
                <p>Comece registrando suas receitas e despesas para ter controle total das suas finanças.</p>
                <div class="d-flex justify-content-center gap-3">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#transactionModal" onclick="setTransactionType('entrada')">
                        <i class="fas fa-plus me-2"></i>
                        Nova Receita
                    </button>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#transactionModal" onclick="setTransactionType('saida')">
                        <i class="fas fa-minus me-2"></i>
                        Nova Despesa
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0 d-flex align-items-center">
                <i class="fas fa-filter me-2"></i>
                Filtros
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= url('/transactions') ?>" class="row g-3">
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Data Inicial</label>
                    <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" value="<?= htmlspecialchars($filters['date_from']) ?>">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Data Final</label>
                    <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" value="<?= htmlspecialchars($filters['date_to']) ?>">
                </div>
                <div class="col-md-2">
                    <label for="account_filter" class="form-label">Conta</label>
                    <select class="form-select form-select-sm" id="account_filter" name="account_id">
                        <option value="">Todas as contas</option>
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?= $account['id'] ?>" <?= $filters['account_id'] == $account['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($account['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="category_filter" class="form-label">Categoria</label>
                    <select class="form-select form-select-sm" id="category_filter" name="category_id">
                        <option value="">Todas as categorias</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= $filters['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="kind_filter" class="form-label">Tipo</label>
                    <select class="form-select form-select-sm" id="kind_filter" name="kind">
                        <option value="">Todos os tipos</option>
                        <?php foreach ($kindOptions as $key => $option): ?>
                            <option value="<?= $key ?>" <?= $filters['kind'] == $key ? 'selected' : '' ?>>
                                <?= $option['nome'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status_filter" class="form-label">Status</label>
                    <select class="form-select form-select-sm" id="status_filter" name="status">
                        <option value="">Todos os status</option>
                        <?php foreach ($statusOptions as $key => $option): ?>
                            <option value="<?= $key ?>" <?= $filters['status'] == $key ? 'selected' : '' ?>>
                                <?= $option['nome'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="order_by" class="form-label">Ordenação</label>
                    <select class="form-select form-select-sm" id="order_by" name="order_by">
                        <option value="" <?= !isset($filters['order_by']) || $filters['order_by'] == '' ? 'selected' : '' ?>>Vencidos primeiro</option>
                        <option value="data_vencimento" <?= $filters['order_by'] == 'data_vencimento' ? 'selected' : '' ?>>Data de vencimento (crescente)</option>
                        <option value="data_vencimento_desc" <?= $filters['order_by'] == 'data_vencimento_desc' ? 'selected' : '' ?>>Data de vencimento (decrescente)</option>
                        <option value="valor" <?= $filters['order_by'] == 'valor' ? 'selected' : '' ?>>Valor (maior primeiro)</option>
                        <option value="created_at" <?= $filters['order_by'] == 'created_at' ? 'selected' : '' ?>>Data de criação</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="text" class="form-control form-control-sm" id="search" name="search" placeholder="Buscar por descrição ou observações..." value="<?= htmlspecialchars($filters['search']) ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="vencidos" name="vencidos" value="true" <?= isset($filters['vencidos']) && $filters['vencidos'] == 'true' ? 'checked' : '' ?>>
                        <label class="form-check-label text-danger fw-bold" for="vencidos">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Apenas Vencidos
                        </label>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search me-1"></i>
                        Filtrar
                    </button>
                    <a href="<?= url('/transactions') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times me-1"></i>
                        Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Transações -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 d-flex align-items-center">
                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                    <i class="fas fa-list text-white"></i>
                </div>
                Lançamentos Recentes
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="120">Data</th>
                            <th>Descrição</th>
                            <th width="120">Conta</th>
                            <th width="120">Categoria</th>
                            <th width="100" class="text-end">Valor</th>
                            <th width="100">Status</th>
                            <th width="80"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                            <?php
                            // Verificar se está vencida (data de competência passou e não está confirmada)
                            $isOverdue = (
                                strtotime($transaction['data_competencia']) < strtotime(date('Y-m-d')) &&
                                $transaction['status'] !== 'confirmado'
                            );
                            $rowClass = $isOverdue ? 'table-danger border-danger' : '';
                            ?>
                            <tr class="<?= $rowClass ?>"<?= $isOverdue ? ' title="Transação vencida - requer atenção"' : '' ?>>
                                <td class="align-middle">
                                    <?php if ($transaction['status'] === 'confirmado' && $transaction['data_pagamento']): ?>
                                        <div class="small fw-bold text-success">
                                            <i class="fas fa-check-circle me-1"></i>
                                            <?= date('d/m/Y', strtotime($transaction['data_pagamento'])) ?>
                                        </div>
                                        <?php if ($transaction['data_pagamento'] !== $transaction['data_competencia']): ?>
                                            <div class="small text-muted">
                                                Vencimento: <?= date('d/m/Y', strtotime($transaction['data_competencia'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="small fw-bold">
                                            <?php if ($isOverdue): ?>
                                                <i class="fas fa-exclamation-triangle text-danger me-1" title="Vencida"></i>
                                            <?php endif; ?>
                                            <?= date('d/m/Y', strtotime($transaction['data_competencia'])) ?>
                                        </div>
                                        <div class="small text-muted">
                                            <?php if ($transaction['status'] === 'agendado'): ?>
                                                Vencimento
                                            <?php elseif ($transaction['status'] === 'rascunho'): ?>
                                                Rascunho
                                            <?php else: ?>
                                                <?= ucfirst($transaction['status']) ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="align-middle">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <div style="width: 32px; height: 32px; background: <?= $kindOptions[$transaction['kind']]['cor'] ?>; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                <i class="<?= $kindOptions[$transaction['kind']]['icone'] ?> text-white fa-sm"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($transaction['descricao']) ?></div>
                                            <?php if ($transaction['contact_name']): ?>
                                                <div class="small text-muted"><?= htmlspecialchars($transaction['contact_name']) ?></div>
                                            <?php endif; ?>
                                            <?php if ($transaction['observacoes']): ?>
                                                <div class="small text-muted"><?= htmlspecialchars($transaction['observacoes']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="align-middle">
                                    <div class="small">
                                        <div class="fw-bold"><?= htmlspecialchars($transaction['account_name']) ?></div>
                                    </div>
                                </td>
                                
                                <td class="align-middle">
                                    <?php if ($transaction['category_name']): ?>
                                        <span class="badge" style="background-color: <?= $transaction['category_color'] ?>; color: white;">
                                            <?= htmlspecialchars($transaction['category_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="align-middle text-end">
                                    <div class="fw-bold" style="color: <?= in_array($transaction['kind'], ['entrada', 'transfer_in']) ? '#28a745' : '#dc3545' ?>">
                                        <?= in_array($transaction['kind'], ['entrada', 'transfer_in']) ? '+' : '-' ?>R$ <?= number_format($transaction['valor'], 2, ',', '.') ?>
                                    </div>
                                </td>
                                
                                <td class="align-middle">
                                    <span class="badge" style="background-color: <?= $statusOptions[$transaction['status']]['cor'] ?>; color: white;">
                                        <i class="<?= $statusOptions[$transaction['status']]['icone'] ?> me-1"></i>
                                        <?= $statusOptions[$transaction['status']]['nome'] ?>
                                    </span>
                                </td>
                                
                                <td class="align-middle">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-link text-muted" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <?php if ($transaction['status'] !== 'confirmado'): ?>
                                                <li>
                                                    <a class="dropdown-item text-success" href="#" onclick="confirmTransaction(<?= $transaction['id'] ?>, '<?= htmlspecialchars($transaction['descricao']) ?>', <?= $transaction['valor'] ?>)">
                                                        <i class="fas fa-check me-2"></i>Confirmar Lançamento
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                            <?php endif; ?>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="editTransaction(<?= $transaction['id'] ?>)">
                                                    <i class="fas fa-edit me-2"></i>Editar
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" onclick="deleteTransaction(<?= $transaction['id'] ?>, '<?= htmlspecialchars($transaction['descricao']) ?>')">
                                                    <i class="fas fa-trash me-2"></i>Excluir
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Totalizadores -->
            <div class="card-footer bg-light">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <small class="text-muted">Total Entradas</small>
                            <div class="h6 text-success mb-0">
                                R$ <?= number_format($totals['entradas'], 2, ',', '.') ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <small class="text-muted">Total Saídas</small>
                            <div class="h6 text-danger mb-0">
                                R$ <?= number_format($totals['saidas'], 2, ',', '.') ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <small class="text-muted">Saldo Filtrado</small>
                            <div class="h6 <?= $totals['saldo'] >= 0 ? 'text-success' : 'text-danger' ?> mb-0">
                                R$ <?= number_format($totals['saldo'], 2, ',', '.') ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <small class="text-muted">Registros</small>
                            <div class="h6 text-info mb-0">
                                <?= number_format($pagination['totalRecords'], 0, ',', '.') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Paginação -->
            <?php if ($pagination['totalPages'] > 1): ?>
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Exibindo <?= min($pagination['limit'], $pagination['totalRecords']) ?> de <?= $pagination['totalRecords'] ?> registros
                        </div>
                        <nav aria-label="Paginação">
                            <ul class="pagination pagination-sm mb-0">
                                <?php
                                $currentPage = $pagination['currentPage'];
                                $totalPages = $pagination['totalPages'];
                                $queryParams = $_GET;
                                
                                // Botão "Anterior"
                                if ($currentPage > 1):
                                    $queryParams['page'] = $currentPage - 1;
                                    $prevUrl = url('/transactions') . '?' . http_build_query($queryParams);
                                ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $prevUrl ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link"><i class="fas fa-chevron-left"></i></span>
                                    </li>
                                <?php endif; ?>
                                
                                <?php
                                // Páginas numeradas
                                $startPage = max(1, $currentPage - 2);
                                $endPage = min($totalPages, $currentPage + 2);
                                
                                if ($startPage > 1):
                                    $queryParams['page'] = 1;
                                    $firstUrl = url('/transactions') . '?' . http_build_query($queryParams);
                                ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $firstUrl ?>">1</a>
                                    </li>
                                    <?php if ($startPage > 2): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <?php if ($i == $currentPage): ?>
                                        <li class="page-item active">
                                            <span class="page-link"><?= $i ?></span>
                                        </li>
                                    <?php else: ?>
                                        <?php
                                        $queryParams['page'] = $i;
                                        $pageUrl = url('/transactions') . '?' . http_build_query($queryParams);
                                        ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= $pageUrl ?>"><?= $i ?></a>
                                        </li>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php
                                if ($endPage < $totalPages):
                                    if ($endPage < $totalPages - 1):
                                ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                    <?php
                                    $queryParams['page'] = $totalPages;
                                    $lastUrl = url('/transactions') . '?' . http_build_query($queryParams);
                                    ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $lastUrl ?>"><?= $totalPages ?></a>
                                    </li>
                                <?php endif; ?>
                                
                                <!-- Botão "Próximo" -->
                                <?php if ($currentPage < $totalPages):
                                    $queryParams['page'] = $currentPage + 1;
                                    $nextUrl = url('/transactions') . '?' . http_build_query($queryParams);
                                ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $nextUrl ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link"><i class="fas fa-chevron-right"></i></span>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Modal para Nova Transação / Editar Transação -->
<div class="modal fade" id="transactionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionModalTitle">Novo Lançamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="transactionForm">
                    <input type="hidden" id="transactionId" name="id">
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="transactionKind" name="kind" required>
                                    <option value="entrada">Receita</option>
                                    <option value="saida">Despesa</option>
                                </select>
                                <label for="transactionKind">Tipo *</label>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="transactionDescricao" name="descricao" required>
                                <label for="transactionDescricao">Descrição *</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control currency-mask" id="transactionValor" name="valor" placeholder="R$ 0,00" required>
                                <label for="transactionValor">Valor *</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" id="transactionDataCompetencia" name="data_competencia" required>
                                <label for="transactionDataCompetencia">Data de Competência *</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" id="transactionDataPagamento" name="data_pagamento">
                                <label for="transactionDataPagamento">Data de Pagamento</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="transactionAccount" name="account_id" required>
                                    <option value="">Selecione uma conta...</option>
                                    <?php foreach ($accounts as $account): ?>
                                        <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="transactionAccount">Conta *</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="transactionCategory" name="category_id">
                                    <option value="">Selecione uma categoria...</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>">
                                            <?= htmlspecialchars($category['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="transactionCategory">Categoria</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="transactionStatus" name="status" required>
                                    <option value="confirmado">Confirmado</option>
                                    <option value="agendado">Agendado</option>
                                    <option value="rascunho">Rascunho</option>
                                </select>
                                <label for="transactionStatus">Status *</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Campos de Recorrência (mostrar apenas quando status = agendado) -->
                    <div id="recurrenceFields" style="display: none;">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Configure a recorrência para gerar automaticamente os próximos lançamentos
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="transactionRecurrenceType" name="recurrence_type">
                                        <option value="">Não recorrente</option>
                                        <option value="weekly">Semanal</option>
                                        <option value="monthly">Mensal</option>
                                        <option value="quarterly">Trimestral</option>
                                        <option value="biannual">Semestral</option>
                                        <option value="yearly">Anual</option>
                                    </select>
                                    <label for="transactionRecurrenceType">Ciclo de Recorrência</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="transactionRecurrenceCount" name="recurrence_count" min="1" max="36" value="1">
                                    <label for="transactionRecurrenceCount">Quantas repetições</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" id="transactionRecurrenceEndDate" name="recurrence_end_date">
                            <label for="transactionRecurrenceEndDate">Data limite (opcional)</label>
                        </div>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <textarea class="form-control" id="transactionObservacoes" name="observacoes" style="height: 80px"></textarea>
                        <label for="transactionObservacoes">Observações</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveTransaction()">
                    <i class="fas fa-save me-2"></i>
                    <span id="transactionSaveButtonText">Salvar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let deleteTransactionId = null;

function setTransactionType(type) {
    document.getElementById('transactionKind').value = type;
}

// Definir data de hoje como padrão e aplicar máscara de moeda
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('transactionDataCompetencia').value = today;
    
    // Inicializar Select2 em todos os campos do modal
    $('#transactionCategory').select2({
        placeholder: 'Selecione uma categoria...',
        allowClear: true,
        language: {
            noResults: function() {
                return "Nenhuma categoria encontrada";
            },
            searching: function() {
                return "Pesquisando...";
            }
        },
        dropdownParent: $('#transactionModal')
    });
    
    $('#transactionAccount').select2({
        placeholder: 'Selecione uma conta...',
        allowClear: true,
        language: {
            noResults: function() {
                return "Nenhuma conta encontrada";
            },
            searching: function() {
                return "Pesquisando...";
            }
        },
        dropdownParent: $('#transactionModal')
    });
    
    $('#transactionKind').select2({
        placeholder: 'Selecione o tipo...',
        allowClear: false,
        minimumResultsForSearch: Infinity,
        language: {
            noResults: function() {
                return "Nenhum tipo encontrado";
            }
        },
        dropdownParent: $('#transactionModal')
    });
    
    $('#transactionStatus').select2({
        placeholder: 'Selecione o status...',
        allowClear: false,
        minimumResultsForSearch: Infinity,
        language: {
            noResults: function() {
                return "Nenhum status encontrado";
            }
        },
        dropdownParent: $('#transactionModal')
    });
    
    $('#transactionRecurrenceType').select2({
        placeholder: 'Não recorrente',
        allowClear: true,
        language: {
            noResults: function() {
                return "Nenhuma opção encontrada";
            }
        },
        dropdownParent: $('#transactionModal')
    });
    
    // Inicializar Select2 em todos os filtros
    $('#category_filter').select2({
        placeholder: 'Todas as categorias',
        allowClear: true,
        language: {
            noResults: function() {
                return "Nenhuma categoria encontrada";
            },
            searching: function() {
                return "Pesquisando...";
            }
        }
    });
    
    $('#account_filter').select2({
        placeholder: 'Todas as contas',
        allowClear: true,
        language: {
            noResults: function() {
                return "Nenhuma conta encontrada";
            },
            searching: function() {
                return "Pesquisando...";
            }
        }
    });
    
    $('#kind_filter').select2({
        placeholder: 'Todos os tipos',
        allowClear: true,
        language: {
            noResults: function() {
                return "Nenhum tipo encontrado";
            },
            searching: function() {
                return "Pesquisando...";
            }
        }
    });
    
    $('#status_filter').select2({
        placeholder: 'Todos os status',
        allowClear: true,
        language: {
            noResults: function() {
                return "Nenhum status encontrado";
            },
            searching: function() {
                return "Pesquisando...";
            }
        }
    });
    
    $('#order_by').select2({
        placeholder: 'Vencidos primeiro',
        allowClear: true,
        language: {
            noResults: function() {
                return "Nenhuma opção encontrada";
            },
            searching: function() {
                return "Pesquisando...";
            }
        }
    });
    
    // Aplicar máscara de moeda usando função simples e confiável
    const valorField = document.getElementById('transactionValor');
    
    // Função para formatar moeda brasileira (sem zeros à esquerda desnecessários)
    function formatBrazilianCurrency(value) {
        // Remove tudo que não é dígito
        let numbers = value.replace(/\D/g, '');
        
        // Se vazio, retorna valor inicial
        if (numbers === '') {
            return 'R$ ';
        }
        
        // Converte para número e divide por 100 (centavos)
        let amount = parseInt(numbers) / 100;
        
        // Formata usando toLocaleString brasileiro
        return 'R$ ' + amount.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    
    // Aplicar máscara ao digitar
    valorField.addEventListener('input', function(e) {
        const cursorPos = e.target.selectionStart;
        const oldLength = e.target.value.length;
        
        e.target.value = formatBrazilianCurrency(e.target.value);
        
        // Ajustar posição do cursor
        const newLength = e.target.value.length;
        const newCursorPos = cursorPos + (newLength - oldLength);
        e.target.setSelectionRange(newCursorPos, newCursorPos);
    });
    
    // Definir valor inicial
    valorField.value = 'R$ 0,00';
    
    // Resetar formulário quando modal for fechado
    const modal = document.getElementById('transactionModal');
    modal.addEventListener('hidden.bs.modal', function() {
        resetTransactionForm();
    });
});

function resetTransactionForm() {
    document.getElementById('transactionModalTitle').textContent = 'Novo Lançamento';
    document.getElementById('transactionSaveButtonText').textContent = 'Salvar';
    document.getElementById('transactionForm').reset();
    document.getElementById('transactionId').value = '';
    
    // Redefinir valores padrão
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('transactionDataCompetencia').value = today;
    document.getElementById('transactionValor').value = 'R$ 0,00';
    
    // Limpar e redefinir todos os Select2
    $('#transactionCategory').val(null).trigger('change');
    $('#transactionAccount').val('').trigger('change');
    $('#transactionKind').val('entrada').trigger('change');
    $('#transactionStatus').val('confirmado').trigger('change');
    $('#transactionRecurrenceType').val('').trigger('change');
}

function editTransaction(id) {
    fetch(`<?= url('/api/transactions/get') ?>?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const transaction = data.transaction;
                
                document.getElementById('transactionModalTitle').textContent = 'Editar Lançamento';
                document.getElementById('transactionSaveButtonText').textContent = 'Atualizar';
                
                document.getElementById('transactionId').value = transaction.id;
                document.getElementById('transactionKind').value = transaction.kind;
                document.getElementById('transactionDescricao').value = transaction.descricao;
                
                // Formatar valor como moeda
                const valor = parseFloat(transaction.valor || 0);
                const valorFormatado = new Intl.NumberFormat('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                }).format(valor);
                document.getElementById('transactionValor').value = valorFormatado;
                document.getElementById('transactionDataCompetencia').value = transaction.data_competencia;
                document.getElementById('transactionDataPagamento').value = transaction.data_pagamento || '';
                
                // Usar Select2 para todos os campos
                $('#transactionAccount').val(transaction.account_id).trigger('change');
                $('#transactionCategory').val(transaction.category_id || null).trigger('change');
                $('#transactionKind').val(transaction.kind).trigger('change');
                $('#transactionStatus').val(transaction.status).trigger('change');
                document.getElementById('transactionObservacoes').value = transaction.observacoes || '';
                
                // Carregar campos de recorrência se existirem
                $('#transactionRecurrenceType').val(transaction.recurrence_type || '').trigger('change');
                document.getElementById('transactionRecurrenceCount').value = transaction.recurrence_count || '1';
                document.getElementById('transactionRecurrenceEndDate').value = transaction.recurrence_end_date || '';
                
                // Mostrar/esconder campos de recorrência baseado no status
                toggleRecurrenceFields();
                
                new bootstrap.Modal(document.getElementById('transactionModal')).show();
            } else {
                Swal.fire('Erro!', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            Swal.fire('Erro!', 'Erro ao carregar dados do lançamento', 'error');
        });
}

function saveTransaction() {
    console.log('=== SAVE TRANSACTION START ===');
    
    const form = document.getElementById('transactionForm');
    const formData = new FormData(form);
    const isEdit = document.getElementById('transactionId').value;
    
    console.log('Is edit mode:', isEdit ? 'YES' : 'NO');
    console.log('Form data before processing:');
    for (let [key, value] of formData.entries()) {
        console.log(`  ${key}: ${value}`);
    }
    
    // Converter valor formatado para número (formato brasileiro)
    const valorField = document.getElementById('transactionValor');
    const valorFormatado = valorField.value;
    console.log('Original valor value:', valorFormatado);
    
    // Função para converter moeda brasileira para decimal
    function parseBrazilianCurrency(value) {
        console.log('Parsing currency:', value);
        if (!value || value === 'R$ ') return '0';
        
        // Remove "R$ " e espaços
        let cleaned = value.replace(/^R\$\s*/, '');
        console.log('After removing R$:', cleaned);
        
        // Formato brasileiro: usa vírgula como separador decimal e ponto como milhares
        // Ex: "1.234,56" deve virar "1234.56"
        
        // Se tem vírgula, é formato brasileiro
        if (cleaned.includes(',')) {
            // Remove pontos (separadores de milhares) e substitui vírgula por ponto
            cleaned = cleaned.replace(/\./g, '').replace(',', '.');
            console.log('Brazilian format converted:', cleaned);
            return cleaned;
        }
        
        // Se não tem vírgula mas tem ponto, pode ser:
        // 1. Separador de milhares (ex: "1.000") 
        // 2. Decimal americano (ex: "123.45")
        if (cleaned.includes('.')) {
            const parts = cleaned.split('.');
            // Se a última parte tem mais de 2 dígitos, são milhares
            if (parts[parts.length - 1].length > 2) {
                const result = cleaned.replace(/\./g, '');
                console.log('Thousands separator format converted:', result);
                return result;
            }
            // Se tem exatamente 2 dígitos após o último ponto, é decimal
            if (parts.length === 2 && parts[1].length <= 2) {
                console.log('American decimal format kept:', cleaned);
                return cleaned; // Mantém formato americano
            }
            // Múltiplos pontos = separadores de milhares
            const result = cleaned.replace(/\./g, '');
            console.log('Multiple dots format converted:', result);
            return result;
        }
        
        console.log('No formatting needed:', cleaned);
        return cleaned;
    }
    
    const valorNumerico = parseBrazilianCurrency(valorFormatado);
    console.log('Final numeric value:', valorNumerico);
    
    // Atualizar o FormData com valor numérico
    formData.set('valor', valorNumerico);
    
    const url = isEdit ? '<?= url('/api/transactions/update') ?>' : '<?= url('/api/transactions/create') ?>';
    console.log('Request URL:', url);
    
    console.log('Final form data being sent:');
    for (let [key, value] of formData.entries()) {
        console.log(`  ${key}: ${value}`);
    }
    
    console.log('Making fetch request...');
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            console.log('Transaction saved successfully!');
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
            console.error('Server returned error:', data.message);
            Swal.fire('Erro!', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        Swal.fire('Erro!', 'Erro ao salvar lançamento', 'error');
    });
    
    console.log('=== SAVE TRANSACTION END ===');
}

function deleteTransaction(id, descricao) {
    Swal.fire({
        title: 'Confirmar Exclusão',
        text: `Tem certeza que deseja excluir o lançamento "${descricao}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id', id);
            
            fetch('<?= url('/api/transactions/delete') ?>', {
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
                Swal.fire('Erro!', 'Erro ao excluir lançamento', 'error');
            });
        }
    });
}

// Resetar formulário quando modal é fechado
document.getElementById('transactionModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('transactionForm').reset();
    document.getElementById('transactionId').value = '';
    document.getElementById('transactionModalTitle').textContent = 'Novo Lançamento';
    document.getElementById('transactionSaveButtonText').textContent = 'Salvar';
    
    // Definir data de hoje novamente
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('transactionDataCompetencia').value = today;
    
    // Limpar todos os Select2
    $('#transactionCategory').val(null).trigger('change');
    $('#transactionAccount').val('').trigger('change');
    $('#transactionKind').val('entrada').trigger('change');
    $('#transactionStatus').val('confirmado').trigger('change');
    $('#transactionRecurrenceType').val('').trigger('change');
    
    // Esconder campos de recorrência ao fechar modal
    document.getElementById('recurrenceFields').style.display = 'none';
});

// Controlar exibição dos campos de recorrência baseado no status
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('transactionStatus');
    const recurrenceFields = document.getElementById('recurrenceFields');
    
    function toggleRecurrenceFields() {
        if (statusSelect.value === 'agendado') {
            recurrenceFields.style.display = 'block';
        } else {
            recurrenceFields.style.display = 'none';
            // Limpar campos de recorrência quando não for agendado
            document.getElementById('transactionRecurrenceType').value = '';
            document.getElementById('transactionRecurrenceCount').value = '1';
            document.getElementById('transactionRecurrenceEndDate').value = '';
        }
    }
    
    // Controlar ao mudar o status
    statusSelect.addEventListener('change', toggleRecurrenceFields);
    
    // Controlar ao abrir modal para edição
    window.toggleRecurrenceFields = toggleRecurrenceFields;
});

// Função para confirmar lançamento (permite editar data e valor)
function confirmTransaction(transactionId, description, originalValue) {
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