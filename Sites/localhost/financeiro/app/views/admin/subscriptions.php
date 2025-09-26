<?php 
$title = 'Assinaturas - Painel Administrativo';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="fas fa-credit-card me-2"></i>Gerenciar Assinaturas</h2>
        <p class="text-muted mb-0">Controle planos, pagamentos e status de assinaturas</p>
    </div>
    <div class="btn-group">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPlanModal">
            <i class="fas fa-plus me-2"></i>Novo Plano
        </button>
        <button class="btn btn-info" onclick="refreshData()">
            <i class="fas fa-sync me-2"></i>Atualizar
        </button>
    </div>
</div>

<!-- Cards de Estatísticas de Receita -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stats-card bg-primary text-white">
            <div class="card-body">
                <h6>Receita Mensal</h6>
                <h3>R$ <?= number_format(array_sum(array_map(fn($sub) => $sub['preco'] ?? 0, array_filter($subscriptions, fn($sub) => $sub['subscription_status'] === 'active'))), 2, ',', '.') ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card bg-success text-white">
            <div class="card-body">
                <h6>Assinaturas Ativas</h6>
                <h3><?= count(array_filter($subscriptions, fn($sub) => $sub['subscription_status'] === 'active')) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card bg-warning text-white">
            <div class="card-body">
                <h6>Trials Ativos</h6>
                <h3><?= count(array_filter($subscriptions, fn($sub) => $sub['subscription_status'] === 'trial')) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card bg-danger text-white">
            <div class="card-body">
                <h6>Suspensas</h6>
                <h3><?= count(array_filter($subscriptions, fn($sub) => in_array($sub['subscription_status'], ['suspended', 'expired']))) ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Planos Disponíveis -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-layer-group me-2"></i>Planos Disponíveis
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($plans as $plan): ?>
                        <div class="col-md-3 mb-3">
                            <div class="card border-<?= $plan['slug'] === 'enterprise' ? 'warning' : ($plan['slug'] === 'professional' ? 'info' : ($plan['slug'] === 'starter' ? 'success' : 'secondary')) ?>">
                                <div class="card-header bg-<?= $plan['slug'] === 'enterprise' ? 'warning' : ($plan['slug'] === 'professional' ? 'info' : ($plan['slug'] === 'starter' ? 'success' : 'secondary')) ?> text-white text-center">
                                    <h6 class="mb-0"><?= htmlspecialchars($plan['nome']) ?></h6>
                                </div>
                                <div class="card-body text-center">
                                    <h4 class="text-<?= $plan['slug'] === 'enterprise' ? 'warning' : ($plan['slug'] === 'professional' ? 'info' : ($plan['slug'] === 'starter' ? 'success' : 'secondary')) ?>">
                                        R$ <?= number_format($plan['preco'], 2, ',', '.') ?><small>/mês</small>
                                    </h4>
                                    <ul class="list-unstyled mt-3 mb-4">
                                        <li><strong>Usuários:</strong> <?= $plan['max_usuarios'] ? $plan['max_usuarios'] : 'Ilimitados' ?></li>
                                        <li><strong>Transações:</strong> <?= $plan['max_transacoes'] ? number_format($plan['max_transacoes']) : 'Ilimitadas' ?></li>
                                        <li><strong>Trial:</strong> <?= $plan['trial_days'] ?> dias</li>
                                    </ul>
                                    <div class="d-flex justify-content-center mb-3">
                                        <small class="badge bg-secondary"><?= $plan['subscription_count'] ?? 0 ?> assinaturas</small>
                                    </div>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-outline-primary btn-sm" onclick="editPlan(<?= $plan['id'] ?>)" title="Editar Plano">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="deletePlan(<?= $plan['id'] ?>, '<?= htmlspecialchars($plan['nome']) ?>')" title="Excluir Plano">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros de Assinaturas -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Filtrar por Status</label>
                <select class="form-select" id="filterStatus" onchange="filterSubscriptions()">
                    <option value="">Todos os Status</option>
                    <option value="trial">Trial</option>
                    <option value="active">Ativo</option>
                    <option value="suspended">Suspenso</option>
                    <option value="expired">Expirado</option>
                    <option value="cancelled">Cancelado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Filtrar por Plano</label>
                <select class="form-select" id="filterPlan" onchange="filterSubscriptions()">
                    <option value="">Todos os Planos</option>
                    <?php foreach ($plans as $plan): ?>
                        <option value="<?= htmlspecialchars($plan['nome']) ?>"><?= htmlspecialchars($plan['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Buscar Organização</label>
                <input type="text" class="form-control" id="searchSub" placeholder="Nome da organização..." onkeyup="filterSubscriptions()">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                    <i class="fas fa-times me-2"></i>Limpar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabela de Assinaturas -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>Assinaturas Ativas
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($subscriptions)): ?>
            <div class="text-center py-5">
                <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                <h5>Nenhuma assinatura encontrada</h5>
                <p class="text-muted">As organizações com assinaturas aparecerão aqui.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="subscriptionsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Organização</th>
                            <th>Plano</th>
                            <th>Status</th>
                            <th>Valor</th>
                            <th>Usuários</th>
                            <th>Transações</th>
                            <th>Período</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscriptions as $sub): ?>
                            <tr data-status="<?= $sub['subscription_status'] ?? '' ?>" data-plan="<?= htmlspecialchars($sub['plan_name'] ?? '') ?>">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-info rounded-circle d-flex align-items-center justify-content-center me-3">
                                            <i class="fas fa-building text-white"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($sub['org_name']) ?></h6>
                                            <small class="text-muted">ID: <?= $sub['org_id'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= htmlspecialchars($sub['plan_name']) ?></span>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = match($sub['subscription_status']) {
                                        'active' => 'success',
                                        'trial' => 'warning',
                                        'suspended' => 'danger',
                                        'expired' => 'secondary',
                                        'cancelled' => 'dark',
                                        default => 'secondary'
                                    };
                                    $statusText = match($sub['subscription_status']) {
                                        'active' => 'Ativo',
                                        'trial' => 'Trial',
                                        'suspended' => 'Suspenso',
                                        'expired' => 'Expirado',
                                        'cancelled' => 'Cancelado',
                                        default => 'Inativo'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>"><?= $statusText ?></span>
                                </td>
                                <td>
                                    <strong class="text-<?= $sub['preco'] > 0 ? 'success' : 'muted' ?>">
                                        R$ <?= number_format($sub['preco'], 2, ',', '.') ?>
                                    </strong>
                                    <small class="text-muted d-block">/mês</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?= $sub['user_count'] ?? 0 ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= $sub['transaction_count'] ?? 0 ?></span>
                                </td>
                                <td>
                                    <small>
                                        <?= date('d/m/Y', strtotime($sub['current_period_start'])) ?><br>
                                        <strong><?= date('d/m/Y', strtotime($sub['current_period_end'])) ?></strong>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="viewSubscription(<?= $sub['id'] ?>)" title="Ver Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning" onclick="editSubscription(<?= $sub['org_id'] ?>)" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-success" onclick="renewSubscription(<?= $sub['id'] ?>)" title="Renovar">
                                            <i class="fas fa-sync"></i>
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

<!-- Modal para Editar Assinatura -->
<div class="modal fade" id="editSubModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Assinatura</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editSubscriptionForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_sub_org_id" name="org_id">
                    
                    <div class="mb-3">
                        <label for="edit_sub_plan_id" class="form-label">Plano</label>
                        <select class="form-select" id="edit_sub_plan_id" name="plan_id" required>
                            <?php foreach ($plans as $plan): ?>
                                <option value="<?= $plan['id'] ?>"><?= htmlspecialchars($plan['nome']) ?> - R$ <?= number_format($plan['preco'], 2, ',', '.') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_sub_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_sub_status" name="status" required>
                            <option value="trial">Trial</option>
                            <option value="active">Ativo</option>
                            <option value="suspended">Suspenso</option>
                            <option value="cancelled">Cancelado</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Atenção:</strong> Alterar o plano afetará imediatamente os limites da organização.
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

<!-- Modal para Editar Plano -->
<div class="modal fade" id="editPlanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Plano</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPlanForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_plan_id" name="id">
                    
                    <div class="mb-3">
                        <label for="edit_plan_nome" class="form-label">Nome do Plano</label>
                        <input type="text" class="form-control" id="edit_plan_nome" name="nome" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_plan_preco" class="form-label">Preço Mensal (R$)</label>
                        <input type="number" class="form-control" id="edit_plan_preco" name="preco" step="0.01" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_plan_max_usuarios" class="form-label">Máx. Usuários</label>
                                <input type="number" class="form-control" id="edit_plan_max_usuarios" name="max_usuarios" placeholder="Deixe vazio para ilimitado">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_plan_max_transacoes" class="form-label">Máx. Transações</label>
                                <input type="number" class="form-control" id="edit_plan_max_transacoes" name="max_transacoes" placeholder="Deixe vazio para ilimitado">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_plan_trial_days" class="form-label">Dias de Trial</label>
                        <input type="number" class="form-control" id="edit_plan_trial_days" name="trial_days" value="7" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_plan_descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="edit_plan_descricao" name="descricao" rows="3"></textarea>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Atenção:</strong> Alterar os limites do plano afetará todas as organizações que o utilizam.
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
function filterSubscriptions() {
    const statusFilter = document.getElementById('filterStatus').value.toLowerCase();
    const planFilter = document.getElementById('filterPlan').value.toLowerCase();
    const searchText = document.getElementById('searchSub').value.toLowerCase();
    const table = document.getElementById('subscriptionsTable');
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
    document.getElementById('searchSub').value = '';
    filterSubscriptions();
}

function refreshData() {
    location.reload();
}

function viewSubscription(subId) {
    Swal.fire({
        title: 'Detalhes da Assinatura',
        text: 'Esta funcionalidade será implementada em breve.',
        icon: 'info'
    });
}

function editSubscription(orgId) {
    document.getElementById('edit_sub_org_id').value = orgId;
    const modal = new bootstrap.Modal(document.getElementById('editSubModal'));
    modal.show();
}

function renewSubscription(subId) {
    Swal.fire({
        title: 'Renovar Assinatura',
        text: 'Deseja renovar esta assinatura por mais um período?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sim, renovar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('Renovada!', 'Assinatura renovada com sucesso.', 'success');
        }
    });
}

function editPlan(planId) {
    // Buscar dados do plano e preencher modal
    fetch(`<?= url('admin/get-plan') ?>?id=${planId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const plan = data.plan;
                document.getElementById('edit_plan_id').value = plan.id;
                document.getElementById('edit_plan_nome').value = plan.nome;
                document.getElementById('edit_plan_preco').value = plan.preco;
                document.getElementById('edit_plan_max_usuarios').value = plan.max_usuarios || '';
                document.getElementById('edit_plan_max_transacoes').value = plan.max_transacoes || '';
                document.getElementById('edit_plan_trial_days').value = plan.trial_days;
                document.getElementById('edit_plan_descricao').value = plan.descricao || '';
                
                const modal = new bootstrap.Modal(document.getElementById('editPlanModal'));
                modal.show();
            } else {
                Swal.fire('Erro!', data.message, 'error');
            }
        })
        .catch(error => {
            Swal.fire('Erro!', 'Erro ao carregar dados do plano', 'error');
        });
}

function deletePlan(planId, planName) {
    Swal.fire({
        title: 'Confirmar Exclusão',
        html: `Tem certeza que deseja excluir o plano <strong>${planName}</strong>?<br><br><div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Esta ação não pode ser desfeita.</div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-trash me-2"></i>Excluir Plano',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('<?= url('admin/delete-plan') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${planId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Excluído!', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Erro!', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Erro!', 'Erro interno do servidor', 'error');
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const editForm = document.getElementById('editSubscriptionForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('<?= url('admin/update-subscription') ?>', {
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
    }
    
    // Form handler para editar plano
    const editPlanForm = document.getElementById('editPlanForm');
    if (editPlanForm) {
        editPlanForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('<?= url('admin/update-plan') ?>', {
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
    }
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