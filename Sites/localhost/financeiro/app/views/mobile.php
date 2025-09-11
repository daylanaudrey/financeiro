<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Financeiro Mobile</title>
    
    <!-- PWA Meta Tags -->
    <meta name="description" content="Sistema de gestão financeira otimizado para mobile">
    <meta name="theme-color" content="#007bff">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Financeiro">
    <meta name="msapplication-TileColor" content="#007bff">
    
    <!-- PWA Icons -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg width='32' height='32' xmlns='http://www.w3.org/2000/svg'%3E%3Crect width='32' height='32' rx='6' ry='6' fill='%23007bff'/%3E%3Ctext x='16' y='22' font-family='Arial' font-size='16' font-weight='bold' text-anchor='middle' fill='white'%3E$%3C/text%3E%3C/svg%3E">
    <link rel="apple-touch-icon" href="data:image/svg+xml,%3Csvg width='180' height='180' xmlns='http://www.w3.org/2000/svg'%3E%3Crect width='180' height='180' rx='32' ry='32' fill='%23007bff'/%3E%3Ccircle cx='90' cy='90' r='52' fill='white'/%3E%3Ctext x='90' y='108' font-family='Arial' font-size='60' font-weight='bold' text-anchor='middle' fill='%23007bff'%3E$%3C/text%3E%3C/svg%3E">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="<?= url('/mobile-manifest.json') ?>">
    
    <!-- Preload Critical Resources -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #007bff;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --light-gray: #f8f9fa;
            --border-color: #dee2e6;
        }
        
        body {
            background-color: var(--light-gray);
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .mobile-container {
            max-width: 100vw;
            margin: 0;
            padding: 10px;
        }
        
        .mobile-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #0056b3 100%);
            color: white;
            padding: 20px 15px;
            margin: -10px -10px 20px -10px;
            text-align: center;
            border-radius: 0 0 20px 20px;
        }
        
        .mobile-header h1 {
            font-size: 1.5rem;
            margin: 0 0 10px 0;
            font-weight: 600;
        }
        
        .balance-info {
            display: flex;
            justify-content: space-around;
            text-align: center;
            margin-top: 15px;
        }
        
        .balance-item {
            flex: 1;
        }
        
        .balance-item .label {
            font-size: 0.8rem;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        
        .balance-item .value {
            font-size: 1.1rem;
            font-weight: bold;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .action-btn {
            padding: 20px;
            border-radius: 15px;
            border: none;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: transform 0.2s;
        }
        
        .action-btn:hover, .action-btn:active {
            transform: translateY(-2px);
        }
        
        .action-btn.success {
            background: linear-gradient(135deg, var(--success-color) 0%, #1e7e34 100%);
        }
        
        .action-btn.danger {
            background: linear-gradient(135deg, var(--danger-color) 0%, #bd2130 100%);
        }
        
        .action-btn i {
            font-size: 1.5rem;
        }
        
        .accounts-section {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .account-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .account-item:last-child {
            border-bottom: none;
        }
        
        .account-info .name {
            font-weight: 600;
            color: #333;
        }
        
        .account-info .type {
            font-size: 0.8rem;
            color: #666;
        }
        
        .account-balance {
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .transactions-section {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .transaction-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .transaction-item:last-child {
            border-bottom: none;
        }
        
        .transaction-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
            font-size: 1.1rem;
        }
        
        .transaction-icon.income {
            background: var(--success-color);
        }
        
        .transaction-icon.expense {
            background: var(--danger-color);
        }
        
        .transaction-details {
            flex: 1;
        }
        
        .transaction-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 3px;
            font-size: 0.95rem;
        }
        
        .transaction-info {
            font-size: 0.8rem;
            color: #666;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .transaction-value {
            font-weight: bold;
            text-align: right;
        }
        
        .transaction-value.income {
            color: var(--success-color);
        }
        
        .transaction-value.expense {
            color: var(--danger-color);
        }
        
        .created-by {
            font-size: 0.7rem;
            color: #999;
            font-style: italic;
        }
        
        /* Modal styles */
        .modal-content {
            border-radius: 15px;
        }
        
        .form-floating label {
            font-size: 0.9rem;
        }
        
        .btn {
            border-radius: 10px;
            font-weight: 600;
        }
        
        /* PWA Specific Styles */
        .pwa-mode .mobile-header {
            padding-top: 40px; /* Safe area for status bar */
        }
        
        #install-button {
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }
        
        #install-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 123, 255, 0.4);
        }
        
        /* Safe area adjustments for PWA */
        @supports (padding: max(0px)) {
            .pwa-mode {
                padding-left: max(10px, env(safe-area-inset-left));
                padding-right: max(10px, env(safe-area-inset-right));
                padding-bottom: max(10px, env(safe-area-inset-bottom));
            }
        }
        
        @media (max-width: 576px) {
            .mobile-container {
                padding: 5px;
            }
            
            .mobile-header {
                margin: -5px -5px 15px -5px;
                padding: 15px 10px;
            }
            
            .pwa-mode .mobile-header {
                padding-top: 35px;
            }
            
            .action-btn {
                padding: 15px;
                font-size: 0.9rem;
            }
            
            .action-btn i {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="mobile-container">
        <!-- Header -->
        <div class="mobile-header">
            <h1><i class="fas fa-wallet me-2"></i>Financeiro</h1>
            <div class="balance-info">
                <div class="balance-item">
                    <div class="label">Saldo Total</div>
                    <div class="value">R$ <?= number_format($totalBalance, 2, ',', '.') ?></div>
                </div>
                <div class="balance-item">
                    <div class="label">Receitas (Mês)</div>
                    <div class="value">R$ <?= number_format($monthlyBalance['receitas'] ?? 0, 2, ',', '.') ?></div>
                </div>
                <div class="balance-item">
                    <div class="label">Despesas (Mês)</div>
                    <div class="value">R$ <?= number_format($monthlyBalance['despesas'] ?? 0, 2, ',', '.') ?></div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <button class="action-btn success" data-bs-toggle="modal" data-bs-target="#incomeModal">
                <i class="fas fa-plus"></i>
                Nova Receita
            </button>
            <button class="action-btn danger" data-bs-toggle="modal" data-bs-target="#expenseModal">
                <i class="fas fa-minus"></i>
                Nova Despesa
            </button>
        </div>
        
        <!-- Accounts Section -->
        <div class="accounts-section">
            <div class="section-title">
                <i class="fas fa-university"></i>
                Saldo das Contas
            </div>
            <?php foreach ($accounts as $account): ?>
                <div class="account-item">
                    <div class="account-info">
                        <div class="name"><?= htmlspecialchars($account['nome']) ?></div>
                        <div class="type"><?= ucfirst($account['tipo']) ?> - <?= $account['pessoa_tipo'] ?></div>
                    </div>
                    <div class="account-balance">
                        R$ <?= number_format($account['saldo_atual'], 2, ',', '.') ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Recent Transactions -->
        <div class="transactions-section">
            <div class="section-title">
                <i class="fas fa-history"></i>
                Últimos Lançamentos
            </div>
            <?php if (!empty($recentTransactions)): ?>
                <?php foreach ($recentTransactions as $transaction): ?>
                    <div class="transaction-item">
                        <div class="transaction-icon <?= $transaction['kind'] === 'entrada' ? 'income' : 'expense' ?>">
                            <i class="fas fa-<?= $transaction['kind'] === 'entrada' ? 'arrow-up' : 'arrow-down' ?>"></i>
                        </div>
                        <div class="transaction-details">
                            <div class="transaction-title"><?= htmlspecialchars($transaction['descricao']) ?></div>
                            <div class="transaction-info">
                                <span><?= htmlspecialchars($transaction['account_name']) ?></span>
                                <span>•</span>
                                <span><?= date('d/m/Y', strtotime($transaction['data_competencia'])) ?></span>
                                <?php if ($transaction['category_name']): ?>
                                    <span>•</span>
                                    <span><?= htmlspecialchars($transaction['category_name']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($transaction['created_by_name']): ?>
                                <div class="created-by">por <?= htmlspecialchars($transaction['created_by_name']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="transaction-value <?= $transaction['kind'] === 'entrada' ? 'income' : 'expense' ?>">
                            <?= $transaction['kind'] === 'entrada' ? '+' : '-' ?>R$ <?= number_format($transaction['valor'], 2, ',', '.') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; color: #666; padding: 20px;">
                    <i class="fas fa-receipt" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.5;"></i>
                    <p>Nenhum lançamento encontrado</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal Nova Receita -->
    <div class="modal fade" id="incomeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Receita</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="incomeForm">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="incomeDescricao" name="descricao" required>
                            <label for="incomeDescricao">Descrição *</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="incomeValor" name="valor" placeholder="R$ 0,00" required>
                            <label for="incomeValor">Valor *</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <select class="form-select" id="incomeAccount" name="account_id" required>
                                <option value="">Selecione uma conta...</option>
                                <?php foreach ($accounts as $account): ?>
                                    <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label for="incomeAccount">Conta *</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <select class="form-select" id="incomeCategory" name="category_id">
                                <option value="">Selecione uma categoria...</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>">
                                        <?= htmlspecialchars($category['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="incomeCategory">Categoria</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" id="incomeData" name="data_competencia" required>
                            <label for="incomeData">Data *</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="saveIncome()">
                        <i class="fas fa-save me-2"></i>Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Nova Despesa -->
    <div class="modal fade" id="expenseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Despesa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="expenseForm">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="expenseDescricao" name="descricao" required>
                            <label for="expenseDescricao">Descrição *</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="expenseValor" name="valor" placeholder="R$ 0,00" required>
                            <label for="expenseValor">Valor *</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <select class="form-select" id="expenseAccount" name="account_id" required>
                                <option value="">Selecione uma conta...</option>
                                <?php foreach ($accounts as $account): ?>
                                    <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label for="expenseAccount">Conta *</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <select class="form-select" id="expenseCategory" name="category_id">
                                <option value="">Selecione uma categoria...</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>">
                                        <?= htmlspecialchars($category['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="expenseCategory">Categoria</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" id="expenseData" name="data_competencia" required>
                            <label for="expenseData">Data *</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" onclick="saveExpense()">
                        <i class="fas fa-save me-2"></i>Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.js"></script>
    
    <script>
        // Configurar data de hoje como padrão
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('incomeData').value = today;
            document.getElementById('expenseData').value = today;
            
            // Aplicar máscara de moeda
            function formatBrazilianCurrency(value) {
                let numbers = value.replace(/\D/g, '');
                if (numbers === '') return 'R$ ';
                let amount = parseInt(numbers) / 100;
                return 'R$ ' + amount.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
            
            function applyMask(field) {
                field.addEventListener('input', function(e) {
                    e.target.value = formatBrazilianCurrency(e.target.value);
                });
            }
            
            applyMask(document.getElementById('incomeValor'));
            applyMask(document.getElementById('expenseValor'));
            
            // Carregar categorias corretas ao abrir modais
            document.getElementById('incomeModal').addEventListener('show.bs.modal', function() {
                updateCategoriesByType('entrada', 'incomeCategory');
            });
            
            document.getElementById('expenseModal').addEventListener('show.bs.modal', function() {
                updateCategoriesByType('saida', 'expenseCategory');
            });
        });
        
        // Função para converter moeda brasileira para decimal
        function parseBrazilianCurrency(value) {
            if (!value || value === 'R$ ') return '0';
            let cleaned = value.replace(/^R\$\s*/, '');
            if (cleaned.includes(',')) {
                cleaned = cleaned.replace(/\./g, '').replace(',', '.');
                return cleaned;
            }
            if (cleaned.includes('.')) {
                const parts = cleaned.split('.');
                if (parts[parts.length - 1].length > 2) {
                    return cleaned.replace(/\./g, '');
                }
                if (parts.length === 2 && parts[1].length <= 2) {
                    return cleaned;
                }
            }
            return cleaned;
        }
        
        // Função para atualizar categorias baseado no tipo
        function updateCategoriesByType(tipo, selectId) {
            if (!tipo) return;
            
            fetch(`<?= url('/api/transactions/categories') ?>?tipo=${tipo}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCategorySelect(data.categories, selectId);
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar categorias:', error);
                });
        }
        
        function updateCategorySelect(categories, selectId) {
            const categorySelect = document.getElementById(selectId);
            const firstOption = categorySelect.querySelector('option:first-child');
            categorySelect.innerHTML = '';
            categorySelect.appendChild(firstOption);
            
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.nome;
                categorySelect.appendChild(option);
            });
        }
        
        function saveIncome() {
            const form = document.getElementById('incomeForm');
            const formData = new FormData(form);
            
            const valorField = document.getElementById('incomeValor');
            const valorNumerico = parseBrazilianCurrency(valorField.value);
            formData.set('valor', valorNumerico);
            formData.set('kind', 'entrada');
            formData.set('status', 'confirmado');
            
            fetch('<?= url('/api/transactions/create') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Erro!', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire('Erro!', 'Erro ao salvar receita', 'error');
            });
        }
        
        function saveExpense() {
            const form = document.getElementById('expenseForm');
            const formData = new FormData(form);
            
            const valorField = document.getElementById('expenseValor');
            const valorNumerico = parseBrazilianCurrency(valorField.value);
            formData.set('valor', valorNumerico);
            formData.set('kind', 'saida');
            formData.set('status', 'confirmado');
            
            fetch('<?= url('/api/transactions/create') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Erro!', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire('Erro!', 'Erro ao salvar despesa', 'error');
            });
        }
        
        // Resetar formulários quando modais fecham
        document.getElementById('incomeModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('incomeForm').reset();
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('incomeData').value = today;
            document.getElementById('incomeValor').value = 'R$ 0,00';
        });
        
        document.getElementById('expenseModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('expenseForm').reset();
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('expenseData').value = today;
            document.getElementById('expenseValor').value = 'R$ 0,00';
        });
        
        // PWA - Registrar Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('<?= url('/mobile-sw.js') ?>')
                    .then(function(registration) {
                        console.log('SW registered successfully: ', registration.scope);
                        return registration.update();
                    })
                    .then(function() {
                        console.log('SW updated successfully');
                    })
                    .catch(function(registrationError) {
                        console.error('SW registration failed: ', registrationError);
                    });
            });
        } else {
            console.log('Service Worker not supported');
        }
        
        // PWA - Detectar instalação
        let deferredPrompt;
        
        window.addEventListener('beforeinstallprompt', (e) => {
            console.log('PWA install prompt available');
            e.preventDefault();
            deferredPrompt = e;
            
            // Mostrar botão de instalação customizado
            showInstallButton();
        });
        
        // PWA - Detectar se PWA é instalável
        if ('getInstalledRelatedApps' in navigator) {
            navigator.getInstalledRelatedApps().then((relatedApps) => {
                console.log('Related apps:', relatedApps);
            });
        }
        
        // PWA - Debug manifest
        if ('serviceWorker' in navigator) {
            fetch('<?= url('/mobile-manifest.json') ?>')
                .then(response => response.json())
                .then(manifest => {
                    console.log('Manifest loaded successfully:', manifest);
                })
                .catch(error => {
                    console.error('Failed to load manifest:', error);
                });
        }
        
        window.addEventListener('appinstalled', (evt) => {
            console.log('PWA was installed');
            hideInstallButton();
        });
        
        function showInstallButton() {
            // Criar botão de instalação se não existir
            if (!document.getElementById('install-button')) {
                const installButton = document.createElement('button');
                installButton.id = 'install-button';
                installButton.innerHTML = '<i class="fas fa-download me-2"></i>Instalar App';
                installButton.className = 'btn btn-primary position-fixed';
                installButton.style.cssText = 'bottom: 20px; right: 20px; z-index: 1000; border-radius: 25px;';
                
                installButton.addEventListener('click', async () => {
                    if (deferredPrompt) {
                        deferredPrompt.prompt();
                        const { outcome } = await deferredPrompt.userChoice;
                        console.log(`User response to the install prompt: ${outcome}`);
                        deferredPrompt = null;
                        hideInstallButton();
                    }
                });
                
                document.body.appendChild(installButton);
            }
        }
        
        function hideInstallButton() {
            const installButton = document.getElementById('install-button');
            if (installButton) {
                installButton.remove();
            }
        }
        
        // PWA - Detectar se está rodando como app instalado
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
            console.log('Running as installed PWA');
            document.body.classList.add('pwa-mode');
        }
        
        // PWA - Lidar com shortcuts do manifest
        const urlParams = new URLSearchParams(window.location.search);
        const action = urlParams.get('action');
        
        if (action === 'income') {
            // Abrir modal de receita automaticamente
            setTimeout(() => {
                const incomeModal = new bootstrap.Modal(document.getElementById('incomeModal'));
                incomeModal.show();
            }, 500);
        } else if (action === 'expense') {
            // Abrir modal de despesa automaticamente
            setTimeout(() => {
                const expenseModal = new bootstrap.Modal(document.getElementById('expenseModal'));
                expenseModal.show();
            }, 500);
        }
    </script>
</body>
</html>