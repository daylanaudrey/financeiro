<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3">
                        <i class="bi bi-box-seam"></i>
                        <?= $action === 'create' ? 'Adicionar Item' : 'Editar Item' ?>
                    </h1>
                    <p class="text-muted">
                        Processo: <strong><?= htmlspecialchars($process['code'] ?? '') ?></strong> -
                        Cliente: <strong><?= htmlspecialchars($process['client_name'] ?? '') ?></strong>
                    </p>
                </div>
                <div>
                    <a href="<?= BASE_URL ?>process-items?process_id=<?= $process['id'] ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar aos Itens
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
                <i class="bi bi-form"></i> Informações do Item
            </h5>
        </div>
        <div class="card-body">
            <form id="itemForm" method="POST" action="<?= BASE_URL ?>api/process-items/<?= $action === 'create' ? 'create' : 'update' ?>">
                <input type="hidden" name="process_id" value="<?= $process['id'] ?>">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                <?php endif; ?>

                <!-- Seleção de Produto -->
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="product_search" class="form-label">Produto <span class="text-danger">*</span></label>

                        <!-- Input para busca -->
                        <div class="position-relative">
                            <input type="text"
                                   class="form-control"
                                   id="product_search"
                                   placeholder="Digite o nome do produto ou NCM para buscar..."
                                   autocomplete="off"
                                   tabindex="1">

                            <!-- Lista de resultados -->
                            <div id="product_results" class="position-absolute w-100 bg-white border border-top-0 rounded-bottom shadow-sm" style="display: none; z-index: 1000; max-height: 300px; overflow-y: auto;">
                                <!-- Resultados aparecem aqui -->
                            </div>

                            <!-- Loading -->
                            <div id="product_loading" class="position-absolute w-100 bg-white border border-top-0 rounded-bottom text-center py-2" style="display: none; z-index: 999;">
                                <small class="text-muted">
                                    <i class="bi bi-hourglass-split"></i> Buscando...
                                </small>
                            </div>

                            <!-- Produto selecionado -->
                            <div id="product_selected" class="mt-2 p-2 bg-light rounded" style="display: none;">
                                <small class="text-muted">Produto selecionado:</small><br>
                                <strong id="selected_product_text"></strong>
                                <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="clearProductSelection()">
                                    <i class="bi bi-x"></i> Limpar
                                </button>
                            </div>
                        </div>

                        <!-- Campo hidden para o valor real -->
                        <input type="hidden" id="product_id" name="product_id" required
                               value="<?= isset($item['product_id']) ? $item['product_id'] : '' ?>">

                        <?php if (isset($item['product_id']) && $item['product_id']): ?>
                            <?php
                            // Se estamos editando, mostrar produto atual
                            foreach ($products as $product) {
                                if ($product['id'] == $item['product_id']) {
                                    $displayDesc = '';
                                    if (!empty($product['variant_description'])) {
                                        $displayDesc = $product['variant_description'];
                                    } elseif (!empty($product['description'])) {
                                        $displayDesc = $product['description'];
                                    } elseif (!empty($product['name'])) {
                                        $displayDesc = $product['name'];
                                    } else {
                                        $displayDesc = 'Produto sem descrição';
                                    }
                                    break;
                                }
                            }
                            ?>
                            <script>
                                // Mostrar produto selecionado quando editando
                                document.addEventListener('DOMContentLoaded', function() {
                                    const selectedDiv = document.getElementById('product_selected');
                                    const selectedText = document.getElementById('selected_product_text');
                                    selectedText.textContent = '<?= htmlspecialchars($displayDesc ?? '') ?> - NCM: <?= htmlspecialchars($product['ncm'] ?? '') ?>';
                                    selectedDiv.style.display = 'block';
                                });
                            </script>
                        <?php endif; ?>
                    </div>
                </div>

                <hr class="my-3">

                <!-- Informações Editáveis do Produto -->
                <h6 class="mb-3"><i class="bi bi-pencil-square"></i> Informações do Produto (editáveis)</h6>

                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="product_description" class="form-label">Descrição do Produto</label>
                        <input type="text" class="form-control" id="product_description" name="product_description"
                               value="<?= htmlspecialchars($item['description'] ?? '') ?>" tabindex="2" placeholder="Descrição do produto">
                        <small class="form-text text-muted">Se alterar a descrição, será criada uma variação do produto</small>
                    </div>

                    <div class="col-md-4">
                        <label for="product_ncm" class="form-label">NCM</label>
                        <input type="text" class="form-control" id="product_ncm" name="product_ncm"
                               value="<?= htmlspecialchars($item['ncm'] ?? '') ?>" maxlength="10" tabindex="3" placeholder="0000.00.00">
                        <small class="form-text text-muted">Código NCM do produto</small>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <!-- Alíquotas de Impostos Editáveis -->
                    <div class="col-md-2">
                        <label for="ii_rate" class="form-label">II (%)</label>
                        <input type="text" class="form-control mask-percentage" id="ii_rate" name="ii_rate"
                               value="<?= isset($item['ii_rate']) ? number_format($item['ii_rate'], 2, ',', '.') : '0,00' ?>" step="0.01" min="0" max="100" tabindex="4">
                    </div>

                    <div class="col-md-2">
                        <label for="ipi_rate" class="form-label">IPI (%)</label>
                        <input type="text" class="form-control mask-percentage" id="ipi_rate" name="ipi_rate"
                               value="<?= isset($item['ipi_rate']) ? number_format($item['ipi_rate'], 2, ',', '.') : '0,00' ?>" step="0.01" min="0" max="100" tabindex="5">
                    </div>

                    <div class="col-md-2">
                        <label for="pis_rate" class="form-label">PIS (%)</label>
                        <input type="text" class="form-control mask-percentage" id="pis_rate" name="pis_rate"
                               value="<?= isset($item['pis_rate']) ? number_format($item['pis_rate'], 2, ',', '.') : '0,00' ?>" step="0.01" min="0" max="100" tabindex="6">
                    </div>

                    <div class="col-md-2">
                        <label for="cofins_rate" class="form-label">COFINS (%)</label>
                        <input type="text" class="form-control mask-percentage" id="cofins_rate" name="cofins_rate"
                               value="<?= isset($item['cofins_rate']) ? number_format($item['cofins_rate'], 2, ',', '.') : '0,00' ?>" step="0.01" min="0" max="100" tabindex="7">
                    </div>

                    <div class="col-md-2">
                        <label for="icms_rate" class="form-label">ICMS (%)</label>
                        <input type="text" class="form-control mask-percentage" id="icms_rate" name="icms_rate"
                               value="<?= isset($item['icms_rate']) ? number_format($item['icms_rate'], 2, ',', '.') : '0,00' ?>" step="0.01" min="0" max="100" tabindex="8">
                    </div>

                    <div class="col-md-2">
                        <label for="product_division" class="form-label">Divisão</label>
                        <select class="form-select" id="product_division" name="product_division" tabindex="9">
                            <option value="QUANTIDADE">QUANTIDADE</option>
                            <option value="KG">KG</option>
                        </select>
                    </div>
                </div>

                <hr class="my-3">

                <!-- Sistema RFB -->
                <h6 class="mb-3"><i class="bi bi-currency-dollar"></i> Sistema RFB (Receita Federal)</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <div class="card bg-info bg-opacity-10 border-info">
                            <div class="card-body">
                                <!-- Valores RFB do Produto -->
                                <div class="row g-2 mb-3">
                                    <div class="col-4">
                                        <label class="form-label small">RFB Mínimo (Produto)</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">$</span>
                                            <input type="text" class="form-control mask-rfb-value" id="rfb_min_product" name="rfb_min_product_editable"
                                                   step="0.01" value="<?= isset($item['rfb_min']) ? number_format((float)$item['rfb_min'], 2, ',', '.') : '0,00' ?>">
                                            <button class="btn btn-outline-secondary btn-sm" type="button" id="save_rfb_min" title="Salvar como padrão do produto">
                                                <i class="bi bi-check"></i>
                                            </button>
                                        </div>
                                        <small class="form-text text-muted">Será salvo como padrão do produto</small>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small">RFB Máximo (Produto)</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">$</span>
                                            <input type="text" class="form-control mask-rfb-value" id="rfb_max_product" name="rfb_max_product_editable"
                                                   step="0.01" value="<?= isset($item['rfb_max']) ? number_format((float)$item['rfb_max'], 2, ',', '.') : '0,00' ?>">
                                            <button class="btn btn-outline-secondary btn-sm" type="button" id="save_rfb_max" title="Salvar como padrão do produto">
                                                <i class="bi bi-check"></i>
                                            </button>
                                        </div>
                                        <small class="form-text text-muted">Será salvo como padrão do produto</small>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small">Opção RFB</label>
                                        <select class="form-select form-select-sm" id="rfb_option" name="rfb_option">
                                            <option value="min">Usar Mínimo</option>
                                            <option value="max">Usar Máximo</option>
                                            <option value="custom" selected>Manual</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Valores Usados -->
                                <div class="row g-2">
                                    <div class="col-4">
                                        <label class="form-label small">RFB Usado <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">$</span>
                                            <input type="text" class="form-control mask-rfb-value" id="rfb_used" name="rfb_used"
                                                   step="0.01" value="<?= isset($item['rfb_used']) ? number_format((float)$item['rfb_used'], 2, ',', '.') : '0,00' ?>"
                                                   tabindex="10" required>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small">Margem (valor)</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">$</span>
                                            <input type="text" class="form-control mask-currency-usd" id="rfb_margin" name="rfb_margin"
                                                   step="0.01" value="<?= isset($item['rfb_margin']) ? number_format($item['rfb_margin'], 2, ',', '.') : '0,00' ?>"
                                                   tabindex="11">
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small">INV Usado (RFB + Margem)</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-success text-white">$</span>
                                            <input type="text" class="form-control fw-bold" id="inv_used_display"
                                                   readonly style="background-color: #d1edff;" value="0.00">
                                        </div>
                                        <input type="hidden" id="inv_used" name="inv_used" value="<?= $item['inv_used'] ?? '0' ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="alert alert-info">
                            <h6 class="alert-heading">Como usar:</h6>
                            <small>
                                1. <strong>RFB Mínimo/Máximo:</strong> Valores aceitos pela Receita Federal<br>
                                2. <strong>RFB Usado:</strong> Valor escolhido para este item<br>
                                3. <strong>Margem:</strong> Percentual adicional<br>
                                4. <strong>INV Usado:</strong> Valor final calculado
                            </small>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Informações do Item -->
                <h6 class="mb-3"><i class="bi bi-box"></i> Informações do Item</h6>

                <div class="row g-3">
                    <div class="col-md-2">
                        <label for="quantity" class="form-label">QTD <span class="text-danger">*</span></label>
                        <input type="text" class="form-control mask-quantity" id="quantity" name="quantity"
                               value="<?= isset($item['quantity']) ? number_format($item['quantity'], 3, ',', '.') : '1.000' ?>"
                               required placeholder="1.000" tabindex="12">
                    </div>

                    <div class="col-md-2">
                        <label for="unit" class="form-label">Unidade</label>
                        <select class="form-select" id="unit" name="unit" tabindex="13">
                            <option value="PCS" <?= ($item['unit'] ?? 'PCS') === 'PCS' ? 'selected' : '' ?>>PCS - Peças</option>
                            <option value="KG" <?= ($item['unit'] ?? '') === 'KG' ? 'selected' : '' ?>>KG - Quilograma</option>
                            <option value="UN" <?= ($item['unit'] ?? '') === 'UN' ? 'selected' : '' ?>>UN - Unidade</option>
                            <option value="MT" <?= ($item['unit'] ?? '') === 'MT' ? 'selected' : '' ?>>MT - Metro</option>
                            <option value="M2" <?= ($item['unit'] ?? '') === 'M2' ? 'selected' : '' ?>>M² - Metro Quadrado</option>
                            <option value="M3" <?= ($item['unit'] ?? '') === 'M3' ? 'selected' : '' ?>>M³ - Metro Cúbico</option>
                            <option value="LT" <?= ($item['unit'] ?? '') === 'LT' ? 'selected' : '' ?>>LT - Litro</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="gross_weight" class="form-label">Peso Bruto (KG) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control mask-weight" id="gross_weight" name="gross_weight"
                               value="<?= isset($item['gross_weight']) ? number_format($item['gross_weight'], 3, ',', '.') : '0.000' ?>"
                               required placeholder="0.000" tabindex="14">
                    </div>

                    <div class="col-md-2">
                        <label for="weight_discount" class="form-label">% Desconto</label>
                        <div class="input-group">
                            <input type="text" class="form-control mask-percentage" id="weight_discount" name="weight_discount"
                                   value="<?= isset($item['weight_discount']) ? number_format($item['weight_discount'], 2, ',', '.') : '0,00' ?>"
                                   placeholder="0" tabindex="15">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label for="net_weight" class="form-label">Peso Líquido (KG)</label>
                        <input type="text" class="form-control mask-weight" id="net_weight" name="net_weight"
                               value="<?= isset($item['net_weight']) ? number_format((float)$item['net_weight'], 2, ',', '.') : '0,00' ?>"
                               readonly style="background-color: #e9ecef;" tabindex="16">
                    </div>


                    <div class="col-md-4">
                        <label for="total_fob_input" class="form-label">Total FOB (USD) (Calculado)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white">$</span>
                            <input type="text" class="form-control fw-bold" id="total_fob_input" name="total_fob_input"
                                   value="<?= isset($item['total_fob_input']) ? number_format($item['total_fob_input'], 2, ',', '.') : '0,00' ?>"
                                   readonly style="background-color: #cfe2ff;" tabindex="17">
                        </div>
                        <small class="form-text text-muted">INV USADO × (Peso Bruto se divisão=KG | QTD se divisão=QUANTIDADE)</small>
                    </div>

                    <div class="col-md-4">
                        <label for="unit_price_usd" class="form-label">VALOR UNIT FOB/USD (Calculado)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-success text-white">$</span>
                            <input type="text" class="form-control fw-bold" id="unit_price_usd" name="unit_price_usd"
                                   value="<?= isset($item['unit_price_usd']) ? number_format($item['unit_price_usd'], 4, ',', '.') : '0,0000' ?>"
                                   readonly style="background-color: #d1edff;">
                        </div>
                        <small class="form-text text-muted">Total FOB (USD) ÷ QTD</small>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-3">
                        <label for="freight_ttl_kg" class="form-label">Frete TTL/KG (USD)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" class="form-control mask-currency-usd" id="freight_ttl_kg" name="freight_ttl_kg"
                                   value="<?= isset($item['freight_ttl_kg']) ? number_format($item['freight_ttl_kg'], 2, ',', '.') : '0,00' ?>"
                                   step="0.01" min="0" placeholder="0.00" tabindex="18">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label for="freight_usd" class="form-label">Frete (USD)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" class="form-control mask-currency-usd" id="freight_usd" name="freight_usd"
                                   value="<?= isset($item['freight_usd']) ? number_format($item['freight_usd'], 2, ',', '.') : '0,00' ?>"
                                   step="0.01" min="0" placeholder="0.00" tabindex="19">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label for="insurance_usd" class="form-label">Seguro (USD)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" class="form-control mask-currency-usd" id="insurance_usd" name="insurance_usd"
                                   value="<?= isset($item['insurance_usd']) ? number_format($item['insurance_usd'], 2, ',', '.') : '0,00' ?>"
                                   step="0.01" min="0" placeholder="0.00" tabindex="20">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Taxa de Câmbio</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" class="form-control" value="<?= number_format($process['exchange_rate'], 4) ?>"
                                   readonly style="background-color: #f8f9fa;">
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Valores calculados -->
                <h6 class="mb-3"><i class="bi bi-calculator"></i> Cálculos Automáticos</h6>

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Total FOB (USD)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" class="form-control" id="total_fob_display" readonly style="background-color: #f8f9fa;">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Total CIF (USD)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" class="form-control" id="total_cif_usd_display" readonly style="background-color: #f8f9fa;">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Total CIF (BRL)</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" class="form-control" id="total_cif_brl_display" readonly style="background-color: #f8f9fa;">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Total Impostos (BRL)</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" class="form-control" id="total_taxes_display" readonly style="background-color: #f8f9fa;">
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Detalhamento dos Impostos</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <strong>II:</strong> R$ <span id="ii_display">0,00</span><br>
                                        <strong>IPI:</strong> R$ <span id="ipi_display">0,00</span><br>
                                        <strong>PIS:</strong> R$ <span id="pis_display">0,00</span>
                                    </div>
                                    <div class="col-6">
                                        <strong>COFINS:</strong> R$ <span id="cofins_display">0,00</span><br>
                                        <strong>ICMS:</strong> R$ <span id="icms_display">0,00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h6 class="card-title">CUSTO TOTAL FINAL</h6>
                                <h3 class="mb-0">R$ <span id="total_cost_display">0,00</span></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-3">

                <!-- Observações -->
                <div class="row g-3">
                    <div class="col-12">
                        <label for="notes" class="form-label">Observações</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"
                                  placeholder="Observações sobre este item..." tabindex="21"><?= htmlspecialchars($item['notes'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Botões -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" tabindex="22">
                                <i class="bi bi-check-circle"></i>
                                <?= $action === 'create' ? 'Adicionar Item' : 'Atualizar Item' ?>
                            </button>
                            <a href="<?= BASE_URL ?>process-items?process_id=<?= $process['id'] ?>" class="btn btn-outline-secondary" tabindex="23">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Botão Flutuante de Salvar -->
<div id="floating-save-btn" class="floating-save-button" style="display: none;">
    <button type="button" class="btn btn-primary btn-lg shadow-lg" onclick="$('#itemForm').submit();" title="<?= $action === 'create' ? 'Adicionar Item' : 'Atualizar Item' ?>">
        <i class="bi bi-check-circle-fill"></i>
        <span class="btn-text"><?= $action === 'create' ? 'Adicionar' : 'Atualizar' ?></span>
    </button>
</div>

<style>
.floating-save-button {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 1000;
    transition: all 0.3s ease;
}

.floating-save-button .btn {
    border-radius: 50px;
    padding: 15px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
}

.floating-save-button .btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 123, 255, 0.4) !important;
}

.floating-save-button .btn i {
    margin-right: 8px;
    font-size: 1.2em;
}

.floating-save-button .btn-text {
    font-size: 1rem;
}

/* Responsivo - esconder texto em telas pequenas */
@media (max-width: 768px) {
    .floating-save-button .btn {
        padding: 15px;
        border-radius: 50%;
        width: 60px;
        height: 60px;
    }

    .floating-save-button .btn-text {
        display: none;
    }

    .floating-save-button .btn i {
        margin-right: 0;
        font-size: 1.5em;
    }
}

/* Animação de entrada */
.floating-save-button.show {
    display: block !important;
    animation: fadeInUp 0.5s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
// Dados do processo
const processData = {
    id: <?= $process['id'] ?>,
    totalFreightUsd: <?= (float)($process['total_freight_usd'] ?? 0) ?>
};
console.log('Process Data:', processData);

// Aguardar jQuery estar disponível
document.addEventListener('DOMContentLoaded', function() {
    // Aguardar jQuery
    function waitForJQuery() {
        if (typeof $ !== 'undefined') {
            initializeForm();
        } else {
            setTimeout(waitForJQuery, 100);
        }
    }
    waitForJQuery();
});

function initializeForm() {
    // Dar foco automático no campo de busca do produto
    const productSearchField = document.getElementById('product_search');
    if (productSearchField) {
        setTimeout(() => productSearchField.focus(), 100);
    }

    // Aplicar máscaras de valor
    initValueMasks();


    // Função para normalizar texto (remover acentos e pontuação)
    function normalizeText(text) {
        if (!text) return '';

        // Remover acentos
        text = text.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        // Remover pontos, traços, espaços extras
        text = text.replace(/[.\-\s]/g, '');
        // Converter para minúsculas
        return text.toLowerCase();
    }

    // ===== SISTEMA DE BUSCA DE PRODUTOS =====
    let searchTimeout = null;
    let currentSearchTerm = '';

    // Função para buscar produtos
    function searchProducts(term) {
        if (!term || term.length < 2) {
            hideProductResults();
            return;
        }

        // Mostrar loading
        $('#product_loading').show();
        $('#product_results').hide();

        // Fazer requisição AJAX
        $.ajax({
            url: '<?= BASE_URL ?>api/products/search',
            method: 'GET',
            data: { term: term },
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(response) {
                $('#product_loading').hide();

                if (response.success && response.data && response.data.length > 0) {
                    showProductResults(response.data);
                } else {
                    showNoResults();
                }
            },
            error: function(xhr, status, error) {
                $('#product_loading').hide();
                showNoResults('Erro na busca. Tente novamente.');
            }
        });
    }

    // Mostrar resultados
    function showProductResults(products) {
        let html = '';

        products.forEach(function(product, index) {
            // Armazenar produto em variável global para evitar problemas com aspas
            window[`tempProduct_${index}`] = product;

            html += `
                <div class="product-result-item p-2 border-bottom"
                     style="cursor: pointer; transition: background-color 0.2s;"
                     onmouseover="this.style.backgroundColor='#f8f9fa'"
                     onmouseout="this.style.backgroundColor='white'"
                     onclick="selectProductFromTemp(${index})">
                    <div><strong>${escapeHtml(product.description || 'Produto sem descrição')}</strong></div>
                    <small class="text-muted">NCM: ${product.ncm}</small>
                </div>
            `;
        });

        $('#product_results').html(html).show();
    }

    // Mostrar quando não há resultados
    function showNoResults(message = 'Nenhum produto encontrado') {
        $('#product_results').html(`
            <div class="p-3 text-center text-muted">
                <i class="bi bi-search"></i><br>
                <small>${message}</small>
            </div>
        `).show();
    }

    // Esconder resultados
    function hideProductResults() {
        $('#product_results').hide();
        $('#product_loading').hide();
    }

    // Escapar HTML
    function escapeHtml(text) {
        if (!text) return '';
        return String(text).replace(/[&<>"'`=/\\]/g, function(match) {
            const escapeMap = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
                '`': '&#96;',
                '=': '&#61;',
                '/': '&#47;',
                '\\': '&#92;'
            };
            return escapeMap[match];
        });
    }

    // Event listeners
    $('#product_search').on('input', function() {
        const term = $(this).val().trim();
        currentSearchTerm = term;

        // Clear timeout anterior
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        // Aguardar 300ms após parar de digitar
        searchTimeout = setTimeout(() => {
            if (currentSearchTerm === term) {
                searchProducts(term);
            }
        }, 300);
    });

    // Esconder resultados ao clicar fora
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#product_search, #product_results, #product_loading').length) {
            hideProductResults();
        }
    });

    // Mostrar resultados ao focar no input (se há termo)
    $('#product_search').on('focus', function() {
        const term = $(this).val().trim();
        if (term.length >= 2) {
            searchProducts(term);
        }
    });

    // ===== FUNÇÕES GLOBAIS =====

    // Selecionar produto via índice temporário (mais seguro que passar strings)
    window.selectProductFromTemp = function(index) {
        const product = window[`tempProduct_${index}`];
        if (product) {
            selectProduct(
                product.id,
                product.text,
                product.description,
                product.ncm,
                product.division_type,
                product.ii_rate,
                product.ipi_rate,
                product.pis_rate,
                product.cofins_rate,
                product.icms_rate,
                product.rfb_min,
                product.rfb_max
            );
        }
    };

    // Selecionar produto (função principal)
    window.selectProduct = function(id, text, description, ncm, divisionType, iiRate, ipiRate, pisRate, cofinsRate, icmsRate, rfbMin, rfbMax) {

        // Preencher campos hidden e visible
        $('#product_id').val(id);
        $('#product_search').val('');

        // Mostrar produto selecionado
        $('#selected_product_text').text(text);
        $('#product_selected').show();

        // Preencher campos do formulário
        $('#product_description').val(description || '');
        $('#product_ncm').val(ncm || '');
        $('#ii_rate').val(iiRate || '0');
        $('#ipi_rate').val(ipiRate || '0');
        $('#pis_rate').val(pisRate || '0');
        $('#cofins_rate').val(cofinsRate || '0');
        $('#icms_rate').val(icmsRate || '0');
        $('#product_division').val(divisionType || 'QUANTIDADE');

        // Preencher valores RFB
        $('#rfb_min_product').val(parseFloat(rfbMin || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#rfb_max_product').val(parseFloat(rfbMax || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

        // Se não estamos editando, usar o valor mínimo como padrão para RFB usado
        if (!$('#rfb_used').val() || $('#rfb_used').val() == '0,00' || $('#rfb_used').val() == '0') {
            if (rfbMin > 0) {
                $('#rfb_used').val(parseFloat(rfbMin).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#rfb_option').val('min');
            }
        }

        // Esconder resultados
        hideProductResults();

        // Aplicar configurações específicas por porto (sobrescreve valores padrão se existir)
        applyPortConfigs(id);

        // Calcular valores (se a função existir)
        if (typeof calculateValues === 'function') {
            calculateValues();
        }

        // Dar foco no campo de descrição após selecionar produto
        setTimeout(() => {
            const descriptionField = document.getElementById('product_description');
            if (descriptionField) {
                descriptionField.focus();
            }
        }, 100);
    };

    // Limpar seleção de produto (chamada pelo onclick)
    window.clearProductSelection = function() {

        // Limpar campos
        $('#product_id').val('');
        $('#product_search').val('');
        $('#product_selected').hide();

        // Limpar campos do formulário
        $('#product_description, #product_ncm').val('');
        $('#ii_rate, #ipi_rate, #pis_rate, #cofins_rate, #icms_rate').val('0');
        $('#product_division').val('QUANTIDADE');

        // Focar no campo de busca
        $('#product_search').focus();
    };

    const exchangeRate = <?= $process['exchange_rate'] ?>;
    let updateProductTimer = null;

    // Navegação com Enter
    $(document).on('keypress', 'input,select,textarea', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            const $next = $('[tabindex]:visible').filter(function() {
                return parseInt($(this).attr('tabindex')) > parseInt(e.target.tabIndex);
            }).first();

            if ($next.length > 0) {
                $next.focus();
                if ($next.hasClass('select2-selection')) {
                    $next.trigger('click');
                }
            } else {
                $('#itemForm').submit();
            }
        }
    });


    // Event listeners removidos - agora usamos as funções globais selectProduct() e clearProductSelection()

    // Atualizar produto quando campos forem alterados
    function scheduleProductUpdate() {
        if (!$('#product_id').val()) return;

        clearTimeout(updateProductTimer);
        updateProductTimer = setTimeout(function() {
            updateProduct();
        }, 1000); // Aguarda 1 segundo após parar de digitar
    }

    // Função para atualizar produto
    function updateProduct() {
        const productId = $('#product_id').val();
        if (!productId) return;

        const data = {
            id: productId,
            // description removido - agora cria variações ao invés de atualizar
            ncm: $('#product_ncm').val(),
            ii_rate: $('#ii_rate').val(),
            ipi_rate: $('#ipi_rate').val(),
            pis_rate: $('#pis_rate').val(),
            cofins_rate: $('#cofins_rate').val(),
            icms_rate: $('#icms_rate').val(),
            division_type: $('#product_division').val()
        };

        // Enviar atualização silenciosamente
        $.ajax({
            url: '<?= BASE_URL ?>api/products/update',
            method: 'POST',
            data: data,
            success: function(response) {
            }
        });
    }

    // Função para auto-save de campo específico
    function autoSaveProductField(fieldName, value, fieldElement) {
        const productId = $('#product_id').val();
        if (!productId) return;

        // Dados para atualização
        const data = {
            id: productId
        };

        // Mapear nome do campo para nome do banco
        // NOTA: product_description foi removido do auto-save pois agora cria variações
        const fieldMapping = {
            'product_ncm': 'ncm',
            'ii_rate': 'ii_rate',
            'ipi_rate': 'ipi_rate',
            'pis_rate': 'pis_rate',
            'cofins_rate': 'cofins_rate',
            'icms_rate': 'icms_rate',
            'product_division': 'division_type'
        };

        const dbFieldName = fieldMapping[fieldName];
        if (dbFieldName) {
            data[dbFieldName] = value;
        }

        // Mostrar indicador de salvamento
        fieldElement.addClass('border-warning');

        // Enviar atualização via AJAX
        $.ajax({
            url: '<?= BASE_URL ?>api/products/update',
            method: 'POST',
            data: data,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {

                // Atualizar valor original e indicar sucesso
                fieldElement.data('original-value', value);
                fieldElement.removeClass('border-warning').addClass('border-success');

                // Toast de sucesso
                showSuccess('Campo salvo automaticamente');

                // Remover indicador após 2 segundos
                setTimeout(function() {
                    fieldElement.removeClass('border-success');
                }, 2000);
            },
            error: function() {
                // Indicar erro
                fieldElement.removeClass('border-warning').addClass('border-danger');
                showError('Erro ao salvar campo');

                // Remover indicador após 3 segundos
                setTimeout(function() {
                    fieldElement.removeClass('border-danger');
                }, 3000);
            }
        });
    }

    // Monitorar mudanças nos campos do produto - auto-save ao sair do campo
    $('#product_description, #product_ncm, #ii_rate, #ipi_rate, #pis_rate, #cofins_rate, #icms_rate, #product_division').on('blur', function() {
        const field = $(this);
        const fieldName = field.attr('name');
        const currentValue = field.val();
        const originalValue = field.data('original-value') || '';

        // Se o valor mudou, salvar automaticamente
        if (currentValue !== originalValue && $('#product_id').val()) {
            autoSaveProductField(fieldName, currentValue, field);
        }

        calculateValues();
    });

    // Armazenar valores originais quando o produto é selecionado
    $('#product_id').on('change', function() {
        setTimeout(function() {
            $('#product_description').data('original-value', $('#product_description').val());
            $('#product_ncm').data('original-value', $('#product_ncm').val());
            $('#ii_rate').data('original-value', $('#ii_rate').val());
            $('#ipi_rate').data('original-value', $('#ipi_rate').val());
            $('#pis_rate').data('original-value', $('#pis_rate').val());
            $('#cofins_rate').data('original-value', $('#cofins_rate').val());
            $('#icms_rate').data('original-value', $('#icms_rate').val());
            $('#product_division').data('original-value', $('#product_division').val());
        }, 100);
    });

    // ===== SISTEMA RFB =====

    // Atualizar campos RFB do produto
    function updateRfbFields(data) {
        const rfbMin = parseFloat(data.rfb_min || 0);
        const rfbMax = parseFloat(data.rfb_max || 0);

        $('#rfb_min_product').val(rfbMin.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#rfb_max_product').val(rfbMax.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

        // Verificar se há valores configurados
        const hasRfbConfig = (rfbMin > 0 || rfbMax > 0);

        // Controlar estado dos campos baseado na configuração
        toggleRfbFieldsState(hasRfbConfig);

        // Se não há valor RFB usado, usar o mínimo por padrão (se existir)
        if (!$('#rfb_used').val() || $('#rfb_used').val() == '0') {
            if (hasRfbConfig) {
                $('#rfb_used').val(rfbMin.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#rfb_option').val('min');
            } else {
                $('#rfb_used').val('0.00');
                $('#rfb_option').val('custom');
            }
        }

        calculateRfbValues();
    }

    // Controlar estado dos campos RFB (habilitado/desabilitado)
    function toggleRfbFieldsState(hasConfig) {
        const $rfbMinField = $('#rfb_min_product');
        const $rfbMaxField = $('#rfb_max_product');
        const $saveMinBtn = $('#save_rfb_min');
        const $saveMaxBtn = $('#save_rfb_max');

        if (hasConfig) {
            // Há configuração: campos readonly, botões ocultos
            $rfbMinField.prop('readonly', true).addClass('bg-light');
            $rfbMaxField.prop('readonly', true).addClass('bg-light');
            $saveMinBtn.hide();
            $saveMaxBtn.hide();
        } else {
            // Sem configuração: campos editáveis, botões visíveis
            $rfbMinField.prop('readonly', false).removeClass('bg-light');
            $rfbMaxField.prop('readonly', false).removeClass('bg-light');
            $saveMinBtn.show();
            $saveMaxBtn.show();
        }
    }

    // Calcular valores RFB
    function calculateRfbValues() {
        // Converter valores com formatação brasileira para números
        const rfbUsedStr = $('#rfb_used').val() || '0';
        const marginStr = $('#rfb_margin').val() || '0';

        // Remover formatação brasileira (vírgula como decimal, pontos como separadores)
        const rfbUsed = parseFloat(rfbUsedStr.replace(/\./g, '').replace(',', '.')) || 0;
        const margin = parseFloat(marginStr.replace(/\./g, '').replace(',', '.')) || 0;

        // INV Usado = RFB Usado + Margem (soma simples, não percentual)
        const invUsed = rfbUsed + margin;

        $('#inv_used_display').val(invUsed.toFixed(2));
        $('#inv_used').val(invUsed.toFixed(2));

        // Recalcular Total FOB quando INV_USED muda
        calculateValues();
    }

    // ===== SISTEMA DE PESO LÍQUIDO =====

    // Função para calcular peso líquido
    function calculateNetWeight() {
        // Converter valores brasileiros para números
        const grossWeightStr = $('#gross_weight').val() || '0';
        const discountStr = $('#weight_discount').val() || '0';

        // Remover formatação brasileira (pontos como separadores de milhares, vírgula como decimal)
        const grossWeight = parseFloat(grossWeightStr.replace(/\./g, '').replace(',', '.')) || 0;
        const discount = parseFloat(discountStr.replace(/\./g, '').replace(',', '.')) || 0;

        // Fórmula: Peso Líquido = Peso Bruto - (Peso Bruto * (%Desconto/100))
        const netWeight = grossWeight - (grossWeight * (discount / 100));

        // Formatação precisa: para 5.198 com 9.80% = 4.68860, mostrar como 4.688,60
        const netWeightFormatted = netWeight.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        $('#net_weight').val(netWeightFormatted);
    }

    // Event listeners para recalcular peso líquido
    $('#gross_weight, #weight_discount').on('input blur', function() {
        calculateNetWeight();
        calculateValues(); // Recalcular Total FOB quando peso líquido muda
    });

    // Calcular peso líquido inicial
    calculateNetWeight();

    // ===== SISTEMA DE CÁLCULO VALOR UNIT FOB/USD =====

    // Função para calcular VALOR UNIT FOB/USD = Total FOB (USD) / QTD
    function calculateUnitPriceFob() {
        // Esta função agora é chamada após calculateValues() calcular o Total FOB
        // A lógica foi movida para calculateValues()
    }

    // ===== FIM SISTEMA VALOR UNIT FOB/USD =====

    // ===== FIM SISTEMA PESO LÍQUIDO =====

    // Evento para mudança na opção RFB
    $('#rfb_option').on('change', function() {
        const option = $(this).val();
        const rfbMin = parseFloat($('#rfb_min_product').val()) || 0;
        const rfbMax = parseFloat($('#rfb_max_product').val()) || 0;

        switch(option) {
            case 'min':
                $('#rfb_used').val(rfbMin.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                break;
            case 'max':
                $('#rfb_used').val(rfbMax.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                break;
            case 'custom':
                // Manter valor atual
                break;
        }

        calculateRfbValues();
    });

    // Eventos para recalcular RFB
    $('#rfb_used, #rfb_margin').on('input blur', calculateRfbValues);

    // Botões para salvar RFB como padrão do produto
    $('#save_rfb_min').on('click', function() {
        saveProductRfbValue('min');
    });

    $('#save_rfb_max').on('click', function() {
        saveProductRfbValue('max');
    });

    // Função para salvar valores RFB no produto
    function saveProductRfbValue(type) {
        const productId = $('#product_id').val();
        if (!productId) {
            showError('Selecione um produto primeiro');
            return;
        }

        const value = type === 'min' ? $('#rfb_min_product').val() : $('#rfb_max_product').val();
        const numValue = parseFloat(value);

        if (isNaN(numValue) || numValue <= 0) {
            showError('Digite um valor válido maior que zero');
            return;
        }

        // Mostrar loading no botão
        const $btn = type === 'min' ? $('#save_rfb_min') : $('#save_rfb_max');
        const originalHtml = $btn.html();
        $btn.html('<i class="bi bi-hourglass-split"></i>').prop('disabled', true);

        // Preparar dados para atualização
        const data = {
            id: productId
        };
        data[`rfb_${type}`] = numValue;

        // Enviar via AJAX
        $.ajax({
            url: '<?= BASE_URL ?>api/products/update',
            method: 'POST',
            data: data,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    showSuccess(`RFB ${type === 'min' ? 'mínimo' : 'máximo'} salvo como padrão do produto`);

                    // Atualizar dados do select para refletir a mudança
                    const $option = $('#product_id option:selected');
                    $option.attr(`data-rfb-${type}`, numValue);

                    // Também criar configuração do porto
                    savePortRfbConfig(productId, type, numValue);

                    // Marcar como configurado
                    const hasMin = parseFloat($('#rfb_min_product').val()) > 0;
                    const hasMax = parseFloat($('#rfb_max_product').val()) > 0;
                    toggleRfbFieldsState(hasMin || hasMax);
                } else {
                    showError(response.message || 'Erro ao salvar valor RFB');
                }
            },
            error: function() {
                showError('Erro ao salvar valor RFB no produto');
            },
            complete: function() {
                $btn.html(originalHtml).prop('disabled', false);
            }
        });
    }

    // Função para salvar configuração RFB do porto
    function savePortRfbConfig(productId, type, value) {
        const processPortId = <?= $process['destination_port_id'] ?? 'null' ?>;

        if (!processPortId) return;

        const configData = {
            product_id: productId,
            port_id: processPortId
        };
        configData[`rfb_${type}`] = value;

        $.ajax({
            url: '<?= BASE_URL ?>api/products/savePortConfig',
            method: 'POST',
            data: configData,
            success: function(response) {
            },
            error: function() {
            }
        });
    }

    // ===== FIM SISTEMA RFB =====

    // Calcular valores
    // Função para converter valores com máscara para número
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
            // Formato brasileiro (3.232,22) - múltiplos pontos ou vírgula após ponto
            value = value.replace(/\./g, '');
            value = value.replace(',', '.');
        } else if (commas > 1 || (commas === 1 && dots === 1 && value.indexOf('.') > value.indexOf(','))) {
            // Formato americano (3,232.22) - múltiplas vírgulas ou ponto após vírgula
            value = value.replace(/,/g, '');
        } else if (commas === 1 && dots === 0) {
            // Apenas vírgula, assumir formato brasileiro
            value = value.replace(',', '.');
        }

        return parseFloat(value) || 0;
    }

    function calculateValues() {
        const quantity = unmaskCurrency($('#quantity').val());
        const invUsed = parseFloat($('#inv_used').val()) || 0;
        const netWeightStr = $('#net_weight').val() || '0';
        const freight = unmaskCurrency($('#freight_usd').val());
        const freightTtlKg = unmaskCurrency($('#freight_ttl_kg').val());
        const insurance = unmaskCurrency($('#insurance_usd').val());

        // Converter peso líquido (formato brasileiro) para número
        const netWeight = parseFloat(netWeightStr.replace(/\./g, '').replace(',', '.')) || 0;

        // Converter peso bruto (formato brasileiro) para número
        const grossWeightStr = $('#gross_weight').val() || '0';
        const grossWeight = parseFloat(grossWeightStr.replace(/\./g, '').replace(',', '.')) || 0;

        // Obter tipo de divisão
        const divisionType = $('#product_division').val();

        // Calcular Total FOB (USD) baseado na divisão
        let totalFob = 0;
        if (divisionType === 'KG') {
            // Quando divisão for KG, usar Peso BRUTO
            totalFob = invUsed * grossWeight;
        } else {
            // Quando divisão for QUANTIDADE, usar INV usado × QTD
            totalFob = invUsed * quantity;
        }
        $('#total_fob_input').val(totalFob.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));

        // Calcular VALOR UNIT FOB/USD = Total FOB (USD) ÷ QTD
        let unitPrice = 0;
        if (quantity > 0 && totalFob > 0) {
            unitPrice = totalFob / quantity;
            $('#unit_price_usd').val(unitPrice.toLocaleString('pt-BR', {
                minimumFractionDigits: 4,
                maximumFractionDigits: 4
            }));
        } else {
            $('#unit_price_usd').val('0,0000');
        }

        // Nova fórmula: Total CIF (USD) = Total FOB (USD) + TTL/KG
        const totalCifUsd = totalFob + freightTtlKg;

        // Nova fórmula: Total CIF (BRL) = Total CIF (USD) * Taxa de Cambio
        const totalCifBrl = totalCifUsd * exchangeRate;


        // Impostos - também usar unmask para percentuais se tiverem máscara
        const iiRate = unmaskCurrency($('#ii_rate').val());
        const ipiRate = unmaskCurrency($('#ipi_rate').val());
        const pisRate = unmaskCurrency($('#pis_rate').val());
        const cofinsRate = unmaskCurrency($('#cofins_rate').val());
        const icmsRate = unmaskCurrency($('#icms_rate').val());

        const iiValue = totalCifBrl * (iiRate / 100);
        const ipiBase = totalCifBrl + iiValue;
        const ipiValue = ipiBase * (ipiRate / 100);

        const pisBase = totalCifBrl + iiValue + ipiValue;
        const pisValue = pisBase * (pisRate / 100);

        const cofinsBase = totalCifBrl + iiValue + ipiValue;
        const cofinsValue = cofinsBase * (cofinsRate / 100);

        const icmsBase = totalCifBrl + iiValue + ipiValue + pisValue + cofinsValue;
        const icmsValue = icmsBase * (icmsRate / 100);

        const totalTaxes = iiValue + ipiValue + pisValue + cofinsValue + icmsValue;
        const totalCost = totalCifBrl + totalTaxes;

        // Atualizar displays com formatação brasileira
        $('#total_fob_display').val(totalFob.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#total_cif_usd_display').val(totalCifUsd.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#total_cif_brl_display').val(totalCifBrl.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#total_taxes_display').val(totalTaxes.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#total_cost_display').text(totalCost.toLocaleString('pt-BR', {minimumFractionDigits: 2}));

        // Detalhamento impostos
        $('#ii_display').text(iiValue.toLocaleString('pt-BR', {minimumFractionDigits: 2}));
        $('#ipi_display').text(ipiValue.toLocaleString('pt-BR', {minimumFractionDigits: 2}));
        $('#pis_display').text(pisValue.toLocaleString('pt-BR', {minimumFractionDigits: 2}));
        $('#cofins_display').text(cofinsValue.toLocaleString('pt-BR', {minimumFractionDigits: 2}));
        $('#icms_display').text(icmsValue.toLocaleString('pt-BR', {minimumFractionDigits: 2}));
    }

    // Calcular Frete TTL/KG automaticamente
    function calculateFreightTtlKg() {
        const grossWeightStr = $('#gross_weight').val() || '0,000';
        const grossWeight = parseFloat(grossWeightStr.replace(/\./g, '').replace(',', '.')) || 0;

        console.log('Debug Frete TTL/KG - Peso Bruto:', grossWeight, 'Frete Processo:', processData.totalFreightUsd);

        // Se não há frete no processo, não calcular automaticamente
        if (processData.totalFreightUsd <= 0) {
            console.log('Processo sem frete configurado - cálculo automático desabilitado');
            return;
        }

        if (grossWeight > 0 && processData.totalFreightUsd > 0) {
            // Buscar peso bruto total de todos os itens do processo via AJAX
            fetch(`<?= BASE_URL ?>api/process-items/total-weight?process_id=${processData.id}`)
                .then(response => response.json())
                .then(data => {
                    console.log('=== CÁLCULO FRETE TTL/KG ===');
                    console.log('1. Frete Total do Processo (USD):', processData.totalFreightUsd);
                    console.log('2. Peso Bruto Total de Todos os Itens (KG):', data.total_gross_weight);
                    console.log('3. Peso Bruto deste Item (KG):', grossWeight);

                    if (data.success) {
                        // Corrigir peso total - incluir peso atual do item sendo editado
                        const currentItemId = $('input[name="id"]').val(); // ID do item atual (se editando)
                        const currentItemOldWeight = parseFloat('<?= isset($item["gross_weight"]) ? $item["gross_weight"] : 0 ?>') || 0;

                        let correctedTotalWeight = data.total_gross_weight;

                        if (currentItemId) {
                            // Editando: remover peso antigo e somar peso atual
                            console.log('   EDITANDO - Peso antigo do item:', currentItemOldWeight);
                            console.log('   CORREÇÃO = Total Atual - Peso Antigo + Peso Novo =', data.total_gross_weight, '-', currentItemOldWeight, '+', grossWeight);
                            correctedTotalWeight = data.total_gross_weight - currentItemOldWeight + grossWeight;
                        } else {
                            // Criando: somar peso atual ao total
                            console.log('   CRIANDO - Somando peso novo ao total:', grossWeight);
                            console.log('   CORREÇÃO = Total Atual + Peso Novo =', data.total_gross_weight, '+', grossWeight);
                            correctedTotalWeight = data.total_gross_weight + grossWeight;
                        }

                        console.log('4. Peso Total CORRIGIDO (incluindo item atual):', correctedTotalWeight);

                        if (correctedTotalWeight > 0) {
                            // FÓRMULA CORRETA: Rateio por proporção de peso
                            const weightProportion = grossWeight / correctedTotalWeight;
                            console.log('5. Proporção de Peso = Peso Item ÷ Peso Total =', grossWeight, '÷', correctedTotalWeight, '=', weightProportion);

                            // Frete TTL/KG do item = Frete Total × Proporção do Peso
                            const itemFreightTtlKg = processData.totalFreightUsd * weightProportion;
                            console.log('6. Frete TTL/KG Item = Frete Total × Proporção =', processData.totalFreightUsd, '×', weightProportion, '=', itemFreightTtlKg);
                            console.log('7. RESULTADO FINAL:', itemFreightTtlKg.toFixed(2), 'USD');
                            console.log('===========================');

                            // Atualizar o campo
                            $('#freight_ttl_kg').val(itemFreightTtlKg.toLocaleString('pt-BR', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }));

                            // Recalcular valores
                            calculateValues();
                        }
                    }
                })
                .catch(error => {
                    console.error('Erro ao calcular Frete TTL/KG:', error);
                });
        } else {
            $('#freight_ttl_kg').val('0,00');
            calculateValues();
        }
    }

    // Eventos para recalcular valores
    $('#quantity, #freight_usd, #freight_ttl_kg, #insurance_usd').on('input blur', calculateValues);
    $('#ii_rate, #ipi_rate, #pis_rate, #cofins_rate, #icms_rate').on('input blur', calculateValues);

    // Evento para calcular Frete TTL/KG quando peso bruto mudar
    $('#gross_weight').on('input blur', calculateFreightTtlKg);

    // Calcular valores iniciais
    if ($('#product_id').val()) {
        calculateRfbValues();
        calculateNetWeight();
        calculateFreightTtlKg();
        calculateValues();
    }

    // Submissão do formulário - NÃO usar AJAX para evitar JSON
    $('#itemForm').on('submit', function(e) {
        // Não prevenir o comportamento padrão
        // O formulário será enviado normalmente

        // Apenas validar campos
        if (!$('#product_id').val()) {
            e.preventDefault();
            showError('Selecione um produto');
            $('#product_id').focus();
            return false;
        }

        const quantity = parseFloat($('#quantity').val());
        // Converter valor unitário brasileiro para número
        const unitPriceStr = $('#unit_price_usd').val();
        const unitPrice = parseFloat(unitPriceStr.replace(/\./g, '').replace(',', '.')) || 0;

        if (!quantity || quantity <= 0) {
            e.preventDefault();
            showError('Quantidade deve ser maior que zero');
            $('#quantity').focus();
            return false;
        }

        // Validação alterada: aceitar valores calculados automaticamente
        if (unitPrice <= 0) {
            e.preventDefault();
            showError('Valores de INV USADO e campos de peso/quantidade devem ser maiores que zero para calcular o preço unitário');
            return false;
        }

        return true;
    });

    // ===== CONFIGURAÇÕES POR PORTO =====

    // Cache de configurações para evitar múltiplas requisições
    let portConfigs = {};

    // Aplicar configurações específicas do porto para o produto
    function applyPortConfigs(productId) {
        const processPortId = <?= $process['destination_port_id'] ?? 'null' ?>;

        if (!processPortId || !productId) {
            return;
        }

        // Verificar se já temos as configurações em cache
        if (portConfigs[processPortId]) {
            applyConfigToProduct(productId, portConfigs[processPortId]);
            return;
        }

        // Buscar configurações via AJAX
        $.get('<?= BASE_URL ?>api/products/getPortConfigs', {
            product_id: productId
        })
        .done(function(response) {
            if (response && response.success) {
                // Converter array de configurações para objeto indexado por port_id
                const configsByPort = {};
                response.data.forEach(config => {
                    if (!configsByPort[config.port_id]) {
                        configsByPort[config.port_id] = {};
                    }
                    configsByPort[config.port_id][config.product_id] = config;
                });

                portConfigs[processPortId] = configsByPort[processPortId] || {};
                applyConfigToProduct(productId, portConfigs[processPortId]);
            } else {
                // Sem configurações específicas, usar valores padrão
                portConfigs[processPortId] = {};
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Erro ao carregar configurações por porto:', error);
            portConfigs[processPortId] = {};
        });
    }

    // Aplicar configuração específica ao produto
    function applyConfigToProduct(productId, configs) {
        const config = configs[productId];

        if (!config) {
            return; // Sem configuração específica para este produto
        }


        // Aplicar valores RFB se configurados (valores do PORTO sobrescrevem valores do PRODUTO)
        if (config.rfb_min !== null && config.rfb_min !== '' && parseFloat(config.rfb_min) > 0) {
            $('#rfb_min_product').val(parseFloat(config.rfb_min).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        }

        if (config.rfb_max !== null && config.rfb_max !== '' && parseFloat(config.rfb_max) > 0) {
            $('#rfb_max_product').val(parseFloat(config.rfb_max).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        }

        // Aplicar divisão específica por porto
        if (config.division_type) {
            const divisionValue = config.division_type === 'PC' ? 'QUANTIDADE' : 'PESO';
            $('#product_division').val(divisionValue);
            console.log('Aplicada divisão específica por porto:', config.division_type, '->', divisionValue);
        }

        // Sempre marcar campos como readonly quando há configuração do porto
        // (mesmo se os valores forem 0, pois indica que há configuração específica)
        toggleRfbFieldsState(true); // Campos readonly pois há configuração do porto

        // Aplicar margem padrão
        if (config.default_margin > 0) {
            $('#rfb_margin').val(parseFloat(config.default_margin).toFixed(2));
        }

        // Aplicar percentuais de frete e seguro se configurados
        if (config.freight_percentage > 0 || config.insurance_percentage > 0) {
            const totalFob = parseFloat($('#total_fob_usd').val()) || 0;

            if (config.freight_percentage > 0 && totalFob > 0) {
                const freightValue = totalFob * (config.freight_percentage / 100);
                $('#freight_usd').val(freightValue.toFixed(2));
            }

            if (config.insurance_percentage > 0 && totalFob > 0) {
                const insuranceValue = totalFob * (config.insurance_percentage / 100);
                $('#insurance_usd').val(insuranceValue.toFixed(2));
            }
        }

        // Recalcular valores após aplicar configurações
        calculateRfbValues();
        calculateValues();

        // Mostrar notificação de configuração aplicada
    }

    // Calcular Frete TTL/KG sempre ao inicializar (não só quando há produto)
    setTimeout(() => {
        console.log('=== INICIALIZANDO CÁLCULOS ===');
        console.log('Peso Bruto:', $('#gross_weight').val());
        console.log('Peso Líquido:', $('#net_weight').val());
        console.log('Frete TTL/KG:', $('#freight_ttl_kg').val());
        calculateFreightTtlKg();
        calculateNetWeight();
    }, 500); // Aguarda 500ms para garantir que campos foram populados

    // ===== CONTROLE DO BOTÃO FLUTUANTE =====
    let isFloatingButtonVisible = false;

    function showFloatingButton() {
        if (!isFloatingButtonVisible) {
            $('#floating-save-btn').addClass('show').show();
            isFloatingButtonVisible = true;
        }
    }

    function hideFloatingButton() {
        if (isFloatingButtonVisible) {
            $('#floating-save-btn').removeClass('show').hide();
            isFloatingButtonVisible = false;
        }
    }

    // Controlar visibilidade baseado no scroll
    $(window).on('scroll', function() {
        const $submitButton = $('button[type="submit"]');
        if ($submitButton.length) {
            const submitButtonOffset = $submitButton.offset().top;
            const windowHeight = $(window).height();
            const scrollTop = $(window).scrollTop();

            // Se o botão original estiver fora da tela (abaixo), mostrar flutuante
            if (scrollTop + windowHeight < submitButtonOffset + 50) {
                showFloatingButton();
            } else {
                hideFloatingButton();
            }
        }
    });

    // Mostrar inicialmente se necessário
    setTimeout(() => {
        $(window).trigger('scroll');
    }, 1000);

    // ===== FIM CONFIGURAÇÕES POR PORTO =====
}
</script>