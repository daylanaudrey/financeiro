<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3">
                        <i class="bi bi-shield-check"></i> Logs de Auditoria
                    </h1>
                    <p class="text-muted">Controle e rastreamento de ações no sistema</p>
                </div>
                <div>
                    <a href="<?= BASE_URL ?>audit/export<?= http_build_query($_GET) ? '?' . http_build_query($_GET) : '' ?>" class="btn btn-outline-success me-2">
                        <i class="bi bi-download"></i> Exportar CSV
                    </a>
                    <a href="<?= BASE_URL ?>audit/cleanup" class="btn btn-outline-danger">
                        <i class="bi bi-trash"></i> Limpeza
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensagens -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?= htmlspecialchars($_SESSION['success'], ENT_QUOTES) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES) ?>
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
            <form method="GET" action="<?= BASE_URL ?>audit">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="action" class="form-label">Ação</label>
                        <select name="action" id="action" class="form-select">
                            <option value="">Todas as ações</option>
                            <?php foreach ($uniqueActions as $action): ?>
                                <option value="<?= htmlspecialchars($action) ?>"
                                    <?= $filters['action'] === $action ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($action) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="table_name" class="form-label">Tabela</label>
                        <select name="table_name" id="table_name" class="form-select">
                            <option value="">Todas as tabelas</option>
                            <?php foreach ($uniqueTables as $table): ?>
                                <option value="<?= htmlspecialchars($table) ?>"
                                    <?= $filters['table_name'] === $table ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($table) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="user_id" class="form-label">Usuário</label>
                        <select name="user_id" id="user_id" class="form-select">
                            <option value="">Todos os usuários</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>"
                                    <?= $filters['user_id'] == $user['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user['name'] . ' (' . $user['email'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Data Inicial</label>
                        <input type="date" name="start_date" id="start_date" class="form-control"
                               value="<?= htmlspecialchars($filters['start_date']) ?>">
                    </div>

                    <div class="col-md-3">
                        <label for="end_date" class="form-label">Data Final</label>
                        <input type="date" name="end_date" id="end_date" class="form-control"
                               value="<?= htmlspecialchars($filters['end_date']) ?>">
                    </div>

                    <div class="col-md-9 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> Filtrar
                        </button>
                        <a href="<?= BASE_URL ?>audit" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-file-text display-4 text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title">Total de Logs</h6>
                            <h3 class="card-text"><?= number_format($totalLogs) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-list-ol display-4 text-info"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title">Página Atual</h6>
                            <h3 class="card-text"><?= $currentPage ?> de <?= max(1, $totalPages) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Logs -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-table"></i> Logs de Auditoria
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($logs)): ?>
                <div class="text-center py-4">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <p class="text-muted mt-3">Nenhum log encontrado com os filtros aplicados.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Data/Hora</th>
                                <th>Usuário</th>
                                <th>Ação</th>
                                <th>Tabela</th>
                                <th>Registro</th>
                                <th>IP</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= $log['id'] ?></td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($log['user_name']): ?>
                                            <strong><?= htmlspecialchars($log['user_name']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($log['user_email']) ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">Sistema</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge
                                            <?php
                                            switch($log['action']) {
                                                case 'CREATE': echo 'bg-success'; break;
                                                case 'UPDATE': echo 'bg-warning text-dark'; break;
                                                case 'DELETE': echo 'bg-danger'; break;
                                                case 'LOGIN': echo 'bg-info'; break;
                                                case 'LOGOUT': echo 'bg-secondary'; break;
                                                default: echo 'bg-primary'; break;
                                            }
                                            ?>">
                                            <?= htmlspecialchars($log['action']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <code><?= htmlspecialchars($log['table_name']) ?></code>
                                    </td>
                                    <td>
                                        <?php if ($log['record_id']): ?>
                                            <a href="<?= BASE_URL ?>audit/history?table=<?= urlencode($log['table_name']) ?>&id=<?= $log['record_id'] ?>"
                                               class="text-decoration-none">
                                                #<?= $log['record_id'] ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="font-monospace"><?= htmlspecialchars($log['ip_address'] ?? '-') ?></small>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= BASE_URL ?>audit/show/<?= $log['id'] ?>"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Ver detalhes">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Navegação de páginas" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($currentPage > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= BASE_URL ?>audit?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>">
                                        <i class="bi bi-chevron-left"></i> Anterior
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                                <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= BASE_URL ?>audit?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($currentPage < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= BASE_URL ?>audit?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>">
                                        Próxima <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>