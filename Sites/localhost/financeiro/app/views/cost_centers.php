<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-building me-3"></i>
        Centros de Custo
    </h1>
    <div class="quick-actions">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#costCenterModal" onclick="resetCostCenterForm()">
            <i class="fas fa-plus me-2"></i>
            Novo Centro de Custo
        </button>
    </div>
</div>

<?php if (empty($costCenters)): ?>
    <!-- Estado Vazio -->
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <i class="fas fa-building"></i>
                <h5>Nenhum centro de custo encontrado</h5>
                <p>Crie centros de custo para organizar e controlar os gastos por departamento, projeto ou área.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#costCenterModal" onclick="resetCostCenterForm()">
                    <i class="fas fa-plus me-2"></i>
                    Criar Primeiro Centro de Custo
                </button>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Lista de Centros de Custo -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 d-flex align-items-center">
                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                    <i class="fas fa-list text-white"></i>
                </div>
                Centros de Custo Cadastrados
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>Centro Pai</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 120px;" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($costCenters as $costCenter): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($costCenter['codigo'] ?? 'N/A') ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <div class="cost-center-icon">
                                                <i class="fas fa-building text-primary"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?= htmlspecialchars($costCenter['nome']) ?></div>
                                            <?php if (!empty($costCenter['descricao'])): ?>
                                                <small class="text-muted"><?= htmlspecialchars($costCenter['descricao']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">Geral</span>
                                </td>
                                <td>
                                    <?php if (!empty($costCenter['parent_name'])): ?>
                                        <small class="text-muted"><?= htmlspecialchars($costCenter['parent_name']) ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">—</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($costCenter['ativo']): ?>
                                        <span class="badge bg-success">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-primary" onclick="editCostCenter(<?= $costCenter['id'] ?>)" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteCostCenter(<?= $costCenter['id'] ?>, '<?= htmlspecialchars($costCenter['nome']) ?>')" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Modal para Novo/Editar Centro de Custo -->
<div class="modal fade" id="costCenterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Centro de Custo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="costCenterForm">
                    <input type="hidden" id="costCenterId">
                    
                    <div class="mb-3">
                        <label for="costCenterCodigo" class="form-label">Código *</label>
                        <input type="text" class="form-control" id="costCenterCodigo" name="codigo" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="costCenterNome" class="form-label">Nome *</label>
                        <input type="text" class="form-control" id="costCenterNome" name="nome" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="costCenterParent" class="form-label">Centro de Custo Pai</label>
                        <select class="form-select" id="costCenterParent" name="parent_id">
                            <option value="">Nenhum (Centro Principal)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="costCenterDescricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="costCenterDescricao" name="descricao" rows="3" placeholder="Descrição opcional do centro de custo"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="costCenterAtivo" name="ativo" checked>
                            <label class="form-check-label" for="costCenterAtivo">
                                Centro de custo ativo
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveCostCenter()">
                    <i class="fas fa-save me-2"></i>
                    Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Inicializar página
document.addEventListener('DOMContentLoaded', function() {
    // Carregar centros de custo pai para o select
    loadParentCostCenters();
    
    // Evento ao fechar modal para limpar formulário
    document.getElementById('costCenterModal').addEventListener('hidden.bs.modal', function() {
        resetCostCenterForm();
    });
});

function resetCostCenterForm() {
    document.getElementById('costCenterForm').reset();
    document.getElementById('costCenterId').value = '';
    document.querySelector('#costCenterModal .modal-title').textContent = 'Novo Centro de Custo';
    document.getElementById('costCenterAtivo').checked = true;
}

function loadParentCostCenters() {
    fetch('<?= url('/api/cost-centers/parents') ?>')
    .then(response => response.json())
    .then(data => {
        const select = document.getElementById('costCenterParent');
        select.innerHTML = '<option value="">Nenhum (Centro Principal)</option>';
        
        if (data.success && data.costCenters) {
            data.costCenters.forEach(costCenter => {
                const option = document.createElement('option');
                option.value = costCenter.id;
                option.textContent = `${costCenter.codigo} - ${costCenter.nome}`;
                select.appendChild(option);
            });
        }
    })
    .catch(error => {
        console.error('Error loading parent cost centers:', error);
    });
}

function saveCostCenter() {
    const form = document.getElementById('costCenterForm');
    const formData = new FormData(form);
    const costCenterId = document.getElementById('costCenterId').value;
    
    // Ajustar checkbox para valor correto
    formData.set('ativo', document.getElementById('costCenterAtivo').checked ? '1' : '0');
    
    const url = costCenterId ? 
        '<?= url('/api/cost-centers/update') ?>' : 
        '<?= url('/api/cost-centers/create') ?>';
    
    if (costCenterId) {
        formData.append('id', costCenterId);
    }
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Sucesso!', data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('costCenterModal')).hide();
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

function editCostCenter(id) {
    fetch(`<?= url('/api/cost-centers/get') ?>?id=${id}`)
    .then(response => response.json())
    .then(data => {
        if (data.success && data.costCenter) {
            const costCenter = data.costCenter;
            
            document.getElementById('costCenterId').value = costCenter.id;
            document.getElementById('costCenterCodigo').value = costCenter.codigo || '';
            document.getElementById('costCenterNome').value = costCenter.nome || '';
            document.getElementById('costCenterParent').value = costCenter.parent_id || '';
            document.getElementById('costCenterDescricao').value = costCenter.descricao || '';
            document.getElementById('costCenterAtivo').checked = costCenter.ativo == 1;
            
            document.querySelector('#costCenterModal .modal-title').textContent = 'Editar Centro de Custo';
            new bootstrap.Modal(document.getElementById('costCenterModal')).show();
        } else {
            Swal.fire('Erro!', 'Centro de custo não encontrado', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Erro!', 'Erro ao carregar centro de custo', 'error');
    });
}

function deleteCostCenter(id, nome) {
    Swal.fire({
        title: 'Confirmar Exclusão',
        html: `Tem certeza que deseja excluir o centro de custo "<strong>${nome}</strong>"?<br><br><div class="alert alert-warning mt-3"><i class="fas fa-exclamation-triangle me-2"></i>Esta ação não pode ser desfeita.</div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '<i class="fas fa-trash me-2"></i>Excluir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id', id);
            
            fetch('<?= url('/api/cost-centers/delete') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Excluído!', data.message, 'success');
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
</script>

<style>
.cost-center-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 8px;
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