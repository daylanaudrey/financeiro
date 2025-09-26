<?php
// Verificar se é admin
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'dashboard');
    exit;
}

$roleLabels = [
    'admin' => 'Administrador',
    'operator' => 'Operador',
    'viewer' => 'Visualizador'
];
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-check"></i> Gerenciar Permissões dos Perfis
                    </h5>
                </div>

                <div class="card-body">
                    <!-- Tabs para cada perfil -->
                    <ul class="nav nav-tabs mb-4" role="tablist">
                        <?php foreach ($roles as $index => $role): ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= $index === 0 ? 'active' : '' ?>"
                                        id="<?= $role ?>-tab"
                                        data-bs-toggle="tab"
                                        data-bs-target="#<?= $role ?>-panel"
                                        type="button">
                                    <i class="bi bi-person-badge"></i>
                                    <?= $roleLabels[$role] ?>
                                    <?php if ($role === 'admin'): ?>
                                        <span class="badge bg-danger ms-1">Todas as permissões</span>
                                    <?php endif; ?>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- Conteúdo das tabs -->
                    <div class="tab-content">
                        <?php foreach ($roles as $index => $role): ?>
                            <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>"
                                 id="<?= $role ?>-panel"
                                 role="tabpanel">

                                <?php if ($role === 'admin'): ?>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i>
                                        <strong>Administradores</strong> têm acesso total ao sistema e suas permissões não podem ser alteradas.
                                    </div>
                                <?php else: ?>
                                    <form id="form-<?= $role ?>" data-role="<?= $role ?>">
                                        <div class="row">
                                            <?php foreach ($modules as $module => $moduleData): ?>
                                                <div class="col-md-6 col-lg-4 mb-4">
                                                    <div class="card h-100">
                                                        <div class="card-header bg-light">
                                                            <h6 class="mb-0">
                                                                <i class="bi bi-folder"></i>
                                                                <?= htmlspecialchars($moduleData['name']) ?>
                                                            </h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <?php foreach ($moduleData['actions'] as $action => $description): ?>
                                                                <?php
                                                                $permissionKey = $module . '.' . $action;
                                                                $isChecked = in_array($permissionKey, $rolePermissions[$role] ?? []);
                                                                ?>
                                                                <div class="form-check mb-2">
                                                                    <input class="form-check-input permission-checkbox"
                                                                           type="checkbox"
                                                                           name="permissions[]"
                                                                           value="<?= $permissionKey ?>"
                                                                           id="<?= $role ?>_<?= $module ?>_<?= $action ?>"
                                                                           data-module="<?= $module ?>"
                                                                           data-action="<?= $action ?>"
                                                                           data-role="<?= $role ?>"
                                                                           <?= $isChecked ? 'checked' : '' ?>
                                                                           <?= $action === 'view' ? 'data-is-view="true"' : '' ?>>
                                                                    <label class="form-check-label"
                                                                           for="<?= $role ?>_<?= $module ?>_<?= $action ?>">
                                                                        <?= htmlspecialchars($description) ?>
                                                                        <?php if ($action === 'view'): ?>
                                                                            <small class="text-muted">(obrigatório)</small>
                                                                        <?php endif; ?>
                                                                    </label>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <div class="mt-4">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-check-circle"></i>
                                                Salvar Permissões do <?= $roleLabels[$role] ?>
                                            </button>
                                            <button type="button" class="btn btn-secondary ms-2"
                                                    onclick="selectAll('<?= $role ?>')">
                                                <i class="bi bi-check-all"></i> Marcar Todas
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary ms-2"
                                                    onclick="unselectAll('<?= $role ?>')">
                                                <i class="bi bi-x-circle"></i> Desmarcar Todas
                                            </button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar verificação de dependências
    checkPermissionDependencies();

    // Handler para cada formulário
    document.querySelectorAll('form[data-role]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const role = this.dataset.role;
            const formData = new FormData(this);
            formData.append('role', role);

            fetch('<?= BASE_URL ?>api/permissions/role', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showAlert('danger', 'Erro ao salvar permissões');
            });
        });
    });
});

// Verificar dependências de permissões
function checkPermissionDependencies() {
    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const module = this.dataset.module;
            const action = this.dataset.action;
            const role = this.dataset.role;
            const isView = this.dataset.isView === 'true';

            // Se desmarcou "view", desmarcar todas as outras do módulo
            if (isView && !this.checked) {
                document.querySelectorAll(`#form-${role} input[data-module="${module}"]:not([data-is-view="true"])`).forEach(otherCheckbox => {
                    otherCheckbox.checked = false;
                });
            }

            // Se marcou uma ação que não é "view", marcar "view" automaticamente
            if (!isView && this.checked) {
                const viewCheckbox = document.querySelector(`#form-${role} input[data-module="${module}"][data-is-view="true"]`);
                if (viewCheckbox) {
                    viewCheckbox.checked = true;
                }
            }
        });
    });
}

// Marcar todas as permissões
function selectAll(role) {
    document.querySelectorAll(`#form-${role} input[type="checkbox"]`).forEach(checkbox => {
        checkbox.checked = true;
    });
}

// Desmarcar todas as permissões
function unselectAll(role) {
    document.querySelectorAll(`#form-${role} input[type="checkbox"]`).forEach(checkbox => {
        checkbox.checked = false;
    });
}

// Mostrar alerta
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);

    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
</script>