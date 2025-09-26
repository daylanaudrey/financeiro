<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="page-title">
            <i class="fas fa-hand-holding-usd me-3"></i>
            Baixas Parciais
        </h1>
        <div class="btn-group">
            <button class="btn btn-outline-secondary" onclick="loadPartialPayments()">
                <i class="fas fa-sync me-2"></i>
                Atualizar
            </button>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label for="filterTransactionId" class="form-label">ID da Transação</label>
                <input type="number" class="form-control" id="filterTransactionId" placeholder="Ex: 123">
            </div>
            <div class="col-md-3">
                <label for="filterDateFrom" class="form-label">Data Inicial</label>
                <input type="date" class="form-control" id="filterDateFrom">
            </div>
            <div class="col-md-3">
                <label for="filterDateTo" class="form-label">Data Final</label>
                <input type="date" class="form-control" id="filterDateTo">
            </div>
            <div class="col-md-3">
                <label for="filterUser" class="form-label">Usuário</label>
                <select class="form-select" id="filterUser">
                    <option value="">Todos</option>
                </select>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <button class="btn btn-primary" onclick="applyFilters()">
                    <i class="fas fa-filter me-2"></i>
                    Filtrar
                </button>
                <button class="btn btn-outline-secondary ms-2" onclick="clearFilters()">
                    <i class="fas fa-times me-2"></i>
                    Limpar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Resumo -->
<div class="row mb-4" id="summaryCards">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="card-title h4" id="totalPayments">0</div>
                        <div class="small">Total de Baixas</div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-hand-holding-usd fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="card-title h4" id="totalValue">R$ 0,00</div>
                        <div class="small">Valor Total</div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-coins fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="card-title h4" id="partialTransactions">0</div>
                        <div class="small">Trans. Parciais</div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-chart-pie fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="card-title h4" id="paidTransactions">0</div>
                        <div class="small">Trans. Quitadas</div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabela de Baixas Parciais -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            Histórico de Baixas Parciais
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="partialPaymentsTable">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Transação</th>
                        <th>Descrição Original</th>
                        <th>Valor da Baixa</th>
                        <th>Data</th>
                        <th>Usuário</th>
                        <th>Conta</th>
                        <th>Status Trans.</th>
                        <th>Observações</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="partialPaymentsBody">
                    <tr>
                        <td colspan="10" class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para detalhes da baixa parcial -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle me-2"></i>
                    Detalhes da Baixa Parcial
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalDetailsBody">
                <!-- Conteúdo será carregado dinamicamente -->
            </div>
        </div>
    </div>
</div>

<script>
// Variáveis globais
let partialPayments = [];
let filteredPayments = [];

// Carregar baixas parciais
async function loadPartialPayments() {
    try {
        const response = await fetch('<?= url('/api/partial-payments/list') ?>');
        const data = await response.json();

        if (data.success) {
            partialPayments = data.payments || [];
            filteredPayments = [...partialPayments];

            updateSummaryCards(data.summary || {});
            renderPartialPaymentsTable();
            loadUsers(); // Carregar usuários para o filtro
        } else {
            showError('Erro ao carregar baixas parciais: ' + (data.message || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('Erro:', error);
        showError('Erro de conexão ao carregar baixas parciais');
    }
}

// Atualizar cards de resumo
function updateSummaryCards(summary) {
    document.getElementById('totalPayments').textContent = summary.total_payments || 0;
    document.getElementById('totalValue').textContent = formatCurrency(summary.total_value || 0);
    document.getElementById('partialTransactions').textContent = summary.partial_transactions || 0;
    document.getElementById('paidTransactions').textContent = summary.paid_transactions || 0;
}

// Renderizar tabela
function renderPartialPaymentsTable() {
    const tbody = document.getElementById('partialPaymentsBody');

    if (filteredPayments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">Nenhuma baixa parcial encontrada</td></tr>';
        return;
    }

    tbody.innerHTML = filteredPayments.map(payment => `
        <tr>
            <td><span class="badge bg-primary">${payment.payment_id}</span></td>
            <td>
                <a href="javascript:void(0)" onclick="showTransactionDetails(${payment.transaction_id})" class="text-decoration-none">
                    #${payment.transaction_id}
                </a>
            </td>
            <td>
                <div class="fw-medium">${payment.transaction_description || 'N/A'}</div>
                <small class="text-muted">R$ ${formatNumber(payment.transaction_value)}</small>
            </td>
            <td>
                <span class="badge bg-success fs-6">R$ ${formatNumber(payment.valor)}</span>
            </td>
            <td>${formatDate(payment.data_pagamento)}</td>
            <td>
                <i class="fas fa-user me-1"></i>
                ${payment.created_by_name || 'N/A'}
            </td>
            <td>${payment.account_name || 'N/A'}</td>
            <td>${getStatusBadge(payment.transaction_status)}</td>
            <td>
                <small class="text-muted">${payment.descricao || ''}</small>
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="showPaymentDetails(${payment.payment_id})" title="Detalhes">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-outline-danger" onclick="cancelPayment(${payment.payment_id})" title="Cancelar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Aplicar filtros
function applyFilters() {
    const transactionId = document.getElementById('filterTransactionId').value;
    const dateFrom = document.getElementById('filterDateFrom').value;
    const dateTo = document.getElementById('filterDateTo').value;
    const userId = document.getElementById('filterUser').value;

    filteredPayments = partialPayments.filter(payment => {
        if (transactionId && payment.transaction_id != transactionId) return false;
        if (dateFrom && payment.data_pagamento < dateFrom) return false;
        if (dateTo && payment.data_pagamento > dateTo) return false;
        if (userId && payment.created_by != userId) return false;
        return true;
    });

    renderPartialPaymentsTable();
}

// Limpar filtros
function clearFilters() {
    document.getElementById('filterTransactionId').value = '';
    document.getElementById('filterDateFrom').value = '';
    document.getElementById('filterDateTo').value = '';
    document.getElementById('filterUser').value = '';

    filteredPayments = [...partialPayments];
    renderPartialPaymentsTable();
}

// Carregar usuários para filtro
async function loadUsers() {
    try {
        const response = await fetch('<?= url('/api/users/list') ?>');
        const data = await response.json();

        if (data.success && data.users) {
            const select = document.getElementById('filterUser');
            select.innerHTML = '<option value="">Todos</option>' +
                data.users.map(user => `<option value="${user.id}">${user.nome}</option>`).join('');
        }
    } catch (error) {
        console.error('Erro ao carregar usuários:', error);
    }
}

// Mostrar detalhes do pagamento
async function showPaymentDetails(paymentId) {
    try {
        const response = await fetch(`<?= url('/api/partial-payments/details') ?>/${paymentId}`);
        const data = await response.json();

        if (data.success) {
            // Preencher modal com detalhes
            document.getElementById('modalDetailsBody').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informações da Baixa</h6>
                        <table class="table table-sm">
                            <tr><td><strong>ID:</strong></td><td>${data.payment.id}</td></tr>
                            <tr><td><strong>Valor:</strong></td><td>R$ ${formatNumber(data.payment.valor)}</td></tr>
                            <tr><td><strong>Data:</strong></td><td>${formatDate(data.payment.data_pagamento)}</td></tr>
                            <tr><td><strong>Usuário:</strong></td><td>${data.payment.created_by_name}</td></tr>
                            <tr><td><strong>Conta:</strong></td><td>${data.payment.account_name}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Transação Original</h6>
                        <table class="table table-sm">
                            <tr><td><strong>ID:</strong></td><td>#${data.transaction.id}</td></tr>
                            <tr><td><strong>Descrição:</strong></td><td>${data.transaction.descricao}</td></tr>
                            <tr><td><strong>Valor Total:</strong></td><td>R$ ${formatNumber(data.transaction.valor_original)}</td></tr>
                            <tr><td><strong>Valor Pago:</strong></td><td>R$ ${formatNumber(data.transaction.valor_pago)}</td></tr>
                            <tr><td><strong>Pendente:</strong></td><td>R$ ${formatNumber(data.transaction.valor_pendente)}</td></tr>
                        </table>
                    </div>
                </div>
            `;

            new bootstrap.Modal(document.getElementById('detailsModal')).show();
        } else {
            showError('Erro ao carregar detalhes: ' + data.message);
        }
    } catch (error) {
        showError('Erro de conexão ao carregar detalhes');
    }
}

// Cancelar pagamento
async function cancelPayment(paymentId) {
    if (!confirm('Tem certeza que deseja cancelar esta baixa parcial? Esta ação não pode ser desfeita.')) {
        return;
    }

    try {
        const response = await fetch(`<?= url('/api/partial-payments/cancel') ?>/${paymentId}`, {
            method: 'POST'
        });
        const data = await response.json();

        if (data.success) {
            showSuccess('Baixa parcial cancelada com sucesso!');
            loadPartialPayments(); // Recarregar dados
        } else {
            showError('Erro ao cancelar: ' + data.message);
        }
    } catch (error) {
        showError('Erro de conexão ao cancelar baixa parcial');
    }
}

// Funções auxiliares
function getStatusBadge(status) {
    const badges = {
        'pendente': '<span class="badge bg-warning">Pendente</span>',
        'parcial': '<span class="badge bg-info">Parcial</span>',
        'quitado': '<span class="badge bg-success">Quitado</span>',
        'cancelado': '<span class="badge bg-danger">Cancelado</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">N/A</span>';
}

function formatNumber(value) {
    return parseFloat(value || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function formatCurrency(value) {
    return 'R$ ' + formatNumber(value);
}

function formatDate(dateString) {
    return new Date(dateString + 'T00:00:00').toLocaleDateString('pt-BR');
}

function showError(message) {
    Swal.fire('Erro!', message, 'error');
}

function showSuccess(message) {
    Swal.fire('Sucesso!', message, 'success');
}

// Inicializar página
document.addEventListener('DOMContentLoaded', function() {
    loadPartialPayments();

    // Definir datas padrão (últimos 30 dias)
    const today = new Date();
    const thirtyDaysAgo = new Date(today);
    thirtyDaysAgo.setDate(today.getDate() - 30);

    document.getElementById('filterDateFrom').value = thirtyDaysAgo.toISOString().split('T')[0];
    document.getElementById('filterDateTo').value = today.toISOString().split('T')[0];
});
</script>