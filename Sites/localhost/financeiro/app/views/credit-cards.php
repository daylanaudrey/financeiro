<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-credit-card me-3"></i>
        Cartões de Crédito
    </h1>
    <div class="quick-actions">
        <button class="btn btn-primary" onclick="openCardModal()">
            <i class="fas fa-plus me-2"></i>
            Novo Cartão
        </button>
    </div>
</div>

<!-- Cards de Resumo -->
<div class="row mb-4">
    <?php
    $totalCards = count($creditCards);
    $activeCards = array_filter($creditCards, function($card) { return $card['ativo']; });
    $totalLimit = array_sum(array_column($activeCards, 'limite_total'));
    $usedLimit = array_sum(array_column($activeCards, 'limite_usado'));
    $availableLimit = $totalLimit - $usedLimit;
    ?>
    
    <div class="col-md-3">
        <div class="stat-card info">
            <div class="position-relative">
                <h6 class="mb-2 opacity-90">Total de Cartões</h6>
                <h4 class="mb-0"><?= $totalCards ?></h4>
                <i class="fas fa-credit-card stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success">
            <div class="position-relative">
                <h6 class="mb-2 opacity-90">Cartões Ativos</h6>
                <h4 class="mb-0"><?= count($activeCards) ?></h4>
                <i class="fas fa-check-circle stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="position-relative">
                <h6 class="mb-2 opacity-90">Limite Total</h6>
                <h4 class="mb-0">R$ <?= number_format($totalLimit, 2, ',', '.') ?></h4>
                <i class="fas fa-wallet stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card <?= $availableLimit < ($totalLimit * 0.2) ? 'danger' : 'success' ?>">
            <div class="position-relative">
                <h6 class="mb-2 opacity-90">Limite Disponível</h6>
                <h4 class="mb-0">R$ <?= number_format($availableLimit, 2, ',', '.') ?></h4>
                <i class="fas fa-hand-holding-usd stat-icon"></i>
            </div>
        </div>
    </div>
</div>

<!-- Faturas de Cartão de Crédito (Mês Atual) -->
<?php if (!empty($creditCardInvoices)): ?>
<div class="row mb-4">
    <div class="col-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="text-muted mb-0">
                <i class="fas fa-file-invoice-dollar me-2"></i>
                Faturas do Mês (<?= date('m/Y') ?>)
            </h5>
            <small class="text-muted">Valores aproximados baseados nos lançamentos</small>
        </div>
    </div>
    <?php foreach ($creditCardInvoices as $invoice): ?>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100" style="border-left: 4px solid <?= htmlspecialchars($invoice['cor']) ?>">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="mb-1 fw-bold"><?= htmlspecialchars($invoice['credit_card_name']) ?></h6>
                            <small class="text-muted">
                                <i class="fab fa-cc-<?= strtolower($invoice['bandeira']) ?> me-1"></i>
                                <?= $invoice['total_transactions'] ?> lançamento<?= $invoice['total_transactions'] == 1 ? '' : 's' ?>
                            </small>
                        </div>
                        <div class="text-end">
                            <small class="text-muted">Total da Fatura</small>
                            <div class="h5 mb-0 text-danger">
                                R$ <?= number_format($invoice['total_fatura'], 2, ',', '.') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (empty($creditCards)): ?>
    <!-- Estado Vazio -->
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <i class="fas fa-credit-card"></i>
                <h5>Nenhum cartão de crédito cadastrado</h5>
                <p>Cadastre seus cartões para ter controle completo dos seus gastos.</p>
                <button class="btn btn-primary" onclick="openCardModal()">
                    <i class="fas fa-plus me-2"></i>
                    Cadastrar Primeiro Cartão
                </button>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Lista de Cartões -->
    <div class="row">
        <?php foreach ($creditCards as $card): ?>
            <?php
            $percentualUso = $card['percentual_uso'] ?? 0;
            $limiteDisponivel = $card['limite_disponivel'] ?? 0;
            $corProgresso = $percentualUso < 50 ? 'success' : ($percentualUso < 80 ? 'warning' : 'danger');
            ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100" style="border-left: 4px solid <?= htmlspecialchars($card['cor']) ?>">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 d-flex align-items-center">
                            <i class="fab fa-cc-<?= strtolower($card['bandeira']) ?> me-2" style="color: <?= htmlspecialchars($card['cor']) ?>"></i>
                            <?= htmlspecialchars($card['nome']) ?>
                        </h6>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <?php if ($card['limite_usado'] > 0): ?>
                                <li><a class="dropdown-item text-success" href="#" onclick="payCard(<?= $card['id'] ?>, '<?= htmlspecialchars($card['nome']) ?>', <?= $card['limite_usado'] ?>)">
                                    <i class="fas fa-credit-card me-2"></i>Pagar Cartão
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="#" onclick="editCard(<?= $card['id'] ?>)">
                                    <i class="fas fa-edit me-2"></i>Editar
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="viewCardStats(<?= $card['id'] ?>)">
                                    <i class="fas fa-chart-bar me-2"></i>Estatísticas
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteCard(<?= $card['id'] ?>, '<?= htmlspecialchars($card['nome']) ?>')">
                                    <i class="fas fa-trash me-2"></i>Excluir
                                </a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Limite Usado</small>
                                <small class="fw-bold"><?= number_format($percentualUso, 1) ?>%</small>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-<?= $corProgresso ?>" style="width: <?= $percentualUso ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <h6 class="text-success mb-1">R$ <?= number_format($limiteDisponivel, 2, ',', '.') ?></h6>
                                    <small class="text-muted">Disponível</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h6 class="text-primary mb-1">R$ <?= number_format($card['limite_total'], 2, ',', '.') ?></h6>
                                <small class="text-muted">Limite Total</small>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Vencimento: </small>
                                <span class="fw-bold">Dia <?= $card['dia_vencimento'] ?></span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Fechamento: </small>
                                <span class="fw-bold">Dia <?= $card['dia_fechamento'] ?></span>
                            </div>
                        </div>
                        
                        <?php if ($card['ultimos_digitos']): ?>
                            <div class="mt-2">
                                <small class="text-muted">Final: </small>
                                <span class="fw-bold">**** <?= htmlspecialchars($card['ultimos_digitos']) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!$card['ativo']): ?>
                            <div class="mt-2">
                                <span class="badge bg-secondary">Inativo</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal para Cartão -->
<div class="modal fade" id="cardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cardModalTitle">Novo Cartão de Crédito</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="cardForm">
                    <input type="hidden" id="cardId" name="id">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="cardNome" class="form-label">Nome do Cartão *</label>
                                <input type="text" class="form-control" id="cardNome" name="nome" required placeholder="Ex: Nubank, Santander, Itaú">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cardBandeira" class="form-label">Bandeira *</label>
                                <select class="form-select" id="cardBandeira" name="bandeira" required>
                                    <option value="">Selecione...</option>
                                    <option value="visa">Visa</option>
                                    <option value="mastercard">Mastercard</option>
                                    <option value="elo">Elo</option>
                                    <option value="amex">American Express</option>
                                    <option value="diners">Diners</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cardLimite" class="form-label">Limite Total *</label>
                                <input type="text" class="form-control currency-mask" id="cardLimite" name="limite_total" required placeholder="R$ 0,00">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cardVencimento" class="form-label">Dia Vencimento *</label>
                                <input type="number" class="form-control" id="cardVencimento" name="dia_vencimento" required min="1" max="31" placeholder="Ex: 15">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cardFechamento" class="form-label">Dia Fechamento *</label>
                                <input type="number" class="form-control" id="cardFechamento" name="dia_fechamento" required min="1" max="31" placeholder="Ex: 10">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cardBanco" class="form-label">Banco</label>
                                <input type="text" class="form-control" id="cardBanco" name="banco" placeholder="Ex: Nubank, Santander">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="cardDigitos" class="form-label">Últimos 4 Dígitos</label>
                                <input type="text" class="form-control" id="cardDigitos" name="ultimos_digitos" maxlength="4" placeholder="1234">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="cardCor" class="form-label">Cor</label>
                                <input type="color" class="form-control form-control-color" id="cardCor" name="cor" value="#6c757d">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="cardObservacoes" class="form-label">Observações</label>
                        <textarea class="form-control" id="cardObservacoes" name="observacoes" rows="3" placeholder="Informações adicionais sobre o cartão"></textarea>
                    </div>
                    
                    <div id="cardStatus" class="mb-3" style="display: none;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="cardAtivo" name="ativo" value="1" checked>
                            <label class="form-check-label" for="cardAtivo">
                                Cartão Ativo
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveCard()">
                    <i class="fas fa-save me-2"></i>
                    Salvar Cartão
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Estatísticas -->
<div class="modal fade" id="statsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statsModalTitle">Estatísticas do Cartão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="statsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Pagamento -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalTitle">Pagar Cartão de Crédito</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    <input type="hidden" id="paymentCardId" name="card_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Cartão</label>
                        <div class="form-control-plaintext" id="paymentCardName"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Valor Usado</label>
                        <div class="form-control-plaintext text-danger fw-bold" id="paymentUsedAmount"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="paymentAccount" class="form-label">Conta para Débito *</label>
                        <select class="form-select" id="paymentAccount" name="account_id" required>
                            <option value="">Selecione a conta...</option>
                            <?php foreach ($accounts as $account): ?>
                                <?php if ($account['ativo']): ?>
                                <option value="<?= $account['id'] ?>">
                                    <?= htmlspecialchars($account['nome']) ?> - R$ <?= number_format($account['saldo_atual'], 2, ',', '.') ?>
                                </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="paymentAmount" class="form-label">Valor a Pagar *</label>
                                <input type="text" class="form-control currency-mask" id="paymentAmount" name="valor" required placeholder="R$ 0,00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="paymentCompetencia" class="form-label">Data de Competência *</label>
                                <input type="date" class="form-control" id="paymentCompetencia" name="data_competencia" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="paymentDate" class="form-label">Data de Pagamento</label>
                        <input type="date" class="form-control" id="paymentDate" name="data_pagamento">
                        <small class="form-text text-muted">Deixe vazio se ainda não foi pago</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="paymentObservacoes" class="form-label">Observações</label>
                        <textarea class="form-control" id="paymentObservacoes" name="observacoes" rows="2" placeholder="Observações sobre o pagamento"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="processPayment()">
                    <i class="fas fa-credit-card me-2"></i>
                    Processar Pagamento
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Resetar formulário quando modal é fechado
document.getElementById('cardModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('cardForm').reset();
    document.getElementById('cardId').value = '';
    document.getElementById('cardStatus').style.display = 'none';
    document.getElementById('cardModalTitle').textContent = 'Novo Cartão de Crédito';
});

function openCardModal() {
    new bootstrap.Modal(document.getElementById('cardModal')).show();
}

function editCard(cardId) {
    fetch(`<?= url('/api/credit-cards/get') ?>?id=${cardId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const card = data.card;
            
            document.getElementById('cardModalTitle').textContent = 'Editar Cartão de Crédito';
            document.getElementById('cardId').value = card.id;
            document.getElementById('cardNome').value = card.nome;
            document.getElementById('cardBandeira').value = card.bandeira;
            document.getElementById('cardLimite').value = 'R$ ' + parseFloat(card.limite_total).toLocaleString('pt-BR', {minimumFractionDigits: 2});
            document.getElementById('cardVencimento').value = card.dia_vencimento;
            document.getElementById('cardFechamento').value = card.dia_fechamento;
            document.getElementById('cardBanco').value = card.banco || '';
            document.getElementById('cardDigitos').value = card.ultimos_digitos || '';
            document.getElementById('cardCor').value = card.cor;
            document.getElementById('cardObservacoes').value = card.observacoes || '';
            document.getElementById('cardAtivo').checked = card.ativo == 1;
            document.getElementById('cardStatus').style.display = 'block';
            
            new bootstrap.Modal(document.getElementById('cardModal')).show();
        } else {
            Swal.fire('Erro!', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Erro!', 'Erro ao carregar dados do cartão', 'error');
    });
}

function saveCard() {
    const form = document.getElementById('cardForm');
    const formData = new FormData(form);
    
    // Validações
    if (!formData.get('nome') || !formData.get('bandeira') || !formData.get('limite_total')) {
        Swal.fire('Atenção!', 'Preencha todos os campos obrigatórios', 'warning');
        return;
    }
    
    // Remover máscara do limite
    const limiteValue = formData.get('limite_total').replace(/[R$\s.]/g, '').replace(',', '.');
    formData.set('limite_total', limiteValue);
    
    const isEditing = formData.get('id');
    const url = isEditing ? '<?= url('/api/credit-cards/update') ?>' : '<?= url('/api/credit-cards/create') ?>';
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                bootstrap.Modal.getInstance(document.getElementById('cardModal')).hide();
                location.reload();
            });
        } else {
            Swal.fire('Erro!', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Erro!', 'Erro ao salvar cartão', 'error');
    });
}

function deleteCard(cardId, cardName) {
    Swal.fire({
        title: 'Confirmar Exclusão',
        html: `Deseja excluir o cartão <strong>${cardName}</strong>?<br><br><div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Esta ação não pode ser desfeita.</div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash me-2"></i>Excluir Cartão',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id', cardId);
            
            fetch('<?= url('/api/credit-cards/delete') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Excluído!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Erro!', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Erro!', 'Erro ao excluir cartão', 'error');
            });
        }
    });
}

function viewCardStats(cardId) {
    const modal = new bootstrap.Modal(document.getElementById('statsModal'));
    modal.show();
    
    fetch(`<?= url('/api/credit-cards/statistics') ?>?card_id=${cardId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const stats = data.statistics;
            const card = data.card;
            const transactions = data.transactions;
            
            document.getElementById('statsModalTitle').textContent = `Estatísticas - ${card.nome}`;
            
            let content = `
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-primary">${stats.total_transacoes || 0}</h4>
                            <small class="text-muted">Transações</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-danger">R$ ${parseFloat(stats.total_gastos || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</h4>
                            <small class="text-muted">Total Gastos</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-warning">R$ ${parseFloat(stats.ticket_medio || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</h4>
                            <small class="text-muted">Ticket Médio</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-info">R$ ${parseFloat(stats.maior_gasto || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</h4>
                            <small class="text-muted">Maior Gasto</small>
                        </div>
                    </div>
                </div>
            `;
            
            if (transactions.length > 0) {
                content += `
                    <h6>Últimas Transações</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Descrição</th>
                                    <th>Categoria</th>
                                    <th class="text-end">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                transactions.forEach(t => {
                    content += `
                        <tr>
                            <td>${new Date(t.data_competencia).toLocaleDateString('pt-BR')}</td>
                            <td>${t.descricao}</td>
                            <td><span class="badge" style="background-color: ${t.categoria_cor}">${t.categoria_nome || 'Sem categoria'}</span></td>
                            <td class="text-end">R$ ${parseFloat(t.valor).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                        </tr>
                    `;
                });
                
                content += '</tbody></table></div>';
            } else {
                content += '<div class="text-center text-muted"><i class="fas fa-info-circle"></i> Nenhuma transação encontrada</div>';
            }
            
            document.getElementById('statsContent').innerHTML = content;
        } else {
            document.getElementById('statsContent').innerHTML = '<div class="alert alert-danger">Erro ao carregar estatísticas</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('statsContent').innerHTML = '<div class="alert alert-danger">Erro ao carregar estatísticas</div>';
    });
}

// Máscara para últimos dígitos
document.getElementById('cardDigitos').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '').substring(0, 4);
});

function payCard(cardId, cardName, usedAmount) {
    document.getElementById('paymentCardId').value = cardId;
    document.getElementById('paymentCardName').textContent = cardName;
    document.getElementById('paymentUsedAmount').textContent = 'R$ ' + parseFloat(usedAmount).toLocaleString('pt-BR', {minimumFractionDigits: 2});
    
    // Definir valor padrão como o valor usado
    document.getElementById('paymentAmount').value = 'R$ ' + parseFloat(usedAmount).toLocaleString('pt-BR', {minimumFractionDigits: 2});
    
    // Definir data de competência padrão como hoje
    document.getElementById('paymentCompetencia').value = new Date().toISOString().split('T')[0];
    
    // Resetar outros campos
    document.getElementById('paymentAccount').value = '';
    document.getElementById('paymentDate').value = '';
    document.getElementById('paymentObservacoes').value = '';
    
    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}

function processPayment() {
    const form = document.getElementById('paymentForm');
    const formData = new FormData(form);
    
    // Validações
    if (!formData.get('card_id') || !formData.get('account_id') || !formData.get('valor') || !formData.get('data_competencia')) {
        Swal.fire('Atenção!', 'Preencha todos os campos obrigatórios', 'warning');
        return;
    }
    
    // Remover máscara do valor
    const valorValue = formData.get('valor').replace(/[R$\s.]/g, '').replace(',', '.');
    formData.set('valor', valorValue);
    
    // Validar se o valor é maior que zero
    if (parseFloat(valorValue) <= 0) {
        Swal.fire('Atenção!', 'O valor deve ser maior que zero', 'warning');
        return;
    }
    
    fetch('<?= url('/api/credit-cards/pay') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Pagamento Processado!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
                location.reload();
            });
        } else {
            Swal.fire('Erro!', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Erro!', 'Erro ao processar pagamento', 'error');
    });
}

// Resetar formulário de pagamento quando modal é fechado
document.getElementById('paymentModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('paymentForm').reset();
    document.getElementById('paymentCardId').value = '';
});
</script>