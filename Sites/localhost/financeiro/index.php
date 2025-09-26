<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // Não mostrar erros na página
ini_set('log_errors', 1); // Manter log de erros

// Definir constantes do projeto
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');

// Incluir funções auxiliares
require_once APP_PATH . '/helpers/functions.php';

// Inicializar logger de erros
require_once APP_PATH . '/services/ErrorLoggerService.php';
ErrorLoggerService::init();

// Função de autoload simples
function autoload($className) {
    $paths = [
        APP_PATH . '/controllers/' . $className . '.php',
        APP_PATH . '/models/' . $className . '.php',
        CONFIG_PATH . '/' . $className . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
}

spl_autoload_register('autoload');

// Router simples
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remover base path se estiver rodando em subdiretório (MAMP)
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath !== '/' && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Para MAMP, remover /financeiro da URI se presente
if (strpos($uri, '/financeiro') === 0) {
    $uri = substr($uri, strlen('/financeiro'));
}

// Remover trailing slash
$uri = rtrim($uri, '/');
if (empty($uri)) {
    $uri = '/';
}

// Rotas básicas
$routes = [
    'GET' => [
        '/' => ['HomeController', 'index'],
        '/dashboard' => ['HomeController', 'dashboard'],
        '/mobile' => ['MobileController', 'index'],
        '/login' => ['AuthController', 'showLogin'],
        '/register' => ['AuthController', 'showRegister'],
        '/logout' => ['AuthController', 'logout'],
        '/verify-email' => ['AuthController', 'verifyEmail'],
        '/resend-verification' => ['AuthController', 'resendVerificationEmail'],
        '/accounts' => ['AccountController', 'index'],
        '/transactions' => ['TransactionController', 'index'],
        '/categories' => ['CategoryController', 'index'],
        '/contacts' => ['ContactController', 'index'],
        '/transfers' => ['TransactionController', 'transfers'],
        '/vaults' => ['VaultController', 'index'],
        '/organizations' => ['OrganizationController', 'index'],
        '/profile' => ['ProfileController', 'index'],
        '/credit-cards' => ['CreditCardController', 'index'],
        '/reports' => ['ReportController', 'index'],
        '/statements' => ['StatementController', 'index'],
        '/statements/export' => ['StatementController', 'export'],
        '/api/accounts/get' => ['AccountController', 'getAccount'],
        '/api/transactions/get' => ['TransactionController', 'getTransaction'],
        '/api/transactions/transfers' => ['TransactionController', 'getTransfers'],
        '/api/transactions/categories' => ['TransactionController', 'getCategoriesByType'],
        '/api/transactions/scheduled' => ['TransactionController', 'getScheduled'],
        '/api/transactions/filter' => ['TransactionController', 'filter'],
        '/api/categories/get' => ['CategoryController', 'getCategory'],
        '/api/contacts/get' => ['ContactController', 'getContact'],
        '/api/vaults/get' => ['VaultController', 'getVault'],
        '/api/vaults/statistics' => ['VaultController', 'getStatistics'],
        '/api/vaults/goals' => ['VaultController', 'getVaultsWithGoals'],
        '/api/credit-cards/get' => ['CreditCardController', 'getCard'],
        '/api/credit-cards/active' => ['CreditCardController', 'getActiveCards'],
        '/api/credit-cards/statistics' => ['CreditCardController', 'getStatistics'],
        '/api/reports/categories' => ['ReportController', 'getCategoriesData'],
        '/api/dashboard/get-layout' => ['DashboardApiController', 'getLayout'],
        // Webhook routes for N8N integration
        '/webhook/test' => ['WebhookController', 'test'],
        '/integrations' => ['IntegrationController', 'index'],
        '/api/integrations/config' => ['IntegrationController', 'getConfig'],
        '/api/integrations/whatsapp-config-edit' => ['IntegrationController', 'getWhatsAppConfigForEdit'],
        '/api/integrations/email-config-edit' => ['IntegrationController', 'getEmailConfigForEdit'],
        '/api/notifications/recent' => ['NotificationController', 'recent'],
        '/api/notifications/pending' => ['NotificationController', 'pending'],
        '/api/notifications/preferences' => ['NotificationController', 'getPreferences'],
        '/api/notifications/audit-log' => ['NotificationController', 'auditLog'],
        // Rotas GET de baixas parciais
        '/api/partial-payments/transaction/{id}' => ['PartialPaymentController', 'listByTransaction'],
        '/api/partial-payments/list' => ['PartialPaymentController', 'listAll'],
        '/partial-payments' => 'partial-payments',
        '/admin' => ['AdminController', 'index'],
        '/admin/organizations' => ['AdminController', 'organizations'],
        '/admin/organizations-users' => ['AdminController', 'organizationsUsers'],
        '/admin/subscriptions' => ['AdminController', 'subscriptions'],
        '/admin/system-config' => ['AdminController', 'systemConfig'],
        '/admin/audit-logs' => ['AdminController', 'auditLogs'],
        // Cron jobs endpoints
        '/cron/due-date-reminders' => ['CronController', 'processDueDateReminders'],
        '/cron/test' => ['CronController', 'testCron'],
        '/cron/status' => ['CronController', 'statusLembretes'],
        // Debug endpoints (temporário)
        '/debug/user-role' => ['OrganizationController', 'debugUserRole'],
        '/debug/due-today' => ['HomeController', 'debugDueToday'],
        // Background job processor
        '/process-notifications' => ['NotificationController', 'processBackground'],
    ],
    'POST' => [
        '/auth/login' => ['AuthController', 'login'],
        '/auth/register' => ['AuthController', 'register'],
        '/api/accounts/create' => ['AccountController', 'create'],
        '/api/accounts/update' => ['AccountController', 'update'],
        '/api/accounts/delete' => ['AccountController', 'delete'],
        '/api/accounts/recalculate' => ['AccountController', 'recalculateBalances'],
        '/api/transactions/create' => ['TransactionController', 'create'],
        '/api/transactions/update' => ['TransactionController', 'update'],
        '/api/transactions/delete' => ['TransactionController', 'delete'],
        '/api/transactions/launch' => ['TransactionController', 'launch'],
        '/api/transactions/confirm' => ['TransactionController', 'confirm'],
        '/api/transactions/partial-payment' => ['TransactionController', 'partialPayment'],
        '/api/transactions/transfer' => ['TransactionController', 'transfer'],
        // Rotas de baixas parciais
        '/api/partial-payments/register' => ['PartialPaymentController', 'register'],
        '/api/partial-payments/cancel' => ['PartialPaymentController', 'cancel'],
        '/api/partial-payments/pending' => ['PartialPaymentController', 'getPendingTransactions'],
        '/api/partial-payments/dashboard' => ['PartialPaymentController', 'dashboard'],
        '/api/partial-payments/toggle' => ['PartialPaymentController', 'togglePartialPayment'],
        '/api/mobile/search' => ['MobileController', 'searchTransactions'],
        '/api/categories/create' => ['CategoryController', 'create'],
        '/api/categories/update' => ['CategoryController', 'update'],
        '/api/categories/delete' => ['CategoryController', 'delete'],
        '/api/contacts/create' => ['ContactController', 'create'],
        '/api/contacts/update' => ['ContactController', 'update'],
        '/api/contacts/delete' => ['ContactController', 'delete'],
        '/api/vaults/create' => ['VaultController', 'create'],
        '/api/vaults/update' => ['VaultController', 'update'],
        '/api/vaults/delete' => ['VaultController', 'delete'],
        '/api/vaults/movement' => ['VaultController', 'addMovement'],
        '/api/vaults/deposit' => ['VaultController', 'deposit'],
        '/api/vaults/withdraw' => ['VaultController', 'withdraw'],
        '/api/credit-cards/create' => ['CreditCardController', 'create'],
        '/api/credit-cards/update' => ['CreditCardController', 'update'],
        '/api/credit-cards/delete' => ['CreditCardController', 'delete'],
        '/api/credit-cards/pay' => ['CreditCardController', 'payCard'],
        '/api/organizations/create' => ['OrganizationController', 'create'],
        '/api/organizations/switch' => ['OrganizationController', 'switchOrg'],
        '/api/organizations/update-member' => ['OrganizationController', 'updateMember'],
        '/api/profile/update' => ['ProfileController', 'update'],
        '/api/organizations/invite' => ['OrganizationController', 'inviteUser'],
        '/api/dashboard/save-layout' => ['DashboardApiController', 'saveLayout'],
        '/api/dashboard/reset-layout' => ['DashboardApiController', 'resetLayout'],
        // Webhook POST routes for N8N integration
        '/webhook/n8n/transaction' => ['WebhookController', 'n8nTransaction'],
        '/api/integrations/test-whatsapp' => ['IntegrationController', 'testWhatsApp'],
        '/api/integrations/send-test-whatsapp' => ['IntegrationController', 'sendTestWhatsApp'],
        '/api/integrations/test-n8n' => ['IntegrationController', 'testN8NWebhook'],
        '/api/integrations/save-config' => ['IntegrationController', 'saveConfig'],
        '/api/integrations/save-whatsapp-config' => ['IntegrationController', 'saveWhatsAppConfig'],
        '/api/integrations/save-email-config' => ['IntegrationController', 'saveEmailConfig'],
        '/api/notifications/preferences' => ['NotificationController', 'savePreferences'],
        '/admin/update-subscription' => ['AdminController', 'updateSubscription'],
        '/admin/update-system-config' => ['AdminController', 'updateSystemConfig'],
        '/admin/test-email-config' => ['AdminController', 'testEmailConfig'],
        '/admin/send-test-email' => ['AdminController', 'sendTestEmail'],
    ]
];

try {
    if (isset($routes[$method][$uri])) {
        $route = $routes[$method][$uri];
        $controllerName = $route[0];
        $actionName = $route[1];
        
        if (class_exists($controllerName)) {
            $controller = new $controllerName();
            if (method_exists($controller, $actionName)) {
                $controller->$actionName();
            } else {
                throw new Exception("Método {$actionName} não encontrado no controller {$controllerName}");
            }
        } else {
            throw new Exception("Controller {$controllerName} não encontrado");
        }
    } else {
        // Dynamic notification routes removed - future: WhatsApp integration
        // Página não encontrada
        http_response_code(404);
        echo "<h1>404 - Página não encontrada</h1>";
        echo "<p>A página solicitada '{$uri}' não foi encontrada.</p>";
        echo "<a href='/'>Voltar ao início</a>";
    }
} catch (Exception $e) {
    error_log("Erro na aplicação: " . $e->getMessage());
    
    // Em desenvolvimento, mostrar erro. Em produção, página de erro genérica
    if (ini_get('display_errors')) {
        echo "<h1>Erro na aplicação</h1>";
        echo "<p><strong>Erro:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
        echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    } else {
        echo "<h1>Erro interno do servidor</h1>";
        echo "<p>Ocorreu um erro inesperado. Tente novamente em alguns instantes.</p>";
    }
}