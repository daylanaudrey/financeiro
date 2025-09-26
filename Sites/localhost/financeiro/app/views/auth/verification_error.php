<?php 
$title = $title ?? 'Erro na Verificação - Sistema Financeiro';
ob_start();
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h2 class="text-danger mb-3"><?= htmlspecialchars($title) ?></h2>
                    
                    <p class="lead mb-4"><?= htmlspecialchars($message) ?></p>
                    
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Precisa de um novo link?</strong><br>
                        Entre em contato com o suporte ou tente se cadastrar novamente.
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="<?= url('register') ?>" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Cadastrar Novamente
                        </a>
                        <a href="<?= url('login') ?>" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Já Tenho Conta
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