<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3">
                        <i class="bi bi-box"></i> Gerenciar Produtos
                    </h1>
                    <p class="text-muted">Cadastro e controle de produtos para importação</p>
                </div>
                <?php if (Permission::check('products.create')): ?>
                    <div>
                        <a href="<?= BASE_URL ?>products/create" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Novo Produto
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
            <form method="GET" action="<?= BASE_URL ?>products">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="name" class="form-label">Nome do Produto</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="<?= htmlspecialchars($filters['name']) ?>" placeholder="Buscar por nome">
                    </div>
                    <div class="col-md-2">
                        <label for="ncm" class="form-label">NCM</label>
                        <input type="text" class="form-control" id="ncm" name="ncm"
                               value="<?= htmlspecialchars($filters['ncm']) ?>" placeholder="0101.21.00 ou 01012100">
                        <small class="form-text text-muted">Aceita formato com ou sem pontos</small>
                    </div>
                    <div class="col-md-2">
                        <label for="division_type" class="form-label">Tipo Divisão</label>
                        <select class="form-select select2" id="division_type" name="division_type">
                            <option value="">Todos</option>
                            <option value="KG" <?= $filters['division_type'] === 'KG' ? 'selected' : '' ?>>Por KG</option>
                            <option value="QUANTIDADE" <?= $filters['division_type'] === 'QUANTIDADE' ? 'selected' : '' ?>>Por Quantidade</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="is_active" class="form-label">Status</label>
                        <select class="form-select select2" id="is_active" name="is_active">
                            <option value="">Todos</option>
                            <option value="1" <?= $filters['is_active'] === 1 ? 'selected' : '' ?>>Ativo</option>
                            <option value="0" <?= $filters['is_active'] === 0 ? 'selected' : '' ?>>Inativo</option>
                        </select>
                    </div>
                    <div class="col-md-3">
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

    <!-- Lista de Produtos -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="bi bi-list"></i> Produtos
            </h5>
            <?php if ($pagination['total_items'] > 0): ?>
                <small class="text-muted">
                    Mostrando <?= $pagination['start_item'] ?> a <?= $pagination['end_item'] ?> de <?= $pagination['total_items'] ?> produtos
                </small>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if (empty($products)): ?>
                <div class="text-center py-4">
                    <i class="bi bi-box display-1 text-muted"></i>
                    <h5 class="mt-3">Nenhum produto encontrado</h5>
                    <p class="text-muted">Comece cadastrando o primeiro produto do sistema.</p>
                    <?php if ($_SESSION['user_role'] !== 'viewer'): ?>
                        <a href="<?= BASE_URL ?>products/create" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Cadastrar Primeiro Produto
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>NCM</th>
                                <th>Nome</th>
                                <th>Divisão</th>
                                <th>RFB Min/Max</th>
                                <th>Alíquotas</th>
                                <th>Status</th>
                                <th width="120">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-info"><?= htmlspecialchars($product['ncm']) ?></span>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($product['name']) ?></strong>
                                        <?php if (!empty($product['description'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($product['description']) ?></small>
                                        <?php endif; ?>
                                        <?php if ($product['weight_kg']): ?>
                                            <br><small class="text-info">Peso: <?= number_format($product['weight_kg'], 3) ?>kg</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($product['division_type'] === 'KG'): ?>
                                            <span class="badge bg-success">Por KG</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Por Quantidade</span>
                                        <?php endif; ?>
                                        <?php if ($product['unit']): ?>
                                            <br><small class="text-muted">Un: <?= htmlspecialchars($product['unit']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($product['rfb_min'] || $product['rfb_max']): ?>
                                            <small>
                                                <?php if ($product['rfb_min']): ?>
                                                    Min: $<?= number_format($product['rfb_min'], 2) ?>
                                                <?php endif; ?>
                                                <?php if ($product['rfb_min'] && $product['rfb_max']): ?>
                                                    <br>
                                                <?php endif; ?>
                                                <?php if ($product['rfb_max']): ?>
                                                    Max: $<?= number_format($product['rfb_max'], 2) ?>
                                                <?php endif; ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small>
                                            II: <?= number_format($product['ii_rate'], 1) ?>%<br>
                                            IPI: <?= number_format($product['ipi_rate'], 1) ?>%<br>
                                            ICMS: <?= number_format($product['icms_rate'], 1) ?>%
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($product['is_active']): ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if ($_SESSION['user_role'] !== 'viewer'): ?>
                                                <a href="<?= BASE_URL ?>products/edit?id=<?= $product['id'] ?>"
                                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            <?php endif; ?>

                                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        title="Excluir" onclick="deleteProduct(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>')">
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

        <!-- Paginação -->
        <?php if ($pagination['total_pages'] > 1): ?>
            <div class="card-footer">
                <nav aria-label="Paginação de produtos">
                    <ul class="pagination justify-content-center mb-0">
                        <!-- Primeira página -->
                        <?php if ($pagination['current_page'] > 3): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= BASE_URL ?>products?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">
                                    <i class="bi bi-chevron-double-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Página anterior -->
                        <?php if ($pagination['has_previous']): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= BASE_URL ?>products?<?= http_build_query(array_merge($_GET, ['page' => $pagination['previous_page']])) ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <i class="bi bi-chevron-left"></i>
                                </span>
                            </li>
                        <?php endif; ?>

                        <!-- Páginas numeradas -->
                        <?php
                        $startPage = max(1, $pagination['current_page'] - 2);
                        $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);

                        for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                            <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                <?php if ($i == $pagination['current_page']): ?>
                                    <span class="page-link"><?= $i ?></span>
                                <?php else: ?>
                                    <a class="page-link" href="<?= BASE_URL ?>products?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endfor; ?>

                        <!-- Próxima página -->
                        <?php if ($pagination['has_next']): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= BASE_URL ?>products?<?= http_build_query(array_merge($_GET, ['page' => $pagination['next_page']])) ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <i class="bi bi-chevron-right"></i>
                                </span>
                            </li>
                        <?php endif; ?>

                        <!-- Última página -->
                        <?php if ($pagination['current_page'] < $pagination['total_pages'] - 2): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= BASE_URL ?>products?<?= http_build_query(array_merge($_GET, ['page' => $pagination['total_pages']])) ?>">
                                    <i class="bi bi-chevron-double-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteProduct(id, name) {
    showConfirm(
        'Confirmar Exclusão',
        `Você tem certeza que deseja excluir o produto "${name}"? Esta ação não pode ser desfeita!`,
        function() {
            fetch('<?= BASE_URL ?>api/products/delete', {
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
                showError('Erro ao excluir produto');
            });
        }
    );
}
</script>