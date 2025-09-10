<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-tags me-3"></i>
        Categorias
    </h1>
    <div class="quick-actions">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="resetCategoryForm()">
            <i class="fas fa-plus me-2"></i>
            Nova Categoria
        </button>
    </div>
</div>

<?php if (empty($categories)): ?>
    <!-- Estado Vazio -->
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <i class="fas fa-tags"></i>
                <h5>Nenhuma categoria encontrada</h5>
                <p>Crie categorias para organizar seus lançamentos financeiros por tipo, projeto ou finalidade.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="resetCategoryForm()">
                    <i class="fas fa-plus me-2"></i>
                    Criar Primeira Categoria
                </button>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Lista de Categorias -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 d-flex align-items-center">
                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                    <i class="fas fa-list text-white"></i>
                </div>
                Categorias Cadastradas
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">Cor</th>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th style="width: 120px;">Lançamentos</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 120px;" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div style="width: 24px; height: 24px; background-color: <?= $category['cor'] ?>; border-radius: 50%; margin-right: 0.5rem;"></div>
                                        <i class="<?= $category['icone'] ?>" style="color: <?= $category['cor'] ?>"></i>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <h6 class="mb-0"><?= htmlspecialchars($category['nome']) ?></h6>
                                        <?php if (!empty($category['descricao'])): ?>
                                            <small class="text-muted"><?= htmlspecialchars($category['descricao']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $typeInfo = $typeOptions[$category['tipo']] ?? ['nome' => ucfirst($category['tipo']), 'cor' => '#6c757d'];
                                    ?>
                                    <span class="badge" style="background-color: <?= $typeInfo['cor'] ?>">
                                        <i class="<?= $typeInfo['icone'] ?? 'fas fa-tag' ?> me-1"></i>
                                        <?= $typeInfo['nome'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= $category['transaction_count'] ?> lançamentos</span>
                                </td>
                                <td>
                                    <?php if ($category['ativo']): ?>
                                        <span class="badge bg-success">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="editCategory(<?= $category['id'] ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="deleteCategory(<?= $category['id'] ?>, '<?= htmlspecialchars($category['nome']) ?>')">
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

<!-- Modal para Nova Categoria / Editar Categoria -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalTitle">Nova Categoria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="categoryForm">
                    <input type="hidden" id="categoryId" name="id">
                    
                    <div class="mb-3">
                        <label for="categoryNome" class="form-label">Nome *</label>
                        <input type="text" class="form-control" id="categoryNome" name="nome" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="categoryTipo" class="form-label">Tipo *</label>
                                <select class="form-select" id="categoryTipo" name="tipo" required>
                                    <option value="">Selecione o tipo...</option>
                                    <?php foreach ($typeOptions as $key => $type): ?>
                                        <option value="<?= $key ?>"><?= $type['nome'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="categoryCor" class="form-label">Cor</label>
                                <input type="color" class="form-control form-control-color" id="categoryCor" name="cor" value="#007bff">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="categoryIcone" class="form-label">Ícone (classe FontAwesome)</label>
                        <input type="text" class="form-control" id="categoryIcone" name="icone" value="fas fa-tag" placeholder="Ex: fas fa-shopping-cart">
                        <small class="text-muted">Use classes do FontAwesome como "fas fa-home", "fas fa-car", etc.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="categoryDescricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="categoryDescricao" name="descricao" rows="3"></textarea>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="categoryAtivo" name="ativo" checked>
                        <label class="form-check-label" for="categoryAtivo">
                            Categoria ativa
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveCategory()">
                    <i class="fas fa-save me-2"></i>
                    <span id="categorySaveButtonText">Salvar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function resetCategoryForm() {
    document.getElementById('categoryModalTitle').textContent = 'Nova Categoria';
    document.getElementById('categorySaveButtonText').textContent = 'Salvar';
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryCor').value = '#007bff';
    document.getElementById('categoryIcone').value = 'fas fa-tag';
    document.getElementById('categoryAtivo').checked = true;
}

function editCategory(id) {
    fetch(`<?= url('/api/categories/get') ?>?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const category = data.category;
                
                document.getElementById('categoryModalTitle').textContent = 'Editar Categoria';
                document.getElementById('categorySaveButtonText').textContent = 'Atualizar';
                
                document.getElementById('categoryId').value = category.id;
                document.getElementById('categoryNome').value = category.nome;
                document.getElementById('categoryTipo').value = category.tipo;
                document.getElementById('categoryCor').value = category.cor;
                document.getElementById('categoryIcone').value = category.icone;
                document.getElementById('categoryDescricao').value = category.descricao || '';
                document.getElementById('categoryAtivo').checked = category.ativo == 1;
                
                new bootstrap.Modal(document.getElementById('categoryModal')).show();
            } else {
                Swal.fire('Erro!', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            Swal.fire('Erro!', 'Erro ao carregar dados da categoria', 'error');
        });
}

function saveCategory() {
    const form = document.getElementById('categoryForm');
    const formData = new FormData(form);
    const isEdit = document.getElementById('categoryId').value;
    
    const url = isEdit ? '<?= url('/api/categories/update') ?>' : '<?= url('/api/categories/create') ?>';
    
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
        Swal.fire('Erro!', 'Erro ao salvar categoria', 'error');
    });
}

function deleteCategory(id, nome) {
    Swal.fire({
        title: 'Confirmar Exclusão',
        html: `Tem certeza que deseja excluir a categoria <strong>${nome}</strong>?<br><br><div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Esta ação não pode ser desfeita.</div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash me-2"></i>Excluir Categoria',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id', id);
            
            fetch('<?= url('/api/categories/delete') ?>', {
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
                Swal.fire('Erro!', 'Erro ao excluir categoria', 'error');
            });
        }
    });
}

// Resetar formulário quando modal é fechado
document.getElementById('categoryModal').addEventListener('hidden.bs.modal', function() {
    resetCategoryForm();
});
</script>