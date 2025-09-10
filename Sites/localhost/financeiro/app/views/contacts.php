<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-address-book me-3"></i>
        Contatos
    </h1>
    <div class="quick-actions">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#contactModal" onclick="resetContactForm()">
            <i class="fas fa-plus me-2"></i>
            Novo Contato
        </button>
    </div>
</div>

<?php if (empty($contacts)): ?>
    <!-- Estado Vazio -->
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <i class="fas fa-address-book"></i>
                <h5>Nenhum contato encontrado</h5>
                <p>Cadastre contatos para associar aos seus lançamentos, como clientes, fornecedores, funcionários, etc.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#contactModal" onclick="resetContactForm()">
                    <i class="fas fa-plus me-2"></i>
                    Cadastrar Primeiro Contato
                </button>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Lista de Contatos -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 d-flex align-items-center">
                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                    <i class="fas fa-list text-white"></i>
                </div>
                Contatos Cadastrados
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>Documento</th>
                            <th>Email</th>
                            <th>Telefone</th>
                            <th style="width: 120px;">Lançamentos</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 120px;" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contacts as $contact): ?>
                            <tr>
                                <td>
                                    <div>
                                        <h6 class="mb-0"><?= htmlspecialchars($contact['nome']) ?></h6>
                                        <?php if (!empty($contact['observacoes'])): ?>
                                            <small class="text-muted"><?= htmlspecialchars($contact['observacoes']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $typeInfo = $typeOptions[$contact['tipo']] ?? ['nome' => ucfirst($contact['tipo']), 'cor' => '#6c757d'];
                                    ?>
                                    <span class="badge" style="background-color: <?= $typeInfo['cor'] ?>">
                                        <i class="<?= $typeInfo['icone'] ?? 'fas fa-user' ?> me-1"></i>
                                        <?= $typeInfo['nome'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($contact['documento'])): ?>
                                        <small><?= htmlspecialchars($contact['documento']) ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($contact['email'])): ?>
                                        <a href="mailto:<?= htmlspecialchars($contact['email']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($contact['email']) ?>
                                        </a>
                                    <?php else: ?>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($contact['telefone'])): ?>
                                        <a href="tel:<?= htmlspecialchars($contact['telefone']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($contact['telefone']) ?>
                                        </a>
                                    <?php else: ?>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= $contact['transaction_count'] ?> lançamentos</span>
                                </td>
                                <td>
                                    <?php if ($contact['ativo']): ?>
                                        <span class="badge bg-success">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="editContact(<?= $contact['id'] ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="deleteContact(<?= $contact['id'] ?>, '<?= htmlspecialchars($contact['nome']) ?>')">
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

<!-- Modal para Novo Contato / Editar Contato -->
<div class="modal fade" id="contactModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactModalTitle">Novo Contato</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="contactForm">
                    <input type="hidden" id="contactId" name="id">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="contactNome" class="form-label">Nome *</label>
                                <input type="text" class="form-control" id="contactNome" name="nome" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="contactTipo" class="form-label">Tipo *</label>
                                <select class="form-select" id="contactTipo" name="tipo" required>
                                    <option value="">Selecione o tipo...</option>
                                    <?php foreach ($typeOptions as $key => $type): ?>
                                        <option value="<?= $key ?>"><?= $type['nome'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="contactDocumento" class="form-label">CPF/CNPJ</label>
                                <input type="text" class="form-control" id="contactDocumento" name="documento" placeholder="000.000.000-00">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="contactEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" id="contactEmail" name="email" placeholder="exemplo@email.com">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="contactTelefone" class="form-label">Telefone</label>
                                <input type="tel" class="form-control" id="contactTelefone" name="telefone" placeholder="(11) 99999-9999">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="contactEndereco" class="form-label">Endereço</label>
                        <input type="text" class="form-control" id="contactEndereco" name="endereco" placeholder="Rua, número, bairro, cidade">
                    </div>
                    
                    <div class="mb-3">
                        <label for="contactObservacoes" class="form-label">Observações</label>
                        <textarea class="form-control" id="contactObservacoes" name="observacoes" rows="3" placeholder="Informações adicionais sobre o contato..."></textarea>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="contactAtivo" name="ativo" checked>
                        <label class="form-check-label" for="contactAtivo">
                            Contato ativo
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveContact()">
                    <i class="fas fa-save me-2"></i>
                    <span id="contactSaveButtonText">Salvar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function resetContactForm() {
    document.getElementById('contactModalTitle').textContent = 'Novo Contato';
    document.getElementById('contactSaveButtonText').textContent = 'Salvar';
    document.getElementById('contactForm').reset();
    document.getElementById('contactId').value = '';
    document.getElementById('contactAtivo').checked = true;
}

function editContact(id) {
    fetch(`<?= url('/api/contacts/get') ?>?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const contact = data.contact;
                
                document.getElementById('contactModalTitle').textContent = 'Editar Contato';
                document.getElementById('contactSaveButtonText').textContent = 'Atualizar';
                
                document.getElementById('contactId').value = contact.id;
                document.getElementById('contactNome').value = contact.nome;
                document.getElementById('contactTipo').value = contact.tipo;
                document.getElementById('contactDocumento').value = contact.documento || '';
                document.getElementById('contactEmail').value = contact.email || '';
                document.getElementById('contactTelefone').value = contact.telefone || '';
                document.getElementById('contactEndereco').value = contact.endereco || '';
                document.getElementById('contactObservacoes').value = contact.observacoes || '';
                document.getElementById('contactAtivo').checked = contact.ativo == 1;
                
                new bootstrap.Modal(document.getElementById('contactModal')).show();
            } else {
                Swal.fire('Erro!', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            Swal.fire('Erro!', 'Erro ao carregar dados do contato', 'error');
        });
}

function saveContact() {
    const form = document.getElementById('contactForm');
    const formData = new FormData(form);
    const isEdit = document.getElementById('contactId').value;
    
    const url = isEdit ? '<?= url('/api/contacts/update') ?>' : '<?= url('/api/contacts/create') ?>';
    
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
        Swal.fire('Erro!', 'Erro ao salvar contato', 'error');
    });
}

function deleteContact(id, nome) {
    Swal.fire({
        title: 'Confirmar Exclusão',
        html: `Tem certeza que deseja excluir o contato <strong>${nome}</strong>?<br><br><div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Esta ação não pode ser desfeita.</div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash me-2"></i>Excluir Contato',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id', id);
            
            fetch('<?= url('/api/contacts/delete') ?>', {
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
                Swal.fire('Erro!', 'Erro ao excluir contato', 'error');
            });
        }
    });
}

// Resetar formulário quando modal é fechado
document.getElementById('contactModal').addEventListener('hidden.bs.modal', function() {
    resetContactForm();
});

// Aplicar máscaras nos campos
document.addEventListener('DOMContentLoaded', function() {
    // Máscara para CPF/CNPJ
    const documentoField = document.getElementById('contactDocumento');
    documentoField.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            // CPF
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})/, '$1-$2');
        } else {
            // CNPJ
            value = value.replace(/(\d{2})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1/$2');
            value = value.replace(/(\d{4})(\d{1,2})/, '$1-$2');
        }
        e.target.value = value;
    });
    
    // Máscara para telefone
    const telefoneField = document.getElementById('contactTelefone');
    telefoneField.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 10) {
            value = value.replace(/(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
        } else {
            value = value.replace(/(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
        }
        e.target.value = value;
    });
});
</script>