<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-file-invoice me-3"></i>
        Extrato Bancário
    </h1>
    <div class="quick-actions">
        <button class="btn btn-outline-success me-2" onclick="exportStatement('csv')">
            <i class="fas fa-file-csv me-2"></i>
            Exportar CSV
        </button>
        <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#filterModal">
            <i class="fas fa-filter me-2"></i>
            Filtros
        </button>
        <a href="<?= url('/accounts') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Voltar às Contas
        </a>
    </div>
</div>

<!-- Informações da Conta -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="account-icon me-3">
                                <i class="fas fa-university" style="color: <?= $account['cor'] ?>"></i>
                            </div>
                            <div>
                                <h5 class="mb-1"><?= htmlspecialchars($account['nome']) ?></h5>
                                <small class="text-muted"><?= ucfirst($account['tipo']) ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="border-end">
                                    <h6 class="text-muted mb-1">Saldo Atual</h6>
                                    <h5 class="mb-0 <?= $account['saldo'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                        R$ <?= number_format($account['saldo'], 2, ',', '.') ?>
                                    </h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border-end">
                                    <h6 class="text-muted mb-1">Saldo Inicial</h6>
                                    <h5 class="mb-0 <?= $initialBalance >= 0 ? 'text-success' : 'text-danger' ?>">
                                        R$ <?= number_format($initialBalance, 2, ',', '.') ?>
                                    </h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border-end">
                                    <h6 class="text-muted mb-1">Entradas</h6>
                                    <h5 class="mb-0 text-success">
                                        R$ <?= number_format($periodTotals['total_entradas'], 2, ',', '.') ?>
                                    </h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <h6 class="text-muted mb-1">Saídas</h6>
                                <h5 class="mb-0 text-danger">
                                    R$ <?= number_format($periodTotals['total_saidas'], 2, ',', '.') ?>
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Período e Filtros Ativos -->
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info d-flex align-items-center">
            <i class="fas fa-info-circle me-2"></i>
            <div class="flex-grow-1">
                <strong>Período:</strong> <?= date('d/m/Y', strtotime($startDate)) ?> a <?= date('d/m/Y', strtotime($endDate)) ?>
                <span class="ms-3"><strong>Transações:</strong> <?= $periodTotals['total_transactions'] ?></span>
                <?php if ($periodTotals['pending_transactions'] > 0): ?>
                    <span class="ms-3 text-warning">
                        <i class="fas fa-clock me-1"></i><?= $periodTotals['pending_transactions'] ?> pendentes
                    </span>
                <?php endif; ?>
            </div>
            <div>
                <strong>Saldo Líquido:</strong> 
                <span class="<?= $periodTotals['saldo_liquido'] >= 0 ? 'text-success' : 'text-danger' ?>">
                    R$ <?= number_format($periodTotals['saldo_liquido'], 2, ',', '.') ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Extrato -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 d-flex align-items-center">
                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                        <i class="fas fa-list text-white"></i>
                    </div>
                    Extrato de Movimentações
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($transactions)): ?>
                    <div class="empty-state">
                        <i class="fas fa-file-invoice"></i>
                        <h5>Nenhuma movimentação encontrada</h5>
                        <p>Não há transações registradas para esta conta no período selecionado.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 100px;">Data</th>
                                    <th>Descrição</th>
                                    <th style="width: 140px;">Categoria</th>
                                    <th style="width: 120px;">Tipo</th>
                                    <th style="width: 80px;">Status</th>
                                    <th style="width: 120px;" class="text-end">Valor</th>
                                    <th style="width: 120px;" class="text-end">Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $runningBalance = $initialBalance;
                                foreach ($transactions as $transaction): 
                                    $isCredit = in_array($transaction['kind'], ['entrada', 'transfer_in']);
                                    $valor = $transaction['valor'];
                                    
                                    // Só atualiza saldo se a transação estiver confirmada
                                    if ($transaction['status'] === 'confirmado') {
                                        $runningBalance += $isCredit ? $valor : -$valor;
                                    }
                                ?>
                                    <tr class="<?= $transaction['status'] === 'pendente' ? 'table-warning' : '' ?>">
                                        <td>
                                            <small><?= date('d/m/Y', strtotime($transaction['data_competencia'])) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($transaction['descricao']) ?></div>
                                            <?php if (!empty($transaction['observacoes'])): ?>
                                                <small class="text-muted"><?= htmlspecialchars($transaction['observacoes']) ?></small>
                                            <?php endif; ?>
                                            <?php if ($transaction['transfer_account_name']): ?>
                                                <small class="text-info d-block">
                                                    <i class="fas fa-exchange-alt me-1"></i>
                                                    <?= $isCredit ? 'De: ' : 'Para: ' ?><?= htmlspecialchars($transaction['transfer_account_name']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($transaction['category_name'])): ?>
                                                <span class="badge" style="background-color: <?= $transaction['category_color'] ?? '#6c757d' ?>">
                                                    <?= htmlspecialchars($transaction['category_name']) ?>
                                                </span>
                                            <?php else: ?>
                                                <small class="text-muted">—</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $typeInfo = [
                                                'entrada' => ['label' => 'Receita', 'color' => 'success', 'icon' => 'fa-plus'],
                                                'saida' => ['label' => 'Despesa', 'color' => 'danger', 'icon' => 'fa-minus'],
                                                'transfer_in' => ['label' => 'Recebida', 'color' => 'info', 'icon' => 'fa-arrow-down'],
                                                'transfer_out' => ['label' => 'Enviada', 'color' => 'warning', 'icon' => 'fa-arrow-up']
                                            ];
                                            $info = $typeInfo[$transaction['kind']] ?? ['label' => ucfirst($transaction['kind']), 'color' => 'secondary', 'icon' => 'fa-circle'];
                                            ?>
                                            <span class="badge bg-<?= $info['color'] ?>">
                                                <i class="fas <?= $info['icon'] ?> me-1"></i>
                                                <?= $info['label'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($transaction['status'] === 'confirmado'): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>Confirmado
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock me-1"></i>Pendente
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <span class="<?= $isCredit ? 'text-success' : 'text-danger' ?>">
                                                <i class="fas <?= $isCredit ? 'fa-plus' : 'fa-minus' ?> me-1"></i>
                                                R$ <?= number_format($valor, 2, ',', '.') ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="<?= $runningBalance >= 0 ? 'text-success' : 'text-danger' ?>">
                                                R$ <?= number_format($runningBalance, 2, ',', '.') ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginação -->
                    <?php if ($totalPages > 1): ?>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    Mostrando <?= count($transactions) ?> de <?= $totalRecords ?> registros
                                </small>
                                <nav>
                                    <ul class="pagination pagination-sm mb-0">
                                        <?php if ($currentPage > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?account_id=<?= $account['id'] ?>&page=<?= $currentPage - 1 ?>&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>">
                                                    <i class="fas fa-chevron-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                                            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                                <a class="page-link" href="?account_id=<?= $account['id'] ?>&page=<?= $i ?>&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($currentPage < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?account_id=<?= $account['id'] ?>&page=<?= $currentPage + 1 ?>&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>">
                                                    <i class="fas fa-chevron-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Filtros -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filtros do Extrato</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="filterForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="startDate" class="form-label">Data Inicial</label>
                                <input type="date" class="form-control" id="startDate" name="start_date" value="<?= $startDate ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="endDate" class="form-label">Data Final</label>
                                <input type="date" class="form-control" id="endDate" name="end_date" value="<?= $endDate ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Períodos Rápidos</label>
                                <div class="btn-group w-100" role="group">
                                    <button type="button" class="btn btn-outline-secondary" onclick="setQuickPeriod('thisMonth')">Este Mês</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="setQuickPeriod('lastMonth')">Mês Passado</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="setQuickPeriod('last3Months')">3 Meses</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="setQuickPeriod('thisYear')">Este Ano</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="applyFilters()">
                    <i class="fas fa-filter me-2"></i>
                    Aplicar Filtros
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function applyFilters() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    if (!startDate || !endDate) {
        Swal.fire('Atenção!', 'Por favor, selecione as datas inicial e final', 'warning');
        return;
    }
    
    if (startDate > endDate) {
        Swal.fire('Atenção!', 'A data inicial não pode ser maior que a data final', 'warning');
        return;
    }
    
    const url = new URL(window.location.href);
    url.searchParams.set('start_date', startDate);
    url.searchParams.set('end_date', endDate);
    url.searchParams.set('page', '1'); // Reset para primeira página
    
    window.location.href = url.toString();
}

function setQuickPeriod(period) {
    const today = new Date();
    let startDate, endDate;
    
    switch (period) {
        case 'thisMonth':
            startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            break;
        case 'lastMonth':
            startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            endDate = new Date(today.getFullYear(), today.getMonth(), 0);
            break;
        case 'last3Months':
            startDate = new Date(today.getFullYear(), today.getMonth() - 2, 1);
            endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            break;
        case 'thisYear':
            startDate = new Date(today.getFullYear(), 0, 1);
            endDate = new Date(today.getFullYear(), 11, 31);
            break;
    }
    
    document.getElementById('startDate').value = startDate.toISOString().split('T')[0];
    document.getElementById('endDate').value = endDate.toISOString().split('T')[0];
}

function exportStatement(format) {
    const url = new URL('<?= url('/statements/export') ?>');
    url.searchParams.set('account_id', '<?= $account['id'] ?>');
    url.searchParams.set('start_date', '<?= $startDate ?>');
    url.searchParams.set('end_date', '<?= $endDate ?>');
    url.searchParams.set('format', format);
    
    // Download direto
    window.open(url.toString(), '_blank');
}
</script>

<style>
.account-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 12px;
    font-size: 1.2rem;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6c757d;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1.5rem;
    opacity: 0.5;
}

.empty-state h5 {
    margin-bottom: 1rem;
    color: #495057;
}

.table tbody tr.table-warning {
    --bs-table-accent-bg: rgba(255, 193, 7, 0.1);
}

.border-end {
    border-right: 1px solid #dee2e6 !important;
}
</style>