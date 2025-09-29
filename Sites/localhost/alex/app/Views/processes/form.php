<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3">
                        <i class="bi bi-file-earmark-text"></i>
                        <?= $action === 'create' ? 'Cadastrar Processo' : 'Editar Processo' ?>
                    </h1>
                    <p class="text-muted">
                        <?= $action === 'create' ? 'Cadastre um novo processo de importação' : 'Edite as informações do processo' ?>
                    </p>
                </div>
                <div>
                    <a href="<?= BASE_URL ?>processes" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensagens de erro -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Formulário -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-form"></i> Informações do Processo
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>api/processes/<?= $action === 'create' ? 'create' : 'update' ?>">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="id" value="<?= $process['id'] ?>">
                <?php endif; ?>

                <div class="row g-3">
                    <!-- Primeira linha: Informações básicas -->
                    <div class="col-md-3">
                        <label for="code" class="form-label">Código do Processo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="code" name="code"
                               value="<?= htmlspecialchars($process['code'] ?? '') ?>"
                               required maxlength="50" placeholder="Ex: IMP001" tabindex="1">
                        <small class="form-text text-muted">Código único do processo</small>
                    </div>

                    <div class="col-md-4">
                        <label for="client_id" class="form-label">Importador <span class="text-danger">*</span></label>
                        <select class="form-select select2" id="client_id" name="client_id" required>
                            <option value="">Selecione o importador</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id'] ?>"
                                        <?= ($process['client_id'] ?? '') == $client['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($client['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="type" class="form-label">Tipo <span class="text-danger">*</span></label>
                        <select class="form-select select2" id="type" name="type" required>
                            <option value="">Selecione</option>
                            <?php foreach ($type_options as $key => $label): ?>
                                <option value="<?= $key ?>"
                                        <?= ($process['type'] ?? '') === $key ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select select2" id="status" name="status" required>
                            <option value="">Selecione o status</option>
                            <?php foreach ($status_options as $key => $label): ?>
                                <option value="<?= $key ?>"
                                        <?= ($process['status'] ?? 'PRE_EMBARQUE') === $key ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <!-- Segunda linha: Datas -->
                    <div class="col-md-4">
                        <label for="process_date" class="form-label">Data do Processo <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="process_date" name="process_date"
                               value="<?= $process['process_date'] ?? date('Y-m-d') ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label for="arrival_date" class="form-label">Data de Chegada</label>
                        <input type="date" class="form-control" id="arrival_date" name="arrival_date"
                               value="<?= $process['arrival_date'] ?? '' ?>">
                    </div>

                    <div class="col-md-4">
                        <label for="clearance_date" class="form-label">Data de Desembaraço</label>
                        <input type="date" class="form-control" id="clearance_date" name="clearance_date"
                               value="<?= $process['clearance_date'] ?? '' ?>">
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <!-- Nova linha: Datas de Chegada e Free Time -->
                    <div class="col-md-4">
                        <label for="estimated_arrival_date" class="form-label">Data Chegada Prevista</label>
                        <input type="date" class="form-control" id="estimated_arrival_date" name="estimated_arrival_date"
                               value="<?= $process['estimated_arrival_date'] ?? '' ?>">
                        <small class="form-text text-muted">Previsão inicial de chegada</small>
                    </div>

                    <div class="col-md-4">
                        <label for="confirmed_arrival_date" class="form-label">Data Chegada Confirmada</label>
                        <input type="date" class="form-control" id="confirmed_arrival_date" name="confirmed_arrival_date"
                               value="<?= $process['confirmed_arrival_date'] ?? '' ?>">
                        <small class="form-text text-muted">Data confirmada de chegada</small>
                    </div>

                    <div class="col-md-4">
                        <label for="free_time_days" class="form-label">Free Time (dias)</label>
                        <input type="number" class="form-control" id="free_time_days" name="free_time_days"
                               value="<?= $process['free_time_days'] ?? 7 ?>"
                               min="1" max="30">
                        <small class="form-text text-muted">Dias de armazenagem sem custo</small>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <!-- Terceira linha: Transporte -->
                    <div class="col-md-3">
                        <label for="modal" class="form-label">Modal de Transporte <span class="text-danger">*</span></label>
                        <select class="form-select select2" id="modal" name="modal" required>
                            <option value="">Selecione</option>
                            <?php foreach ($modal_options as $key => $label): ?>
                                <option value="<?= $key ?>"
                                        <?= ($process['modal'] ?? 'MARITIME') === $key ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="destination_port_id" class="form-label">Porto de Destino</label>
                        <select class="form-select select2" id="destination_port_id" name="destination_port_id">
                            <option value="">Selecione o porto</option>
                            <?php foreach ($ports as $port): ?>
                                <option value="<?= $port['id'] ?>"
                                        <?= ($process['destination_port_id'] ?? '') == $port['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($port['name']) ?> - <?= htmlspecialchars($port['city']) ?>/<?= htmlspecialchars($port['state']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Porto onde a carga será desembarcada</small>
                    </div>

                    <div class="col-md-3">
                        <label for="container_number" class="form-label">Número do Container</label>
                        <input type="text" class="form-control" id="container_number" name="container_number"
                               value="<?= htmlspecialchars($process['container_number'] ?? '') ?>"
                               maxlength="50" placeholder="Ex: TCLU1234567">
                    </div>

                    <div class="col-md-3">
                        <label for="bl_number" class="form-label">Número BL/AWB</label>
                        <input type="text" class="form-control" id="bl_number" name="bl_number"
                               value="<?= htmlspecialchars($process['bl_number'] ?? '') ?>"
                               maxlength="50" placeholder="Ex: BL2024001">
                    </div>

                    <div class="col-md-3">
                        <label for="incoterm" class="form-label">Incoterm <span class="text-danger">*</span></label>
                        <select class="form-select select2" id="incoterm" name="incoterm" required>
                            <option value="">Selecione</option>
                            <?php foreach ($incoterm_options as $key => $label): ?>
                                <option value="<?= $key ?>"
                                        <?= ($process['incoterm'] ?? 'FOB') === $key ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <hr class="my-4">

                <h6 class="mb-3"><i class="bi bi-currency-dollar"></i> Valores Financeiros</h6>

                <div class="row g-3">
                    <!-- Valores em USD -->
                    <div class="col-md-3">
                        <label for="total_fob_usd" class="form-label">Valor FOB (USD)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" class="form-control mask-currency-usd" id="total_fob_usd" name="total_fob_usd"
                                   value="<?= isset($process['total_fob_usd']) && $process['total_fob_usd'] > 0 ? number_format($process['total_fob_usd'], 2, ',', '.') : '' ?>"
                                   placeholder="0,00">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label for="total_freight_usd" class="form-label">Frete (USD)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" class="form-control mask-currency-usd" id="total_freight_usd" name="total_freight_usd"
                                   value="<?= isset($process['total_freight_usd']) && $process['total_freight_usd'] > 0 ? number_format($process['total_freight_usd'], 2, ',', '.') : '' ?>"
                                   placeholder="0,00">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label for="total_insurance_usd" class="form-label">Seguro (USD)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" class="form-control mask-currency-usd" id="total_insurance_usd" name="total_insurance_usd"
                                   value="<?= isset($process['total_insurance_usd']) && $process['total_insurance_usd'] > 0 ? number_format($process['total_insurance_usd'], 2, ',', '.') : '' ?>"
                                   placeholder="0,00">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label for="exchange_rate" class="form-label">Taxa de Câmbio <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="number" class="form-control" id="exchange_rate" name="exchange_rate"
                                   value="<?= $process['exchange_rate'] ?? $current_ptax_rate ?? '5.0000' ?>"
                                   step="0.0001" min="0.0001" required placeholder="5.0000">
                            <button type="button" class="btn btn-outline-secondary" id="use_ptax_rate" title="Usar PTAX do dia anterior">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                        <small class="form-text text-muted">
                            <i class="bi bi-info-circle"></i> PTAX D-1 (dia anterior): R$ <?= number_format($current_ptax_rate ?? 5.0000, 4, ',', '.') ?>
                        </small>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <!-- Valores calculados (readonly) -->
                    <div class="col-md-4">
                        <label for="total_cif_usd_display" class="form-label">Total CIF (USD)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" class="form-control" id="total_cif_usd_display"
                                   readonly style="background-color: #f8f9fa;">
                        </div>
                        <small class="form-text text-muted">FOB + Frete + Seguro</small>
                    </div>

                    <div class="col-md-4">
                        <label for="total_cif_brl_display" class="form-label">Total CIF (BRL)</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" class="form-control" id="total_cif_brl_display"
                                   readonly style="background-color: #f8f9fa;">
                        </div>
                        <small class="form-text text-muted">CIF USD × Taxa de Câmbio</small>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Observações -->
                <div class="row g-3">
                    <div class="col-12">
                        <label for="notes" class="form-label">Observações</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"
                                  placeholder="Observações sobre o processo..."><?= htmlspecialchars($process['notes'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Botões -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i>
                                <?= $action === 'create' ? 'Cadastrar Processo' : 'Atualizar Processo' ?>
                            </button>
                            <?php if ($action === 'edit'): ?>
                                <a href="<?= BASE_URL ?>process-items?process_id=<?= $process['id'] ?>" class="btn btn-success">
                                    <i class="bi bi-plus-circle"></i> Gerenciar Itens
                                </a>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>processes" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.pendingScripts = window.pendingScripts || [];
window.pendingScripts.push(function() {
    // Aplicar máscaras monetárias com limite maior
    $('.mask-currency-usd').mask('000.000.000.000.000,00', {
        reverse: true
    });

    // Inicializar Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        placeholder: function() {
            return $(this).find('option[value=""]').text();
        }
    });

    // Navegação com Enter
    $(document).on('keypress', 'input,select,textarea', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            const $next = $('[tabindex]:visible').filter(function() {
                return parseInt($(this).attr('tabindex')) > parseInt(e.target.tabIndex || 0);
            }).first();

            if ($next.length > 0) {
                $next.focus();
                if ($next.hasClass('select2-selection')) {
                    $next.trigger('click');
                }
            } else {
                $('form').submit();
            }
        }
    });

    // Função para converter valor com máscara para número
    function unmaskCurrency(value) {
        if (!value) return 0;

        // Converter para string se necessário
        value = String(value);

        // Remove espaços e o símbolo $
        value = value.trim().replace('$', '').trim();

        // Para determinar o formato, vamos contar pontos e vírgulas
        const dots = (value.match(/\./g) || []).length;
        const commas = (value.match(/,/g) || []).length;

        if (dots > 1 || (dots === 1 && commas === 1 && value.indexOf(',') > value.indexOf('.'))) {
            // Formato brasileiro (32.323.232,32) - múltiplos pontos ou vírgula após ponto
            console.log('Formato BR detectado:', value);
            // Remove pontos de milhar
            value = value.replace(/\./g, '');
            // Substitui vírgula por ponto
            value = value.replace(',', '.');
        } else if (commas > 1 || (commas === 1 && dots === 1 && value.indexOf('.') > value.indexOf(','))) {
            // Formato americano (32,323,232.32) - múltiplas vírgulas ou ponto após vírgula
            console.log('Formato US detectado:', value);
            // Remove vírgulas (separadores de milhar)
            value = value.replace(/,/g, '');
        } else if (commas === 1 && dots === 0) {
            // Apenas vírgula, assumir formato brasileiro
            value = value.replace(',', '.');
        }
        // Se tem apenas ponto ou nada, já está ok

        const result = parseFloat(value) || 0;
        console.log('UnmaskCurrency:', 'Input:', arguments[0], 'Output:', result);
        return result;
    }

    // Calcular valores automaticamente
    function calculateValues() {
        const fob = unmaskCurrency($('#total_fob_usd').val());
        const freight = unmaskCurrency($('#total_freight_usd').val());
        const insurance = unmaskCurrency($('#total_insurance_usd').val());
        const exchangeRate = parseFloat($('#exchange_rate').val()) || 0;

        console.log('Calculating:', {fob, freight, insurance, exchangeRate});

        const cifUsd = fob + freight + insurance;
        const cifBrl = cifUsd * exchangeRate;

        console.log('Results:', {cifUsd, cifBrl});

        // Formatar valores para exibição usando formato brasileiro
        // Para valores grandes, precisamos formatar corretamente
        const formatBRL = (value) => {
            return value.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        };

        $('#total_cif_usd_display').val(formatBRL(cifUsd));
        $('#total_cif_brl_display').val(formatBRL(cifBrl));
    }

    // Eventos para recalcular valores
    $('#total_fob_usd, #total_freight_usd, #total_insurance_usd, #exchange_rate').on('input blur', calculateValues);

    // Calcular valores iniciais
    calculateValues();

    // Mostrar/ocultar container baseado no modal
    function toggleContainerField() {
        const modal = $('#modal').val();
        const containerGroup = $('#container_number').closest('.col-md-3');

        if (modal === 'MARITIME') {
            containerGroup.show();
        } else {
            containerGroup.hide();
            $('#container_number').val('');
        }
    }

    $('#modal').on('change', toggleContainerField);
    toggleContainerField(); // Executar na inicialização

    // Controlar botão PTAX baseado no status
    function updatePtaxButtonState() {
        const status = $('#status').val();
        const finalizedStatuses = ['CANAL_VERDE', 'CANAL_VERMELHO', 'CANAL_CINZA'];
        const isFinalized = finalizedStatuses.includes(status);

        $('#use_ptax_rate').prop('disabled', isFinalized);
        $('#exchange_rate').prop('readonly', isFinalized);

        if (isFinalized) {
            $('#use_ptax_rate').attr('title', 'Processo finalizado - taxa não pode ser alterada');
            $('#exchange_rate').addClass('bg-light');
        } else {
            $('#use_ptax_rate').attr('title', 'Usar PTAX do dia anterior');
            $('#exchange_rate').removeClass('bg-light');
        }
    }

    $('#status').on('change', updatePtaxButtonState);
    updatePtaxButtonState(); // Executar na inicialização

    // Validação de datas - removida validação que impedia data de chegada anterior ao processo

    $('#clearance_date').on('change', function() {
        const arrivalDate = $('#arrival_date').val();
        const clearanceDate = $(this).val();

        if (arrivalDate && clearanceDate && clearanceDate < arrivalDate) {
            showError('Data de desembaraço não pode ser anterior à data de chegada');
            $(this).val('');
        }
    });

    // Botão para usar PTAX do dia anterior
    $('#use_ptax_rate').on('click', function() {
        // Verificar se processo está finalizado
        const status = $('#status').val();
        const finalizedStatuses = ['CANAL_VERDE', 'CANAL_VERMELHO', 'CANAL_CINZA'];

        if (finalizedStatuses.includes(status)) {
            showError('Não é possível alterar a taxa de câmbio de um processo finalizado');
            return;
        }

        // Fazer requisição AJAX para buscar a taxa mais atual do dia anterior
        const button = $(this);
        button.prop('disabled', true);
        button.html('<i class="bi bi-hourglass-split"></i>');

        $.ajax({
            url: '<?= BASE_URL ?>api/exchange-rates/get-current',
            method: 'GET',
            success: function(response) {
                if (response.success && response.rate) {
                    $('#exchange_rate').val(parseFloat(response.rate).toFixed(4));
                    calculateValues();
                    showSuccess('Taxa PTAX D-1 aplicada: R$ ' + parseFloat(response.rate).toFixed(4));
                } else {
                    // Fallback para a variável PHP caso a API falhe
                    const ptaxRate = <?= $current_ptax_rate ?? 5.0000 ?>;
                    $('#exchange_rate').val(ptaxRate.toFixed(4));
                    calculateValues();
                    showSuccess('Taxa PTAX D-1 aplicada: R$ ' + ptaxRate.toFixed(4));
                }
            },
            error: function() {
                // Fallback para a variável PHP caso a requisição falhe
                const ptaxRate = <?= $current_ptax_rate ?? 5.0000 ?>;
                $('#exchange_rate').val(ptaxRate.toFixed(4));
                calculateValues();
                showSuccess('Taxa PTAX D-1 aplicada: R$ ' + ptaxRate.toFixed(4));
            },
            complete: function() {
                button.prop('disabled', false);
                button.html('<i class="bi bi-arrow-clockwise"></i>');
            }
        });
    });

    // Máscara para taxa de câmbio
    $('#exchange_rate').on('input', function() {
        let value = $(this).val();
        // Permitir apenas números e pontos
        value = value.replace(/[^0-9.]/g, '');
        $(this).val(value);
    });

    // Validação do formulário antes de enviar
    $('form').on('submit', function(e) {
        const exchangeRate = parseFloat($('#exchange_rate').val());

        if (exchangeRate <= 0) {
            e.preventDefault();
            showError('Taxa de câmbio deve ser maior que zero');
            $('#exchange_rate').focus();
            return false;
        }

        // Debug: verificar valores antes de enviar
        console.log('FOB value before submit:', $('#total_fob_usd').val());
        console.log('Freight value before submit:', $('#total_freight_usd').val());
        console.log('Insurance value before submit:', $('#total_insurance_usd').val());
    });
}); 
</script>
