<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Sistema Financeiro' ?></title>
    
    <!-- PWA Meta Tags -->
    <meta name="description" content="Sistema completo para gestão financeira pessoal e empresarial">
    <meta name="theme-color" content="#007bff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Financeiro">
    <meta name="msapplication-TileColor" content="#007bff">
    <meta name="msapplication-TileImage" content="/assets/icons/icon-144x144.png">
    
    <!-- PWA Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/icons/icon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/icons/icon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/icons/icon-192x192.png">
    <link rel="mask-icon" href="/assets/icons/icon-512x512.png" color="#007bff">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <!-- CSS Local -->
    <link href="<?= url('/assets/css/style.css') ?>" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --secondary-color: #e0e7ff;
            --accent-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --dark-color: #1f2937;
            --light-gray: #f9fafb;
            --border-color: #e5e7eb;
        }
        
        /* Fallback CSS caso os CDNs não carreguem */
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .row { display: flex; flex-wrap: wrap; margin: -15px; }
        .col-lg-3, .col-md-6, .col-lg-6, .col-12 { padding: 15px; }
        .col-lg-3 { flex: 0 0 25%; }
        .col-md-6 { flex: 0 0 50%; }
        .col-lg-6 { flex: 0 0 50%; }
        .col-12 { flex: 0 0 100%; }
        .btn { 
            display: inline-block; 
            padding: 8px 16px; 
            margin: 4px; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
            text-decoration: none; 
            color: white;
            cursor: pointer;
        }
        .btn-success { background-color: #28a745; border-color: #28a745; }
        .btn-danger { background-color: #dc3545; border-color: #dc3545; }
        .card { 
            border: 1px solid #dee2e6; 
            border-radius: 8px; 
            margin-bottom: 20px; 
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card-header { 
            padding: 12px 20px; 
            background-color: #f8f9fa; 
            border-bottom: 1px solid #dee2e6; 
            font-weight: bold;
        }
        .card-body { padding: 20px; }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .page-title { margin: 0; font-size: 2rem; color: #333; }
        .quick-actions { display: flex; gap: 10px; }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .stat-card.success { border-left: 4px solid #28a745; }
        .stat-card.danger { border-left: 4px solid #dc3545; }
        .stat-card.info { border-left: 4px solid #17a2b8; }
        .stat-card.warning { border-left: 4px solid #ffc107; }
        h4, h5, h6 { margin-top: 0; margin-bottom: 10px; }
        h4 { font-size: 1.5rem; color: #333; }
        h5 { font-size: 1.25rem; color: #333; }
        h6 { font-size: 1rem; color: #666; }
        .text-muted { color: #6c757d !important; }
        .mb-2 { margin-bottom: 8px !important; }
        .mb-3 { margin-bottom: 16px !important; }
        .mb-4 { margin-bottom: 24px !important; }
        .mb-5 { margin-bottom: 32px !important; }
        .d-flex { display: flex !important; }
        .justify-content-between { justify-content: space-between !important; }
        .align-items-center { align-items: center !important; }
        
        /* Fix para gráficos Chart.js */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
            overflow: hidden;
        }
        .chart-container canvas {
            max-height: 300px !important;
            max-width: 100% !important;
        }
        
        body {
            background-color: var(--light-gray);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 0 20px 20px 0;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .sidebar h5 {
            color: white;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 12px;
            margin-bottom: 0.5rem;
            padding: 12px 16px;
            transition: all 0.3s ease;
            border: none;
            font-weight: 500;
        }
        
        .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(4px);
        }
        
        .nav-link.active {
            background-color: rgba(255,255,255,0.2);
            color: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .main-content {
            min-height: 100vh;
            padding: 2rem;
        }
        
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid var(--border-color);
            border-radius: 16px 16px 0 0 !important;
            padding: 1.5rem;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }
        
        .stat-card h4 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stat-card h6 {
            font-weight: 600;
            opacity: 0.9;
        }
        
        .stat-card .stat-icon {
            position: absolute;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3rem;
            opacity: 0.2;
        }
        
        .stat-card.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .stat-card.danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        
        .stat-card.warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        .stat-card.info {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }
        
        .user-card {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 16px;
        }
        
        .user-card .card-body {
            padding: 1rem;
        }
        
        .btn {
            border-radius: 12px;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border: none;
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--accent-color) 0%, #059669 100%);
            border: none;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%);
            border: none;
        }
        
        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 1.5rem;
        }
        
        .empty-state h5 {
            color: #6b7280;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .empty-state p {
            color: #9ca3af;
            margin-bottom: 2rem;
        }
        
        .page-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem 0;
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
        }
        
        .quick-actions {
            display: flex;
            gap: 1rem;
        }
        
        /* Select2 Custom Styles */
        .select2-container {
            width: 100% !important;
        }
        
        /* Select2 para filtros (tamanho menor) */
        .select2-container .select2-selection--single {
            height: 38px !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 0.375rem !important;
            background-color: #fff !important;
        }
        
        .select2-container .select2-selection--single .select2-selection__rendered {
            padding-left: 12px !important;
            padding-right: 12px !important;
            color: #212529 !important;
            line-height: 36px !important;
        }
        
        .select2-container .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            right: 12px !important;
        }
        
        /* Select2 para modal (tamanho floating label) */
        .modal .select2-container .select2-selection--single {
            height: 58px !important;
            padding-top: 16px !important;
        }
        
        .modal .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: 1.5 !important;
            padding-top: 8px !important;
        }
        
        .modal .select2-container .select2-selection--single .select2-selection__arrow {
            height: 56px !important;
        }
        
        .select2-dropdown {
            border-radius: 0.375rem !important;
            border: 1px solid #dee2e6 !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08) !important;
        }
        
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--primary-color) !important;
        }
        
        .select2-container--default .select2-results__option--selected {
            background-color: #e9ecef !important;
            color: #212529 !important;
        }
        
        /* Float label for Select2 */
        .form-floating .select2-container {
            height: calc(3.5rem + 2px) !important;
        }
        
        .form-floating .select2-container .select2-selection {
            border: none !important;
            background: none !important;
        }
        
        .form-floating > .select2-container ~ label {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            padding: 1rem 0.75rem;
            pointer-events: none;
            border: 1px solid transparent;
            transform-origin: 0 0;
            transition: opacity 0.1s ease-in-out, transform 0.1s ease-in-out;
            color: #6c757d;
            font-size: 1rem;
            line-height: 1.25;
            z-index: 2;
        }
        
        .form-floating > .select2-container.select2-container--open ~ label,
        .form-floating > .select2-container:not(.select2-container--disabled) ~ label {
            opacity: 0.65;
            transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 col-lg-2 sidebar p-3">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Financeiro
                    </h5>
                </div>
                
                <!-- Info do usuário -->
                <?php if (isset($user) && $user): ?>
                <div class="user-card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-white opacity-75 small">Olá,</div>
                                <div class="text-white font-weight-bold"><?= htmlspecialchars(explode(' ', $user['nome'])[0]) ?></div>
                                <div class="badge" style="background: rgba(255,255,255,0.2); color: white; font-size: 0.7rem;">
                                    <?= ucfirst($user['role']) ?>
                                </div>
                            </div>
                        </div>
                        <a href="<?= url('/logout') ?>" class="btn btn-sm w-100" style="background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2);">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            Sair
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <nav class="nav flex-column">
                    <a class="nav-link <?= ($page ?? '') === 'dashboard' ? 'active' : '' ?>" href="<?= url('/') ?>">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Dashboard
                    </a>
                    <a class="nav-link <?= ($page ?? '') === 'accounts' ? 'active' : '' ?>" href="<?= url('/accounts') ?>">
                        <i class="fas fa-university me-2"></i>
                        Contas
                    </a>
                    <a class="nav-link <?= ($page ?? '') === 'transactions' ? 'active' : '' ?>" href="<?= url('/transactions') ?>">
                        <i class="fas fa-list me-2"></i>
                        Lançamentos
                    </a>
                    <a class="nav-link <?= ($page ?? '') === 'transfers' ? 'active' : '' ?>" href="<?= url('/transfers') ?>">
                        <i class="fas fa-exchange-alt me-2"></i>
                        Transferências
                    </a>
                    <a class="nav-link <?= ($page ?? '') === 'vaults' ? 'active' : '' ?>" href="<?= url('/vaults') ?>">
                        <i class="fas fa-bullseye me-2"></i>
                        Vaults e Objetivos
                    </a>
                    <a class="nav-link <?= ($page ?? '') === 'categories' ? 'active' : '' ?>" href="<?= url('/categories') ?>">
                        <i class="fas fa-tags me-2"></i>
                        Categorias
                    </a>
                    <a class="nav-link <?= ($page ?? '') === 'cost-centers' ? 'active' : '' ?>" href="<?= url('/cost-centers') ?>">
                        <i class="fas fa-building me-2"></i>
                        Centros de Custo
                    </a>
                    <a class="nav-link <?= ($page ?? '') === 'contacts' ? 'active' : '' ?>" href="<?= url('/contacts') ?>">
                        <i class="fas fa-address-book me-2"></i>
                        Contatos
                    </a>
                    <a class="nav-link <?= ($page ?? '') === 'reports' ? 'active' : '' ?>" href="<?= url('/reports') ?>">
                        <i class="fas fa-chart-bar me-2"></i>
                        Relatórios
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 col-lg-10 main-content p-4">
                <?php 
                $contentPage = $page ?? 'dashboard';
                $contentPath = __DIR__ . "/{$contentPage}.php";
                
                if (file_exists($contentPath)) {
                    include $contentPath;
                } else {
                    echo "<div class='alert alert-warning'>Página '{$contentPage}' não encontrada.</div>";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.all.min.js"></script>
    <!-- jQuery (necessário para Select2) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        // Configuração global para SweetAlert2
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
    </script>
    
    <!-- PWA Service Worker -->
    <script>
        // Registrar Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('[PWA] Service Worker registrado com sucesso:', registration.scope);
                        
                        // Verificar por atualizações
                        registration.addEventListener('updatefound', function() {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', function() {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // Nova versão disponível
                                    showUpdateAvailable();
                                }
                            });
                        });
                    })
                    .catch(function(error) {
                        console.log('[PWA] Falha ao registrar Service Worker:', error);
                    });
            });
        }
        
        // Mostrar notificação de atualização disponível
        function showUpdateAvailable() {
            const toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: true,
                confirmButtonText: 'Atualizar',
                showCancelButton: true,
                cancelButtonText: 'Depois',
                timer: 10000,
                timerProgressBar: true
            });
            
            toast.fire({
                icon: 'info',
                title: 'Atualização Disponível',
                text: 'Uma nova versão do app está disponível!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.reload();
                }
            });
        }
        
        // Detectar quando o app está sendo executado em modo standalone (PWA instalado)
        function isPWA() {
            return window.matchMedia('(display-mode: standalone)').matches || 
                   window.navigator.standalone === true;
        }
        
        // Mostrar banner de instalação PWA
        let deferredPrompt;
        
        window.addEventListener('beforeinstallprompt', function(e) {
            // Prevenir o prompt automático
            e.preventDefault();
            deferredPrompt = e;
            
            // Mostrar botão de instalação customizado
            showInstallBanner();
        });
        
        function showInstallBanner() {
            // Verificar se já foi instalado ou se o banner já foi mostrado
            if (isPWA() || localStorage.getItem('pwa-install-dismissed')) {
                return;
            }
            
            const banner = document.createElement('div');
            banner.innerHTML = `
                <div class="alert alert-info alert-dismissible fade show position-fixed" 
                     style="bottom: 20px; right: 20px; max-width: 300px; z-index: 9999; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-mobile-alt me-2"></i>
                        <div class="flex-grow-1">
                            <strong>Instalar App</strong>
                            <div style="font-size: 0.9rem;">Adicione à sua tela inicial para acesso rápido!</div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <button class="btn btn-sm btn-primary me-2" onclick="installPWA()">
                            <i class="fas fa-download me-1"></i> Instalar
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="dismissInstallBanner()">
                            Depois
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(banner);
        }
        
        // Instalar PWA
        window.installPWA = function() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(function(choiceResult) {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('[PWA] Usuário aceitou instalar o app');
                    }
                    deferredPrompt = null;
                    dismissInstallBanner();
                });
            }
        };
        
        // Dispensar banner de instalação
        window.dismissInstallBanner = function() {
            localStorage.setItem('pwa-install-dismissed', 'true');
            const banner = document.querySelector('.alert.position-fixed');
            if (banner) {
                banner.remove();
            }
        };
        
        // Sincronização em background
        if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
            // Registrar para sincronização quando voltar online
            window.addEventListener('online', function() {
                navigator.serviceWorker.ready.then(function(registration) {
                    return registration.sync.register('sync-transactions');
                });
            });
        }
        
        // Notificar sobre status offline/online
        window.addEventListener('offline', function() {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'warning',
                title: 'Você está offline',
                text: 'Algumas funcionalidades podem estar limitadas',
                showConfirmButton: false,
                timer: 3000
            });
        });
        
        window.addEventListener('online', function() {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Conexão restaurada',
                text: 'Todas as funcionalidades estão disponíveis',
                showConfirmButton: false,
                timer: 3000
            });
        });
    </script>
</body>
</html>