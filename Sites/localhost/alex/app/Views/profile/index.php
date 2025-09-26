<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3">
                        <i class="bi bi-person-circle"></i> Meu Perfil
                    </h1>
                    <p class="text-muted">Gerencie suas informações pessoais e configurações</p>
                </div>
                <div>
                    <a href="<?= BASE_URL ?>dashboard" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar ao Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensagens -->
    <?php if (isset($_SESSION['success'])): ?>
        <script>
            window.pendingMessages = window.pendingMessages || [];
            window.pendingMessages.push({
                type: 'success',
                message: '<?= htmlspecialchars($_SESSION['success'], ENT_QUOTES) ?>'
            });
        </script>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <script>
            window.pendingMessages = window.pendingMessages || [];
            window.pendingMessages.push({
                type: 'error',
                message: '<?= htmlspecialchars($_SESSION['error'], ENT_QUOTES) ?>'
            });
        </script>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Formulário de Perfil -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person-gear"></i> Informações Pessoais
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>profile/update" method="POST">
                        <!-- Informações Básicas -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="text-primary">
                                    <i class="bi bi-info-circle"></i> Dados Básicos
                                </h6>
                                <hr class="mt-1">
                            </div>

                            <div class="col-md-6">
                                <label for="name" class="form-label">
                                    <i class="bi bi-person"></i> Nome Completo *
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="name"
                                       name="name"
                                       value="<?= htmlspecialchars($user['name']) ?>"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope"></i> Email *
                                </label>
                                <input type="email"
                                       class="form-control"
                                       id="email"
                                       name="email"
                                       value="<?= htmlspecialchars($user['email']) ?>"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label for="role" class="form-label">
                                    <i class="bi bi-shield-check"></i> Perfil de Acesso
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="role"
                                       value="<?= ucfirst($user['role']) ?>"
                                       readonly
                                       disabled>
                                <div class="form-text">
                                    Somente administradores podem alterar perfis de acesso.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="last_login" class="form-label">
                                    <i class="bi bi-clock-history"></i> Último Acesso
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="last_login"
                                       value="<?= $user['last_login'] ? date('d/m/Y H:i:s', strtotime($user['last_login'])) : 'Nunca' ?>"
                                       readonly
                                       disabled>
                            </div>
                        </div>

                        <!-- Alteração de Senha -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="text-warning">
                                    <i class="bi bi-key"></i> Alterar Senha
                                </h6>
                                <hr class="mt-1">
                                <p class="text-muted small">
                                    Preencha os campos abaixo apenas se desejar alterar sua senha atual.
                                </p>
                            </div>

                            <div class="col-md-4">
                                <label for="current_password" class="form-label">
                                    <i class="bi bi-lock"></i> Senha Atual
                                </label>
                                <input type="password"
                                       class="form-control"
                                       id="current_password"
                                       name="current_password"
                                       placeholder="Digite sua senha atual">
                            </div>

                            <div class="col-md-4">
                                <label for="new_password" class="form-label">
                                    <i class="bi bi-lock-fill"></i> Nova Senha
                                </label>
                                <input type="password"
                                       class="form-control"
                                       id="new_password"
                                       name="new_password"
                                       placeholder="Digite sua nova senha"
                                       minlength="6">
                                <div class="form-text">Mínimo 6 caracteres</div>
                            </div>

                            <div class="col-md-4">
                                <label for="confirm_password" class="form-label">
                                    <i class="bi bi-check-circle"></i> Confirmar Senha
                                </label>
                                <input type="password"
                                       class="form-control"
                                       id="confirm_password"
                                       name="confirm_password"
                                       placeholder="Confirme sua nova senha">
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="row">
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <a href="<?= BASE_URL ?>dashboard" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Salvar Alterações
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Informações de Conta -->
    <div class="row justify-content-center mt-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle"></i> Informações da Conta
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <strong>Data de Criação:</strong><br>
                            <span class="text-muted">
                                <?= date('d/m/Y H:i:s', strtotime($user['created_at'])) ?>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Última Atualização:</strong><br>
                            <span class="text-muted">
                                <?= date('d/m/Y H:i:s', strtotime($user['updated_at'])) ?>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Status da Conta:</strong><br>
                            <span class="badge <?= $user['is_active'] ? 'bg-success' : 'bg-danger' ?>">
                                <?= $user['is_active'] ? 'Ativa' : 'Inativa' ?>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>ID do Usuário:</strong><br>
                            <span class="text-muted font-monospace">#<?= $user['id'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.pendingScripts = window.pendingScripts || [];
window.pendingScripts.push(function() {
    // Validação de senhas
    $('#new_password, #confirm_password').on('input', function() {
        const newPassword = $('#new_password').val();
        const confirmPassword = $('#confirm_password').val();

        if (newPassword && confirmPassword) {
            if (newPassword !== confirmPassword) {
                $('#confirm_password')[0].setCustomValidity('As senhas não coincidem');
            } else {
                $('#confirm_password')[0].setCustomValidity('');
            }
        }
    });

    // Validação antes de enviar
    $('form').on('submit', function(e) {
        const newPassword = $('#new_password').val();
        const confirmPassword = $('#confirm_password').val();
        const currentPassword = $('#current_password').val();

        // Se uma nova senha foi fornecida, validar
        if (newPassword || confirmPassword || currentPassword) {
            if (!currentPassword) {
                e.preventDefault();
                showError('Digite sua senha atual para alterar a senha');
                $('#current_password').focus();
                return false;
            }

            if (!newPassword) {
                e.preventDefault();
                showError('Digite a nova senha');
                $('#new_password').focus();
                return false;
            }

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                showError('As senhas não coincidem');
                $('#confirm_password').focus();
                return false;
            }

            if (newPassword.length < 6) {
                e.preventDefault();
                showError('A nova senha deve ter pelo menos 6 caracteres');
                $('#new_password').focus();
                return false;
            }
        }
    });
});
</script>