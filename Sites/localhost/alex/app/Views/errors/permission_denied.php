<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-shield-x"></i> Acesso Negado
                    </h5>
                </div>
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-shield-exclamation text-danger" style="font-size: 4rem;"></i>
                    </div>

                    <h4 class="text-danger mb-3">Permissão Insuficiente</h4>

                    <p class="text-muted mb-4">
                        Você não possui permissão para acessar esta funcionalidade.
                        <?php if (isset($required_permission)): ?>
                            <br><small>Permissão necessária: <code><?= htmlspecialchars($required_permission) ?></code></small>
                        <?php endif; ?>
                    </p>

                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle"></i>
                        <strong>Seu perfil atual:</strong>
                        <span class="badge bg-primary"><?= ucfirst($_SESSION['user_role'] ?? 'indefinido') ?></span>
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <a href="<?= BASE_URL ?>dashboard" class="btn btn-primary">
                            <i class="bi bi-house"></i> Voltar ao Dashboard
                        </a>

                        <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                            <i class="bi bi-arrow-left"></i> Página Anterior
                        </button>
                    </div>

                    <hr class="my-4">

                    <div class="text-muted">
                        <small>
                            <i class="bi bi-question-circle"></i>
                            Precisa de acesso? Entre em contato com o administrador do sistema.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.bi-shield-exclamation {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
    100% {
        opacity: 1;
    }
}
</style>