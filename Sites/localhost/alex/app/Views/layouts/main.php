<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Aduaneiro - Numerário de Importação</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= PUBLIC_URL ?>css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= BASE_URL ?>">
                <i class="bi bi-box-seam"></i> Sistema Aduaneiro
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>dashboard">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <?php if (Permission::check('products.view')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>products">
                                <i class="bi bi-box"></i> Produtos
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (Permission::check('clients.view')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>clients">
                                <i class="bi bi-people"></i> Importadores
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (Permission::check('ports.view')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>ports">
                                <i class="bi bi-geo-alt"></i> Portos
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (Permission::check('processes.view')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>processes">
                                <i class="bi bi-file-text"></i> Processos
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (Permission::check('system.exchange_rates')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>exchange-rates">
                                <i class="bi bi-currency-exchange"></i> Taxa de Câmbio
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-gear"></i> Admin
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>users">
                                    <i class="bi bi-people"></i> Usuários
                                </a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>permissions/roles">
                                    <i class="bi bi-shield-check"></i> Permissões
                                </a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>audit">
                                    <i class="bi bi-file-text"></i> Auditoria
                                </a></li>
                            </ul>
                        </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?= $_SESSION['user_name'] ?? 'Usuário' ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>profile">
                                    <i class="bi bi-person"></i> Meu Perfil
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>logout">
                                    <i class="bi bi-box-arrow-right"></i> Sair
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>login">
                                <i class="bi bi-box-arrow-in-right"></i> Entrar
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Conteúdo Principal -->
    <main class="py-4">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">
                Sistema Aduaneiro &copy; <?= date('Y') ?> - DAG Solução Digital
            </span>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- jQuery Mask Plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.all.min.js"></script>
    <!-- Custom JS -->
    <script src="<?= PUBLIC_URL ?>js/script.js"></script>

    <!-- Configuração global Select2 e SweetAlert2 -->
    <script>
        // Array global para mensagens pendentes
        window.pendingMessages = window.pendingMessages || [];

        $(document).ready(function() {
            // Configuração padrão Select2
            $('.select2').select2({
                theme: 'bootstrap-5',
                placeholder: 'Selecione...',
                allowClear: true
            });

            // Configuração global de máscaras
            initValueMasks();

            // Executar mensagens pendentes
            if (window.pendingMessages && window.pendingMessages.length > 0) {
                window.pendingMessages.forEach(function(msg) {
                    if (msg.type === 'success') {
                        showSuccess(msg.message);
                    } else if (msg.type === 'error') {
                        showError(msg.message);
                    }
                });
                window.pendingMessages = []; // Limpar após execução
            }

            // Executar scripts pendentes
            if (window.pendingScripts && window.pendingScripts.length > 0) {
                window.pendingScripts.forEach(function(script) {
                    if (typeof script === 'function') {
                        script();
                    }
                });
                window.pendingScripts = []; // Limpar após execução
            }
        });

        // Configuração global SweetAlert2
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        // Função helper para sucesso
        function showSuccess(message) {
            Toast.fire({
                icon: 'success',
                title: message
            });
        }

        // Função helper para erro
        function showError(message) {
            Toast.fire({
                icon: 'error',
                title: message
            });
        }

        // Função helper para confirmação
        function showConfirm(title, text, confirmCallback) {
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, confirmar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed && confirmCallback) {
                    confirmCallback();
                }
            });
        }

        // Sistema de máscaras de valores
        function initValueMasks() {
            // Máscara para valores monetários (dólar) - permitindo valores grandes
            $('[data-mask="currency-usd"]').mask('000.000.000.000.000,00', {
                reverse: true
            });

            // Máscara para valores monetários (real)
            $('[data-mask="currency-brl"]').mask('000.000.000.000.000,00', {
                reverse: true
            });

            // Máscara para percentuais
            $('[data-mask="percentage"]').mask('##0,00', {
                reverse: true,
                translation: {
                    '#': {pattern: /[0-9]/}
                }
            });

            // Máscara para peso (kg) - permitir valores maiores
            $('[data-mask="weight"]').mask('##.##0,000', {
                reverse: true,
                translation: {
                    '#': {pattern: /[0-9]/}
                }
            });

            // Máscara para RFB (valores simples sem formatação complexa)
            $('[data-mask="rfb-value"]').mask('00000,00', {
                reverse: true,
                placeholder: '0,00'
            });

            // Aplicar máscaras automaticamente baseado em classes CSS
            $('.mask-currency-usd').attr('data-mask', 'currency-usd');
            $('.mask-currency-brl').attr('data-mask', 'currency-brl');
            $('.mask-percentage').attr('data-mask', 'percentage');
            $('.mask-weight').attr('data-mask', 'weight');
            $('.mask-quantity').attr('data-mask', 'quantity');
            $('.mask-rfb-value').attr('data-mask', 'rfb-value');

            // Reaplicar máscaras
            $('.mask-currency-usd').mask('000.000.000.000.000,00', {reverse: true});
            $('.mask-currency-brl').mask('000.000.000.000.000,00', {reverse: true});
            $('.mask-percentage').mask('##0,00', {reverse: true});
            $('.mask-weight').mask('##.##0,000', {reverse: true});
            $('.mask-quantity').mask('#.##0,000', {reverse: true});
            // Máscara RFB simples - valores monetários normais
            $('.mask-rfb-value').mask('000.000.000,00', {reverse: true});

            // NÃO formatar currency-usd no blur para manter o formato brasileiro

            $('.mask-percentage').on('blur', function() {
                let val = $(this).val().replace(/\D/g, '');
                if (val) {
                    let formatted = (parseFloat(val) / 100).toLocaleString('pt-BR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    $(this).val(formatted);
                }
            });
        }
    </script>
</body>
</html>