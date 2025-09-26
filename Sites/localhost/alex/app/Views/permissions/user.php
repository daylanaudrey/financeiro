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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-person-gear"></i>
                        Permissões Específicas: <?= htmlspecialchars($user['name']) ?>
                    </h5>
                    <a href="<?= BASE_URL ?>users" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>

                <div class="card-body">
                    <!-- Informações do usuário -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Informações do Usuário</h6>
                                    <p class="mb-1"><strong>Nome:</strong> <?= htmlspecialchars($user['name']) ?></p>
                                    <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                                    <p class="mb-1">
                                        <strong>Perfil:</strong>
                                        <span class="badge bg-primary"><?= $roleLabels[$user['role']] ?></span>
                                    </p>
                                    <p class="mb-0">
                                        <strong>Status:</strong>
                                        <?php if ($user['is_active']): ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inativo</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="bi bi-info-circle"></i> Como funciona?
                                    </h6>
                                    <p class="small mb-1">
                                        • O usuário herda as permissões do seu <strong>perfil</strong> (<?= $roleLabels[$user['role']] ?>)
                                    </p>
                                    <p class="small mb-1">
                                        • Permissões específicas <strong>adicionam</strong> funcionalidades ao usuário
                                    </p>
                                    <p class="small mb-0">
                                        • Marque apenas permissões <strong>extras</strong> que este usuário deve ter
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($user['role'] === 'admin'): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Administradores</strong> já possuem acesso total ao sistema. Não é possível alterar suas permissões.
                        </div>
                    <?php else: ?>
                        <!-- Formulário de permissões -->
                        <form id="userPermissionsForm" data-user-id="<?= $user['id'] ?>">
                            <div class="row">
                                <!-- Permissões do perfil (somente leitura) -->
                                <div class="col-lg-6">
                                    <h6 class="border-bottom pb-2 mb-3">
                                        <i class="bi bi-shield"></i> Permissões do Perfil "<?= $roleLabels[$user['role']] ?>"
                                        <small class="text-muted">(herdadas automaticamente)</small>
                                    </h6>

                                    <?php foreach ($modules as $module => $moduleData): ?>
                                        <?php
                                        $hasRolePermission = false;
                                        foreach ($moduleData['actions'] as $action => $description) {
                                            $permissionKey = $module . '.' . $action;
                                            if (in_array($permissionKey, $rolePermissions)) {
                                                $hasRolePermission = true;
                                                break;
                                            }
                                        }
                                        ?>

                                        <?php if ($hasRolePermission): ?>
                                            <div class="card mb-3">
                                                <div class="card-header bg-secondary text-white">
                                                    <h6 class="mb-0">
                                                        <i class="bi bi-folder"></i>
                                                        <?= htmlspecialchars($moduleData['name']) ?>
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <?php foreach ($moduleData['actions'] as $action => $description): ?>
                                                        <?php
                                                        $permissionKey = $module . '.' . $action;
                                                        $hasPermission = in_array($permissionKey, $rolePermissions);
                                                        ?>
                                                        <?php if ($hasPermission): ?>
                                                            <div class="form-check mb-1">
                                                                <input class="form-check-input"
                                                                       type="checkbox"
                                                                       checked
                                                                       disabled>
                                                                <label class="form-check-label text-muted">
                                                                    <?= htmlspecialchars($description) ?>
                                                                </label>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Permissões específicas do usuário -->
                                <div class="col-lg-6">
                                    <h6 class="border-bottom pb-2 mb-3">
                                        <i class="bi bi-person-plus"></i> Permissões Específicas do Usuário
                                        <small class="text-muted">(adicionais ao perfil)</small>
                                    </h6>

                                    <?php foreach ($modules as $module => $moduleData): ?>
                                        <div class="card mb-3">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="mb-0">
                                                    <i class="bi bi-folder"></i>
                                                    <?= htmlspecialchars($moduleData['name']) ?>
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <?php foreach ($moduleData['actions'] as $action => $description): ?>
                                                    <?php
                                                    $permissionKey = $module . '.' . $action;
                                                    $hasRolePermission = in_array($permissionKey, $rolePermissions);
                                                    $hasUserPermission = in_array($permissionKey, $userPermissions);
                                                    ?>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input user-permission-checkbox"
                                                               type="checkbox"
                                                               name="permissions[]"
                                                               value="<?= $permissionKey ?>"
                                                               id="user_<?= $module ?>_<?= $action ?>"
                                                               data-module="<?= $module ?>"
                                                               data-action="<?= $action ?>"
                                                               <?= $hasUserPermission ? 'checked' : '' ?>
                                                               <?= $hasRolePermission ? 'disabled title="Já possui esta permissão pelo perfil"' : '' ?>
                                                               <?= $action === 'view' ? 'data-is-view="true"' : '' ?>>
                                                        <label class="form-check-label <?= $hasRolePermission ? 'text-muted' : '' ?>"
                                                               for="user_<?= $module ?>_<?= $action ?>">
                                                            <?= htmlspecialchars($description) ?>
                                                            <?php if ($hasRolePermission): ?>
                                                                <small class="text-muted">(já possui)</small>
                                                            <?php elseif ($action === 'view'): ?>
                                                                <small class="text-muted">(obrigatório)</small>
                                                            <?php endif; ?>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="mt-4 text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle"></i>
                                    Salvar Permissões Específicas
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-lg ms-2"
                                        onclick="clearAllPermissions()">
                                    <i class="bi bi-x-circle"></i>
                                    Remover Todas as Específicas
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
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

.form-check-input:disabled {
    opacity: 0.6;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-1px);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar verificação de dependências para permissões do usuário
    checkUserPermissionDependencies();

    const form = document.getElementById('userPermissionsForm');

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const userId = this.dataset.userId;
            const formData = new FormData(this);
            formData.append('user_id', userId);

            fetch('<?= BASE_URL ?>api/permissions/user', {
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
    }
});

// Verificar dependências de permissões do usuário
function checkUserPermissionDependencies() {
    document.querySelectorAll('.user-permission-checkbox:not([disabled])').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const module = this.dataset.module;
            const action = this.dataset.action;
            const isView = this.dataset.isView === 'true';

            // Se desmarcou "view", desmarcar todas as outras do módulo
            if (isView && !this.checked) {
                document.querySelectorAll(`#userPermissionsForm input[data-module="${module}"]:not([data-is-view="true"]):not([disabled])`).forEach(otherCheckbox => {
                    otherCheckbox.checked = false;
                });
            }

            // Se marcou uma ação que não é "view", marcar "view" automaticamente
            if (!isView && this.checked) {
                const viewCheckbox = document.querySelector(`#userPermissionsForm input[data-module="${module}"][data-is-view="true"]:not([disabled])`);
                if (viewCheckbox) {
                    viewCheckbox.checked = true;
                }
            }
        });
    });
}

// Limpar todas as permissões específicas
function clearAllPermissions() {
    document.querySelectorAll('#userPermissionsForm input[type="checkbox"]:not([disabled])').forEach(checkbox => {
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