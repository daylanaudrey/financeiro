<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3">
                        <i class="bi bi-box"></i> <?= $action === 'create' ? 'Cadastrar Produto' : 'Editar Produto' ?>
                    </h1>
                    <p class="text-muted">Preencha os dados do produto para importação</p>
                </div>
                <div>
                    <a href="<?= BASE_URL ?>products" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensagens -->
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

    <!-- Formulário -->
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-form"></i> Dados do Produto
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>api/products/<?= $action === 'create' ? 'create' : 'update' ?>" method="POST">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="id" value="<?= $product['id'] ?>">
                        <?php endif; ?>

                        <!-- Informações Básicas -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="text-primary"><i class="bi bi-info-circle"></i> Informações Básicas</h6>
                                <hr class="mt-1">
                            </div>

                            <!-- Nome do Produto -->
                            <div class="col-md-6">
                                <label for="name" class="form-label">
                                    <i class="bi bi-box"></i> Nome do Produto *
                                </label>
                                <input type="text" class="form-control" id="name" name="name"
                                       value="<?= htmlspecialchars($product['name'] ?? '') ?>"
                                       placeholder="Ex: Café em grãos" required>
                            </div>

                            <!-- NCM -->
                            <div class="col-md-6">
                                <label for="ncm" class="form-label">
                                    <i class="bi bi-tag"></i> NCM *
                                </label>
                                <input type="text" class="form-control" id="ncm" name="ncm"
                                       value="<?= htmlspecialchars($product['ncm'] ?? '') ?>"
                                       placeholder="12345678" maxlength="8" required>
                                <div class="form-text">Nomenclatura Comum do Mercosul (8 dígitos)</div>
                            </div>

                            <!-- Descrição -->
                            <div class="col-12">
                                <label for="description" class="form-label">
                                    <i class="bi bi-chat-text"></i> Descrição
                                </label>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                          placeholder="Descrição detalhada do produto..."><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Características Físicas -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="text-success"><i class="bi bi-rulers"></i> Características Físicas</h6>
                                <hr class="mt-1">
                            </div>

                            <!-- Tipo de Divisão (DESTAQUE) -->
                            <div class="col-md-6">
                                <label for="division_type" class="form-label">
                                    <i class="bi bi-arrow-down-up"></i> Tipo de Divisão *
                                    <span class="badge bg-warning">IMPORTANTE</span>
                                </label>
                                <select class="form-select select2" id="division_type" name="division_type" required>
                                    <option value="">Selecione o tipo...</option>
                                    <option value="KG" <?= ($product['division_type'] ?? '') === 'KG' ? 'selected' : '' ?>>
                                        Por Peso (KG) - Para produtos sólidos/líquidos
                                    </option>
                                    <option value="QUANTIDADE" <?= ($product['division_type'] ?? '') === 'QUANTIDADE' ? 'selected' : '' ?>>
                                        Por Quantidade - Para produtos unitários
                                    </option>
                                </select>
                                <div class="form-text">
                                    <strong>KG:</strong> Café, açúcar, óleo, etc. | <strong>QUANTIDADE:</strong> Smartphones, notebooks, etc.
                                </div>
                            </div>

                            <!-- Unidade -->
                            <div class="col-md-6">
                                <label for="unit" class="form-label">
                                    <i class="bi bi-box-seam"></i> Unidade
                                </label>
                                <select class="form-select select2" id="unit" name="unit">
                                    <option value="">Selecione...</option>
                                    <option value="KG" <?= ($product['unit'] ?? '') === 'KG' ? 'selected' : '' ?>>KG - Quilograma</option>
                                    <option value="G" <?= ($product['unit'] ?? '') === 'G' ? 'selected' : '' ?>>G - Grama</option>
                                    <option value="LT" <?= ($product['unit'] ?? '') === 'LT' ? 'selected' : '' ?>>LT - Litro</option>
                                    <option value="ML" <?= ($product['unit'] ?? '') === 'ML' ? 'selected' : '' ?>>ML - Mililitro</option>
                                    <option value="UN" <?= ($product['unit'] ?? '') === 'UN' ? 'selected' : '' ?>>UN - Unidade</option>
                                    <option value="PÇ" <?= ($product['unit'] ?? '') === 'PÇ' ? 'selected' : '' ?>>PÇ - Peça</option>
                                    <option value="CX" <?= ($product['unit'] ?? '') === 'CX' ? 'selected' : '' ?>>CX - Caixa</option>
                                    <option value="PAR" <?= ($product['unit'] ?? '') === 'PAR' ? 'selected' : '' ?>>PAR - Par</option>
                                </select>
                            </div>
                        </div>

                        <!-- Valores RFB -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="text-info"><i class="bi bi-currency-dollar"></i> Valores de Referência RFB</h6>
                                <hr class="mt-1">
                            </div>

                        </div>

                        <!-- Alíquotas de Impostos -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="text-danger"><i class="bi bi-percent"></i> Alíquotas de Impostos</h6>
                                <hr class="mt-1">
                            </div>

                            <div class="col-md-2">
                                <label for="ii_rate" class="form-label">II (%)</label>
                                <input type="text" class="form-control mask-percentage" id="ii_rate" name="ii_rate"
                                       value="<?= $product['ii_rate'] ?? '0' ?>"
                                       min="0" max="100" step="0.01">
                                <div class="form-text">Imposto de Importação</div>
                            </div>

                            <div class="col-md-2">
                                <label for="ipi_rate" class="form-label">IPI (%)</label>
                                <input type="text" class="form-control mask-percentage" id="ipi_rate" name="ipi_rate"
                                       value="<?= $product['ipi_rate'] ?? '0' ?>"
                                       min="0" max="100" step="0.01">
                                <div class="form-text">Imposto sobre Produtos Industrializados</div>
                            </div>

                            <div class="col-md-2">
                                <label for="pis_rate" class="form-label">PIS (%)</label>
                                <input type="text" class="form-control mask-percentage" id="pis_rate" name="pis_rate"
                                       value="<?= $product['pis_rate'] ?? '1.65' ?>"
                                       min="0" max="100" step="0.01">
                            </div>

                            <div class="col-md-3">
                                <label for="cofins_rate" class="form-label">COFINS (%)</label>
                                <input type="text" class="form-control mask-percentage" id="cofins_rate" name="cofins_rate"
                                       value="<?= $product['cofins_rate'] ?? '7.60' ?>"
                                       min="0" max="100" step="0.01">
                            </div>

                            <div class="col-md-3">
                                <label for="icms_rate" class="form-label">ICMS (%)</label>
                                <input type="text" class="form-control mask-percentage" id="icms_rate" name="icms_rate"
                                       value="<?= $product['icms_rate'] ?? '17.00' ?>"
                                       min="0" max="100" step="0.01">
                                <div class="form-text">Varia por estado</div>
                            </div>
                        </div>

                        <!-- RFB por Porto -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="text-warning"><i class="bi bi-geo-alt"></i> Configurações RFB por Porto</h6>
                                <hr class="mt-1">
                            </div>

                            <!-- RFB Padrão (Global) -->
                            <div class="col-12">
                                <div class="card border-info">
                                    <div class="card-header bg-info bg-opacity-10">
                                        <h6 class="mb-0"><i class="bi bi-globe"></i> RFB Padrão (Todos os Portos)</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="rfb_min" class="form-label">
                                                    <i class="bi bi-arrow-down-circle"></i> RFB Mínimo
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="text" class="form-control mask-rfb-value" id="rfb_min" name="rfb_min"
                                                           value="<?= isset($product['rfb_min']) && $product['rfb_min'] ? number_format((float)$product['rfb_min'], 2, ',', '.') : '' ?>"
                                                           placeholder="0,00">
                                                </div>
                                                <small class="text-muted">Valor padrão usado se não houver configuração específica por porto</small>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="rfb_max" class="form-label">
                                                    <i class="bi bi-arrow-up-circle"></i> RFB Máximo
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="text" class="form-control mask-rfb-value" id="rfb_max" name="rfb_max"
                                                           value="<?= isset($product['rfb_max']) && $product['rfb_max'] ? number_format((float)$product['rfb_max'], 2, ',', '.') : '' ?>"
                                                           placeholder="0,00">
                                                </div>
                                                <small class="text-muted">Valor padrão usado se não houver configuração específica por porto</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning bg-opacity-10 d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><i class="bi bi-geo-alt"></i> RFB Específico por Porto</h6>
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-port-config">
                                            <i class="bi bi-plus-circle"></i> Adicionar Porto
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div id="port-configs-container">
                                            <!-- Configurações específicas por porto serão carregadas aqui -->
                                            <div class="alert alert-info">
                                                <i class="bi bi-info-circle"></i>
                                                <strong>Configure valores específicos por porto:</strong><br>
                                                Quando um processo selecionar um porto específico, os valores RFB configurados aqui serão usados ao invés dos valores padrão.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="text-secondary"><i class="bi bi-gear"></i> Configurações</h6>
                                <hr class="mt-1">
                            </div>

                            <div class="col-md-6">
                                <label for="is_active" class="form-label">
                                    <i class="bi bi-power"></i> Status
                                </label>
                                <select class="form-select select2" id="is_active" name="is_active">
                                    <option value="1" <?= ($product['is_active'] ?? true) ? 'selected' : '' ?>>Ativo</option>
                                    <option value="0" <?= isset($product['is_active']) && !$product['is_active'] ? 'selected' : '' ?>>Inativo</option>
                                </select>
                                <div class="form-text">Produtos inativos não aparecem nas listas de seleção</div>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="<?= BASE_URL ?>products" class="btn btn-secondary">
                                        <i class="bi bi-x-circle"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i>
                                        <?= $action === 'create' ? 'Cadastrar Produto' : 'Atualizar Produto' ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Aguardar jQuery estar disponível
function initProductForm() {
    if (typeof $ === 'undefined') {
        // jQuery ainda não carregado, tentar novamente em 100ms
        setTimeout(initProductForm, 100);
        return;
    }

    // jQuery disponível, executar código
    // Inicializar Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        allowClear: false
    });

    // Formatação automática do NCM
    $('#ncm').on('input', function() {
        // Remove tudo que não for número
        this.value = this.value.replace(/\D/g, '');
    });

    // Validação dinâmica de RFB
    $('#rfb_min, #rfb_max').on('change', function() {
        validateRFB();
    });

    // Mudança no tipo de divisão
    $('#division_type').on('change', function() {
        const divisionType = $(this).val();
        const $weightField = $('#weight_kg');
        const $unitField = $('#unit');

        if (divisionType === 'KG') {
            // Para produtos por peso, sugerir unidades de peso/volume
            $unitField.html(`
                <option value="">Selecione...</option>
                <option value="KG">KG - Quilograma</option>
                <option value="G">G - Grama</option>
                <option value="LT">LT - Litro</option>
                <option value="ML">ML - Mililitro</option>
            `);
        } else if (divisionType === 'QUANTIDADE') {
            // Para produtos por quantidade, sugerir unidades unitárias
            $unitField.html(`
                <option value="">Selecione...</option>
                <option value="UN">UN - Unidade</option>
                <option value="PÇ">PÇ - Peça</option>
                <option value="CX">CX - Caixa</option>
                <option value="PAR">PAR - Par</option>
            `);
        }

        // Reinicializar Select2
        $unitField.select2({
            theme: 'bootstrap-5',
            allowClear: false
        });
    });

    // Inicializar sistema de configurações por porto
    loadAvailablePorts();
    loadPortConfigs();

    // Aplicar máscaras de valor
    initValueMasks();

    // Evento do botão Adicionar Porto
    $('#btn-add-port-config').on('click', function() {
        addPortConfig();
    });

    // Auto-save das configurações em modo edição
    <?php if ($action === 'edit'): ?>
    $(document).on('change', '.port-rfb-min, .port-rfb-max', function() {
        const portId = $(this).data('port-id');
        const rfbMin = $(`.port-rfb-min[data-port-id="${portId}"]`).val();
        const rfbMax = $(`.port-rfb-max[data-port-id="${portId}"]`).val();

        // Validar valores
        if (rfbMin && rfbMax && parseFloat(rfbMin) > parseFloat(rfbMax)) {
            showError('RFB mínimo não pode ser maior que o RFB máximo.');
            return;
        }

        $.ajax({
            url: '<?= BASE_URL ?>api/products/savePortConfig',
            type: 'POST',
            data: {
                product_id: <?= $product['id'] ?>,
                port_id: portId,
                rfb_min: rfbMin || null,
                rfb_max: rfbMax || null
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Auto-save silencioso
                    console.log('Configuração RFB salva automaticamente');
                } else {
                    showError(response.message);
                }
            }
        });
    });
    <?php endif; ?>
}

// Sistema de configurações por porto
let portConfigs = [];
let availablePorts = [];

function validateRFB() {
    const rfbMin = parseFloat($('#rfb_min').val()) || 0;
    const rfbMax = parseFloat($('#rfb_max').val()) || 0;

    if (rfbMin > 0 && rfbMax > 0 && rfbMin > rfbMax) {
        showError('RFB mínimo não pode ser maior que o RFB máximo!');
        $('#rfb_min').focus();
    }
}

// Carregar portos disponíveis
function loadAvailablePorts() {
    $.ajax({
        url: '<?= BASE_URL ?>api/products/getPorts',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                availablePorts = response.data;
            }
        },
        error: function() {
            console.error('Erro ao carregar portos');
        }
    });
}

// Carregar configurações existentes (modo edição)
function loadPortConfigs() {
    <?php if ($action === 'edit' && isset($product['id'])): ?>
    $.ajax({
        url: '<?= BASE_URL ?>api/products/getPortConfigs',
        type: 'GET',
        data: { product_id: <?= $product['id'] ?> },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                portConfigs = response.data;
                renderPortConfigs();
            }
        },
        error: function() {
            console.error('Erro ao carregar configurações por porto');
        }
    });
    <?php endif; ?>
}

// Renderizar configurações por porto
function renderPortConfigs() {
    const container = $('#port-configs-container');

    if (portConfigs.length === 0) {
        container.html(`
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Configure valores específicos por porto:</strong><br>
                Quando um processo selecionar um porto específico, os valores RFB configurados aqui serão usados ao invés dos valores padrão.
            </div>
        `);
        return;
    }

    let html = '';
    portConfigs.forEach((config, index) => {
        // Usar o port_name que vem do banco ou buscar no array local como fallback
        const portName = config.port_name || (availablePorts.find(p => p.id == config.port_id)?.name) || `Porto ID ${config.port_id}`;

        html += `
            <div class="card mb-3" data-port-id="${config.port_id}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-geo-alt-fill text-primary"></i> ${portName}
                    </h6>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removePortConfig(${config.port_id})">
                        <i class="bi bi-trash"></i> Remover
                    </button>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="bi bi-arrow-down-circle"></i> RFB Mínimo
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" class="form-control port-rfb-min mask-rfb-value"
                                       data-port-id="${config.port_id}"
                                       value="${config.rfb_min ? parseFloat(config.rfb_min).toFixed(2).replace('.', ',') : ''}"
                                       placeholder="0.00" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="bi bi-arrow-up-circle"></i> RFB Máximo
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" class="form-control port-rfb-max mask-rfb-value"
                                       data-port-id="${config.port_id}"
                                       value="${config.rfb_max ? parseFloat(config.rfb_max).toFixed(2).replace('.', ',') : ''}"
                                       placeholder="0.00" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="bi bi-diagram-3"></i> Divisão neste Porto
                            </label>
                            <select class="form-control port-division-type"
                                    data-port-id="${config.port_id}">
                                <option value="PC" ${(config.division_type || 'PC') === 'PC' ? 'selected' : ''}>PC - Por Quantidade</option>
                                <option value="KG" ${(config.division_type || 'PC') === 'KG' ? 'selected' : ''}>KG - Por Peso</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    container.html(html);

    // Aplicar máscaras nos novos elementos criados
    initValueMasks();
}

// Adicionar configuração de novo porto
function addPortConfig() {
    const usedPortIds = portConfigs.map(c => parseInt(c.port_id));
    const unusedPorts = availablePorts.filter(p => !usedPortIds.includes(parseInt(p.id)));

    if (unusedPorts.length === 0) {
        showError('Todos os portos já foram configurados.');
        return;
    }

    let optionsHtml = '<option value="">Selecione um porto...</option>';
    unusedPorts.forEach(port => {
        optionsHtml += `<option value="${port.id}">${port.name}</option>`;
    });

    const modalHtml = `
        <div class="modal fade" id="addPortModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-plus-circle"></i> Adicionar Configuração por Porto
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addPortConfigForm">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-geo-alt"></i> Selecionar Porto
                                </label>
                                <select class="form-select" id="newPortId" required>
                                    ${optionsHtml}
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-diagram-3"></i> Divisão neste Porto
                                </label>
                                <select class="form-select" id="newDivisionType" required>
                                    <option value="PC">PC - Por Quantidade</option>
                                    <option value="KG">KG - Por Peso</option>
                                </select>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">RFB Mínimo</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="text" class="form-control mask-rfb-value" id="newRfbMin"
                                               placeholder="0.00" min="0" step="0.01">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">RFB Máximo</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="text" class="form-control mask-rfb-value" id="newRfbMax"
                                               placeholder="0.00" min="0" step="0.01">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-primary" onclick="saveNewPortConfig()">
                            <i class="bi bi-check-circle"></i> Adicionar Configuração
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    $('body').append(modalHtml);
    $('#addPortModal').modal('show');

    // Aplicar máscaras nos campos do modal
    initValueMasks();

    $('#addPortModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

// Salvar nova configuração de porto
function saveNewPortConfig() {
    const portId = $('#newPortId').val();
    const rfbMin = $('#newRfbMin').val();
    const rfbMax = $('#newRfbMax').val();
    const divisionType = $('#newDivisionType').val();

    if (!portId) {
        showError('Selecione um porto.');
        return;
    }

    // Validar valores
    if (rfbMin && rfbMax && parseFloat(rfbMin) > parseFloat(rfbMax)) {
        showError('RFB mínimo não pode ser maior que o RFB máximo.');
        return;
    }

    <?php if ($action === 'edit'): ?>
    // Modo edição - salvar via AJAX
    $.ajax({
        url: '<?= BASE_URL ?>api/products/savePortConfig',
        type: 'POST',
        data: {
            product_id: <?= $product['id'] ?>,
            port_id: portId,
            rfb_min: rfbMin || null,
            rfb_max: rfbMax || null,
            division_type: divisionType || 'PC'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showSuccess(response.message);
                $('#addPortModal').modal('hide');
                loadPortConfigs(); // Recarregar configurações
            } else {
                showError(response.message);
            }
        },
        error: function() {
            showError('Erro ao salvar configuração.');
        }
    });
    <?php else: ?>
    // Modo criação - adicionar à lista local
    const port = availablePorts.find(p => p.id == portId);
    if (port) {
        portConfigs.push({
            port_id: portId,
            rfb_min: rfbMin || null,
            rfb_max: rfbMax || null,
            division_type: divisionType || 'PC',
            port_name: port.name
        });
        renderPortConfigs();
        showSuccess('Configuração adicionada. Salve o produto para confirmar.');
    }
    $('#addPortModal').modal('hide');
    <?php endif; ?>
}

// Remover configuração de porto
function removePortConfig(portId) {
    <?php if ($action === 'edit'): ?>
    // Modo edição - remover via AJAX
    if (confirm('Deseja realmente remover esta configuração?')) {
        $.ajax({
            url: '<?= BASE_URL ?>api/products/deletePortConfig',
            type: 'POST',
            data: {
                product_id: <?= $product['id'] ?>,
                port_id: portId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showSuccess(response.message);
                    loadPortConfigs(); // Recarregar configurações
                } else {
                    showError(response.message);
                }
            },
            error: function() {
                showError('Erro ao remover configuração.');
            }
        });
    }
    <?php else: ?>
    // Modo criação - remover da lista local
    if (confirm('Deseja realmente remover esta configuração?')) {
        portConfigs = portConfigs.filter(c => c.port_id != portId);
        renderPortConfigs();
        showSuccess('Configuração removida.');
    }
    <?php endif; ?>
}

// Iniciar a função quando o script for executado
initProductForm();

// Auto-salvar mudanças nos campos RFB por porto (apenas no modo edição)
<?php if ($action === 'edit'): ?>
window.pendingScripts = window.pendingScripts || [];
window.pendingScripts.push(function() {
    $(document).on('change', '.port-rfb-min, .port-rfb-max, .port-division-type', function() {
        const portId = $(this).data('port-id');
        const $container = $(this).closest('.card');

        const rfbMin = $container.find('.port-rfb-min').val();
        const rfbMax = $container.find('.port-rfb-max').val();
        const divisionType = $container.find('.port-division-type').val();

        // Salvar via AJAX
        $.ajax({
            url: '<?= BASE_URL ?>api/products/savePortConfig',
            type: 'POST',
            data: {
                product_id: <?= $product['id'] ?>,
                port_id: portId,
                rfb_min: rfbMin || null,
                rfb_max: rfbMax || null,
                division_type: divisionType || 'PC'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Não mostrar mensagem para não spam
                    console.log('Configuração salva automaticamente');
                } else {
                    showError(response.message);
                }
            },
            error: function() {
                showError('Erro ao salvar configuração automaticamente.');
            }
        });
    });
});
<?php endif; ?>
</script>