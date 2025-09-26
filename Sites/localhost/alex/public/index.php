<?php
/**
 * Sistema Aduaneiro
 * Ponto de entrada da aplicação
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Iniciar sessão
session_start();

// Carregar configurações
require_once '../config/config.php';

// Autoloader simples
function autoload($className) {
    $paths = [
        APP_PATH . '/Controllers/' . $className . '.php',
        APP_PATH . '/Models/' . $className . '.php',
        APP_PATH . '/Core/' . $className . '.php',
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

// Para MAMP, remover /alex da URI se presente
if (strpos($uri, '/alex') === 0) {
    $uri = substr($uri, strlen('/alex'));
}

// Remover trailing slash
$uri = rtrim($uri, '/');
if (empty($uri)) {
    $uri = '/';
}

// Rotas básicas
$routes = [
    'GET' => [
        '/' => ['AuthController', 'showLogin'],
        '/login' => ['AuthController', 'showLogin'],
        '/dashboard' => ['HomeController', 'dashboard'],
        '/products' => ['ProductController', 'index'],
        '/products/create' => ['ProductController', 'create'],
        '/products/edit' => ['ProductController', 'edit'],
        '/api/products/getPorts' => ['ProductController', 'getPorts'],
        '/api/products/getPortConfigs' => ['ProductController', 'getPortConfigs'],
        '/api/products/search' => ['ProductController', 'search'],
        '/api/process-items/total-weight' => ['ProcessItemController', 'getTotalWeight'],
        '/clients' => ['ClientController', 'index'],
        '/clients/create' => ['ClientController', 'create'],
        '/clients/edit' => ['ClientController', 'edit'],
        '/processes' => ['ProcessController', 'index'],
        '/processes/create' => ['ProcessController', 'create'],
        '/processes/edit' => ['ProcessController', 'edit'],
        '/process-items' => ['ProcessItemController', 'index'],
        '/process-items/create' => ['ProcessItemController', 'create'],
        '/process-items/edit' => ['ProcessItemController', 'edit'],
        '/reports' => ['ReportController', 'index'],
        '/users' => ['UserController', 'index'],
        '/users/create' => ['UserController', 'create'],
        '/users/edit' => ['UserController', 'edit'],
        '/ports' => ['PortController', 'index'],
        '/ports/create' => ['PortController', 'create'],
        '/ports/edit' => ['PortController', 'edit'],
        '/exchange-rates' => ['ExchangeRateController', 'index'],
        '/audit' => ['AuditController', 'index'],
        '/audit/show' => ['AuditController', 'show'],
        '/audit/history' => ['AuditController', 'history'],
        '/audit/cleanup' => ['AuditController', 'cleanup'],
        '/audit/export' => ['AuditController', 'export'],
        '/permissions/roles' => ['PermissionController', 'roles'],
        '/permissions/user' => ['PermissionController', 'userPermissions'],
        '/profile' => ['ProfileController', 'index'],
        '/logout' => ['AuthController', 'logout'],
        '/api/ports/configs' => ['PortController', 'getConfigs'],
        '/api/exchange-rates/get-current' => ['ExchangeRateController', 'getCurrent'],
    ],
    'POST' => [
        '/auth/login' => ['AuthController', 'login'],
        '/login' => ['AuthController', 'login'],
        '/api/products/create' => ['ProductController', 'store'],
        '/api/products/update' => ['ProductController', 'update'],
        '/api/products/delete' => ['ProductController', 'delete'],
        '/api/products/getPorts' => ['ProductController', 'getPorts'],
        '/api/products/getPortConfigs' => ['ProductController', 'getPortConfigs'],
        '/api/products/search' => ['ProductController', 'search'],
        '/api/products/savePortConfig' => ['ProductController', 'savePortConfig'],
        '/api/products/deletePortConfig' => ['ProductController', 'deletePortConfig'],
        '/api/clients/create' => ['ClientController', 'store'],
        '/api/clients/update' => ['ClientController', 'update'],
        '/api/clients/delete' => ['ClientController', 'delete'],
        '/api/processes/create' => ['ProcessController', 'store'],
        '/api/processes/update' => ['ProcessController', 'update'],
        '/api/processes/delete' => ['ProcessController', 'delete'],
        '/api/process-items/create' => ['ProcessItemController', 'store'],
        '/api/process-items/update' => ['ProcessItemController', 'update'],
        '/api/process-items/delete' => ['ProcessItemController', 'delete'],
        '/api/users/create' => ['UserController', 'store'],
        '/api/users/update' => ['UserController', 'update'],
        '/api/users/delete' => ['UserController', 'delete'],
        '/api/ports/create' => ['PortController', 'store'],
        '/api/ports/update' => ['PortController', 'update'],
        '/api/ports/delete' => ['PortController', 'delete'],
        '/api/ncm/search' => ['NCMController', 'search'],
        '/api/ncm/details' => ['NCMController', 'details'],
        '/api/ncm/stats' => ['NCMController', 'stats'],
        '/api/exchange-rates/update' => ['ExchangeRateController', 'update'],
        '/audit/cleanup' => ['AuditController', 'cleanup'],
        '/profile/update' => ['ProfileController', 'update'],
        '/api/permissions/role' => ['PermissionController', 'updateRole'],
        '/api/permissions/user' => ['PermissionController', 'updateUserPermissions'],
    ]
];

try {
    $routeFound = false;
    $controller = null;
    $actionName = null;
    $params = [];

    // Verificar rotas exatas primeiro
    if (isset($routes[$method][$uri])) {
        $route = $routes[$method][$uri];
        $controllerName = $route[0];
        $actionName = $route[1];
        $routeFound = true;
    } else {
        // Verificar rotas com parâmetros
        $uriParts = explode('/', trim($uri, '/'));

        // Tratar rota /audit/show/{id}
        if (count($uriParts) >= 3 && $uriParts[0] === 'audit' && $uriParts[1] === 'show' && is_numeric($uriParts[2])) {
            $controllerName = 'AuditController';
            $actionName = 'show';
            $params[] = (int)$uriParts[2];
            $routeFound = true;
        }
    }

    if ($routeFound) {
        if (class_exists($controllerName)) {
            $controller = new $controllerName();
            if (method_exists($controller, $actionName)) {
                // Chamar método com parâmetros se existirem
                if (!empty($params)) {
                    $controller->$actionName(...$params);
                } else {
                    $controller->$actionName();
                }
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

    // Em desenvolvimento, mostrar erro
    echo "<h1>Erro na aplicação</h1>";
    echo "<p><strong>Erro:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}