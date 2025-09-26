<?php 
$title = 'Painel Administrativo - Sistema Financeiro';
ob_start();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-cogs me-2"></i>Painel Administrativo</h1>
        <div class="btn-group">
            <a href="<?= url('/admin/organizations') ?>" class="btn btn-outline-primary">
                <i class="fas fa-building me-1"></i>Organizações
            </a>
            <a href="<?= url('/admin/subscriptions') ?>" class="btn btn-outline-info">
                <i class="fas fa-credit-card me-1"></i>Assinaturas
            </a>
            <a href="<?= url('/admin/system-config') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-wrench me-1"></i>Configurações
            </a>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1">Organizações</h5>
                            <h2 class="mb-0"><?= $stats['total_organizations'] ?></h2>
                        </div>
                        <i class="fas fa-building fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1">Usuários Ativos</h5>
                            <h2 class="mb-0"><?= $stats['total_users'] ?></h2>
                        </div>
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1">Assinaturas Ativas</h5>
                            <h2 class="mb-0"><?= $stats['active_subscriptions'] ?></h2>
                            <small>+ <?= $stats['trial_subscriptions'] ?> em trial</small>
                        </div>
                        <i class="fas fa-credit-card fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1">Receita Mensal</h5>
                            <h2 class="mb-0">R$ <?= number_format((float)$stats['monthly_revenue'], 2, ',', '.') ?></h2>
                        </div>
                        <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Organizações Recentes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-building me-2"></i>Organizações Recentes
                    </h5>
                    <a href="<?= url('/admin/organizations') ?>" class="btn btn-sm btn-primary">Ver Todas</a>
                </div>
                <div class="card-body">
                    <?php if (empty($organizations)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-building fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Nenhuma organização encontrada</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Organização</th>
                                        <th>Plano</th>
                                        <th>Status</th>
                                        <th>Usuários</th>
                                        <th>Criada em</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($organizations, 0, 10) as $org): ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong><?= htmlspecialchars($org['nome']) ?></strong>
                                                    <?php if ($org['cnpj']): ?>
                                                        <small class="text-muted d-block"><?= htmlspecialchars($org['cnpj']) ?></small>
                                                    <?php endif; ?>
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
                                                
                                                <?php if ($org['subscription_status'] === 'trial' && $org['trial_ends_at']): ?>
                                                    <small class="text-muted d-block">
                                                        Expira em <?= date('d/m/Y', strtotime($org['trial_ends_at'])) ?>
                                                    </small>
                                                <?php elseif ($org['subscription_status'] === 'active' && $org['current_period_end']): ?>
                                                    <small class="text-muted d-block">
                                                        Até <?= date('d/m/Y', strtotime($org['current_period_end'])) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?= $org['user_count'] ?? 0 ?></span>
                                            </td>
                                            <td>
                                                <?= date('d/m/Y H:i', strtotime($org['created_at'])) ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" onclick="viewOrganization(<?= $org['id'] ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-warning" onclick="editSubscription(<?= $org['id'] ?>)">
                                                        <i class="fas fa-credit-card"></i>
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
        </div>
    </div>
</div>

<!-- Modal para Visualizar Organização -->
<div class="modal fade" id="viewOrgModal" tabindex="-1" aria-hidden="true">
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
<div class="modal fade" id="editSubscriptionModal" tabindex="-1" aria-hidden="true">
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
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewOrganization(orgId) {
    const modal = new bootstrap.Modal(document.getElementById('viewOrgModal'));
    modal.show();
    
    // Aqui você carregaria os detalhes da organização via AJAX
    document.getElementById('orgDetails').innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
    
    // Simulação - substituir por chamada AJAX real
    setTimeout(() => {
        document.getElementById('orgDetails').innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Informações Básicas</h6>
                    <p><strong>ID:</strong> ${orgId}</p>
                    <p><strong>Status:</strong> Ativo</p>
                </div>
                <div class="col-md-6">
                    <h6>Uso Atual</h6>
                    <p><strong>Usuários:</strong> 5/10</p>
                    <p><strong>Transações:</strong> 150/500</p>
                </div>
            </div>
        `;
    }, 1000);
}

function editSubscription(orgId) {
    document.getElementById('edit_org_id').value = orgId;
    const modal = new bootstrap.Modal(document.getElementById('editSubscriptionModal'));
    modal.show();
    
    // Carregar planos disponíveis
    loadAvailablePlans();
}

function loadAvailablePlans() {
    // Aqui você carregaria os planos via AJAX
    const plans = [
        {id: 1, name: 'Trial Gratuito'},
        {id: 2, name: 'Starter'},
        {id: 3, name: 'Professional'},
        {id: 4, name: 'Enterprise'}
    ];
    
    const select = document.getElementById('edit_plan_id');
    select.innerHTML = '<option value="">Selecione um plano</option>';
    
    plans.forEach(plan => {
        const option = document.createElement('option');
        option.value = plan.id;
        option.textContent = plan.name;
        select.appendChild(option);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const subscriptionForm = document.getElementById('subscriptionForm');
    if (subscriptionForm) {
        subscriptionForm.addEventListener('submit', function(e) {
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
});
</script>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>