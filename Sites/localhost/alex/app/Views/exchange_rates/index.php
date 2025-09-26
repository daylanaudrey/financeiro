<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3">
                        <i class="bi bi-currency-exchange"></i> Taxa de Câmbio PTAX
                    </h1>
                    <p class="text-muted">
                        Acompanhe o histórico das taxas de câmbio USD/BRL do Banco Central
                    </p>
                </div>
                <div>
                    <?php if (Permission::check('system.exchange_rates')): ?>
                        <button type="button" class="btn btn-primary" id="updateRateBtn">
                            <i class="bi bi-arrow-clockwise"></i> Atualizar Taxa
                        </button>
                    <?php endif; ?>
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

    <!-- Taxa Atual -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-currency-dollar"></i> Taxa Atual
                    </h5>
                </div>
                <div class="card-body text-center">
                    <?php if ($current_rate): ?>
                        <h2 class="text-primary" id="current-rate-display">
                            R$ <?= number_format($current_rate['rate'], 4, ',', '.') ?>
                        </h2>
                        <p class="text-muted mb-0">
                            <small>
                                <i class="bi bi-calendar"></i>
                                <?= date('d/m/Y', strtotime($current_rate['date'])) ?>
                            </small>
                        </p>
                        <p class="text-muted mb-0">
                            <small>
                                <i class="bi bi-bank"></i>
                                Fonte: <?= htmlspecialchars($current_rate['source']) ?>
                            </small>
                        </p>
                    <?php else: ?>
                        <h2 class="text-muted">N/A</h2>
                        <p class="text-muted">Taxa não disponível</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock"></i> Última Atualização
                    </h5>
                </div>
                <div class="card-body text-center">
                    <?php if ($last_update): ?>
                        <h5 class="text-info">
                            <?= date('d/m/Y', strtotime($last_update)) ?>
                        </h5>
                        <p class="text-muted mb-0">
                            <small>
                                Status:
                                <?php
                                $statusText = match($update_status ?? '') {
                                    'updated' => '<span class="text-success">Atualizada</span>',
                                    'failed' => '<span class="text-warning">Falha</span>',
                                    'error' => '<span class="text-danger">Erro</span>',
                                    default => '<span class="text-muted">Desconhecido</span>'
                                };
                                echo $statusText;
                                ?>
                            </small>
                        </p>
                    <?php else: ?>
                        <h5 class="text-muted">Nunca</h5>
                        <p class="text-muted mb-0">
                            <small>Primeira execução</small>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-graph-up"></i> Histórico
                    </h5>
                </div>
                <div class="card-body text-center">
                    <h5 class="text-success">
                        <?= count($rates) ?> registros
                    </h5>
                    <p class="text-muted mb-0">
                        <small>Últimos 30 dias</small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Histórico de Taxas -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-table"></i> Histórico de Taxas
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($rates)): ?>
                <div class="text-center py-4">
                    <i class="bi bi-currency-exchange display-1 text-muted"></i>
                    <h5 class="mt-3">Nenhuma taxa registrada</h5>
                    <p class="text-muted">Execute uma atualização para carregar as taxas PTAX.</p>
                    <?php if (Permission::check('system.exchange_rates')): ?>
                        <button type="button" class="btn btn-primary" onclick="document.getElementById('updateRateBtn').click()">
                            <i class="bi bi-arrow-clockwise"></i> Atualizar Agora
                        </button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Taxa (USD/BRL)</th>
                                <th>Fonte</th>
                                <th>Atualizada em</th>
                                <th>Variação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $previousRate = null;
                            foreach ($rates as $rate):
                                $variation = null;
                                $variationClass = '';
                                $variationIcon = '';

                                if ($previousRate !== null) {
                                    $variation = $rate['rate'] - $previousRate;
                                    $variationClass = $variation > 0 ? 'text-danger' : ($variation < 0 ? 'text-success' : 'text-muted');
                                    $variationIcon = $variation > 0 ? 'bi-arrow-up' : ($variation < 0 ? 'bi-arrow-down' : 'bi-dash');
                                }
                            ?>
                                <tr>
                                    <td>
                                        <strong><?= date('d/m/Y', strtotime($rate['date'])) ?></strong>
                                        <br><small class="text-muted"><?= date('D', strtotime($rate['date'])) ?></small>
                                    </td>
                                    <td>
                                        <span class="font-monospace h6">
                                            R$ <?= number_format($rate['rate'], 4, ',', '.') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?= htmlspecialchars($rate['source']) ?></span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($rate['updated_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($variation !== null): ?>
                                            <span class="<?= $variationClass ?>">
                                                <i class="bi <?= $variationIcon ?>"></i>
                                                <?= number_format(abs($variation), 4, ',', '.') ?>
                                                (<?= number_format(($variation / $previousRate) * 100, 2, ',', '.') ?>%)
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php
                                $previousRate = $rate['rate'];
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
window.pendingScripts = window.pendingScripts || [];
window.pendingScripts.push(function() {
    // Botão para atualizar taxa
    $('#updateRateBtn').on('click', function() {
        const button = $(this);
        const originalText = button.html();

        // Mostrar loading
        button.html('<i class="bi bi-arrow-clockwise spin"></i> Atualizando...').prop('disabled', true);

        // Fazer requisição AJAX
        $.ajax({
            url: '<?= BASE_URL ?>api/exchange-rates/update',
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    showSuccess(response.message);

                    // Atualizar taxa na tela se disponível
                    if (response.formatted_rate) {
                        $('#current-rate-display').text(response.formatted_rate);
                    }

                    // Recarregar página após 2 segundos para mostrar histórico atualizado
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showError(response.message);
                }
            },
            error: function() {
                showError('Erro ao atualizar taxa. Tente novamente.');
            },
            complete: function() {
                // Restaurar botão
                button.html(originalText).prop('disabled', false);
            }
        });
    });

    // CSS para animação de loading
    const style = document.createElement('style');
    style.textContent = `
        .spin {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
});
</script>