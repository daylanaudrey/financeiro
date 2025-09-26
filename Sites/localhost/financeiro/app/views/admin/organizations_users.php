<?php 
$title = 'Organizações e Usuários - Painel Administrativo';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="fas fa-users-cog me-2"></i>Organizações e Usuários</h2>
        <p class="text-muted mb-0">Visualize todas as organizações e seus usuários associados</p>
    </div>
    <div class="btn-group">
        <button class="btn btn-success" onclick="refreshData()">
            <i class="fas fa-sync me-2"></i>Atualizar
        </button>
        <button class="btn btn-info" onclick="exportData()">
            <i class="fas fa-download me-2"></i>Exportar
        </button>
    </div>
</div>

<!-- Cards de Estatísticas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stats-card bg-primary text-white">
            <div class="card-body">
                <h6>Total Organizações</h6>
                <h3><?= count($organizations ?? []) ?></h3>
                <small>Organizações ativas</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card bg-success text-white">
            <div class="card-body">
                <h6>Total Usuários</h6>
                <h3><?= array_sum(array_map(fn($org) => count($org['users'] ?? []), $organizations ?? [])) ?></h3>
                <small>Usuários ativos</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card bg-warning text-white">
            <div class="card-body">
                <h6>Administradores</h6>
                <h3><?= array_sum(array_map(fn($org) => count(array_filter($org['users'] ?? [], fn($user) => $user['role'] === 'admin')), $organizations ?? [])) ?></h3>
                <small>Usuários admin</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card bg-info text-white">
            <div class="card-body">
                <h6>Usuários Ativos</h6>
                <h3><?= array_sum(array_map(fn($org) => count(array_filter($org['users'] ?? [], fn($user) => $user['status'] === 'ativo')), $organizations ?? [])) ?></h3>
                <small>Status ativo</small>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Filtrar por Organização</label>
                <input type="text" class="form-control" id="filterOrg" placeholder="Nome da organização..." onkeyup="filterData()">
            </div>
            <div class="col-md-3">
                <label class="form-label">Filtrar por Usuário</label>
                <input type="text" class="form-control" id="filterUser" placeholder="Nome do usuário..." onkeyup="filterData()">
            </div>
            <div class="col-md-2">
                <label class="form-label">Role</label>
                <select class="form-select" id="filterRole" onchange="filterData()">
                    <option value="">Todas</option>
                    <option value="admin">Admin</option>
                    <option value="financeiro">Financeiro</option>
                    <option value="operador">Operador</option>
                    <option value="leitor">Leitor</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select class="form-select" id="filterStatus" onchange="filterData()">
                    <option value="">Todos</option>
                    <option value="ativo">Ativo</option>
                    <option value="inativo">Inativo</option>
                    <option value="pendente">Pendente</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                    <i class="fas fa-times me-1"></i>Limpar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Organizações e Usuários -->
<div class="row" id="organizationsContainer">
    <?php if (empty($organizations)): ?>
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                <h5>Nenhuma organização encontrada</h5>
                <p class="text-muted">As organizações aparecerão aqui quando houver dados.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($organizations as $org): ?>
            <div class="col-lg-6 mb-4 org-card" data-org-name="<?= strtolower(htmlspecialchars($org['nome'])) ?>">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="fas fa-building me-2 text-primary"></i>
                                <?= htmlspecialchars($org['nome']) ?>
                            </h5>
                            <small class="text-muted">ID: <?= $org['id'] ?></small>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-<?= $org['subscription_status'] === 'active' ? 'success' : ($org['subscription_status'] === 'trial' ? 'warning' : 'secondary') ?> me-2">
                                <?= ucfirst($org['subscription_status'] ?? 'Sem plano') ?>
                            </span>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="viewOrgDetails(<?= $org['id'] ?>)">
                                        <i class="fas fa-eye me-2"></i>Ver Detalhes
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="editOrgSubscription(<?= $org['id'] ?>)">
                                        <i class="fas fa-credit-card me-2"></i>Gerenciar Assinatura
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($org['users'])): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-user-slash text-muted mb-2"></i>
                                <p class="text-muted mb-0">Nenhum usuário encontrado</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Usuário</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Último Login</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($org['users'] as $user): ?>
                                            <tr class="user-row" 
                                                data-user-name="<?= strtolower(htmlspecialchars($user['nome'])) ?>"
                                                data-user-role="<?= $user['role'] ?>"
                                                data-user-status="<?= $user['status'] ?>">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-xs bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'financeiro' ? 'warning' : 'primary') ?> rounded-circle d-flex align-items-center justify-content-center me-2">
                                                            <i class="fas fa-user text-white" style="font-size: 10px;"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold"><?= htmlspecialchars($user['nome']) ?></div>
                                                            <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'financeiro' ? 'warning' : 'primary') ?>">
                                                        <?= ucfirst($user['role']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $user['status'] === 'ativo' ? 'success' : ($user['status'] === 'pendente' ? 'warning' : 'secondary') ?>">
                                                        <?= ucfirst($user['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Nunca' ?>
                                                    </small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="row text-center">
                            <div class="col-4">
                                <small class="text-muted">Total Usuários</small>
                                <div class="fw-bold"><?= count($org['users']) ?></div>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Transações</small>
                                <div class="fw-bold"><?= $org['transaction_count'] ?? 0 ?></div>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Criada em</small>
                                <div class="fw-bold"><?= date('d/m/Y', strtotime($org['created_at'])) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function filterData() {
    const orgFilter = document.getElementById('filterOrg').value.toLowerCase();
    const userFilter = document.getElementById('filterUser').value.toLowerCase();
    const roleFilter = document.getElementById('filterRole').value;
    const statusFilter = document.getElementById('filterStatus').value;
    
    const orgCards = document.querySelectorAll('.org-card');
    
    orgCards.forEach(card => {
        const orgName = card.getAttribute('data-org-name');
        const userRows = card.querySelectorAll('.user-row');
        
        // Filtrar organização
        const orgMatch = !orgFilter || orgName.includes(orgFilter);
        
        // Filtrar usuários dentro da organização
        let hasVisibleUsers = false;
        userRows.forEach(row => {
            const userName = row.getAttribute('data-user-name');
            const userRole = row.getAttribute('data-user-role');
            const userStatus = row.getAttribute('data-user-status');
            
            const userMatch = !userFilter || userName.includes(userFilter);
            const roleMatch = !roleFilter || userRole === roleFilter;
            const statusMatch = !statusFilter || userStatus === statusFilter;
            
            if (userMatch && roleMatch && statusMatch) {
                row.style.display = '';
                hasVisibleUsers = true;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Mostrar organização se ela corresponde ao filtro E tem usuários visíveis
        if (orgMatch && (hasVisibleUsers || userRows.length === 0)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

function clearFilters() {
    document.getElementById('filterOrg').value = '';
    document.getElementById('filterUser').value = '';
    document.getElementById('filterRole').value = '';
    document.getElementById('filterStatus').value = '';
    filterData();
}

function refreshData() {
    location.reload();
}

function exportData() {
    Swal.fire({
        title: 'Exportar Dados',
        text: 'Selecione o formato de exportação:',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Excel',
        cancelButtonText: 'PDF',
        showDenyButton: true,
        denyButtonText: 'CSV'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Exportando em Excel...', 'info');
        } else if (result.isDenied) {
            showNotification('Exportando em CSV...', 'info');
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            showNotification('Exportando em PDF...', 'info');
        }
    });
}

function viewOrgDetails(orgId) {
    // Redirecionar para página de detalhes da organização
    window.location.href = `<?= url('admin/organizations') ?>?view=${orgId}`;
}

function editOrgSubscription(orgId) {
    // Redirecionar para página de assinaturas com foco na organização
    window.location.href = `<?= url('admin/subscriptions') ?>?org=${orgId}`;
}
</script>

<style>
.avatar-xs {
    width: 20px;
    height: 20px;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15) !important;
    transition: all 0.3s ease;
}

.org-card {
    transition: all 0.3s ease;
}

.org-card:hover {
    transform: translateY(-2px);
}

.card-header {
    border-bottom: 1px solid rgba(0,0,0,0.125);
}

.table th {
    border-top: none;
    font-size: 0.875rem;
    font-weight: 600;
}

.table td {
    font-size: 0.875rem;
    vertical-align: middle;
}
</style>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>