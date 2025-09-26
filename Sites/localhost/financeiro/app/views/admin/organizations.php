<?php 
$title = 'Organizações - Painel Administrativo';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="fas fa-building me-2"></i>Gerenciar Organizações</h2>
        <p class="text-muted mb-0">Controle todas as empresas cadastradas na plataforma</p>
    </div>
    <div class="btn-group">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addOrgModal">
            <i class="fas fa-plus me-2"></i>Nova Organização
        </button>
        <button class="btn btn-info" onclick="refreshData()">
            <i class="fas fa-sync me-2"></i>Atualizar
        </button>
    </div>
</div>

<!-- Cards de Estatísticas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stats-card bg-primary text-white">
            <div class="card-body">
                <h5>Total de Organizações</h5>
                <h2><?= count($organizations) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card bg-success text-white">
            <div class="card-body">
                <h5>Assinaturas Ativas</h5>
                <h2><?= count(array_filter($organizations, fn($org) => $org['subscription_status'] === 'active')) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card bg-warning text-white">
            <div class="card-body">
                <h5>Em Trial</h5>
                <h2><?= count(array_filter($organizations, fn($org) => $org['subscription_status'] === 'trial')) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card bg-danger text-white">
            <div class="card-body">
                <h5>Suspensas</h5>
                <h2><?= count(array_filter($organizations, fn($org) => in_array($org['subscription_status'], ['suspended', 'expired']))) ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Filtrar por Status</label>
                <select class="form-select" id="filterStatus" onchange="filterTable()">
                    <option value="">Todos os Status</option>
                    <option value="trial">Trial</option>
                    <option value="active">Ativo</option>
                    <option value="suspended">Suspenso</option>
                    <option value="expired">Expirado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Filtrar por Plano</label>
                <select class="form-select" id="filterPlan" onchange="filterTable()">
                    <option value="">Todos os Planos</option>
                    <option value="Trial Gratuito">Trial Gratuito</option>
                    <option value="Starter">Starter</option>
                    <option value="Professional">Professional</option>
                    <option value="Enterprise">Enterprise</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Buscar Organização</label>
                <input type="text" class="form-control" id="searchOrg" placeholder="Nome ou CNPJ..." onkeyup="filterTable()">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                    <i class="fas fa-times me-2"></i>Limpar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabela de Organizações -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>Lista de Organizações
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($organizations)): ?>
            <div class="text-center py-5">
                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                <h5>Nenhuma organização encontrada</h5>
                <p class="text-muted">Adicione a primeira organização para começar.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="organizationsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Organização</th>
                            <th>Plano</th>
                            <th>Status</th>
                            <th>Usuários</th>
                            <th>Criada em</th>
                            <th>Validade</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($organizations as $org): ?>
                            <tr data-status="<?= $org['subscription_status'] ?? '' ?>" data-plan="<?= htmlspecialchars($org['plan_name'] ?? '') ?>">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                            <i class="fas fa-building text-white"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($org['nome']) ?></h6>
                                            <?php if ($org['cnpj']): ?>
                                                <small class="text-muted"><?= htmlspecialchars($org['cnpj']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($org['plan_name']): ?>
                                        <span class="badge bg-info"><?= htmlspecialchars($org['plan_name']) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Sem plano</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = match($org['subscription_status']) {
                                        'active' => 'success',
                                        'trial' => 'warning',
                                        'suspended' => 'danger',
                                        'expired' => 'secondary',
                                        default => 'secondary'
                                    };
                                    $statusText = match($org['subscription_status']) {
                                        'active' => 'Ativo',
                                        'trial' => 'Trial',
                                        'suspended' => 'Suspenso',
                                        'expired' => 'Expirado',
                                        default => 'Inativo'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>"><?= $statusText ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= $org['user_count'] ?? 0 ?></span>
                                </td>
                                <td>
                                    <small><?= date('d/m/Y', strtotime($org['created_at'])) ?></small>
                                </td>
                                <td>
                                    <?php if ($org['subscription_status'] === 'trial' && $org['trial_ends_at']): ?>
                                        <small class="text-warning">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= date('d/m/Y', strtotime($org['trial_ends_at'])) ?>
                                        </small>
                                    <?php elseif ($org['subscription_status'] === 'active' && $org['current_period_end']): ?>
                                        <small class="text-success">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('d/m/Y', strtotime($org['current_period_end'])) ?>
                                        </small>
                                    <?php else: ?>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="viewOrganization(<?= $org['id'] ?>)" title="Ver Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning" onclick="editSubscription(<?= $org['id'] ?>)" title="Editar Assinatura">
                                            <i class="fas fa-credit-card"></i>
                                        </button>
                                        <button class="btn btn-outline-info" onclick="loginAsOrg(<?= $org['id'] ?>)" title="Fazer Login como Organização">
                                            <i class="fas fa-sign-in-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para Visualizar Organização -->
<div class="modal fade" id="viewOrgModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes da Organização</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orgDetails">
                <div class="text-center">
                    <div class="spinner-border" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Editar Assinatura -->
<div class="modal fade" id="editSubscriptionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Assinatura</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="subscriptionForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_org_id" name="org_id">
                    
                    <div class="mb-3">
                        <label for="edit_plan_id" class="form-label">Plano</label>
                        <select class="form-select" id="edit_plan_id" name="plan_id" required>
                            <option value="">Selecione um plano</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="trial">Trial</option>
                            <option value="active">Ativo</option>
                            <option value="suspended">Suspenso</option>
                            <option value="cancelled">Cancelado</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function filterTable() {
    const statusFilter = document.getElementById('filterStatus').value.toLowerCase();
    const planFilter = document.getElementById('filterPlan').value.toLowerCase();
    const searchText = document.getElementById('searchOrg').value.toLowerCase();
    const table = document.getElementById('organizationsTable');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const status = row.getAttribute('data-status').toLowerCase();
        const plan = row.getAttribute('data-plan').toLowerCase();
        const orgName = row.cells[0].textContent.toLowerCase();
        
        const statusMatch = !statusFilter || status.includes(statusFilter);
        const planMatch = !planFilter || plan.includes(planFilter);
        const searchMatch = !searchText || orgName.includes(searchText);
        
        if (statusMatch && planMatch && searchMatch) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

function clearFilters() {
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterPlan').value = '';
    document.getElementById('searchOrg').value = '';
    filterTable();
}

function refreshData() {
    location.reload();
}

function viewOrganization(orgId) {
    $('#viewOrgModal').modal('show');
    $('#orgDetails').html('<div class="text-center"><div class="spinner-border" role="status"></div></div>');
    
    // Aqui você implementaria a busca dos detalhes via AJAX
    setTimeout(() => {
        $('#orgDetails').html(`
            <div class="row">
                <div class="col-md-6">
                    <h6>Informações Básicas</h6>
                    <p><strong>ID:</strong> ${orgId}</p>
                    <p><strong>Status:</strong> Em desenvolvimento...</p>
                </div>
                <div class="col-md-6">
                    <h6>Uso Atual</h6>
                    <p>Funcionalidade em desenvolvimento...</p>
                </div>
            </div>
        `);
    }, 1000);
}

function editSubscription(orgId) {
    $('#edit_org_id').val(orgId);
    $('#editSubscriptionModal').modal('show');
    loadAvailablePlans();
}

function loadAvailablePlans() {
    const plans = [
        {id: 1, name: 'Trial Gratuito'},
        {id: 2, name: 'Starter'},
        {id: 3, name: 'Professional'},
        {id: 4, name: 'Enterprise'}
    ];
    
    const select = $('#edit_plan_id');
    select.empty().append('<option value="">Selecione um plano</option>');
    
    plans.forEach(plan => {
        select.append(`<option value="${plan.id}">${plan.name}</option>`);
    });
}

function loginAsOrg(orgId) {
    Swal.fire({
        title: 'Login como Organização',
        text: 'Esta funcionalidade será implementada em breve.',
        icon: 'info'
    });
}

$('#subscriptionForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/admin/update-subscription', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Sucesso!', data.message, 'success').then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Erro!', data.message, 'error');
        }
    })
    .catch(error => {
        Swal.fire('Erro!', 'Erro interno do servidor', 'error');
    });
});
</script>

<style>
.avatar-sm {
    width: 40px;
    height: 40px;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15) !important;
}
</style>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>