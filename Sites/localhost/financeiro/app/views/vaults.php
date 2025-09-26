<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-bullseye me-3"></i>
        Vaults e Objetivos
    </h1>
    <div class="quick-actions">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#vaultModal">
            <i class="fas fa-plus me-2"></i>
            Novo Objetivo
        </button>
    </div>
</div>

<!-- Cards de Estatísticas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon bg-primary">
                <i class="fas fa-bullseye"></i>
            </div>
            <div class="stats-info">
                <h3><?= $statistics['total_vaults'] ?? 0 ?></h3>
                <span class="stats-label">Total de Objetivos</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon bg-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stats-info">
                <h3><?= $statistics['concluidos'] ?? 0 ?></h3>
                <span class="stats-label">Concluídos</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon bg-warning">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stats-info">
                <h3><?= $statistics['em_andamento'] ?? 0 ?></h3>
                <span class="stats-label">Em Andamento</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon bg-info">
                <i class="fas fa-percent"></i>
            </div>
            <div class="stats-info">
                <h3><?= number_format($statistics['progresso_medio'] ?? 0, 1) ?>%</h3>
                <span class="stats-label">Progresso Médio</span>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Vaults -->
<div class="row">
    <?php if (empty($vaults)): ?>
        <div class="col-12">
            <div class="empty-state">
                <i class="fas fa-bullseye"></i>
                <h5>Nenhum objetivo encontrado</h5>
                <p>Crie seu primeiro objetivo financeiro para começar a poupar!</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#vaultModal">
                    <i class="fas fa-plus me-2"></i>
                    Criar Primeiro Objetivo
                </button>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($vaults as $vault): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="vault-card <?= $vault['concluido'] ? 'vault-completed' : '' ?>">
                    <div class="vault-header" style="border-left: 4px solid <?= htmlspecialchars($vault['cor']) ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="vault-icon" style="color: <?= htmlspecialchars($vault['cor']) ?>">
                                <i class="<?= htmlspecialchars($vault['icone']) ?>"></i>
                            </div>
                            <div class="vault-actions">
                                <button class="btn btn-sm btn-outline-secondary" onclick="showVaultDetails(<?= $vault['id'] ?>)" title="Ver Detalhes">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary" onclick="editVault(<?= $vault['id'] ?>)" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteVault(<?= $vault['id'] ?>, '<?= htmlspecialchars($vault['titulo']) ?>')" title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <h5 class="vault-title"><?= htmlspecialchars($vault['titulo']) ?></h5>
                        <p class="vault-category">
                            <span class="badge bg-secondary"><?= ucfirst($vault['categoria']) ?></span>
                            <span class="badge bg-<?= $vault['prioridade'] == 'alta' ? 'danger' : ($vault['prioridade'] == 'media' ? 'warning' : 'info') ?>">
                                <?= ucfirst($vault['prioridade']) ?>
                            </span>
                        </p>
                    </div>
                    
                    <div class="vault-body">
                        <div class="vault-progress mb-3">
                            <div class="progress-info d-flex justify-content-between mb-2">
                                <span class="current-amount">R$ <?= number_format($vault['valor_atual'], 2, ',', '.') ?></span>
                                <span class="target-amount text-muted">R$ <?= number_format($vault['valor_meta'], 2, ',', '.') ?></span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?= $vault['progresso_percentual'] ?>%; background-color: <?= $vault['cor'] ?>"
                                     role="progressbar" aria-valuenow="<?= $vault['progresso_percentual'] ?>" 
                                     aria-valuemin="0" aria-valuemax="100">
                                    <?= number_format($vault['progresso_percentual'], 1) ?>%
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($vault['descricao']): ?>
                            <p class="vault-description"><?= htmlspecialchars($vault['descricao']) ?></p>
                        <?php endif; ?>
                        
                        <div class="vault-meta">
                            <?php if ($vault['data_meta']): ?>
                                <div class="vault-deadline">
                                    <i class="fas fa-calendar me-1"></i>
                                    Meta: <?= date('d/m/Y', strtotime($vault['data_meta'])) ?>
                                    <?php if ($vault['dias_restantes'] !== null): ?>
                                        <span class="days-remaining <?= $vault['dias_restantes'] < 0 ? 'text-danger' : 'text-muted' ?>">
                                            (<?= $vault['dias_restantes'] < 0 ? abs($vault['dias_restantes']) . ' dias atrasado' : $vault['dias_restantes'] . ' dias' ?>)
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="vault-status mt-2">
                                <?php if ($vault['concluido']): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i>
                                        Objetivo Alcançado!
                                    </span>
                                <?php else: ?>
                                    <?php 
                                    $statusClass = 'primary';
                                    $statusText = 'Em Andamento';
                                    $statusIcon = 'fas fa-hourglass-half';
                                    
                                    if ($vault['status_meta'] == 'atrasado') {
                                        $statusClass = 'danger';
                                        $statusText = 'Atrasado';
                                        $statusIcon = 'fas fa-exclamation-triangle';
                                    } elseif ($vault['status_meta'] == 'proximo') {
                                        $statusClass = 'warning';
                                        $statusText = 'Próximo da Meta';
                                        $statusIcon = 'fas fa-rocket';
                                    }
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>">
                                        <i class="<?= $statusIcon ?> me-1"></i>
                                        <?= $statusText ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="vault-footer">
                        <button class="btn btn-sm btn-success me-2" onclick="addDeposit(<?= $vault['id'] ?>)">
                            <i class="fas fa-plus me-1"></i>
                            Depositar
                        </button>
                        <?php if ($vault['valor_atual'] > 0): ?>
                        <button class="btn btn-sm btn-warning me-2" onclick="addWithdraw(<?= $vault['id'] ?>)">
                            <i class="fas fa-minus me-1"></i>
                            Resgatar
                        </button>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-outline-secondary" onclick="showVaultDetails(<?= $vault['id'] ?>)">
                            <i class="fas fa-chart-line me-1"></i>
                            Histórico
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal para Novo/Editar Vault -->
<div class="modal fade" id="vaultModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Objetivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="vaultForm">
                    <input type="hidden" id="vaultId" name="id">
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="vaultTitulo" class="form-label">Título do Objetivo *</label>
                                <input type="text" class="form-control" id="vaultTitulo" name="titulo" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="vaultDescricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="vaultDescricao" name="descricao" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="vaultValorMeta" class="form-label">Valor da Meta *</label>
                                <input type="text" class="form-control currency-mask" id="vaultValorMeta" name="valor_meta" placeholder="R$ 0,00" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="vaultDataMeta" class="form-label">Data da Meta</label>
                                <input type="date" class="form-control" id="vaultDataMeta" name="data_meta">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="vaultPrioridade" class="form-label">Prioridade</label>
                                <select class="form-select" id="vaultPrioridade" name="prioridade">
                                    <option value="baixa">Baixa</option>
                                    <option value="media" selected>Média</option>
                                    <option value="alta">Alta</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vaultCategoria" class="form-label">Categoria</label>
                                <select class="form-select" id="vaultCategoria" name="categoria">
                                    <option value="emergencia">Emergência</option>
                                    <option value="viagem">Viagem</option>
                                    <option value="compra">Compra</option>
                                    <option value="investimento">Investimento</option>
                                    <option value="educacao">Educação</option>
                                    <option value="saude">Saúde</option>
                                    <option value="casa">Casa</option>
                                    <option value="veiculo">Veículo</option>
                                    <option value="aposentadoria">Aposentadoria</option>
                                    <option value="outros" selected>Outros</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="vaultCor" class="form-label">Cor</label>
                                <input type="color" class="form-control form-control-color" id="vaultCor" name="cor" value="#007bff">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="vaultIcone" class="form-label">Ícone</label>
                                <select class="form-select" id="vaultIcone" name="icone">
                                    <option value="fas fa-bullseye">🎯 Alvo</option>
                                    <option value="fas fa-piggy-bank">🐷 Porquinho</option>
                                    <option value="fas fa-plane">✈️ Avião</option>
                                    <option value="fas fa-home">🏠 Casa</option>
                                    <option value="fas fa-car">🚗 Carro</option>
                                    <option value="fas fa-graduation-cap">🎓 Educação</option>
                                    <option value="fas fa-heart">❤️ Saúde</option>
                                    <option value="fas fa-shield-alt">🛡️ Proteção</option>
                                    <option value="fas fa-chart-line">📈 Investimento</option>
                                    <option value="fas fa-gift">🎁 Presente</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveVault()">
                    <i class="fas fa-save me-2"></i>
                    Salvar Objetivo
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalhes do Vault -->
<div class="modal fade" id="vaultDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Objetivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="vaultDetailsContent">
                <!-- Conteúdo carregado via JavaScript -->
            </div>
        </div>
    </div>
</div>

<!-- Modal para Depósito em Vault -->
<div class="modal fade" id="vaultDepositModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Depósito para Objetivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="vaultDepositForm">
                    <div class="mb-3">
                        <label for="depositAccountFrom" class="form-label">Conta de Origem *</label>
                        <select class="form-select" id="depositAccountFrom" name="account_from" required>
                            <option value="">Selecione a conta de origem...</option>
                            <?php foreach ($accounts ?? [] as $account): ?>
                                <option value="<?= $account['id'] ?>" data-balance="<?= $account['saldo_atual'] ?>">
                                    <?= htmlspecialchars($account['nome']) ?> (R$ <?= number_format($account['saldo_atual'], 2, ',', '.') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted" id="depositFromBalance"></small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="depositVaultGoal" class="form-label">Objetivo *</label>
                        <input type="text" class="form-control" id="depositVaultGoal" readonly>
                        <input type="hidden" id="depositVaultGoalId" name="vault_goal_id">
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
                        <input type="text" class="form-control" id="depositDescricao" name="descricao" placeholder="Depósito para objetivo" value="Depósito para objetivo">
                    </div>
                    
                    <div class="mb-3">
                        <label for="depositObservacoes" class="form-label">Observações</label>
                        <textarea class="form-control" id="depositObservacoes" name="observacoes" rows="3" placeholder="Observações adicionais (opcional)"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="saveVaultDeposit()">
                    <i class="fas fa-plus me-2"></i>
                    Realizar Depósito
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Resgate de Vault -->
<div class="modal fade" id="vaultWithdrawModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resgate de Objetivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="vaultWithdrawForm">
                    <div class="mb-3">
                        <label for="withdrawVaultGoal" class="form-label">Objetivo</label>
                        <input type="text" class="form-control" id="withdrawVaultGoal" readonly>
                        <input type="hidden" id="withdrawVaultGoalId" name="vault_goal_id">
                    </div>
                    
                    <div class="mb-3">
                        <label for="withdrawAccountTo" class="form-label">Conta de Destino *</label>
                        <select class="form-select" id="withdrawAccountTo" name="account_to" required>
                            <option value="">Selecione a conta de destino...</option>
                            <?php foreach ($accounts as $account): ?>
                                <option value="<?= $account['id'] ?>">
                                    <?= htmlspecialchars($account['nome']) ?>
                                    (R$ <?= number_format($account['saldo_atual'], 2, ',', '.') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="withdrawValor" class="form-label">Valor do Resgate *</label>
                        <input type="text" class="form-control currency-mask" id="withdrawValor" name="valor" placeholder="R$ 0,00" required>
                        <div class="form-text">
                            <span class="text-muted">Saldo disponível: </span>
                            <span class="fw-bold text-success" id="withdrawSaldoDisponivel">R$ 0,00</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="withdrawData" class="form-label">Data do Resgate *</label>
                        <input type="date" class="form-control" id="withdrawData" name="data_competencia" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="withdrawDescricao" class="form-label">Descrição</label>
                        <input type="text" class="form-control" id="withdrawDescricao" name="descricao" placeholder="Resgate de objetivo" value="Resgate de objetivo">
                    </div>
                    
                    <div class="mb-3">
                        <label for="withdrawObservacoes" class="form-label">Observações</label>
                        <textarea class="form-control" id="withdrawObservacoes" name="observacoes" rows="3" placeholder="Observações adicionais (opcional)"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="saveVaultWithdraw()">
                    <i class="fas fa-minus me-2"></i>
                    Realizar Resgate
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Inicializar página
document.addEventListener('DOMContentLoaded', function() {
    // Evento ao fechar modal para limpar formulário
    document.getElementById('vaultModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('vaultForm').reset();
        document.getElementById('vaultId').value = '';
        document.querySelector('#vaultModal .modal-title').textContent = 'Novo Objetivo';
    });
});

function saveVault() {
    const form = document.getElementById('vaultForm');
    const formData = new FormData(form);
    
    // Determinar URL baseada se é criação ou edição
    const id = document.getElementById('vaultId').value;
    const url = id ? '<?= url('/api/vaults/update') ?>' : '<?= url('/api/vaults/create') ?>';
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Sucesso!', data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('vaultModal')).hide();
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

function editVault(id) {
    fetch(`<?= url('/api/vaults/get') ?>?id=${id}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const vault = data.vault;
            
            // Preencher formulário
            document.getElementById('vaultId').value = vault.id;
            document.getElementById('vaultTitulo').value = vault.titulo;
            document.getElementById('vaultDescricao').value = vault.descricao || '';
            document.getElementById('vaultValorMeta').value = 'R$ ' + parseFloat(vault.valor_meta).toLocaleString('pt-BR', {minimumFractionDigits: 2});
            document.getElementById('vaultDataMeta').value = vault.data_meta || '';
            document.getElementById('vaultPrioridade').value = vault.prioridade;
            document.getElementById('vaultCategoria').value = vault.categoria;
            document.getElementById('vaultCor').value = vault.cor;
            document.getElementById('vaultIcone').value = vault.icone;
            
            // Alterar título do modal
            document.querySelector('#vaultModal .modal-title').textContent = 'Editar Objetivo';
            
            // Mostrar modal
            new bootstrap.Modal(document.getElementById('vaultModal')).show();
        } else {
            Swal.fire('Erro!', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Erro!', 'Erro ao carregar dados do objetivo', 'error');
    });
}

function deleteVault(id, titulo) {
    Swal.fire({
        title: 'Confirmar Exclusão',
        html: `Tem certeza que deseja excluir o objetivo "<strong>${titulo}</strong>"?<br><br><div class="alert alert-warning mt-3"><i class="fas fa-exclamation-triangle me-2"></i>Esta ação não pode ser desfeita.</div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash me-2"></i>Excluir Objetivo',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id', id);
            
            fetch('<?= url('/api/vaults/delete') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Sucesso!', data.message, 'success');
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
    });
}

function showVaultDetails(id) {
    fetch(`<?= url('/api/vaults/get') ?>?id=${id}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const vault = data.vault;
            const movements = data.movements || [];
            
            let html = `
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h4 class="text-${vault.cor.replace('#', '')}">${vault.titulo}</h4>
                        <p class="text-muted">${vault.descricao || 'Sem descrição'}</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="vault-progress-large">
                            <h2>R$ ${parseFloat(vault.valor_atual).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</h2>
                            <p class="text-muted">de R$ ${parseFloat(vault.valor_meta).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</p>
                            <div class="progress mb-2" style="height: 10px;">
                                <div class="progress-bar" style="width: ${vault.progresso_percentual}%; background-color: ${vault.cor}" 
                                     role="progressbar">${vault.progresso_percentual}%</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            if (movements.length > 0) {
                html += `
                    <h5>Histórico de Movimentações</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Tipo</th>
                                    <th>Valor</th>
                                    <th>Descrição</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                movements.forEach(movement => {
                    const date = new Date(movement.data_movimento).toLocaleDateString('pt-BR');
                    const valor = parseFloat(movement.valor).toLocaleString('pt-BR', {
                        style: 'currency',
                        currency: 'BRL'
                    });
                    const badgeClass = movement.tipo === 'deposito' ? 'success' : 'danger';
                    const icon = movement.tipo === 'deposito' ? 'plus' : 'minus';
                    
                    html += `
                        <tr>
                            <td>${date}</td>
                            <td>
                                <span class="badge bg-${badgeClass}">
                                    <i class="fas fa-${icon} me-1"></i>
                                    ${movement.tipo === 'deposito' ? 'Depósito' : 'Retirada'}
                                </span>
                            </td>
                            <td class="text-${badgeClass}">${valor}</td>
                            <td>${movement.descricao || movement.transaction_description}</td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
            } else {
                html += `
                    <div class="empty-state">
                        <i class="fas fa-chart-line"></i>
                        <h5>Nenhuma movimentação encontrada</h5>
                        <p>Ainda não foram registradas movimentações para este objetivo.</p>
                    </div>
                `;
            }
            
            document.getElementById('vaultDetailsContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('vaultDetailsModal')).show();
        } else {
            Swal.fire('Erro!', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Erro!', 'Erro ao carregar detalhes do objetivo', 'error');
    });
}

function addDeposit(vaultId) {
    // Abrir modal de depósito preenchendo o vault goal
    openDepositModal(vaultId);
}

function openDepositModal(vaultId) {
    // Buscar informações do vault
    fetch(`<?= url('/api/vaults/get') ?>?id=${vaultId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success && data.vault) {
            // Preencher informações do objetivo
            document.getElementById('depositVaultGoal').value = data.vault.titulo;
            document.getElementById('depositVaultGoalId').value = vaultId;
            document.getElementById('depositData').value = new Date().toISOString().split('T')[0];
            document.getElementById('depositDescricao').value = `Depósito para ${data.vault.titulo}`;
            
            // Resetar outros campos
            document.getElementById('depositAccountFrom').value = '';
            document.getElementById('depositValor').value = '';
            document.getElementById('depositObservacoes').value = '';
            document.getElementById('depositFromBalance').textContent = '';
            
            // Abrir modal
            new bootstrap.Modal(document.getElementById('vaultDepositModal')).show();
        } else {
            Swal.fire('Erro!', 'Não foi possível carregar informações do objetivo', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Erro!', 'Erro ao carregar objetivo', 'error');
    });
}

function saveVaultDeposit() {
    const form = document.getElementById('vaultDepositForm');
    const formData = new FormData(form);
    
    // Validações
    if (!formData.get('account_from')) {
        Swal.fire('Erro!', 'Selecione a conta de origem', 'error');
        return;
    }
    
    if (!formData.get('vault_goal_id')) {
        Swal.fire('Erro!', 'Objetivo não identificado', 'error');
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
            document.getElementById('vaultDepositForm').reset();
            bootstrap.Modal.getInstance(document.getElementById('vaultDepositModal')).hide();
            // Recarregar página para atualizar os valores
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

// Funções para Resgate
function addWithdraw(vaultId) {
    // Abrir modal de resgate preenchendo o vault goal
    openWithdrawModal(vaultId);
}

function openWithdrawModal(vaultId) {
    // Buscar informações do vault
    fetch(`<?= url('/api/vaults/get') ?>?id=${vaultId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success && data.vault) {
            // Preencher informações do objetivo
            document.getElementById('withdrawVaultGoal').value = data.vault.titulo;
            document.getElementById('withdrawVaultGoalId').value = vaultId;
            document.getElementById('withdrawData').value = new Date().toISOString().split('T')[0];
            document.getElementById('withdrawDescricao').value = `Resgate de ${data.vault.titulo}`;
            document.getElementById('withdrawSaldoDisponivel').textContent = 
                'R$ ' + parseFloat(data.vault.valor_atual).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            
            // Resetar outros campos
            document.getElementById('withdrawAccountTo').value = '';
            document.getElementById('withdrawValor').value = '';
            document.getElementById('withdrawObservacoes').value = '';
            
            // Abrir modal
            new bootstrap.Modal(document.getElementById('vaultWithdrawModal')).show();
        } else {
            Swal.fire('Erro!', 'Não foi possível carregar informações do objetivo', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Erro!', 'Erro ao carregar objetivo', 'error');
    });
}

function saveVaultWithdraw() {
    const form = document.getElementById('vaultWithdrawForm');
    const formData = new FormData(form);
    
    // Validações
    if (!formData.get('account_to')) {
        Swal.fire('Atenção!', 'Selecione a conta de destino', 'warning');
        return;
    }
    
    if (!formData.get('valor')) {
        Swal.fire('Atenção!', 'Informe o valor do resgate', 'warning');
        return;
    }
    
    // Enviar dados para endpoint de resgate Vault
    fetch('<?= url('/api/vaults/withdraw') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Sucesso!', data.message, 'success');
            document.getElementById('vaultWithdrawForm').reset();
            bootstrap.Modal.getInstance(document.getElementById('vaultWithdrawModal')).hide();
            // Recarregar página para atualizar os valores
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

// Função para atualizar saldo quando conta de origem for selecionada
document.addEventListener('DOMContentLoaded', function() {
    const depositFromSelect = document.getElementById('depositAccountFrom');
    
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
});
</script>

<style>
.stats-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    height: 100%;
}

.stats-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stats-info h3 {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
}

.stats-label {
    color: #6c757d;
    font-size: 0.9rem;
    font-weight: 500;
}

.vault-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.vault-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.vault-card.vault-completed {
    border: 2px solid #28a745;
    background: linear-gradient(135deg, #ffffff 0%, #f8fff8 100%);
}

.vault-header {
    padding: 1.5rem;
    border-bottom: 1px solid #eee;
}

.vault-icon {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.vault-title {
    margin: 0.5rem 0;
    font-weight: 600;
    color: #2c3e50;
}

.vault-category {
    margin: 0;
}

.vault-actions {
    display: flex;
    gap: 0.25rem;
}

.vault-body {
    padding: 1.5rem;
    flex: 1;
}

.vault-progress .progress {
    height: 8px;
    border-radius: 4px;
    background-color: #f1f1f1;
}

.vault-progress .progress-bar {
    border-radius: 4px;
    font-size: 0.75rem;
    line-height: 8px;
}

.vault-description {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 1rem;
}

.vault-deadline {
    font-size: 0.85rem;
    color: #6c757d;
}

.days-remaining {
    font-weight: 500;
}

.vault-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #eee;
    background: #f8f9fa;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6c757d;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1.5rem;
    opacity: 0.5;
}

.empty-state h5 {
    margin-bottom: 1rem;
    color: #495057;
}
</style>