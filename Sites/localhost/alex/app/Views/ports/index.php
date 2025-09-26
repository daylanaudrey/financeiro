<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3">
                        <i class="bi bi-geo-alt"></i> Gerenciar Portos
                    </h1>
                    <p class="text-muted">Cadastro e controle de portos para importação</p>
                </div>
                <?php if (Permission::check('ports.create')): ?>
                    <div>
                        <a href="<?= BASE_URL ?>ports/create" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Novo Porto
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Mensagens -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-funnel"></i> Filtros
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>ports">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="name" class="form-label">Nome do Porto</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="<?= htmlspecialchars($filters['name']) ?>" placeholder="Buscar por nome">
                    </div>
                    <div class="col-md-3">
                        <label for="city" class="form-label">Cidade</label>
                        <input type="text" class="form-control" id="city" name="city"
                               value="<?= htmlspecialchars($filters['city']) ?>" placeholder="Buscar por cidade">
                    </div>
                    <div class="col-md-2">
                        <label for="state" class="form-label">Estado</label>
                        <input type="text" class="form-control" id="state" name="state"
                               value="<?= htmlspecialchars($filters['state']) ?>" placeholder="UF">
                    </div>
                    <div class="col-md-2">
                        <label for="is_active" class="form-label">Status</label>
                        <select class="form-select select2" id="is_active" name="is_active">
                            <option value="">Todos</option>
                            <option value="1" <?= $filters['is_active'] === 1 ? 'selected' : '' ?>>Ativo</option>
                            <option value="0" <?= $filters['is_active'] === 0 ? 'selected' : '' ?>>Inativo</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Portos -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-list"></i> Portos (<?= count($ports) ?>)
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($ports)): ?>
                <div class="text-center py-4">
                    <i class="bi bi-geo-alt display-1 text-muted"></i>
                    <h5 class="mt-3">Nenhum porto encontrado</h5>
                    <p class="text-muted">Comece cadastrando o primeiro porto do sistema.</p>
                    <?php if (Permission::check('ports.create')): ?>
                        <a href="<?= BASE_URL ?>ports/create" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Cadastrar Primeiro Porto
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Prefixo</th>
                                <th>Nome</th>
                                <th>Cidade/Estado</th>
                                <th>Código Recinto</th>
                                <th>Status</th>
                                <th width="120">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ports as $port): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary"><?= htmlspecialchars($port['prefix']) ?></span>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($port['name']) ?></strong>
                                        <?php if (!empty($port['notes'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($port['notes']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($port['city']) ?>
                                        <?php if (!empty($port['state'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($port['state']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($port['customs_code'])): ?>
                                            <span class="font-monospace"><?= htmlspecialchars($port['customs_code']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($port['is_active']): ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if (Permission::check('ports.create')): ?>
                                                <a href="<?= BASE_URL ?>ports/edit?id=<?= $port['id'] ?>"
                                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            <?php endif; ?>

                                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        title="Excluir" onclick="deletePort(<?= $port['id'] ?>, '<?= htmlspecialchars($port['name'], ENT_QUOTES) ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
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

<script>
function deletePort(id, name) {
    showConfirm(
        'Confirmar Exclusão',
        `Você tem certeza que deseja excluir o porto "${name}"? Esta ação não pode ser desfeita!`,
        function() {
            fetch('<?= BASE_URL ?>api/ports/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess(data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                showError('Erro ao excluir porto');
            });
        }
    );
}
</script>