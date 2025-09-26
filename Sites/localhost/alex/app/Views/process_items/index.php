<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3">
                        <i class="bi bi-box-seam"></i> Itens do Processo
                    </h1>
                    <p class="text-muted">
                        Processo: <strong><?= htmlspecialchars($process['code']) ?></strong> -
                        Cliente: <strong><?= htmlspecialchars($process['client_name']) ?></strong>
                    </p>
                </div>
                <div>
                    <?php if (Permission::check('process_items.create')): ?>
                        <a href="<?= BASE_URL ?>process-items/create?process_id=<?= $process['id'] ?>" class="btn btn-primary me-2">
                            <i class="bi bi-plus-circle"></i> Adicionar Item
                        </a>
                    <?php endif; ?>
                    <?php if (Permission::check('processes.edit')): ?>
                        <a href="<?= BASE_URL ?>processes/edit?id=<?= $process['id'] ?>" class="btn btn-warning me-2">
                            <i class="bi bi-pencil-square"></i> Editar Processo
                        </a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>processes" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar aos Processos
                    </a>
                </div>
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

    <!-- Informações do Processo -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-info-circle"></i> Informações do Processo
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Código:</strong> <?= htmlspecialchars($process['code']) ?><br>
                    <strong>Tipo:</strong> <?= $process['type'] === 'NUMERARIO' ? 'Numerário' : 'Mapa' ?><br>
                    <strong>Modal:</strong> <?= ucfirst(strtolower($process['modal'])) ?>
                </div>
                <div class="col-md-3">
                    <strong>Status:</strong>
                    <?php
                    $statusLabels = Process::getStatusOptions();
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
                    <span class="badge <?= $statusClass ?>"><?= $statusLabels[$process['status']] ?></span>
                    <br>
                    <strong>Incoterm:</strong> <?= htmlspecialchars($process['incoterm']) ?>
                </div>
                <div class="col-md-3">
                    <strong>Data Processo:</strong> <?= date('d/m/Y', strtotime($process['process_date'])) ?><br>
                    <strong>Taxa Câmbio:</strong> R$ <?= number_format($process['exchange_rate'], 4, ',', '.') ?>
                </div>
                <div class="col-md-3">
                    <strong>Total CIF:</strong> USD <?= number_format($process['total_cif_usd'] ?? 0, 2, ',', '.') ?><br>
                    <strong>Total BRL:</strong> R$ <?= number_format($process['total_cif_brl'] ?? 0, 2, ',', '.') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Itens -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list"></i> Itens do Processo (<?= count($items) ?>)
                </h5>
                <?php if (!empty($items)): ?>
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" id="search-items" placeholder="Buscar por produto, NCM ou notas...">
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($items)): ?>
                <div class="text-center py-4">
                    <i class="bi bi-box-seam display-1 text-muted"></i>
                    <h5 class="mt-3">Nenhum item adicionado</h5>
                    <p class="text-muted">Comece adicionando produtos a este processo.</p>
                    <?php if (Permission::check('process_items.create')): ?>
                        <a href="<?= BASE_URL ?>process-items/create?process_id=<?= $process['id'] ?>" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Adicionar Primeiro Item
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Preço Unit.</th>
                                <th>FOB USD</th>
                                <th>Frete USD</th>
                                <th>CIF USD</th>
                                <th>CIF BRL</th>
                                <th>Impostos</th>
                                <th>Custo Total</th>
                                <th width="120">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($item['description']) ?></strong>
                                        <br><small class="text-muted">NCM: <?= htmlspecialchars($item['ncm']) ?></small>
                                        <?php if (!empty($item['notes'])): ?>
                                            <br><small class="text-info"><?= htmlspecialchars($item['notes']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= number_format($item['quantity'], 3, ',', '.') ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($item['unit']) ?></small>
                                        <?php if ($item['weight_kg'] > 0): ?>
                                            <br><small class="text-secondary"><?= number_format($item['weight_kg'], 3, ',', '.') ?> kg</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="font-monospace">$ <?= number_format($item['unit_price_usd'], 4, ',', '.') ?></span>
                                    </td>
                                    <td>
                                        <strong>$ <?= number_format($item['total_fob_usd'], 2, ',', '.') ?></strong>
                                        <?php if ($item['freight_usd'] > 0): ?>
                                            <br><small class="text-muted">+ Frete: $ <?= number_format($item['freight_usd'], 2, ',', '.') ?></small>
                                        <?php endif; ?>
                                        <?php if ($item['insurance_usd'] > 0): ?>
                                            <br><small class="text-muted">+ Seguro: $ <?= number_format($item['insurance_usd'], 2, ',', '.') ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong>$ <?= number_format($item['freight_ttl_kg'] ?? 0, 2, ',', '.') ?></strong>
                                        <br><small class="text-muted">TTL/KG</small>
                                    </td>
                                    <td>
                                        <strong>$ <?= number_format($item['cif_usd'], 2, ',', '.') ?></strong>
                                    </td>
                                    <td>
                                        <strong>R$ <?= number_format($item['cif_brl'], 2, ',', '.') ?></strong>
                                    </td>
                                    <td>
                                        <small>
                                            II: R$ <?= number_format($item['ii_value'], 2, ',', '.') ?><br>
                                            IPI: R$ <?= number_format($item['ipi_value'], 2, ',', '.') ?><br>
                                            PIS/COFINS: R$ <?= number_format($item['pis_value'] + $item['cofins_value'], 2, ',', '.') ?><br>
                                            <strong>Total: R$ <?= number_format($item['total_taxes'], 2, ',', '.') ?></strong>
                                        </small>
                                    </td>
                                    <td>
                                        <strong class="text-success">R$ <?= number_format($item['total_cost_brl'], 2, ',', '.') ?></strong>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if (Permission::check('process_items.edit')): ?>
                                                <a href="<?= BASE_URL ?>process-items/edit?id=<?= $item['id'] ?>"
                                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            <?php endif; ?>

                                            <?php if (Permission::check('process_items.delete')): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        title="Excluir" onclick="deleteItem(<?= $item['id'] ?>, '<?= htmlspecialchars($item['description'], ENT_QUOTES) ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3">TOTAIS</th>
                                <th>$ <?= number_format($totals['total_fob_usd'], 2, ',', '.') ?></th>
                                <th>$ <?= number_format($totals['total_freight_ttl_kg'] ?? 0, 2, ',', '.') ?></th>
                                <th>$ <?= number_format($totals['total_cif_usd'], 2, ',', '.') ?></th>
                                <th>R$ <?= number_format($totals['total_cif_brl'], 2, ',', '.') ?></th>
                                <th>R$ <?= number_format($totals['total_taxes'], 2, ',', '.') ?></th>
                                <th class="text-success"><strong>R$ <?= number_format($totals['total_cost_brl'], 2, ',', '.') ?></strong></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function deleteItem(id, description) {
    showConfirm(
        'Confirmar Exclusão',
        `Você tem certeza que deseja excluir o item "${description}"? Esta ação não pode ser desfeita!`,
        function() {
            fetch('<?= BASE_URL ?>api/process-items/delete', {
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
                showError('Erro ao excluir item');
            });
        }
    );
}

// Funcionalidade de busca de itens
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-items');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const tableRows = document.querySelectorAll('tbody tr');
            let visibleCount = 0;

            tableRows.forEach(function(row) {
                // Pegar o texto de todas as colunas relevantes (produto, NCM, notas)
                const productCell = row.cells[0]; // Coluna do produto
                const productText = productCell.textContent.toLowerCase();

                // Verificar se o termo de busca está presente no texto
                const isVisible = productText.includes(searchTerm);

                if (isVisible) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Atualizar contador no cabeçalho
            const titleElement = document.querySelector('.card-title');
            if (titleElement) {
                const originalText = titleElement.textContent.split(' (')[0];
                if (searchTerm) {
                    titleElement.innerHTML = `<i class="bi bi-list"></i> ${originalText} (${visibleCount} encontrado${visibleCount !== 1 ? 's' : ''})`;
                } else {
                    titleElement.innerHTML = `<i class="bi bi-list"></i> ${originalText} (<?= count($items) ?>)`;
                }
            }

            // Mostrar mensagem se não encontrar resultados
            const tbody = document.querySelector('tbody');
            let noResultsRow = document.getElementById('no-results-row');

            if (visibleCount === 0 && searchTerm) {
                if (!noResultsRow) {
                    noResultsRow = document.createElement('tr');
                    noResultsRow.id = 'no-results-row';
                    noResultsRow.innerHTML = `
                        <td colspan="10" class="text-center py-4 text-muted">
                            <i class="bi bi-search"></i><br>
                            Nenhum item encontrado para "${searchTerm}"
                        </td>
                    `;
                    tbody.appendChild(noResultsRow);
                }
                noResultsRow.style.display = '';
            } else if (noResultsRow) {
                noResultsRow.style.display = 'none';
            }
        });

        // Limpar busca com Escape
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                this.dispatchEvent(new Event('input'));
            }
        });
    }
});
</script>