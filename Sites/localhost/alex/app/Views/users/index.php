<?php
// Verificar se é admin
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'dashboard');
    exit;
}
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-people"></i> Gerenciar Usuários
                    </h5>
                    <a href="<?= BASE_URL ?>users/create" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Novo Usuário
                    </a>
                </div>

                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Nome</label>
                                    <input type="text" name="name" class="form-control"
                                           value="<?= htmlspecialchars($filters['name'] ?? '') ?>"
                                           placeholder="Filtrar por nome">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control"
                                           value="<?= htmlspecialchars($filters['email'] ?? '') ?>"
                                           placeholder="Filtrar por email">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Perfil</label>
                                    <select name="role" class="form-select">
                                        <option value="">Todos</option>
                                        <?php foreach ($roleOptions as $value => $label): ?>
                                            <option value="<?= $value ?>" <?= ($filters['role'] ?? '') === $value ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Status</label>
                                    <select name="is_active" class="form-select">
                                        <option value="">Todos</option>
                                        <option value="1" <?= ($filters['is_active'] ?? '') === 1 ? 'selected' : '' ?>>Ativo</option>
                                        <option value="0" <?= ($filters['is_active'] ?? '') === 0 ? 'selected' : '' ?>>Inativo</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-outline-primary me-2">
                                        <i class="bi bi-search"></i> Filtrar
                                    </button>
                                    <a href="<?= BASE_URL ?>users" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle"></i> Limpar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabela -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Perfil</th>
                                    <th>Status</th>
                                    <th>Último Login</th>
                                    <th>Criado em</th>
                                    <th width="150" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                            Nenhum usuário encontrado
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?= $user['id'] ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($user['name']) ?></strong>
                                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                    <span class="badge bg-info ms-1">Você</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td>
                                                <?php
                                                $roleClass = [
                                                    'admin' => 'bg-danger',
                                                    'operator' => 'bg-success',
                                                    'viewer' => 'bg-secondary'
                                                ];
                                                ?>
                                                <span class="badge <?= $roleClass[$user['role']] ?? 'bg-secondary' ?>">
                                                    <?= htmlspecialchars($roleOptions[$user['role']] ?? $user['role']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($user['is_active']): ?>
                                                    <span class="badge bg-success">Ativo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inativo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Nunca' ?>
                                            </td>
                                            <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <a href="<?= BASE_URL ?>users/edit?id=<?= $user['id'] ?>"
                                                           class="btn btn-sm btn-outline-primary"
                                                           title="Editar">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="<?= BASE_URL ?>permissions/user?user_id=<?= $user['id'] ?>"
                                                           class="btn btn-sm btn-outline-info"
                                                           title="Permissões Específicas">
                                                            <i class="bi bi-key"></i>
                                                        </a>
                                                        <?php if ($user['role'] !== 'admin'): ?>
                                                            <button type="button"
                                                                    class="btn btn-sm btn-outline-danger"
                                                                    title="Excluir"
                                                                    onclick="confirmDelete(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name']) ?>')">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <a href="<?= BASE_URL ?>profile"
                                                           class="btn btn-sm btn-outline-info"
                                                           title="Meu Perfil">
                                                            <i class="bi bi-person-circle"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Deseja realmente excluir o usuário <strong id="userName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    Esta ação não pode ser desfeita.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash"></i> Excluir Usuário
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    window.confirmDelete = function(userId, userName) {
        document.getElementById('userName').textContent = userName;

        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();

        document.getElementById('confirmDeleteBtn').onclick = function() {
            deleteUser(userId);
            modal.hide();
        };
    };

    function deleteUser(userId) {
        fetch('<?= BASE_URL ?>api/users/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + userId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao excluir usuário');
        });
    }
});
</script>