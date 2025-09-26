<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3">
                        <i class="bi bi-eye"></i> Detalhes do Log #<?= $log['id'] ?>
                    </h1>
                    <p class="text-muted">Informações detalhadas do log de auditoria</p>
                </div>
                <div>
                    <a href="<?= BASE_URL ?>audit" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Informações Básicas -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle"></i> Informações Básicas
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>ID do Log:</strong></td>
                            <td><?= $log['id'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>Data/Hora:</strong></td>
                            <td><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Ação:</strong></td>
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
                        </tr>
                        <tr>
                            <td><strong>Tabela:</strong></td>
                            <td><code><?= htmlspecialchars($log['table_name']) ?></code></td>
                        </tr>
                        <tr>
                            <td><strong>ID do Registro:</strong></td>
                            <td>
                                <?php if ($log['record_id']): ?>
                                    <a href="<?= BASE_URL ?>audit/history?table=<?= urlencode($log['table_name']) ?>&id=<?= $log['record_id'] ?>"
                                       class="text-decoration-none">
                                        #<?= $log['record_id'] ?>
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person"></i> Informações do Usuário
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Nome:</strong></td>
                            <td><?= htmlspecialchars($log['user_name'] ?? 'Sistema') ?></td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td><?= htmlspecialchars($log['user_email'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <td><strong>ID do Usuário:</strong></td>
                            <td><?= $log['user_id'] ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <td><strong>Endereço IP:</strong></td>
                            <td><code><?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?></code></td>
                        </tr>
                        <tr>
                            <td><strong>User Agent:</strong></td>
                            <td>
                                <?php if ($log['user_agent']): ?>
                                    <small class="text-muted" title="<?= htmlspecialchars($log['user_agent']) ?>">
                                        <?= htmlspecialchars(substr($log['user_agent'], 0, 50)) ?>...
                                    </small>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Valores Antigos e Novos -->
    <div class="row">
        <?php if ($log['old_values']): ?>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-arrow-left-circle text-danger"></i> Valores Antigos
                        </h5>
                    </div>
                    <div class="card-body">
                        <pre class="bg-light p-3 rounded"><code><?= htmlspecialchars(json_encode(json_decode($log['old_values']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></code></pre>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($log['new_values']): ?>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-arrow-right-circle text-success"></i> Valores Novos
                        </h5>
                    </div>
                    <div class="card-body">
                        <pre class="bg-light p-3 rounded"><code><?= htmlspecialchars(json_encode(json_decode($log['new_values']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></code></pre>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!$log['old_values'] && !$log['new_values']): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i>
                    Este log não possui valores de dados armazenados.
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Botões de Ação -->
    <div class="row mt-4">
        <div class="col-12 text-center">
            <a href="<?= BASE_URL ?>audit" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar à Lista
            </a>
            <?php if ($log['record_id']): ?>
                <a href="<?= BASE_URL ?>audit/history?table=<?= urlencode($log['table_name']) ?>&id=<?= $log['record_id'] ?>"
                   class="btn btn-primary">
                    <i class="bi bi-clock-history"></i> Ver Histórico Completo
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>