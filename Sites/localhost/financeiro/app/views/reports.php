<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-chart-bar me-3"></i>
        Relatórios Financeiros
    </h1>
    <div class="quick-actions">
        <button class="btn btn-outline-primary" onclick="exportReport()">
            <i class="fas fa-download me-2"></i>
            Exportar
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
            <i class="fas fa-filter me-2"></i>
            Filtros
        </button>
    </div>
</div>

<!-- Cards de Resumo -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card bg-gradient-success">
            <div class="stats-icon">
                <i class="fas fa-arrow-up"></i>
            </div>
            <div class="stats-info">
                <h3 class="text-white">R$ <?= number_format($monthlyBalance['receitas'] ?? 0, 2, ',', '.') ?></h3>
                <span class="stats-label text-light">Receitas do Mês</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card bg-gradient-danger">
            <div class="stats-icon">
                <i class="fas fa-arrow-down"></i>
            </div>
            <div class="stats-info">
                <h3 class="text-white">R$ <?= number_format($monthlyBalance['despesas'] ?? 0, 2, ',', '.') ?></h3>
                <span class="stats-label text-light">Despesas do Mês</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card bg-gradient-primary">
            <div class="stats-icon">
                <i class="fas fa-balance-scale"></i>
            </div>
            <div class="stats-info">
                <?php 
                $balance = ($monthlyBalance['receitas'] ?? 0) - ($monthlyBalance['despesas'] ?? 0);
                $balanceColor = $balance >= 0 ? 'text-white' : 'text-warning';
                ?>
                <h3 class="<?= $balanceColor ?>">R$ <?= number_format($balance, 2, ',', '.') ?></h3>
                <span class="stats-label text-light">Saldo do Mês</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card bg-gradient-info">
            <div class="stats-icon">
                <i class="fas fa-list"></i>
            </div>
            <div class="stats-info">
                <h3 class="text-white"><?= $monthlyBalance['total_transactions'] ?? 0 ?></h3>
                <span class="stats-label text-light">Transações</span>
            </div>
        </div>
    </div>
</div>

<!-- Card de Gráfico de Categorias com Seletor de Período -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="fas fa-chart-pie me-2"></i>
                            Análise por Categorias
                        </h5>
                        <small class="text-muted">Incluindo todos os tipos de lançamentos (confirmados e agendados)</small>
                    </div>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="categoryPeriodSelect" onchange="updateCategoryPeriod()">
                            <option value="current_month">Mês Atual</option>
                            <option value="last_month">Mês Anterior</option>
                            <option value="current_quarter">Trimestre Atual</option>
                            <option value="current_year">Ano Atual</option>
                            <option value="custom">Período Personalizado</option>
                        </select>
                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#categoryFilterModal">
                            <i class="fas fa-cog"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6">
                        <canvas id="categoryPieChart" height="300"></canvas>
                    </div>
                    <div class="col-lg-6">
                        <div class="category-summary">
                            <h6 class="mb-3">Resumo por Categoria</h6>
                            <div id="categorySummaryList">
                                <!-- Lista será preenchida dinamicamente -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos e Relatórios -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Evolução Mensal
                </h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-pie-chart me-2"></i>
                    Por Categoria (Resumo)
                </h5>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tabela de Transações Recentes -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>
                    Transações Recentes
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($recentTransactions)): ?>
                    <div class="empty-state">
                        <i class="fas fa-receipt"></i>
                        <h5>Nenhuma transação encontrada</h5>
                        <p>Não há transações registradas no período selecionado.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Descrição</th>
                                    <th>Categoria</th>
                                    <th>Conta</th>
                                    <th class="text-end">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentTransactions as $transaction): ?>
                                    <tr>
                                        <td>
                                            <small><?= date('d/m/Y', strtotime($transaction['data_competencia'])) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($transaction['descricao']) ?></div>
                                            <?php if (!empty($transaction['observacoes'])): ?>
                                                <small class="text-muted"><?= htmlspecialchars($transaction['observacoes']) ?></small>
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
                                            <small class="text-muted"><?= htmlspecialchars($transaction['account_name'] ?? 'N/A') ?></small>
                                        </td>
                                        <td class="text-end">
                                            <?php 
                                            $isIncome = in_array($transaction['kind'], ['entrada', 'transfer_in']);
                                            $textColor = $isIncome ? 'text-success' : 'text-danger';
                                            $icon = $isIncome ? 'fa-plus' : 'fa-minus';
                                            ?>
                                            <span class="<?= $textColor ?>">
                                                <i class="fas <?= $icon ?> me-1"></i>
                                                R$ <?= number_format($transaction['valor'], 2, ',', '.') ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
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
                <h5 class="modal-title">Filtros de Relatório</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="filterForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="startDate" class="form-label">Data Inicial</label>
                                <input type="date" class="form-control" id="startDate" value="<?= date('Y-m-01') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="endDate" class="form-label">Data Final</label>
                                <input type="date" class="form-control" id="endDate" value="<?= date('Y-m-t') ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="accountFilter" class="form-label">Conta</label>
                        <select class="form-select" id="accountFilter">
                            <option value="">Todas as contas</option>
                            <?php foreach ($accounts as $account): ?>
                                <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="categoryFilter" class="form-label">Categoria</label>
                        <select class="form-select" id="categoryFilter">
                            <option value="">Todas as categorias</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="typeFilter" class="form-label">Tipo</label>
                        <select class="form-select" id="typeFilter">
                            <option value="">Todos os tipos</option>
                            <option value="entrada">Receitas</option>
                            <option value="saida">Despesas</option>
                            <option value="transfer">Transferências</option>
                        </select>
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

<!-- Modal de Filtros de Categorias -->
<div class="modal fade" id="categoryFilterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filtros de Análise por Categorias</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="categoryFilterForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="categoryStartDate" class="form-label">Data Inicial</label>
                                <input type="date" class="form-control" id="categoryStartDate" value="<?= date('Y-m-01') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="categoryEndDate" class="form-label">Data Final</label>
                                <input type="date" class="form-control" id="categoryEndDate" value="<?= date('Y-m-t') ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tipos de Lançamentos</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="includeConfirmed" checked>
                            <label class="form-check-label" for="includeConfirmed">
                                Confirmados
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="includeScheduled" checked>
                            <label class="form-check-label" for="includeScheduled">
                                Agendados
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="includeDrafts">
                            <label class="form-check-label" for="includeDrafts">
                                Rascunhos
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tipos de Movimento</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="includeReceitas" checked>
                            <label class="form-check-label" for="includeReceitas">
                                Receitas
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="includeDespesas" checked>
                            <label class="form-check-label" for="includeDespesas">
                                Despesas
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="applyCategoryFilters()">
                    <i class="fas fa-filter me-2"></i>
                    Aplicar Filtros
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let monthlyChart, categoryChart, categoryPieChart;

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    loadStaticData();
    updateCategoryPeriod(); // Carregar gráfico de categorias inicial
});

function initializeCharts() {
    // Gráfico Mensal
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    monthlyChart = new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Receitas',
                data: [],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1
            }, {
                label: 'Despesas',
                data: [],
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    }
                }
            }
        }
    });
    
    // Gráfico por Categoria (pequeno)
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: []
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
    
    // Gráfico de Categorias Principal
    const categoryPieCtx = document.getElementById('categoryPieChart').getContext('2d');
    categoryPieChart = new Chart(categoryPieCtx, {
        type: 'pie',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.raw / total) * 100).toFixed(1);
                            return context.label + ': R$ ' + context.raw.toLocaleString('pt-BR', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

function loadStaticData() {
    // Dados da evolução mensal (PHP)
    const monthlyEvolution = <?= json_encode($monthlyEvolution) ?>;
    updateMonthlyChart(monthlyEvolution || []);
    
    // Dados de categoria (PHP)
    const categoryExpenses = <?= json_encode($categoryExpenses) ?>;
    updateCategoryChart(categoryExpenses || []);
}

function updateMonthlyChart(data) {
    const labels = data.map(item => {
        const monthNames = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 
                           'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        return `${monthNames[item.month - 1]}/${item.year}`;
    });
    const receitas = data.map(item => parseFloat(item.receitas));
    const despesas = data.map(item => parseFloat(item.despesas));
    
    monthlyChart.data.labels = labels;
    monthlyChart.data.datasets[0].data = receitas;
    monthlyChart.data.datasets[1].data = despesas;
    monthlyChart.update();
}

function updateCategoryChart(data) {
    const labels = data.map(item => item.nome || 'Sem categoria');
    const values = data.map(item => parseFloat(item.total));
    const colors = data.map(item => item.cor || '#6c757d');
    
    categoryChart.data.labels = labels;
    categoryChart.data.datasets[0].data = values;
    categoryChart.data.datasets[0].backgroundColor = colors;
    categoryChart.update();
}

function applyFilters() {
    loadReportsData();
    bootstrap.Modal.getInstance(document.getElementById('filterModal')).hide();
    Swal.fire('Filtros aplicados!', 'Os relatórios foram atualizados', 'success');
}

function exportReport() {
    Swal.fire('Em desenvolvimento', 'Funcionalidade de exportação será implementada em breve', 'info');
}

// Funções para o gráfico de categorias
function updateCategoryPeriod() {
    const periodSelect = document.getElementById('categoryPeriodSelect');
    const period = periodSelect.value;
    
    let startDate, endDate;
    const today = new Date();
    
    switch(period) {
        case 'current_month':
            startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            break;
        case 'last_month':
            startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            endDate = new Date(today.getFullYear(), today.getMonth(), 0);
            break;
        case 'current_quarter':
            const quarter = Math.floor(today.getMonth() / 3);
            startDate = new Date(today.getFullYear(), quarter * 3, 1);
            endDate = new Date(today.getFullYear(), quarter * 3 + 3, 0);
            break;
        case 'current_year':
            startDate = new Date(today.getFullYear(), 0, 1);
            endDate = new Date(today.getFullYear(), 11, 31);
            break;
        case 'custom':
            // Abrir modal para período personalizado
            new bootstrap.Modal(document.getElementById('categoryFilterModal')).show();
            return;
        default:
            startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    }
    
    loadCategoryData(formatDate(startDate), formatDate(endDate));
}

function formatDate(date) {
    return date.toISOString().split('T')[0];
}

function loadCategoryData(startDate, endDate) {
    const includeConfirmed = document.getElementById('includeConfirmed')?.checked !== false;
    const includeScheduled = document.getElementById('includeScheduled')?.checked !== false;
    const includeDrafts = document.getElementById('includeDrafts')?.checked || false;
    const includeReceitas = document.getElementById('includeReceitas')?.checked !== false;
    const includeDespesas = document.getElementById('includeDespesas')?.checked !== false;
    
    // Construir parâmetros da query
    const params = new URLSearchParams({
        start_date: startDate,
        end_date: endDate,
        include_confirmed: includeConfirmed ? '1' : '0',
        include_scheduled: includeScheduled ? '1' : '0',
        include_drafts: includeDrafts ? '1' : '0',
        include_receitas: includeReceitas ? '1' : '0',
        include_despesas: includeDespesas ? '1' : '0'
    });
    
    fetch(`<?= url('/api/reports/categories') ?>?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCategoryPieChart(data.categories);
                updateCategorySummary(data.categories);
            } else {
                console.error('Erro ao carregar dados de categorias:', data.message);
                Swal.fire('Erro', 'Não foi possível carregar os dados de categorias', 'error');
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            Swal.fire('Erro', 'Erro na conexão com o servidor', 'error');
        });
}

function updateCategoryPieChart(categories) {
    if (!categories || categories.length === 0) {
        categoryPieChart.data.labels = ['Nenhuma categoria encontrada'];
        categoryPieChart.data.datasets[0].data = [1];
        categoryPieChart.data.datasets[0].backgroundColor = ['#e9ecef'];
        categoryPieChart.update();
        return;
    }
    
    const labels = categories.map(cat => cat.nome || 'Sem categoria');
    const values = categories.map(cat => parseFloat(cat.total));
    const colors = categories.map(cat => cat.cor || '#6c757d');
    
    categoryPieChart.data.labels = labels;
    categoryPieChart.data.datasets[0].data = values;
    categoryPieChart.data.datasets[0].backgroundColor = colors;
    categoryPieChart.update();
}

function updateCategorySummary(categories) {
    const summaryList = document.getElementById('categorySummaryList');
    
    if (!categories || categories.length === 0) {
        summaryList.innerHTML = '<p class="text-muted">Nenhuma categoria encontrada no período selecionado.</p>';
        return;
    }
    
    const total = categories.reduce((sum, cat) => sum + parseFloat(cat.total), 0);
    
    let html = '';
    categories.forEach(category => {
        const percentage = total > 0 ? ((parseFloat(category.total) / total) * 100).toFixed(1) : 0;
        const valor = parseFloat(category.total);
        
        html += `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <div class="me-2" style="width: 16px; height: 16px; background-color: ${category.cor || '#6c757d'}; border-radius: 3px;"></div>
                    <div>
                        <div class="fw-semibold">${category.nome || 'Sem categoria'}</div>
                        <small class="text-muted">${category.transaction_count} transação(ões)</small>
                    </div>
                </div>
                <div class="text-end">
                    <div class="fw-bold">R$ ${valor.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                    <small class="text-muted">${percentage}%</small>
                </div>
            </div>
        `;
    });
    
    summaryList.innerHTML = html;
}

function applyCategoryFilters() {
    const startDate = document.getElementById('categoryStartDate').value;
    const endDate = document.getElementById('categoryEndDate').value;
    
    if (!startDate || !endDate) {
        Swal.fire('Atenção', 'Por favor, selecione as datas inicial e final', 'warning');
        return;
    }
    
    if (startDate > endDate) {
        Swal.fire('Atenção', 'A data inicial não pode ser maior que a data final', 'warning');
        return;
    }
    
    // Fechar modal e aplicar filtros
    bootstrap.Modal.getInstance(document.getElementById('categoryFilterModal')).hide();
    loadCategoryData(startDate, endDate);
    
    // Atualizar o select para "custom"
    document.getElementById('categoryPeriodSelect').value = 'custom';
    
    Swal.fire({
        icon: 'success',
        title: 'Filtros aplicados!',
        text: 'A análise por categorias foi atualizada',
        timer: 1500,
        showConfirmButton: false
    });
}
</script>

<style>
.stats-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    height: 100%;
    position: relative;
    overflow: hidden;
}

.stats-card.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.stats-card.bg-gradient-danger {
    background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
}

.stats-card.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);
}

.stats-card.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
}

.stats-icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stats-info h3 {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 700;
}

.stats-label {
    font-size: 0.9rem;
    font-weight: 500;
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: #6c757d;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state h5 {
    margin-bottom: 0.5rem;
    color: #495057;
}

.category-summary {
    max-height: 400px;
    overflow-y: auto;
}

.category-summary::-webkit-scrollbar {
    width: 6px;
}

.category-summary::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.category-summary::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.category-summary::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>