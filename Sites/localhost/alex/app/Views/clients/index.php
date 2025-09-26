<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3">
                        <i class="bi bi-people"></i> Gerenciar Importadores
                    </h1>
                    <p class="text-muted">Cadastro e controle de importadores</p>
                </div>
                <?php if (Permission::check('clients.create')): ?>
                    <div>
                        <a href="<?= BASE_URL ?>clients/create" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Novo Importador
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Mensagens -->
    <?php if (isset($_SESSION['success'])): ?>
        <script>
            window.pendingMessages = window.pendingMessages || [];
            window.pendingMessages.push({
                type: 'success',
                message: '<?= htmlspecialchars($_SESSION['success'], ENT_QUOTES) ?>'
            });
        </script>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <script>
            window.pendingMessages = window.pendingMessages || [];
            window.pendingMessages.push({
                type: 'error',
                message: '<?= htmlspecialchars($_SESSION['error'], ENT_QUOTES) ?>'
            });
        </script>
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
            <form method="GET" action="<?= BASE_URL ?>clients">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="name" class="form-label">Razão Social</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="<?= htmlspecialchars($filters['name']) ?>" placeholder="Buscar por nome">
                    </div>
                    <div class="col-md-2">
                        <label for="document" class="form-label">CNPJ</label>
                        <input type="text" class="form-control" id="document" name="document"
                               value="<?= htmlspecialchars($filters['document']) ?>" placeholder="Documento">
                    </div>
                    <input type="hidden" name="type" value="PJ">
                    <div class="col-md-2">
                        <label for="city" class="form-label">Cidade</label>
                        <input type="text" class="form-control" id="city" name="city"
                               value="<?= htmlspecialchars($filters['city']) ?>" placeholder="Cidade">
                    </div>
                    <div class="col-md-1">
                        <label for="state" class="form-label">UF</label>
                        <input type="text" class="form-control" id="state" name="state"
                               value="<?= htmlspecialchars($filters['state']) ?>" placeholder="UF" maxlength="2">
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

    <!-- Lista de Clientes -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-list"></i> Importadores (<?= count($clients) ?>)
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($clients)): ?>
                <div class="text-center py-4">
                    <i class="bi bi-people display-1 text-muted"></i>
                    <h5 class="mt-3">Nenhum importador encontrado</h5>
                    <p class="text-muted">Comece cadastrando o primeiro importador do sistema.</p>
                    <?php if (Permission::check('clients.create')): ?>
                        <a href="<?= BASE_URL ?>clients/create" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Cadastrar Primeiro Importador
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Razão Social</th>
                                <th>Documento</th>
                                <th>Cidade/Estado</th>
                                <th>Contato</th>
                                <th>Status</th>
                                <th width="120">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clients as $client): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($client['name']) ?></strong>
                                        <?php if (!empty($client['contact_name'])): ?>
                                            <br><small class="text-muted">Contato: <?= htmlspecialchars($client['contact_name']) ?></small>
                                        <?php endif; ?>
                                        <?php if (!empty($client['incoterm'])): ?>
                                            <br><small class="text-primary">Incoterm: <?= htmlspecialchars($client['incoterm']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="font-monospace"><?= htmlspecialchars($client['document']) ?></span>
                                        <?php if (!empty($client['ie'])): ?>
                                            <br><small class="text-muted">IE: <?= htmlspecialchars($client['ie']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($client['city'])): ?>
                                            <?= htmlspecialchars($client['city']) ?>
                                            <?php if (!empty($client['state'])): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($client['state']) ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($client['email'])): ?>
                                            <small>
                                                <i class="bi bi-envelope"></i> <?= htmlspecialchars($client['email']) ?>
                                            </small>
                                        <?php endif; ?>
                                        <?php if (!empty($client['phone'])): ?>
                                            <br><small>
                                                <i class="bi bi-telephone"></i> <?= htmlspecialchars($client['phone']) ?>
                                            </small>
                                        <?php endif; ?>
                                        <?php if (!empty($client['mobile'])): ?>
                                            <br><small>
                                                <i class="bi bi-phone"></i> <?= htmlspecialchars($client['mobile']) ?>
                                            </small>
                                        <?php endif; ?>
                                        <?php if (empty($client['email']) && empty($client['phone']) && empty($client['mobile'])): ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($client['is_active']): ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if (Permission::check('clients.create')): ?>
                                                <a href="<?= BASE_URL ?>clients/edit?id=<?= $client['id'] ?>"
                                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            <?php endif; ?>

                                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        title="Excluir" onclick="deleteClient(<?= $client['id'] ?>, '<?= htmlspecialchars($client['name'], ENT_QUOTES) ?>')">
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
function deleteClient(id, name) {
    showConfirm(
        'Confirmar Exclusão',
        `Você tem certeza que deseja excluir o importador "${name}"? Esta ação não pode ser desfeita!`,
        function() {
            fetch('<?= BASE_URL ?>api/clients/delete', {
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
                showError('Erro ao excluir importador');
            });
        }
    );
}
</script>