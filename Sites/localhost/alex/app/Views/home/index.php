<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Hero Section -->
            <div class="text-center py-5">
                <h1 class="display-4 fw-bold">
                    <i class="bi bi-box-seam text-primary"></i>
                    Sistema Aduaneiro
                </h1>
                <p class="lead text-muted">
                    Numerário de Importação Direta - Gestão completa de processos aduaneiros
                </p>
            </div>

            <?php if (!isset($_SESSION['user_id'])): ?>
                <!-- Card para login -->
                <div class="row justify-content-center mb-5">
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-body text-center p-5">
                                <i class="bi bi-box-arrow-in-right display-1 text-primary mb-3"></i>
                                <h3 class="card-title">Acesso ao Sistema</h3>
                                <p class="card-text">
                                    Faça login para acessar o sistema de gestão aduaneira
                                </p>
                                <a href="<?= BASE_URL ?>login" class="btn btn-primary btn-lg">
                                    <i class="bi bi-box-arrow-in-right"></i> Fazer Login
                                </a>
                                <hr class="my-4">
                                <div class="text-muted">
                                    <small>
                                        <i class="bi bi-shield-lock"></i>
                                        Sistema restrito. Contate o administrador para obter acesso.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Cards para usuários logados -->
                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-speedometer2 display-3 text-primary mb-3"></i>
                                <h4 class="card-title">Dashboard</h4>
                                <p class="card-text">
                                    Visão geral dos processos e estatísticas
                                </p>
                                <a href="<?= BASE_URL ?>dashboard" class="btn btn-primary">
                                    Acessar
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-file-text display-3 text-success mb-3"></i>
                                <h4 class="card-title">Processos</h4>
                                <p class="card-text">
                                    Gerencie os processos de importação
                                </p>
                                <a href="<?= BASE_URL ?>processos" class="btn btn-success">
                                    Acessar
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-box display-3 text-info mb-3"></i>
                                <h4 class="card-title">Produtos</h4>
                                <p class="card-text">
                                    Cadastro e gestão de produtos NCM
                                </p>
                                <a href="<?= BASE_URL ?>produtos" class="btn btn-info text-white">
                                    Acessar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Features -->
            <div class="row g-4 mt-5">
                <div class="col-12">
                    <h2 class="text-center mb-4">Funcionalidades do Sistema</h2>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <i class="bi bi-calculator display-4 text-primary mb-3"></i>
                        <h5>Cálculos Automáticos</h5>
                        <p class="text-muted small">
                            Cálculo automático de impostos e taxas aduaneiras
                        </p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <i class="bi bi-file-pdf display-4 text-danger mb-3"></i>
                        <h5>Exportação</h5>
                        <p class="text-muted small">
                            Exporte relatórios em PDF e Excel
                        </p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <i class="bi bi-shield-check display-4 text-success mb-3"></i>
                        <h5>Segurança</h5>
                        <p class="text-muted small">
                            Sistema seguro com controle de acesso
                        </p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <i class="bi bi-graph-up display-4 text-warning mb-3"></i>
                        <h5>Relatórios</h5>
                        <p class="text-muted small">
                            Dashboards e relatórios gerenciais
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>