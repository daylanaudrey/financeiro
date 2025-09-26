<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3">
                        <i class="bi bi-clock-history"></i> Histórico - <?= htmlspecialchars($tableName) ?> #<?= $recordId ?>
                    </h1>
                    <p class="text-muted">Todas as alterações realizadas neste registro</p>
                </div>
                <div>
                    <a href="<?= BASE_URL ?>audit" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline de Logs -->
    <?php if (empty($logs)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h5 class="mt-3">Nenhum histórico encontrado</h5>
                <p class="text-muted">Este registro não possui logs de auditoria.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-ul"></i> Linha do Tempo (<?= count($logs) ?> eventos)
                </h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php foreach ($logs as $index => $log): ?>
                        <div class="timeline-item <?= $index === 0 ? 'timeline-item-latest' : '' ?>">
                            <div class="timeline-marker">
                                <div class="timeline-marker-icon
                                    <?php
                                    switch($log['action']) {
                                        case 'CREATE': echo 'bg-success'; break;
                                        case 'UPDATE': echo 'bg-warning'; break;
                                        case 'DELETE': echo 'bg-danger'; break;
                                        default: echo 'bg-primary'; break;
                                    }
                                    ?>">
                                    <i class="bi
                                        <?php
                                        switch($log['action']) {
                                            case 'CREATE': echo 'bi-plus'; break;
                                            case 'UPDATE': echo 'bi-pencil'; break;
                                            case 'DELETE': echo 'bi-trash'; break;
                                            default: echo 'bi-activity'; break;
                                        }
                                        ?>"></i>
                                </div>
                            </div>

                            <div class="timeline-content">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge
                                                    <?php
                                                    switch($log['action']) {
                                                        case 'CREATE': echo 'bg-success'; break;
                                                        case 'UPDATE': echo 'bg-warning text-dark'; break;
                                                        case 'DELETE': echo 'bg-danger'; break;
                                                        default: echo 'bg-primary'; break;
                                                    }
                                                    ?>">
                                                    <?= htmlspecialchars($log['action']) ?>
                                                </span>
                                                <strong class="ms-2">
                                                    <?php
                                                    switch($log['action']) {
                                                        case 'CREATE': echo 'Registro criado'; break;
                                                        case 'UPDATE': echo 'Registro atualizado'; break;
                                                        case 'DELETE': echo 'Registro excluído'; break;
                                                        default: echo 'Ação realizada'; break;
                                                    }
                                                    ?>
                                                </strong>
                                            </div>
                                            <small class="text-muted">
                                                <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                                            </small>
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        <!-- Informações do Usuário -->
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <strong>Usuário:</strong>
                                                <?= htmlspecialchars($log['user_name'] ?? 'Sistema') ?>
                                                <?php if ($log['user_email']): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($log['user_email']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>IP:</strong>
                                                <code><?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?></code>
                                            </div>
                                        </div>

                                        <!-- Mudanças de Valores -->
                                        <?php if ($log['old_values'] || $log['new_values']): ?>
                                            <div class="row">
                                                <?php if ($log['old_values']): ?>
                                                    <div class="col-md-6">
                                                        <h6 class="text-danger">
                                                            <i class="bi bi-arrow-left-circle"></i> Valores Antigos:
                                                        </h6>
                                                        <div class="bg-light p-2 rounded">
                                                            <small>
                                                                <?php
                                                                $oldValues = json_decode($log['old_values'], true);
                                                                if ($oldValues) {
                                                                    foreach ($oldValues as $key => $value) {
                                                                        echo "<strong>" . htmlspecialchars($key) . ":</strong> " . htmlspecialchars($value) . "<br>";
                                                                    }
                                                                }
                                                                ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ($log['new_values']): ?>
                                                    <div class="col-md-6">
                                                        <h6 class="text-success">
                                                            <i class="bi bi-arrow-right-circle"></i> Valores Novos:
                                                        </h6>
                                                        <div class="bg-light p-2 rounded">
                                                            <small>
                                                                <?php
                                                                $newValues = json_decode($log['new_values'], true);
                                                                if ($newValues) {
                                                                    foreach ($newValues as $key => $value) {
                                                                        echo "<strong>" . htmlspecialchars($key) . ":</strong> " . htmlspecialchars($value) . "<br>";
                                                                    }
                                                                }
                                                                ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Link para Detalhes -->
                                        <div class="mt-3">
                                            <a href="<?= BASE_URL ?>audit/show/<?= $log['id'] ?>"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> Ver detalhes completos
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 0;
}

.timeline-item {
    position: relative;
    padding-left: 60px;
    margin-bottom: 30px;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: 20px;
    top: 40px;
    bottom: -30px;
    width: 2px;
    background: #dee2e6;
}

.timeline-item:last-child:before {
    display: none;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 20px;
    width: 40px;
    height: 40px;
}

.timeline-marker-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
    border: 3px solid white;
    box-shadow: 0 0 0 3px #dee2e6;
}

.timeline-item-latest .timeline-marker-icon {
    box-shadow: 0 0 0 3px #007bff;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 3px #007bff;
    }
    50% {
        box-shadow: 0 0 0 6px rgba(0, 123, 255, 0.3);
    }
    100% {
        box-shadow: 0 0 0 3px #007bff;
    }
}
</style>