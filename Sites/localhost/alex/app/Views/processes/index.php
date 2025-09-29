<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3">
                        <i class="bi bi-file-earmark-text"></i> Gerenciar Processos
                    </h1>
                    <p class="text-muted">Controle de processos de importação</p>
                </div>
                <?php if (Permission::check('processes.create')): ?>
                    <div>
                        <a href="<?= BASE_URL ?>processes/create" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Novo Processo
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
            <form method="GET" action="<?= BASE_URL ?>processes">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label for="code" class="form-label">Código</label>
                        <input type="text" class="form-control" id="code" name="code"
                               value="<?= htmlspecialchars($filters['code']) ?>" placeholder="Ex: IMP001">
                    </div>
                    <div class="col-md-3">
                        <label for="client_id" class="form-label">Importador</label>
                        <select class="form-select select2" id="client_id" name="client_id">
                            <option value="">Todos os importadores</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id'] ?>"
                                        <?= $filters['client_id'] == $client['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($client['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select select2" id="status" name="status">
                            <option value="">Todos os status</option>
                            <?php foreach ($status_options as $key => $label): ?>
                                <option value="<?= $key ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="type" class="form-label">Tipo</label>
                        <select class="form-select select2" id="type" name="type">
                            <option value="">Todos os tipos</option>
                            <?php foreach ($type_options as $key => $label): ?>
                                <option value="<?= $key ?>" <?= $filters['type'] === $key ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="modal" class="form-label">Modal</label>
                        <select class="form-select select2" id="modal" name="modal">
                            <option value="">Todos os modais</option>
                            <?php foreach ($modal_options as $key => $label): ?>
                                <option value="<?= $key ?>" <?= $filters['modal'] === $key ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label>&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">Data Processo (De)</label>
                        <input type="date" class="form-control" id="date_from" name="date_from"
                               value="<?= htmlspecialchars($filters['date_from']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">Data Processo (Até)</label>
                        <input type="date" class="form-control" id="date_to" name="date_to"
                               value="<?= htmlspecialchars($filters['date_to']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <div class="d-grid">
                            <a href="<?= BASE_URL ?>processes" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Limpar Filtros
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Processos -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-list"></i> Processos (<?= count($processes) ?>)
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($processes)): ?>
                <div class="text-center py-4">
                    <i class="bi bi-file-earmark-text display-1 text-muted"></i>
                    <h5 class="mt-3">Nenhum processo encontrado</h5>
                    <p class="text-muted">Comece cadastrando o primeiro processo de importação.</p>
                    <?php if (Permission::check('processes.create')): ?>
                        <a href="<?= BASE_URL ?>processes/create" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Cadastrar Primeiro Processo
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Importador</th>
                                <th>Tipo/Modal</th>
                                <th>Status</th>
                                <th>Data Processo</th>
                                <th>Valor CIF</th>
                                <th>Taxa Câmbio</th>
                                <th width="120">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($processes as $process): ?>
                                <tr>
                                    <td>
                                        <a href="<?= BASE_URL ?>processes/edit?id=<?= $process['id'] ?>" class="text-decoration-none">
                                            <strong><?= htmlspecialchars($process['code']) ?></strong>
                                        </a>
                                        <?php if (!empty($process['bl_number'])): ?>
                                            <br><small class="text-muted">BL: <?= htmlspecialchars($process['bl_number']) ?></small>
                                        <?php endif; ?>
                                        <?php if (!empty($process['container_number'])): ?>
                                            <br><small class="text-primary">Container: <?= htmlspecialchars($process['container_number']) ?></small>
                                        <?php endif; ?>
                                        <?php if (!empty($process['destination_port_name'])): ?>
                                            <br><small class="text-info">Porto: <?= htmlspecialchars($process['destination_port_name']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($process['client_name']) ?></strong>
                                        <?php if ($process['client_type'] === 'PF'): ?>
                                            <span class="badge bg-info ms-1">PF</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary ms-1">PJ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= $type_options[$process['type']] ?></span>
                                        <br><small class="text-muted"><?= $modal_options[$process['modal']] ?></small>
                                        <br><small class="text-info"><?= htmlspecialchars($process['incoterm']) ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = match($process['status']) {
                                            'PRE_EMBARQUE' => 'bg-secondary',
                                            'EMBARCADO' => 'bg-primary',
                                            'REGISTRADO' => 'bg-info',
                                            'CANAL_VERDE' => 'bg-success',
                                            'CANAL_VERMELHO' => 'bg-danger',
                                            'CANAL_CINZA' => 'bg-warning',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $statusClass ?>"><?= $status_options[$process['status']] ?></span>
                                    </td>
                                    <td>
                                        <strong><?= date('d/m/Y', strtotime($process['process_date'])) ?></strong>
                                        <?php if (!empty($process['arrival_date'])): ?>
                                            <br><small class="text-muted">Chegada: <?= date('d/m/Y', strtotime($process['arrival_date'])) ?></small>
                                        <?php endif; ?>
                                        <?php if (!empty($process['clearance_date'])): ?>
                                            <br><small class="text-success">Desembaraço: <?= date('d/m/Y', strtotime($process['clearance_date'])) ?></small>
                                        <?php endif; ?>

                                        <!-- Free Time Status -->
                                        <?php if (!empty($process['confirmed_arrival_date']) && isset($process['free_time_days'])): ?>
                                            <?php
                                            $arrivalDate = new DateTime($process['confirmed_arrival_date']);
                                            $freeTimeEnd = clone $arrivalDate;
                                            $freeTimeEnd->add(new DateInterval('P' . $process['free_time_days'] . 'D'));
                                            $today = new DateTime();
                                            $daysLeft = $today->diff($freeTimeEnd)->days * ($today < $freeTimeEnd ? 1 : -1);

                                            if ($daysLeft > 5) {
                                                $statusClass = 'text-success';
                                                $statusIcon = 'bi-check-circle-fill';
                                            } elseif ($daysLeft >= 2) {
                                                $statusClass = 'text-warning';
                                                $statusIcon = 'bi-exclamation-triangle-fill';
                                            } else {
                                                $statusClass = 'text-danger';
                                                $statusIcon = 'bi-exclamation-circle-fill';
                                            }
                                            ?>
                                            <br><small class="<?= $statusClass ?>">
                                                <i class="bi <?= $statusIcon ?>"></i>
                                                Free Time:
                                                <?php if ($daysLeft > 0): ?>
                                                    <?= $daysLeft ?> dias restantes
                                                <?php elseif ($daysLeft == 0): ?>
                                                    Vence hoje
                                                <?php else: ?>
                                                    Vencido há <?= abs($daysLeft) ?> dias
                                                <?php endif; ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong>USD <?= number_format($process['total_cif_usd'], 2) ?></strong>
                                        <br><small class="text-muted">BRL <?= number_format($process['total_cif_brl'], 2) ?></small>
                                    </td>
                                    <td>
                                        <span class="font-monospace"><?= number_format($process['exchange_rate'], 4) ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if (Permission::check('process_items.view')): ?>
                                                <a href="<?= BASE_URL ?>process-items?process_id=<?= $process['id'] ?>&auto_save=1"
                                                   class="btn btn-sm btn-outline-info" title="Gerenciar Itens">
                                                    <i class="bi bi-box-seam"></i>
                                                </a>
                                            <?php endif; ?>

                                            <?php if (Permission::check('processes.edit')): ?>
                                                <a href="<?= BASE_URL ?>processes/edit?id=<?= $process['id'] ?>"
                                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            <?php endif; ?>

                                            <?php if (Permission::check('processes.delete')): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        title="Excluir" onclick="deleteProcess(<?= $process['id'] ?>, '<?= htmlspecialchars($process['code'], ENT_QUOTES) ?>')">
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
function deleteProcess(id, code) {
    showConfirm(
        'Confirmar Exclusão',
        `Você tem certeza que deseja excluir o processo "${code}"? Esta ação não pode ser desfeita!`,
        function() {
            fetch('<?= BASE_URL ?>api/processes/delete', {
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
                showError('Erro ao excluir processo');
            });
        }
    );
}
</script>