<?php 
$title = $title ?? 'Email Verificado - Sistema Financeiro';
ob_start();
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h2 class="text-success mb-3"><?= $title ?></h2>
                    
                    <p class="lead mb-4"><?= htmlspecialchars($message) ?></p>
                    
                    <?php if (isset($user_name)): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-user-check me-2"></i>
                            <strong>Conta ativada para:</strong> <?= htmlspecialchars($user_name) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2">
                        <a href="<?= url('login') ?>" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Fazer Login
                        </a>
                        <a href="<?= url('/') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i>Voltar ao Início
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>