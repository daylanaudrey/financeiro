<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-exchange-alt me-3"></i>
        Transferências e Depósitos
    </h1>
    <div class="quick-actions">
        <button class="btn btn-success me-2" onclick="openDepositModal()">
            <i class="fas fa-bullseye me-2"></i>
            Depósito para Vault
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#transferModal">
            <i class="fas fa-exchange-alt me-2"></i>
            Nova Transferência
        </button>
    </div>
</div>

<!-- Saldos das Contas -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-wallet me-2"></i>
                    Saldos Atuais das Contas
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($accounts as $account): ?>
                        <div class="col-md-4 mb-3">
                            <div class="account-balance-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($account['nome']) ?></h6>
                                        <small class="text-muted"><?= ucfirst($account['tipo']) ?> - <?= $account['pessoa_tipo'] ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="h5 mb-0 <?= $account['saldo_atual'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                            R$ <?= number_format($account['saldo_atual'], 2, ',', '.') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Histórico de Transferências -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-history me-2"></i>
            Histórico de Transferências
        </h5>
    </div>
    <div class="card-body">
        <div id="transfersTable">
            <div class="text-center py-4">
                <i class="fas fa-sync fa-spin fa-2x text-muted mb-3"></i>
                <p class="text-muted">Carregando transferências...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Nova Transferência -->
<div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nova Transferência</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="transferForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="accountFrom" class="form-label">Conta de Origem *</label>
                                <select class="form-select" id="accountFrom" name="account_from" required>
                                    <option value="">Selecione a conta de origem...</option>
                                    <?php foreach ($accounts as $account): ?>
                                        <option value="<?= $account['id'] ?>" data-balance="<?= $account['saldo_atual'] ?>">
                                            <?= htmlspecialchars($account['nome']) ?> (R$ <?= number_format($account['saldo_atual'], 2, ',', '.') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted" id="originBalance"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="accountTo" class="form-label">Conta de Destino *</label>
                                <select class="form-select" id="accountTo" name="account_to" required>
                                    <option value="">Selecione a conta de destino...</option>
                                    <?php foreach ($accounts as $account): ?>
                                        <option value="<?= $account['id'] ?>" data-balance="<?= $account['saldo_atual'] ?>">
                                            <?= htmlspecialchars($account['nome']) ?> (R$ <?= number_format($account['saldo_atual'], 2, ',', '.') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted" id="destinyBalance"></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="transferValor" class="form-label">Valor *</label>
                        <input type="text" class="form-control currency-mask" id="transferValor" name="valor" placeholder="R$ 0,00" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="transferData" class="form-label">Data da Transferência *</label>
                        <input type="date" class="form-control" id="transferData" name="data_competencia" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="transferDescricao" class="form-label">Descrição</label>
                        <input type="text" class="form-control" id="transferDescricao" name="descricao" placeholder="Transferência entre contas" value="Transferência entre contas">
                    </div>
                    
                    <div class="mb-3">
                        <label for="transferObservacoes" class="form-label">Observações</label>
                        <textarea class="form-control" id="transferObservacoes" name="observacoes" rows="3" placeholder="Observações adicionais (opcional)"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveTransfer()">
                    <i class="fas fa-exchange-alt me-2"></i>
                    Transferir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Depósito em Vault -->
<div class="modal fade" id="depositModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Depósito para Vault</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="depositForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="depositAccountFrom" class="form-label">Conta de Origem *</label>
                                <select class="form-select" id="depositAccountFrom" name="account_from" required>
                                    <option value="">Selecione a conta de origem...</option>
                                    <?php foreach ($accounts as $account): ?>
                                        <option value="<?= $account['id'] ?>" data-balance="<?= $account['saldo_atual'] ?>">
                                            <?= htmlspecialchars($account['nome']) ?> (R$ <?= number_format($account['saldo_atual'], 2, ',', '.') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted" id="depositFromBalance"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="depositVaultTo" class="form-label">Objetivo Vault *</label>
                                <select class="form-select" id="depositVaultTo" name="vault_goal_id" required>
                                    <option value="">Selecione o objetivo...</option>
                                </select>
                                <small class="text-muted" id="depositVaultProgress"></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="depositValor" class="form-label">Valor do Depósito *</label>
                        <input type="text" class="form-control currency-mask" id="depositValor" name="valor" placeholder="R$ 0,00" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="depositData" class="form-label">Data do Depósito *</label>
                        <input type="date" class="form-control" id="depositData" name="data_competencia" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="depositDescricao" class="form-label">Descrição</label>
                        <input type="text" class="form-control" id="depositDescricao" name="descricao" placeholder="Depósito para objetivo Vault" value="Depósito para objetivo Vault">
                    </div>
                    
                    <div class="mb-3">
                        <label for="depositObservacoes" class="form-label">Observações</label>
                        <textarea class="form-control" id="depositObservacoes" name="observacoes" rows="3" placeholder="Observações adicionais (opcional)"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="saveDeposit()">
                    <i class="fas fa-bullseye me-2"></i>
                    Realizar Depósito
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Inicializar página
document.addEventListener('DOMContentLoaded', function() {
    // Data de hoje como padrão
    document.getElementById('transferData').value = new Date().toISOString().split('T')[0];
    
    // Carregar histórico
    loadTransferHistory();
    
    // Eventos dos selects
    document.getElementById('accountFrom').addEventListener('change', updateBalanceDisplay);
    document.getElementById('accountTo').addEventListener('change', updateBalanceDisplay);
});

function updateBalanceDisplay() {
    const fromSelect = document.getElementById('accountFrom');
    const toSelect = document.getElementById('accountTo');
    const originBalanceEl = document.getElementById('originBalance');
    const destinyBalanceEl = document.getElementById('destinyBalance');
    
    // Mostrar saldo da conta origem
    if (fromSelect.value) {
        const selectedOption = fromSelect.options[fromSelect.selectedIndex];
        const balance = parseFloat(selectedOption.dataset.balance);
        originBalanceEl.textContent = `Saldo disponível: R$ ${balance.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
        originBalanceEl.className = balance >= 0 ? 'text-success' : 'text-danger';
    } else {
        originBalanceEl.textContent = '';
    }
    
    // Mostrar saldo da conta destino
    if (toSelect.value) {
        const selectedOption = toSelect.options[toSelect.selectedIndex];
        const balance = parseFloat(selectedOption.dataset.balance);
        destinyBalanceEl.textContent = `Saldo atual: R$ ${balance.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
        destinyBalanceEl.className = balance >= 0 ? 'text-success' : 'text-danger';
    } else {
        destinyBalanceEl.textContent = '';
    }
}

function saveTransfer() {
    const form = document.getElementById('transferForm');
    const formData = new FormData(form);
    
    // Validações
    if (!formData.get('account_from') || !formData.get('account_to')) {
        Swal.fire('Erro!', 'Selecione as contas de origem e destino', 'error');
        return;
    }
    
    if (formData.get('account_from') === formData.get('account_to')) {
        Swal.fire('Erro!', 'A conta de origem deve ser diferente da conta de destino', 'error');
        return;
    }
    
    if (!formData.get('valor') || formData.get('valor') === 'R$ 0,00') {
        Swal.fire('Erro!', 'Informe o valor da transferência', 'error');
        return;
    }
    
    // Enviar dados
    fetch('<?= url('/api/transactions/transfer') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Sucesso!', data.message, 'success');
            document.getElementById('transferForm').reset();
            document.getElementById('transferData').value = new Date().toISOString().split('T')[0];
            bootstrap.Modal.getInstance(document.getElementById('transferModal')).hide();
            loadTransferHistory();
            // Recarregar saldos atualizando a página
            setTimeout(() => location.reload(), 1000);
        } else {
            Swal.fire('Erro!', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Erro!', 'Erro de comunicação com o servidor', 'error');
    });
}

function loadTransferHistory() {
    document.getElementById('transfersTable').innerHTML = `
        <div class="text-center py-4">
            <i class="fas fa-sync fa-spin fa-2x text-muted mb-3"></i>
            <p class="text-muted">Carregando histórico...</p>
        </div>
    `;
    
    fetch('<?= url('/api/transactions/transfers') ?>')
    .then(response => response.json())
    .then(data => {
        if (data.success && data.transfers.length > 0) {
            let html = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Origem → Destino</th>
                                <th>Valor</th>
                                <th>Descrição</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            data.transfers.forEach(transfer => {
                const dataFormatada = new Date(transfer.data_competencia).toLocaleDateString('pt-BR');
                const valorFormatado = parseFloat(transfer.valor).toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                });
                
                const isVaultDeposit = transfer.tipo_operacao === 'vault_deposit';
                const destinationIcon = isVaultDeposit ? 'fas fa-bullseye' : 'fas fa-plus';
                const destinationBadgeColor = isVaultDeposit ? 'bg-purple' : 'bg-success';
                const operationType = isVaultDeposit ? 'Depósito para Vault' : 'Transferência entre contas';
                
                html += `
                    <tr>
                        <td>
                            <strong>${dataFormatada}</strong>
                            <br><small class="text-muted">${new Date(transfer.created_at).toLocaleString('pt-BR')}</small>
                        </td>
                        <td>
                            <div class="transfer-flow">
                                <span class="badge bg-danger me-2">
                                    <i class="fas fa-minus me-1"></i>
                                    ${transfer.account_from_name}
                                </span>
                                <i class="fas fa-arrow-right text-muted mx-2"></i>
                                <span class="badge ${destinationBadgeColor}">
                                    <i class="${destinationIcon} me-1"></i>
                                    ${transfer.account_to_name}
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="h6 text-primary">${valorFormatado}</span>
                        </td>
                        <td>
                            ${transfer.descricao || operationType}
                            ${transfer.observacoes ? `<br><small class="text-muted">${transfer.observacoes}</small>` : ''}
                        </td>
                        <td>
                            <span class="badge bg-success">
                                <i class="fas fa-check me-1"></i>
                                Confirmado
                            </span>
                        </td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            document.getElementById('transfersTable').innerHTML = html;
        } else {
            document.getElementById('transfersTable').innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exchange-alt"></i>
                    <h5>Nenhuma transferência encontrada</h5>
                    <p>Ainda não foram realizadas transferências. Clique no botão acima para fazer sua primeira transferência.</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('transfersTable').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Erro ao carregar histórico de transferências
            </div>
        `;
    });
}

function openDepositModal() {
    // Limpar formulário de depósito
    document.getElementById('depositForm').reset();
    document.getElementById('depositData').value = new Date().toISOString().split('T')[0];
    
    // Carregar objetivos Vault
    loadVaultGoals();
    
    // Abrir modal de depósito
    new bootstrap.Modal(document.getElementById('depositModal')).show();
}

function loadVaultGoals() {
    const vaultSelect = document.getElementById('depositVaultTo');
    vaultSelect.innerHTML = '<option value="">Carregando objetivos...</option>';
    
    fetch('<?= url('/api/vaults/goals') ?>?active_only=1')
    .then(response => response.json())
    .then(data => {
        vaultSelect.innerHTML = '<option value="">Selecione o objetivo...</option>';
        
        if (data.success && data.vaults && data.vaults.length > 0) {
            data.vaults.forEach(vault => {
                const progress = vault.progresso_percentual || 0;
                const metaFormatada = parseFloat(vault.valor_meta).toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
                const atualFormatado = parseFloat(vault.valor_atual || 0).toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
                
                const option = document.createElement('option');
                option.value = vault.id;
                option.dataset.meta = vault.valor_meta;
                option.dataset.atual = vault.valor_atual || 0;
                option.dataset.progress = progress;
                option.textContent = `${vault.titulo} (${atualFormatado} / ${metaFormatada} - ${progress}%)`;
                
                vaultSelect.appendChild(option);
            });
        } else {
            vaultSelect.innerHTML = '<option value="">Nenhum objetivo encontrado</option>';
        }
    })
    .catch(error => {
        console.error('Error loading vault goals:', error);
        vaultSelect.innerHTML = '<option value="">Erro ao carregar objetivos</option>';
    });
}

function saveDeposit() {
    const form = document.getElementById('depositForm');
    const formData = new FormData(form);
    
    // Validações
    if (!formData.get('account_from')) {
        Swal.fire('Erro!', 'Selecione a conta de origem', 'error');
        return;
    }
    
    if (!formData.get('vault_goal_id')) {
        Swal.fire('Erro!', 'Selecione o objetivo Vault', 'error');
        return;
    }
    
    if (!formData.get('valor') || formData.get('valor') === 'R$ 0,00') {
        Swal.fire('Erro!', 'Informe o valor do depósito', 'error');
        return;
    }
    
    if (!formData.get('data_competencia')) {
        Swal.fire('Erro!', 'Informe a data do depósito', 'error');
        return;
    }
    
    // Enviar dados para endpoint de depósito Vault
    fetch('<?= url('/api/vaults/deposit') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Sucesso!', data.message, 'success');
            document.getElementById('depositForm').reset();
            document.getElementById('depositData').value = new Date().toISOString().split('T')[0];
            bootstrap.Modal.getInstance(document.getElementById('depositModal')).hide();
            loadTransferHistory();
            // Recarregar saldos atualizando a página
            setTimeout(() => location.reload(), 1000);
        } else {
            Swal.fire('Erro!', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Erro!', 'Erro de comunicação com o servidor', 'error');
    });
}

// Função para atualizar saldos das contas no modal de depósito
document.addEventListener('DOMContentLoaded', function() {
    const depositFromSelect = document.getElementById('depositAccountFrom');
    const depositVaultSelect = document.getElementById('depositVaultTo');
    
    if (depositFromSelect) {
        depositFromSelect.addEventListener('change', function() {
            const balanceEl = document.getElementById('depositFromBalance');
            
            if (this.value) {
                const selectedOption = this.options[this.selectedIndex];
                const balance = parseFloat(selectedOption.dataset.balance);
                balanceEl.textContent = `Saldo disponível: R$ ${balance.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
                balanceEl.className = balance >= 0 ? 'text-success' : 'text-danger';
            } else {
                balanceEl.textContent = '';
            }
        });
    }
    
    if (depositVaultSelect) {
        depositVaultSelect.addEventListener('change', function() {
            const progressEl = document.getElementById('depositVaultProgress');
            
            if (this.value) {
                const selectedOption = this.options[this.selectedIndex];
                const progress = parseFloat(selectedOption.dataset.progress || 0);
                const atual = parseFloat(selectedOption.dataset.atual || 0);
                const meta = parseFloat(selectedOption.dataset.meta || 0);
                
                const atualFormatado = atual.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
                const metaFormatada = meta.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
                
                progressEl.innerHTML = `
                    Progresso: ${progress.toFixed(1)}% (${atualFormatado} / ${metaFormatada})
                    <div class="progress mt-1" style="height: 6px;">
                        <div class="progress-bar" style="width: ${Math.min(progress, 100)}%"></div>
                    </div>
                `;
                progressEl.className = progress >= 100 ? 'text-success' : 'text-info';
            } else {
                progressEl.innerHTML = '';
            }
        });
    }
});

// Restaurar título quando modal fechar
document.getElementById('transferModal').addEventListener('hidden.bs.modal', function() {
    document.querySelector('#transferModal .modal-title').textContent = 'Nova Transferência';
});
</script>

<style>
.account-balance-card {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6c757d;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state h5 {
    margin-bottom: 0.5rem;
    color: #495057;
}

.transfer-flow {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
    background-color: #f8f9fa;
}

.table td {
    vertical-align: middle;
}

.bg-purple {
    background-color: #6f42c1 !important;
}
</style>