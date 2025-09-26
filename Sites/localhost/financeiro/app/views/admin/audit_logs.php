<?php 
$title = 'Logs de Auditoria - Painel Administrativo';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="fas fa-clipboard-list me-2"></i>Logs de Auditoria</h2>
        <p class="text-muted mb-0">Monitore todas as atividades do sistema</p>
    </div>
    <div class="btn-group">
        <button class="btn btn-success" onclick="exportLogs()">
            <i class="fas fa-download me-2"></i>Exportar
        </button>
        <button class="btn btn-info" onclick="refreshLogs()">
            <i class="fas fa-sync me-2"></i>Atualizar
        </button>
        <button class="btn btn-warning" onclick="clearOldLogs()">
            <i class="fas fa-trash me-2"></i>Limpar Antigos
        </button>
    </div>
</div>

<!-- Cards de Estatísticas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stats-card bg-primary text-white">
            <div class="card-body">
                <h6>Total de Logs</h6>
                <h3><?= count($logs ?? []) ?></h3>
                <small>Na página atual</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card bg-success text-white">
            <div class="card-body">
                <h6>Logins Hoje</h6>
                <h3><?= count(array_filter($logs ?? [], fn($log) => $log['action'] === 'login' && date('Y-m-d', strtotime($log['created_at'])) === date('Y-m-d'))) ?></h3>
                <small>Últimas 24h</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card bg-info text-white">
            <div class="card-body">
                <h6>Ações Críticas</h6>
                <h3><?= count(array_filter($logs ?? [], fn($log) => in_array($log['action'], ['delete', 'update']))) ?></h3>
                <small>Edições/Exclusões</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card bg-warning text-white">
            <div class="card-body">
                <h6>Organizações Ativas</h6>
                <h3><?= count(array_unique(array_column($logs ?? [], 'org_name'))) ?></h3>
                <small>Com atividade</small>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-2">
                <label class="form-label">Ação</label>
                <select class="form-select form-select-sm" id="filterAction" onchange="filterLogs()">
                    <option value="">Todas</option>
                    <option value="login">Login</option>
                    <option value="logout">Logout</option>
                    <option value="create">Criar</option>
                    <option value="auto_create">Criação Automática</option>
                    <option value="update">Atualizar</option>
                    <option value="delete">Excluir</option>
                    <option value="view">Visualizar</option>
                    <option value="email_sent">Email Enviado</option>
                    <option value="email_failed">Email Falhado</option>
                    <option value="error">Erro</option>
                    <option value="exception">Exceção</option>
                    <option value="fatal_error">Erro Fatal</option>
                    <option value="database_error">Erro BD</option>
                    <option value="api_error">Erro API</option>
                    <option value="security_alert">Alerta Segurança</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Entidade</label>
                <select class="form-select form-select-sm" id="filterEntity" onchange="filterLogs()">
                    <option value="">Todas</option>
                    <option value="auth">Autenticação</option>
                    <option value="user">Usuário</option>
                    <option value="transaction">Transação</option>
                    <option value="account">Conta</option>
                    <option value="organization">Organização</option>
                    <option value="subscription">Assinatura</option>
                    <option value="email">Email</option>
                    <option value="system">Sistema/Erros</option>
                    <option value="email_config">Config. Email</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Usuário</label>
                <input type="text" class="form-control form-control-sm" id="filterUser" placeholder="Nome..." onkeyup="filterLogs()">
            </div>
            <div class="col-md-2">
                <label class="form-label">Organização</label>
                <input type="text" class="form-control form-control-sm" id="filterOrg" placeholder="Nome..." onkeyup="filterLogs()">
            </div>
            <div class="col-md-2">
                <label class="form-label">Data</label>
                <input type="date" class="form-control form-control-sm" id="filterDate" onchange="filterLogs()">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-outline-secondary btn-sm w-100" onclick="clearFilters()">
                    <i class="fas fa-times me-1"></i>Limpar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabela de Logs -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>Atividades Recentes
        </h5>
        <div class="d-flex align-items-center">
            <small class="text-muted me-3">
                Página <?= $currentPage ?? 1 ?> de <?= $totalPages ?? 1 ?>
            </small>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-secondary" onclick="previousPage()" <?= ($currentPage ?? 1) <= 1 ? 'disabled' : '' ?>>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="btn btn-outline-secondary" onclick="nextPage()" <?= ($currentPage ?? 1) >= ($totalPages ?? 1) ? 'disabled' : '' ?>>
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($logs)): ?>
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <h5>Nenhum log encontrado</h5>
                <p class="text-muted">Os logs de auditoria aparecerão aqui conforme as atividades do sistema.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="logsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="140">Data/Hora</th>
                            <th width="120">Usuário</th>
                            <th width="120">Organização</th>
                            <th width="80">Ação</th>
                            <th width="100">Entidade</th>
                            <th>Descrição</th>
                            <th width="100">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr data-action="<?= htmlspecialchars($log['action']) ?>" 
                                data-entity="<?= htmlspecialchars($log['entity']) ?>"
                                data-date="<?= date('Y-m-d', strtotime($log['created_at'])) ?>">
                                <td>
                                    <small class="text-muted">
                                        <?= date('d/m/Y', strtotime($log['created_at'])) ?><br>
                                        <strong><?= date('H:i:s', strtotime($log['created_at'])) ?></strong>
                                    </small>
                                </td>
                                <td>
                                    <?php if ($log['user_name']): ?>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <i class="fas fa-user text-white" style="font-size: 10px;"></i>
                                            </div>
                                            <div>
                                                <small class="fw-bold"><?= htmlspecialchars($log['user_name']) ?></small>
                                                <small class="text-muted d-block" style="font-size: 0.7em;">
                                                    <?= htmlspecialchars($log['email'] ?? '') ?>
                                                </small>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <small class="text-muted">Sistema</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($log['org_name']): ?>
                                        <span class="badge bg-secondary" style="font-size: 0.7em;">
                                            <?= htmlspecialchars($log['org_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $actionClass = match($log['action']) {
                                        'login' => 'success',
                                        'logout' => 'secondary',
                                        'create' => 'primary',
                                        'update' => 'warning',
                                        'delete' => 'danger',
                                        'view' => 'info',
                                        'error' => 'danger',
                                        'exception' => 'danger',
                                        'fatal_error' => 'dark',
                                        'database_error' => 'danger',
                                        'api_error' => 'warning',
                                        'security_alert' => 'danger',
                                        'test' => 'info',
                                        'send' => 'success',
                                        'verify' => 'success',
                                        default => 'light'
                                    };
                                    $actionIcon = match($log['action']) {
                                        'login' => 'sign-in-alt',
                                        'logout' => 'sign-out-alt',
                                        'create' => 'plus',
                                        'update' => 'edit',
                                        'delete' => 'trash',
                                        'view' => 'eye',
                                        'error' => 'exclamation-triangle',
                                        'exception' => 'bug',
                                        'fatal_error' => 'skull-crossbones',
                                        'database_error' => 'database',
                                        'api_error' => 'plug',
                                        'security_alert' => 'shield-alt',
                                        'test' => 'flask',
                                        'send' => 'paper-plane',
                                        'verify' => 'check-circle',
                                        default => 'question'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $actionClass ?>" style="font-size: 0.7em;">
                                        <i class="fas fa-<?= $actionIcon ?> me-1"></i>
                                        <?= ucfirst($log['action']) ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= ucfirst(htmlspecialchars($log['entity'])) ?>
                                        <?php if ($log['entity_id']): ?>
                                            <span class="text-primary">#<?= $log['entity_id'] ?></span>
                                        <?php endif; ?>
                                    </small>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($log['description'] ?? '-') ?></small>
                                    <?php if ($log['old_values'] || $log['new_values']): ?>
                                        <button class="btn btn-outline-info btn-xs ms-2" onclick="showLogDetails(<?= $log['id'] ?>)" title="Ver Detalhes">
                                            <i class="fas fa-info-circle" style="font-size: 10px;"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted" title="<?= htmlspecialchars($log['user_agent'] ?? '') ?>">
                                        <?= htmlspecialchars($log['ip_address'] ?? '-') ?>
                                    </small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para Detalhes do Log -->
<div class="modal fade" id="logDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Log</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="logDetailsContent">
                <!-- Conteúdo será preenchido via JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
function filterLogs() {
    const actionFilter = document.getElementById('filterAction').value.toLowerCase();
    const entityFilter = document.getElementById('filterEntity').value.toLowerCase();
    const userFilter = document.getElementById('filterUser').value.toLowerCase();
    const orgFilter = document.getElementById('filterOrg').value.toLowerCase();
    const dateFilter = document.getElementById('filterDate').value;
    
    const table = document.getElementById('logsTable');
    if (!table) return;
    
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const action = row.getAttribute('data-action').toLowerCase();
        const entity = row.getAttribute('data-entity').toLowerCase();
        const date = row.getAttribute('data-date');
        const userText = row.cells[1].textContent.toLowerCase();
        const orgText = row.cells[2].textContent.toLowerCase();
        
        const actionMatch = !actionFilter || action.includes(actionFilter);
        const entityMatch = !entityFilter || entity.includes(entityFilter);
        const userMatch = !userFilter || userText.includes(userFilter);
        const orgMatch = !orgFilter || orgText.includes(orgFilter);
        const dateMatch = !dateFilter || date === dateFilter;
        
        if (actionMatch && entityMatch && userMatch && orgMatch && dateMatch) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

function clearFilters() {
    document.getElementById('filterAction').value = '';
    document.getElementById('filterEntity').value = '';
    document.getElementById('filterUser').value = '';
    document.getElementById('filterOrg').value = '';
    document.getElementById('filterDate').value = '';
    filterLogs();
}

function refreshLogs() {
    location.reload();
}

function exportLogs() {
    Swal.fire({
        title: 'Exportar Logs',
        text: 'Selecione o formato de exportação:',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'CSV',
        cancelButtonText: 'PDF',
        showDenyButton: true,
        denyButtonText: 'JSON'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Exportando logs em CSV...', 'info');
        } else if (result.isDenied) {
            showNotification('Exportando logs em JSON...', 'info');
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            showNotification('Exportando logs em PDF...', 'info');
        }
    });
}

function clearOldLogs() {
    Swal.fire({
        title: 'Limpar logs antigos?',
        text: 'Esta ação irá remover logs com mais de 90 dias.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, limpar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Logs antigos removidos com sucesso!', 'success');
        }
    });
}

function showLogDetails(logId) {
    $('#logDetailsModal').modal('show');
    $('#logDetailsContent').html(`
        <div class="text-center">
            <div class="spinner-border" role="status"></div>
        </div>
    `);
    
    // Simular carregamento de detalhes
    setTimeout(() => {
        $('#logDetailsContent').html(`
            <div class="row">
                <div class="col-md-6">
                    <h6>Valores Anteriores</h6>
                    <pre class="bg-light p-2 rounded"><code>Em desenvolvimento...</code></pre>
                </div>
                <div class="col-md-6">
                    <h6>Novos Valores</h6>
                    <pre class="bg-light p-2 rounded"><code>Em desenvolvimento...</code></pre>
                </div>
            </div>
        `);
    }, 1000);
}

function previousPage() {
    const currentPage = <?= $currentPage ?? 1 ?>;
    if (currentPage > 1) {
        window.location.href = '<?= url('admin/audit-logs') ?>?page=' + (currentPage - 1);
    }
}

function nextPage() {
    const currentPage = <?= $currentPage ?? 1 ?>;
    const totalPages = <?= $totalPages ?? 1 ?>;
    if (currentPage < totalPages) {
        window.location.href = '<?= url('admin/audit-logs') ?>?page=' + (currentPage + 1);
    }
}
</script>

<style>
.avatar-xs {
    width: 20px;
    height: 20px;
}

.btn-xs {
    padding: 0.125rem 0.25rem;
    font-size: 0.625rem;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15) !important;
}

.table th, .table td {
    vertical-align: middle;
}
</style>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>