<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-users me-3"></i>
        Minha Equipe
    </h1>
    <div class="quick-actions">
        <button class="btn btn-primary" onclick="inviteUser(<?= $organizations[0]['id'] ?? 1 ?>, 'Minha Equipe')">
            <i class="fas fa-user-plus me-2"></i>
            Convidar Pessoa
        </button>
    </div>
</div>

<!-- Cards de Resumo -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card success">
            <div class="position-relative">
                <h6 class="mb-2 opacity-90">Membros da Equipe</h6>
                <h4 class="mb-0"><?= count($teamMembers ?? []) ?></h4>
                <i class="fas fa-users stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card info">
            <div class="position-relative">
                <h6 class="mb-2 opacity-90">Meu Papel</h6>
                <h4 class="mb-0"><?= ucfirst($_SESSION['current_org_role'] ?? 'Proprietário') ?></h4>
                <i class="fas fa-user-tag stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card warning">
            <div class="position-relative">
                <h6 class="mb-2 opacity-90">Sistema</h6>
                <h4 class="mb-0 small">Meu Financeiro</h4>
                <i class="fas fa-wallet stat-icon"></i>
            </div>
        </div>
    </div>
</div>

<!-- Informação sobre Equipe -->
<div class="alert alert-info mb-4">
    <div class="d-flex align-items-center">
        <i class="fas fa-info-circle fa-2x me-3"></i>
        <div>
            <h6 class="mb-1">Como funciona sua Equipe?</h6>
            <p class="mb-0">Você pode convidar outras pessoas para acessar seu sistema financeiro. Cada pessoa terá um papel específico com permissões diferentes.</p>
        </div>
    </div>
</div>

<?php if (empty($teamMembers)): ?>
    <!-- Estado Vazio -->
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h5>Sua equipe ainda está vazia</h5>
                <p>Convide pessoas para colaborar com seu sistema financeiro.</p>
                <button class="btn btn-primary" onclick="inviteUser(1, 'Minha Equipe')">
                    <i class="fas fa-user-plus me-2"></i>
                    Convidar Primeira Pessoa
                </button>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Lista de Membros da Equipe -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 d-flex align-items-center">
                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                    <i class="fas fa-users text-white"></i>
                </div>
                Membros da Equipe
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Membro</th>
                            <th>Email</th>
                            <th>Papel</th>
                            <th>Status</th>
                            <th>Data de Entrada</th>
                            <th style="width: 200px;" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teamMembers as $member): ?>
                            <?php
                            $isCurrentUser = $_SESSION['user_id'] == $member['id'];
                            $rowClass = $isCurrentUser ? 'table-primary' : '';
                            ?>
                            <tr class="<?= $rowClass ?>">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <div style="width: 32px; height: 32px; background: <?= $member['role'] === 'admin' ? '#dc3545' : '#6c757d' ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-user text-white fa-sm"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($member['nome']) ?></div>
                                            <small class="text-muted"><?= $isCurrentUser ? 'Você' : 'Membro da equipe' ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-envelope me-2 text-muted"></i>
                                        <span><?= htmlspecialchars($member['email']) ?></span>
                                    </div>
                                    <?php if ($member['telefone']): ?>
                                        <div class="mt-1">
                                            <i class="fas fa-phone me-2 text-muted" style="font-size: 0.8rem;"></i>
                                            <small class="text-muted"><?= htmlspecialchars($member['telefone']) ?></small>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $roleInfo = [
                                        'admin' => ['nome' => 'Proprietário', 'cor' => '#dc3545', 'icone' => 'fas fa-crown'],
                                        'financeiro' => ['nome' => 'Financeiro', 'cor' => '#28a745', 'icone' => 'fas fa-chart-line'],
                                        'operador' => ['nome' => 'Operador', 'cor' => '#007bff', 'icone' => 'fas fa-user-edit'],
                                        'leitor' => ['nome' => 'Visualizador', 'cor' => '#6c757d', 'icone' => 'fas fa-eye']
                                    ];
                                    $role = $roleInfo[$member['role']] ?? $roleInfo['leitor'];
                                    ?>
                                    <span class="badge" style="background-color: <?= $role['cor'] ?>">
                                        <i class="<?= $role['icone'] ?> me-1"></i>
                                        <?= $role['nome'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $member['status'] === 'ativo' ? 'success' : 'warning' ?>">
                                        <?= ucfirst($member['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <small><?= date('d/m/Y', strtotime($member['joined_at'])) ?></small>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <?php if (!$isCurrentUser): ?>
                                        <button class="btn btn-outline-primary" onclick="editMember(<?= $member['id'] ?>, '<?= htmlspecialchars($member['nome']) ?>', '<?= htmlspecialchars($member['email']) ?>', '<?= htmlspecialchars($member['telefone'] ?? '') ?>', '<?= $member['role'] ?>')" title="Editar membro">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($member['role'] !== 'admin'): ?>
                                        <button class="btn btn-outline-danger" onclick="removeMember(<?= $member['id'] ?>, '<?= htmlspecialchars($member['nome']) ?>')" title="Remover da equipe">
                                            <i class="fas fa-user-times"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php else: ?>
                                        <span class="text-muted small">Você</span>
                                        <?php endif; ?>
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


<!-- Modal para Editar Membro -->
<div class="modal fade" id="editMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Membro da Equipe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editMemberForm">
                    <input type="hidden" id="editMemberId" name="user_id">

                    <div class="mb-3">
                        <label for="editMemberName" class="form-label">Nome Completo *</label>
                        <input type="text" class="form-control" id="editMemberName" name="nome" required>
                    </div>

                    <div class="mb-3">
                        <label for="editMemberEmail" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="editMemberEmail" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="editMemberPhone" class="form-label">Telefone</label>
                        <input type="tel" class="form-control" id="editMemberPhone" name="telefone" placeholder="(11) 99999-9999">
                    </div>

                    <div class="mb-3">
                        <label for="editMemberRole" class="form-label">Papel na Equipe *</label>
                        <select class="form-select" id="editMemberRole" name="role" required>
                            <option value="leitor">Visualizador - Apenas ver relatórios e extratos</option>
                            <option value="operador">Operador - Criar e editar lançamentos</option>
                            <option value="financeiro">Financeiro - Gestão financeira completa</option>
                            <option value="admin">Proprietário - Acesso total</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="editMemberPassword" class="form-label">Nova Senha</label>
                        <input type="password" class="form-control" id="editMemberPassword" name="new_password" placeholder="Deixe em branco para manter a senha atual">
                        <div class="form-text">Mínimo de 6 caracteres. Deixe em branco para não alterar.</div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Atenção:</strong> Se alterar o email, o membro precisará usar o novo email para fazer login.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="updateMember()">
                    <i class="fas fa-save me-2"></i>
                    Salvar Alterações
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Convidar Usuário -->
<div class="modal fade" id="inviteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="inviteModalTitle">Convidar Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="inviteForm">
                    <input type="hidden" id="inviteOrgId" name="org_id">
                    
                    <div class="mb-3">
                        <label for="inviteName" class="form-label">Nome Completo *</label>
                        <input type="text" class="form-control" id="inviteName" name="nome" required placeholder="Ex: João Silva">
                    </div>
                    
                    <div class="mb-3">
                        <label for="inviteEmail" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="inviteEmail" name="email" required placeholder="joao@exemplo.com">
                    </div>
                    
                    <div class="mb-3">
                        <label for="invitePhone" class="form-label">Telefone</label>
                        <input type="tel" class="form-control" id="invitePhone" name="telefone" placeholder="(11) 99999-9999">
                        <div class="form-text">Opcional</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="inviteRole" class="form-label">Papel na Equipe *</label>
                        <select class="form-select" id="inviteRole" name="role" required>
                            <option value="">Selecione o papel...</option>
                            <option value="leitor">Visualizador - Apenas ver relatórios e extratos</option>
                            <option value="operador">Operador - Criar e editar lançamentos</option>
                            <option value="financeiro">Financeiro - Gestão financeira completa</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Como funciona:</strong> Será criada uma conta automaticamente e enviado um email com senha aleatória para a pessoa acessar o sistema.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="sendInvite()">
                    <i class="fas fa-user-plus me-2"></i>
                    Convidar Pessoa
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Máscara para telefone
function phoneMask(input) {
    let value = input.value.replace(/\D/g, '');

    if (value.length <= 10) {
        // Formato: (11) 9999-9999
        value = value.replace(/^(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3');
    } else {
        // Formato: (11) 99999-9999
        value = value.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
    }

    input.value = value;
}

// Aplicar máscara nos campos de telefone
document.getElementById('invitePhone').addEventListener('input', function() {
    phoneMask(this);
});

document.getElementById('editMemberPhone').addEventListener('input', function() {
    phoneMask(this);
});

// Resetar formulários quando modals são fechados
document.getElementById('inviteModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('inviteForm').reset();
});

document.getElementById('editMemberModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('editMemberForm').reset();
});

function inviteUser(orgId, teamName) {
    document.getElementById('inviteModalTitle').textContent = `Convidar Pessoa para ${teamName}`;
    document.getElementById('inviteOrgId').value = orgId;
    new bootstrap.Modal(document.getElementById('inviteModal')).show();
}

function removeMember(userId, userName) {
    Swal.fire({
        title: 'Remover da Equipe',
        html: `Deseja remover <strong>${userName}</strong> da sua equipe?<br><br><div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Esta ação não pode ser desfeita.</div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-user-times me-2"></i>Remover',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implementar remoção de membro
            Swal.fire('Info', 'Funcionalidade em desenvolvimento', 'info');
        }
    });
}

function editMember(userId, userName, userEmail, userPhone, userRole) {
    // Preencher dados no modal
    document.getElementById('editMemberId').value = userId;
    document.getElementById('editMemberName').value = userName;
    document.getElementById('editMemberEmail').value = userEmail;
    document.getElementById('editMemberPhone').value = userPhone || '';
    document.getElementById('editMemberRole').value = userRole;
    document.getElementById('editMemberPassword').value = '';

    // Aplicar máscara no telefone se tiver valor
    if (userPhone) {
        phoneMask(document.getElementById('editMemberPhone'));
    }

    // Mostrar modal
    new bootstrap.Modal(document.getElementById('editMemberModal')).show();
}

function updateMember() {
    const form = document.getElementById('editMemberForm');
    const formData = new FormData(form);

    if (!formData.get('nome') || !formData.get('email') || !formData.get('role')) {
        Swal.fire('Atenção!', 'Preencha todos os campos obrigatórios (Nome, Email e Papel)', 'warning');
        return;
    }

    // Validar senha se fornecida
    const newPassword = formData.get('new_password');
    if (newPassword && newPassword.length < 6) {
        Swal.fire('Atenção!', 'A nova senha deve ter pelo menos 6 caracteres', 'warning');
        return;
    }

    fetch('<?= url('/api/organizations/update-member') ?>', {
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
                bootstrap.Modal.getInstance(document.getElementById('editMemberModal')).hide();
                location.reload();
            });
        } else {
            Swal.fire('Erro!', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Erro!', 'Erro ao atualizar membro', 'error');
    });
}

function sendInvite() {
    const form = document.getElementById('inviteForm');
    const formData = new FormData(form);
    
    if (!formData.get('nome') || !formData.get('email') || !formData.get('role')) {
        Swal.fire('Atenção!', 'Preencha todos os campos obrigatórios (Nome, Email e Papel)', 'warning');
        return;
    }
    
    fetch('<?= url('/api/organizations/invite') ?>', {
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
                bootstrap.Modal.getInstance(document.getElementById('inviteModal')).hide();
                location.reload();
            });
        } else {
            Swal.fire('Erro!', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Erro!', 'Erro ao convidar pessoa', 'error');
    });
}
</script>