<?php

namespace App\Core;

/**
 * Classe Router
 * Gerencia rotas e requisições
 */
class Router
{
    private array $routes = [];
    private string $controllerNamespace = 'App\\Controllers\\';

    /**
     * Adicionar rota GET
     */
    public function get(string $path, $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    /**
     * Adicionar rota POST
     */
    public function post(string $path, $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    /**
     * Processar requisição
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $url = $this->getUrl();

        // Debug
        if (ENVIRONMENT === 'development') {
            error_log("Router Debug - Method: $method, URL: $url");
            error_log("Available routes: " . print_r($this->routes, true));
        }

        // Procurar rota exata
        if (isset($this->routes[$method][$url])) {
            $this->callHandler($this->routes[$method][$url]);
            return;
        }

        // Procurar rota com parâmetros
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = $this->convertRouteToRegex($route);
            if (preg_match($pattern, $url, $matches)) {
                array_shift($matches); // Remove o match completo
                $this->callHandler($handler, $matches);
                return;
            }
        }

        // Rota não encontrada
        $this->notFound();
    }

    /**
     * Obter URL da requisição
     */
    private function getUrl(): string
    {
        $url = $_GET['url'] ?? '';
        $url = rtrim($url, '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);
        return $url ?: '/';
    }

    /**
     * Converter rota para regex
     */
    private function convertRouteToRegex(string $route): string
    {
        $route = preg_replace('/\//', '\/', $route);
        $route = preg_replace('/\{([a-z]+)\}/', '([^\/]+)', $route);
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '($2)', $route);
        return '/^' . $route . '$/i';
    }

    /**
     * Chamar handler da rota
     */
    private function callHandler($handler, array $params = []): void
    {
        // Se for uma closure
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        // Se for string no formato Controller@method
        if (is_string($handler) && strpos($handler, '@') !== false) {
            [$controllerName, $method] = explode('@', $handler);

            $controllerClass = $this->controllerNamespace . $controllerName;

            if (!class_exists($controllerClass)) {
                $this->notFound();
                return;
            }

            $controller = new $controllerClass();

            if (!method_exists($controller, $method)) {
                $this->notFound();
                return;
            }

            call_user_func_array([$controller, $method], $params);
            return;
        }

        // Handler inválido
        $this->notFound();
    }

    /**
     * Página não encontrada
     */
    private function notFound(): void
    {
        http_response_code(404);
        if (file_exists(APP_PATH . 'Views/errors/404.php')) {
            require APP_PATH . 'Views/errors/404.php';
        } else {
            echo "404 - Página não encontrada";
        }
        exit;
    }
}