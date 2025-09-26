<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Painel Administrativo - Sistema Financeiro' ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .admin-sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
        }
        
        .admin-sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 4px 0;
            transition: all 0.3s ease;
        }
        
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
            transform: translateX(5px);
        }
        
        .admin-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .admin-header {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
            margin-bottom: 2rem;
        }
        
        .stats-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .admin-badge {
            background: linear-gradient(45deg, #ff6b6b, #ffa500);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar Admin -->
            <div class="col-md-3 col-lg-2 admin-sidebar">
                <div class="p-3">
                    <div class="d-flex align-items-center mb-4">
                        <i class="fas fa-crown fs-4 me-2"></i>
                        <div>
                            <h5 class="mb-0">Super Admin</h5>
                            <small class="admin-badge">SISTEMA</small>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <small class="text-light opacity-75">Olá,</small>
                        <div class="fw-bold"><?= $_SESSION['user_name'] ?? 'Admin' ?></div>
                        <small class="text-light opacity-75"><?= $_SESSION['user_email'] ?? '' ?></small>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin') !== false && !strpos($_SERVER['REQUEST_URI'], '/admin/')) ? 'active' : '' ?>" href="<?= url('admin') ?>">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/organizations') !== false && strpos($_SERVER['REQUEST_URI'], '/admin/organizations-users') === false ? 'active' : '' ?>" href="<?= url('admin/organizations') ?>">
                            <i class="fas fa-building me-2"></i>Organizações
                        </a>
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/organizations-users') !== false ? 'active' : '' ?>" href="<?= url('admin/organizations-users') ?>">
                            <i class="fas fa-users-cog me-2"></i>Org. e Usuários
                        </a>
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/subscriptions') !== false ? 'active' : '' ?>" href="<?= url('admin/subscriptions') ?>">
                            <i class="fas fa-credit-card me-2"></i>Assinaturas
                        </a>
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/system-config') !== false ? 'active' : '' ?>" href="<?= url('admin/system-config') ?>">
                            <i class="fas fa-cogs me-2"></i>Configurações
                        </a>
                        <?php $isSuperAdmin = isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'] === true; ?>
                        <?php if ($isSuperAdmin): ?>
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/integrations') !== false ? 'active' : '' ?>" href="<?= url('/integrations') ?>">
                                <i class="fas fa-plug me-2"></i>Integrações
                            </a>
                        <?php else: ?>
                            <a class="nav-link text-muted" href="javascript:void(0)" onclick="showSuperAdminRequired()" title="Apenas superadmin">
                                <i class="fas fa-plug me-2"></i>Integrações
                                <small class="ms-2 badge bg-warning text-dark">Super Admin</small>
                            </a>
                        <?php endif; ?>
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/audit-logs') !== false ? 'active' : '' ?>" href="<?= url('admin/audit-logs') ?>">
                            <i class="fas fa-clipboard-list me-2"></i>Logs de Auditoria
                        </a>
                        
                        <hr class="my-3 opacity-25">
                        
                        <a class="nav-link" href="<?= url('/') ?>">
                            <i class="fas fa-eye me-2"></i>Ver como Usuário
                        </a>
                        <a class="nav-link" href="<?= url('logout') ?>">
                            <i class="fas fa-sign-out-alt me-2"></i>Sair
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Conteúdo Principal -->
            <div class="col-md-9 col-lg-10 admin-content">
                <div class="admin-header">
                    <div class="container-fluid">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0">
                                    <i class="fas fa-crown text-warning me-2"></i>
                                    Painel de Administração do Sistema
                                </h4>
                                <small class="text-muted">Controle total da plataforma SaaS</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success me-2">Sistema Multi-Tenant Ativo</span>
                                <span class="text-muted"><?= date('d/m/Y H:i') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="container-fluid">
                    <!-- Conteúdo da página será inserido aqui -->
                    <?php if (isset($content)) echo $content; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <script>
    function showSuperAdminRequired() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Acesso Restrito',
                text: 'Apenas super administradores podem acessar as configurações de integrações.',
                confirmButtonText: 'Entendi',
                confirmButtonColor: '#ffc107'
            });
        } else {
            alert('Acesso Restrito\n\nApenas super administradores podem acessar as configurações de integrações.');
        }
    }
    </script>
    
    <script>
    // Função para mostrar notificações
    function showNotification(message, type = 'success') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            icon: type,
            title: message
        });
    }
    
    // Auto-refresh de estatísticas a cada 30 segundos
    <?php if (strpos($_SERVER['REQUEST_URI'], '/admin') !== false && !strpos($_SERVER['REQUEST_URI'], '/admin/')): ?>
    setInterval(function() {
        // Refresh das estatísticas do dashboard admin
        location.reload();
    }, 30000);
    <?php endif; ?>
    </script>
</body>
</html>