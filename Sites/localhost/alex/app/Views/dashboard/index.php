<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3">
                <i class="bi bi-speedometer2"></i> Dashboard
            </h1>
            <p class="text-muted">
                Bem-vindo(a), <?= htmlspecialchars($user_name) ?>!
                <span class="badge bg-secondary"><?= ucfirst($user_role) ?></span>
            </p>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row g-3 mb-4">
        <?php if (Permission::check('products.view')): ?>
        <!-- Total de Produtos -->
        <div class="col-md-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number"><?= number_format($stats['total_products']) ?></div>
                            <div class="stat-label">Produtos Cadastrados</div>
                        </div>
                        <div>
                            <i class="bi bi-box display-4 text-primary opacity-25"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>products" class="btn btn-sm btn-outline-primary">
                            Ver produtos <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (Permission::check('clients.view')): ?>
        <!-- Total de Importadores -->
        <div class="col-md-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number"><?= number_format($stats['total_clients']) ?></div>
                            <div class="stat-label">Importadores Ativos</div>
                        </div>
                        <div>
                            <i class="bi bi-people display-4 text-success opacity-25"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>clients" class="btn btn-sm btn-outline-success">
                            Ver importadores <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (Permission::check('processes.view')): ?>
        <!-- Total de Processos -->
        <div class="col-md-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number"><?= number_format($stats['total_processes']) ?></div>
                            <div class="stat-label">Processos Total</div>
                        </div>
                        <div>
                            <i class="bi bi-file-text display-4 text-info opacity-25"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>processes" class="btn btn-sm btn-outline-info">
                            Ver processos <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (Permission::check('processes.view')): ?>
        <!-- Processos Pendentes -->
        <div class="col-md-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number"><?= number_format($stats['pending_processes']) ?></div>
                            <div class="stat-label">Processos Pendentes</div>
                        </div>
                        <div>
                            <i class="bi bi-clock-history display-4 text-warning opacity-25"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>processes?status=PRE+EMBARQUE" class="btn btn-sm btn-outline-warning">
                            Ver pendentes <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (Permission::check('ports.view')): ?>
        <!-- Portos Ativos -->
        <div class="col-md-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number"><?= number_format($stats['total_ports']) ?></div>
                            <div class="stat-label">Portos Ativos</div>
                        </div>
                        <div>
                            <i class="bi bi-geo-alt display-4 text-secondary opacity-25"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>ports" class="btn btn-sm btn-outline-secondary">
                            Ver portos <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Ações Rápidas -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning"></i> Ações Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php if (Permission::check('processes.create')): ?>
                            <div class="col-md-3">
                                <a href="<?= BASE_URL ?>processes/create" class="btn btn-primary w-100">
                                    <i class="bi bi-plus-circle"></i> Novo Processo
                                </a>
                            </div>
                        <?php endif; ?>
                        <?php if (Permission::check('products.create')): ?>
                            <div class="col-md-3">
                                <a href="<?= BASE_URL ?>products/create" class="btn btn-success w-100">
                                    <i class="bi bi-box-seam"></i> Cadastrar Produto
                                </a>
                            </div>
                        <?php endif; ?>
                        <?php if (Permission::check('clients.create')): ?>
                            <div class="col-md-3">
                                <a href="<?= BASE_URL ?>clients/create" class="btn btn-info text-white w-100">
                                    <i class="bi bi-person-plus"></i> Cadastrar Importador
                                </a>
                            </div>
                        <?php endif; ?>
                        <?php if (Permission::check('ports.create')): ?>
                            <div class="col-md-3">
                                <a href="<?= BASE_URL ?>ports/create" class="btn btn-secondary w-100">
                                    <i class="bi bi-geo-alt-fill"></i> Cadastrar Porto
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php
                        $hasAnyCreatePermission = Permission::checkAny([
                            'processes.create', 'products.create', 'clients.create', 'ports.create'
                        ]);
                        ?>
                        <?php if (!$hasAnyCreatePermission): ?>
                            <div class="col-12">
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle"></i>
                                    Você tem permissão apenas para visualizar os dados do sistema.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Processos Recentes (se houver) -->
    <?php if (!empty($stats['recent_processes'])): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clock-history"></i> Processos Recentes
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Importador</th>
                                        <th>Data</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Processos recentes serão listados aqui -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Informações do Sistema -->
    <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-gear"></i> Administração
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <a href="<?= BASE_URL ?>users" class="btn btn-outline-dark w-100">
                                    <i class="bi bi-people"></i> Gerenciar Usuários
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="<?= BASE_URL ?>reports" class="btn btn-outline-dark w-100">
                                    <i class="bi bi-file-earmark-bar-graph"></i> Relatórios Gerenciais
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>