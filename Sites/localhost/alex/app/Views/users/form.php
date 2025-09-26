<?php
// Verificar se é admin
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'dashboard');
    exit;
}

$isEdit = ($action ?? '') === 'edit';
$pageTitle = $isEdit ? 'Editar Usuário' : 'Cadastrar Usuário';
$submitText = $isEdit ? 'Atualizar' : 'Cadastrar';
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-person<?= $isEdit ? '-check' : '-plus' ?>"></i>
                        <?= $pageTitle ?>
                    </h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>api/users/<?= $isEdit ? 'update' : 'create' ?>"
                          novalidate>

                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id" value="<?= $user['id'] ?? '' ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label required">
                                        <i class="bi bi-person"></i> Nome Completo
                                    </label>
                                    <input type="text"
                                           class="form-control"
                                           id="name"
                                           name="name"
                                           value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                                           required
                                           maxlength="100">
                                    <div class="invalid-feedback">
                                        Nome é obrigatório
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label required">
                                        <i class="bi bi-envelope"></i> Email
                                    </label>
                                    <input type="email"
                                           class="form-control"
                                           id="email"
                                           name="email"
                                           value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                           required
                                           maxlength="150">
                                    <div class="invalid-feedback">
                                        Email válido é obrigatório
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label <?= !$isEdit ? 'required' : '' ?>">
                                        <i class="bi bi-lock"></i>
                                        <?= $isEdit ? 'Nova Senha (deixe em branco para manter)' : 'Senha' ?>
                                    </label>
                                    <input type="password"
                                           class="form-control"
                                           id="password"
                                           name="password"
                                           <?= !$isEdit ? 'required' : '' ?>
                                           minlength="6">
                                    <div class="form-text">
                                        A senha deve ter pelo menos 6 caracteres
                                    </div>
                                    <div class="invalid-feedback">
                                        <?= $isEdit ? 'Se informada, a senha deve ter pelo menos 6 caracteres' : 'Senha é obrigatória e deve ter pelo menos 6 caracteres' ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label required">
                                        <i class="bi bi-shield-check"></i> Perfil de Acesso
                                    </label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="">Selecione...</option>
                                        <?php foreach ($roleOptions as $value => $label): ?>
                                            <option value="<?= $value ?>"
                                                    <?= ($user['role'] ?? '') === $value ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Perfil de acesso é obrigatório
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-toggle-on"></i> Status
                                    </label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               id="is_active"
                                               name="is_active"
                                               value="1"
                                               <?= (!isset($user['is_active']) || $user['is_active']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="is_active">
                                            Usuário ativo
                                        </label>
                                    </div>
                                    <div class="form-text">
                                        Usuários inativos não podem fazer login no sistema
                                    </div>
                                </div>
                            </div>

                            <?php if ($isEdit): ?>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-info-circle"></i> Informações
                                        </label>
                                        <div class="small text-muted">
                                            <div><strong>Criado em:</strong> <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></div>
                                            <?php if ($user['last_login']): ?>
                                                <div><strong>Último login:</strong> <?= date('d/m/Y H:i', strtotime($user['last_login'])) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= BASE_URL ?>users" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Voltar
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> <?= $submitText ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.required:after {
    content: " *";
    color: red;
}

.form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');

    // Validação customizada
    form.addEventListener('submit', function(event) {
        event.preventDefault();

        // Resetar validações
        form.classList.remove('was-validated');

        let isValid = true;

        // Validar nome
        const name = document.getElementById('name');
        if (!name.value.trim()) {
            name.setCustomValidity('Nome é obrigatório');
            isValid = false;
        } else {
            name.setCustomValidity('');
        }

        // Validar email
        const email = document.getElementById('email');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email.value.trim()) {
            email.setCustomValidity('Email é obrigatório');
            isValid = false;
        } else if (!emailRegex.test(email.value)) {
            email.setCustomValidity('Email inválido');
            isValid = false;
        } else {
            email.setCustomValidity('');
        }

        // Validar senha
        const password = document.getElementById('password');
        const isEdit = <?= $isEdit ? 'true' : 'false' ?>;

        if (!isEdit && !password.value) {
            password.setCustomValidity('Senha é obrigatória');
            isValid = false;
        } else if (password.value && password.value.length < 6) {
            password.setCustomValidity('A senha deve ter pelo menos 6 caracteres');
            isValid = false;
        } else {
            password.setCustomValidity('');
        }

        // Validar role
        const role = document.getElementById('role');
        if (!role.value) {
            role.setCustomValidity('Perfil de acesso é obrigatório');
            isValid = false;
        } else {
            role.setCustomValidity('');
        }

        form.classList.add('was-validated');

        if (isValid) {
            // Enviar formulário via fetch
            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    return response.text();
                }
            })
            .then(data => {
                if (data) {
                    // Se não houve redirecionamento, recarregar a página
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao salvar usuário');
            });
        }
    });
});
</script>