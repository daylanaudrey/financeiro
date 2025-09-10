<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Definir constantes do projeto
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');

// Incluir funções auxiliares
require_once CONFIG_PATH . '/functions.php';

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
        '/login' => ['AuthController', 'showLogin'],
        '/register' => ['AuthController', 'showRegister'],
        '/logout' => ['AuthController', 'logout'],
        '/accounts' => ['AccountController', 'index'],
        '/transactions' => ['TransactionController', 'index'],
        '/categories' => ['CategoryController', 'index'],
        '/contacts' => ['ContactController', 'index'],
        '/transfers' => ['TransactionController', 'transfers'],
        '/vaults' => ['VaultController', 'index'],
        '/cost-centers' => ['CostCenterController', 'index'],
        '/reports' => ['ReportController', 'index'],
        '/statements' => ['StatementController', 'index'],
        '/statements/export' => ['StatementController', 'export'],
        '/api/accounts/get' => ['AccountController', 'getAccount'],
        '/api/transactions/get' => ['TransactionController', 'getTransaction'],
        '/api/transactions/transfers' => ['TransactionController', 'getTransfers'],
        '/api/categories/get' => ['CategoryController', 'getCategory'],
        '/api/contacts/get' => ['ContactController', 'getContact'],
        '/api/vaults/get' => ['VaultController', 'getVault'],
        '/api/vaults/statistics' => ['VaultController', 'getStatistics'],
        '/api/vaults/goals' => ['VaultController', 'getVaultsWithGoals'],
        '/api/cost-centers/get' => ['CostCenterController', 'getCostCenter'],
        '/api/cost-centers/active' => ['CostCenterController', 'getActiveCostCenters'],
        '/api/cost-centers/parents' => ['CostCenterController', 'getParentOptions'],
        '/api/cost-centers/report' => ['CostCenterController', 'getReport'],
        '/api/reports/categories' => ['ReportController', 'getCategoriesData'],
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
        '/api/transactions/transfer' => ['TransactionController', 'transfer'],
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
        '/api/cost-centers/create' => ['CostCenterController', 'create'],
        '/api/cost-centers/update' => ['CostCenterController', 'update'],
        '/api/cost-centers/delete' => ['CostCenterController', 'delete'],
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