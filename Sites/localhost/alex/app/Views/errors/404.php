<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="text-center py-5">
                <!-- Error Icon -->
                <i class="bi bi-exclamation-triangle display-1 text-warning mb-4"></i>

                <!-- Error Message -->
                <h1 class="display-4 fw-bold">404</h1>
                <h2 class="h4 mb-3">Página não encontrada</h2>
                <p class="text-muted mb-4">
                    A página que você está procurando não existe ou foi movida.
                </p>

                <!-- Actions -->
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                    <a href="<?= BASE_URL ?>" class="btn btn-primary">
                        <i class="bi bi-house"></i> Página Inicial
                    </a>

                    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                        <a href="<?= BASE_URL ?>dashboard" class="btn btn-outline-primary">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>login" class="btn btn-outline-primary">
                            <i class="bi bi-box-arrow-in-right"></i> Fazer Login
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Help Text -->
                <div class="mt-5">
                    <small class="text-muted">
                        Se você acredita que isso é um erro, entre em contato com o suporte.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>