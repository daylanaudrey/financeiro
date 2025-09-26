<div class="login-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card login-card shadow">
                    <div class="card-body p-4">
                        <!-- Logo e Título -->
                        <div class="text-center mb-4">
                            <i class="bi bi-box-seam display-4 text-primary"></i>
                            <h2 class="mt-2">Sistema Aduaneiro</h2>
                            <p class="text-muted">Numerário de Importação Direta</p>
                        </div>

                        <!-- Mensagem de erro -->
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Formulário de Login -->
                        <form action="<?= BASE_URL ?>login" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope"></i> Email
                                </label>
                                <input type="email"
                                       class="form-control form-control-lg"
                                       id="email"
                                       name="email"
                                       placeholder="seu@email.com"
                                       required
                                       autofocus>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock"></i> Senha
                                </label>
                                <div class="input-group">
                                    <input type="password"
                                           class="form-control form-control-lg"
                                           id="password"
                                           name="password"
                                           placeholder="Sua senha"
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="bi bi-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-box-arrow-in-right"></i> Entrar
                                </button>
                            </div>
                        </form>

                        <!-- Nota -->
                        <div class="text-center mt-4">
                            <small class="text-muted">
                                <i class="bi bi-shield-lock"></i>
                                Sistema restrito. Acesso somente para usuários autorizados.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center mt-3">
                    <small class="text-muted">
                        &copy; <?= date('Y') ?> DAG Solução Digital
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
document.getElementById('togglePassword')?.addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('bi-eye');
        toggleIcon.classList.add('bi-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('bi-eye-slash');
        toggleIcon.classList.add('bi-eye');
    }
});
</script>