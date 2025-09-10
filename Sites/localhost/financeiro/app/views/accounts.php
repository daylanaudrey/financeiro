<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-university me-3"></i>
        Contas Bancárias
    </h1>
    <div class="quick-actions">
        <button class="btn btn-warning me-2" onclick="recalculateBalances()">
            <i class="fas fa-sync-alt me-2"></i>
            Recalcular Saldos
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#accountModal">
            <i class="fas fa-plus me-2"></i>
            Nova Conta
        </button>
    </div>
</div>

<!-- Cards de Resumo -->
<div class="row mb-5">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card success">
            <div class="position-relative">
                <h6 class="mb-2 opacity-90">Saldo Total</h6>
                <h4 class="mb-0">R$ <?= number_format($totalBalance, 2, ',', '.') ?></h4>
                <i class="fas fa-wallet stat-icon"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card info">
            <div class="position-relative">
                <h6 class="mb-2 opacity-90">Total de Contas</h6>
                <h4 class="mb-0"><?= count($accounts) ?></h4>
                <i class="fas fa-university stat-icon"></i>
            </div>
        </div>
    </div>
    
    <?php 
    $pfBalance = 0;
    $pjBalance = 0;
    foreach ($balanceByPessoaTipo as $balance) {
        if ($balance['pessoa_tipo'] === 'PF') $pfBalance = $balance['total'];
        if ($balance['pessoa_tipo'] === 'PJ') $pjBalance = $balance['total'];
    }
    ?>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card warning">
            <div class="position-relative">
                <h6 class="mb-2 opacity-90">Saldo PF</h6>
                <h4 class="mb-0">R$ <?= number_format($pfBalance, 2, ',', '.') ?></h4>
                <i class="fas fa-user stat-icon"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card danger">
            <div class="position-relative">
                <h6 class="mb-2 opacity-90">Saldo PJ</h6>
                <h4 class="mb-0">R$ <?= number_format($pjBalance, 2, ',', '.') ?></h4>
                <i class="fas fa-building stat-icon"></i>
            </div>
        </div>
    </div>
</div>

<?php if (empty($accounts)): ?>
    <!-- Estado Vazio -->
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <i class="fas fa-university"></i>
                <h5>Nenhuma conta cadastrada</h5>
                <p>Comece criando sua primeira conta bancária para gerenciar suas finanças de forma eficiente.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#accountModal">
                    <i class="fas fa-plus me-2"></i>
                    Criar Primeira Conta
                </button>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Lista de Contas -->
    <div class="row">
        <?php foreach ($accounts as $account): ?>
            <div class="col-lg-6 col-xl-4 mb-4">
                <div class="card h-100 <?= $account['ativo'] ? '' : 'opacity-75' ?>">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="d-flex align-items-center">
                                <div style="width: 48px; height: 48px; background: <?= htmlspecialchars($account['cor']) ?>; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                                    <i class="<?= $accountTypes[$account['tipo']]['icone'] ?> text-white fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold"><?= htmlspecialchars($account['nome']) ?></h6>
                                    <div class="mb-1">
                                        <span class="badge bg-light text-dark"><?= $accountTypes[$account['tipo']]['nome'] ?></span>
                                        <span class="badge <?= $account['pessoa_tipo'] === 'PF' ? 'bg-primary' : 'bg-warning' ?> ms-1">
                                            <?= $account['pessoa_tipo'] ?>
                                        </span>
                                        <?php if (!$account['ativo']): ?>
                                            <span class="badge bg-secondary ms-1">Inativa</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($account['pessoa_tipo'] === 'PJ' && $account['razao_social']): ?>
                                        <small class="text-muted"><?= htmlspecialchars($account['razao_social']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link text-muted" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="viewStatement(<?= $account['id'] ?>, '<?= htmlspecialchars($account['nome']) ?>')">
                                            <i class="fas fa-file-invoice me-2"></i>Ver Extrato
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="editAccount(<?= $account['id'] ?>)">
                                            <i class="fas fa-edit me-2"></i>Editar
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="#" onclick="deleteAccount(<?= $account['id'] ?>, '<?= htmlspecialchars($account['nome']) ?>')">
                                            <i class="fas fa-trash me-2"></i>Excluir
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="text-center py-3">
                            <div class="h3 mb-1" style="color: <?= $account['saldo_atual'] >= 0 ? '#28a745' : '#dc3545' ?>">
                                R$ <?= number_format($account['saldo_atual'], 2, ',', '.') ?>
                            </div>
                            <div class="small text-muted">Saldo Atual</div>
                        </div>
                        
                        <?php if ($account['banco'] || $account['cpf'] || $account['cnpj']): ?>
                            <div class="border-top pt-3 mt-3">
                                <div class="row text-center small">
                                    <?php if ($account['banco']): ?>
                                        <div class="col-4">
                                            <div class="text-muted">Banco</div>
                                            <div class="fw-semibold"><?= htmlspecialchars($account['banco']) ?></div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($account['agencia']): ?>
                                        <div class="col-4">
                                            <div class="text-muted">Agência</div>
                                            <div class="fw-semibold"><?= htmlspecialchars($account['agencia']) ?></div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($account['conta']): ?>
                                        <div class="col-4">
                                            <div class="text-muted">Conta</div>
                                            <div class="fw-semibold"><?= htmlspecialchars($account['conta']) ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($account['cpf'] || $account['cnpj']): ?>
                                    <div class="row text-center small mt-2">
                                        <?php if ($account['pessoa_tipo'] === 'PF' && $account['cpf']): ?>
                                            <div class="col">
                                                <div class="text-muted">CPF</div>
                                                <div class="fw-semibold"><?= htmlspecialchars($account['cpf']) ?></div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($account['pessoa_tipo'] === 'PJ' && $account['cnpj']): ?>
                                            <div class="col">
                                                <div class="text-muted">CNPJ</div>
                                                <div class="fw-semibold"><?= htmlspecialchars($account['cnpj']) ?></div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($account['pessoa_tipo'] === 'PJ' && $account['inscricao_estadual']): ?>
                                            <div class="col">
                                                <div class="text-muted">I.E.</div>
                                                <div class="fw-semibold"><?= htmlspecialchars($account['inscricao_estadual']) ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($account['descricao']): ?>
                            <div class="border-top pt-3 mt-3">
                                <div class="small text-muted mb-1">Descrição</div>
                                <div class="small"><?= htmlspecialchars($account['descricao']) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal para Nova Conta / Editar Conta -->
<div class="modal fade" id="accountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="accountModalTitle">Nova Conta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="accountForm">
                    <input type="hidden" id="accountId" name="id">
                    
                    <!-- Tipo de Pessoa -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-floating">
                                <select class="form-select" id="accountPessoaTipo" name="pessoa_tipo" required>
                                    <option value="PF">Pessoa Física</option>
                                    <option value="PJ">Pessoa Jurídica</option>
                                </select>
                                <label for="accountPessoaTipo">Tipo de Pessoa *</label>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="accountNome" name="nome" required>
                                <label for="accountNome">Nome da Conta *</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Campos específicos para PJ -->
                    <div id="pjFields" style="display: none;">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="accountRazaoSocial" name="razao_social">
                                    <label for="accountRazaoSocial">Razão Social *</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="accountCnpj" name="cnpj" maxlength="18">
                                    <label for="accountCnpj">CNPJ</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="accountInscricaoEstadual" name="inscricao_estadual">
                                    <label for="accountInscricaoEstadual">Inscrição Estadual</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Campos específicos para PF -->
                    <div id="pfFields" style="display: none;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="accountCpf" name="cpf" maxlength="14">
                                    <label for="accountCpf">CPF</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="accountTipo" name="tipo" required>
                                    <option value="">Selecione...</option>
                                    <?php foreach ($accountTypes as $key => $type): ?>
                                        <option value="<?= $key ?>" data-cor="<?= $type['cor'] ?>"><?= $type['nome'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="accountTipo">Tipo da Conta *</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="accountBanco" name="banco">
                                <label for="accountBanco">Banco</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="accountAgencia" name="agencia">
                                <label for="accountAgencia">Agência</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="accountConta" name="conta">
                                <label for="accountConta">Conta</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="number" step="0.01" class="form-control" id="accountSaldo" name="saldo_inicial" value="0">
                                <label for="accountSaldo">Saldo Inicial</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Cor da Conta</label>
                                <input type="color" class="form-control form-control-color" id="accountCor" name="cor" value="#007bff">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <textarea class="form-control" id="accountDescricao" name="descricao" style="height: 100px"></textarea>
                        <label for="accountDescricao">Descrição</label>
                    </div>
                    
                    <div class="form-check" id="accountAtivoDiv" style="display: none;">
                        <input class="form-check-input" type="checkbox" id="accountAtivo" name="ativo" checked>
                        <label class="form-check-label" for="accountAtivo">
                            Conta Ativa
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveAccount()">
                    <i class="fas fa-save me-2"></i>
                    <span id="saveButtonText">Salvar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir a conta <strong id="deleteAccountName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Esta ação não pode ser desfeita.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteAccount()">
                    <i class="fas fa-trash me-2"></i>Excluir Conta
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let deleteAccountId = null;

// Controlar exibição dos campos PF/PJ
document.getElementById('accountPessoaTipo').addEventListener('change', function() {
    const pessoaTipo = this.value;
    const pjFields = document.getElementById('pjFields');
    const pfFields = document.getElementById('pfFields');
    
    if (pessoaTipo === 'PJ') {
        pjFields.style.display = 'block';
        pfFields.style.display = 'none';
        document.getElementById('accountRazaoSocial').required = true;
    } else {
        pjFields.style.display = 'none';
        pfFields.style.display = 'block';
        document.getElementById('accountRazaoSocial').required = false;
    }
});

// Auto-selecionar cor baseada no tipo
document.getElementById('accountTipo').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const cor = selectedOption.getAttribute('data-cor');
    if (cor) {
        document.getElementById('accountCor').value = cor;
    }
});

// Máscaras para CPF e CNPJ
document.getElementById('accountCpf').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    e.target.value = value;
});

document.getElementById('accountCnpj').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/(\d{2})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d)/, '$1/$2');
    value = value.replace(/(\d{4})(\d{1,2})$/, '$1-$2');
    e.target.value = value;
});

// Inicializar campos ao carregar
document.addEventListener('DOMContentLoaded', function() {
    const pessoaTipoSelect = document.getElementById('accountPessoaTipo');
    pessoaTipoSelect.dispatchEvent(new Event('change'));
});

function editAccount(id) {
    fetch(`<?= url('/api/accounts/get') ?>?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const account = data.account;
                
                document.getElementById('accountModalTitle').textContent = 'Editar Conta';
                document.getElementById('saveButtonText').textContent = 'Atualizar';
                document.getElementById('accountAtivoDiv').style.display = 'block';
                
                document.getElementById('accountId').value = account.id;
                document.getElementById('accountPessoaTipo').value = account.pessoa_tipo || 'PF';
                document.getElementById('accountNome').value = account.nome;
                document.getElementById('accountRazaoSocial').value = account.razao_social || '';
                document.getElementById('accountCnpj').value = account.cnpj || '';
                document.getElementById('accountCpf').value = account.cpf || '';
                document.getElementById('accountInscricaoEstadual').value = account.inscricao_estadual || '';
                document.getElementById('accountTipo').value = account.tipo;
                document.getElementById('accountBanco').value = account.banco || '';
                document.getElementById('accountAgencia').value = account.agencia || '';
                document.getElementById('accountConta').value = account.conta || '';
                document.getElementById('accountDescricao').value = account.descricao || '';
                document.getElementById('accountCor').value = account.cor;
                document.getElementById('accountAtivo').checked = account.ativo == 1;
                
                // Disparar evento para mostrar campos corretos
                document.getElementById('accountPessoaTipo').dispatchEvent(new Event('change'));
                
                // Desabilitar campo de saldo inicial na edição
                document.getElementById('accountSaldo').disabled = true;
                document.getElementById('accountSaldo').value = account.saldo_inicial;
                
                new bootstrap.Modal(document.getElementById('accountModal')).show();
            } else {
                Swal.fire('Erro!', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            Swal.fire('Erro!', 'Erro ao carregar dados da conta', 'error');
        });
}

function saveAccount() {
    const form = document.getElementById('accountForm');
    const formData = new FormData(form);
    const isEdit = document.getElementById('accountId').value;
    
    const url = isEdit ? '<?= url('/api/accounts/update') ?>' : '<?= url('/api/accounts/create') ?>';
    
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
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Erro!', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire('Erro!', 'Erro ao salvar conta', 'error');
    });
}

function deleteAccount(id, nome) {
    deleteAccountId = id;
    document.getElementById('deleteAccountName').textContent = nome;
    new bootstrap.Modal(document.getElementById('deleteAccountModal')).show();
}

function confirmDeleteAccount() {
    if (!deleteAccountId) return;
    
    const formData = new FormData();
    formData.append('id', deleteAccountId);
    
    fetch('<?= url('/api/accounts/delete') ?>', {
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
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Erro!', data.message, 'error');
        }
        
        bootstrap.Modal.getInstance(document.getElementById('deleteAccountModal')).hide();
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire('Erro!', 'Erro ao excluir conta', 'error');
    });
}

function recalculateBalances() {
    Swal.fire({
        title: 'Recalcular Saldos',
        text: 'Esta operação irá recalcular os saldos de todas as contas baseado apenas nas transações confirmadas. Deseja continuar?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, recalcular!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            
            fetch('<?= url('/api/accounts/recalculate') ?>', {
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
                        location.reload();
                    });
                } else {
                    Swal.fire('Erro!', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire('Erro!', 'Erro ao recalcular saldos', 'error');
            });
        }
    });
}

// Resetar formulário quando modal é fechado
document.getElementById('accountModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('accountForm').reset();
    document.getElementById('accountId').value = '';
    document.getElementById('accountModalTitle').textContent = 'Nova Conta';
    document.getElementById('saveButtonText').textContent = 'Salvar';
    document.getElementById('accountAtivoDiv').style.display = 'none';
    document.getElementById('accountSaldo').disabled = false;
    document.getElementById('accountCor').value = '#007bff';
});

function viewStatement(accountId, accountName) {
    window.location.href = `<?= url('/statements') ?>?account_id=${accountId}`;
}
</script>