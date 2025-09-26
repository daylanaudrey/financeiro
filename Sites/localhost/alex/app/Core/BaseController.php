<?php

namespace App\Core;

/**
 * Classe BaseController
 * Controller base para todos os controllers
 */
abstract class BaseController
{
    /**
     * Renderizar view
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data);

        $viewFile = APP_PATH . 'Views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            die("View '{$view}' não encontrada");
        }

        // Iniciar buffer de saída
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Carregar layout se existir
        if (file_exists(APP_PATH . 'Views/layouts/main.php')) {
            require APP_PATH . 'Views/layouts/main.php';
        } else {
            echo $content;
        }
    }

    /**
     * Responder com JSON
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirecionar
     */
    protected function redirect(string $url): void
    {
        if (!str_starts_with($url, 'http')) {
            $url = BASE_URL . $url;
        }
        header("Location: {$url}");
        exit;
    }

    /**
     * Validar dados de entrada
     */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $fieldRules = explode('|', $rule);
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $fieldRule) {
                $ruleName = $fieldRule;
                $ruleParam = null;

                if (str_contains($fieldRule, ':')) {
                    [$ruleName, $ruleParam] = explode(':', $fieldRule);
                }

                switch ($ruleName) {
                    case 'required':
                        if (empty($value)) {
                            $errors[$field][] = "O campo {$field} é obrigatório";
                        }
                        break;

                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = "O campo {$field} deve ser um email válido";
                        }
                        break;

                    case 'min':
                        if (strlen($value) < (int)$ruleParam) {
                            $errors[$field][] = "O campo {$field} deve ter no mínimo {$ruleParam} caracteres";
                        }
                        break;

                    case 'max':
                        if (strlen($value) > (int)$ruleParam) {
                            $errors[$field][] = "O campo {$field} deve ter no máximo {$ruleParam} caracteres";
                        }
                        break;

                    case 'numeric':
                        if (!is_numeric($value)) {
                            $errors[$field][] = "O campo {$field} deve ser numérico";
                        }
                        break;
                }
            }
        }

        return $errors;
    }

    /**
     * Verificar se é requisição POST
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Obter dados POST
     */
    protected function getPostData(): array
    {
        return $_POST;
    }

    /**
     * Verificar CSRF Token
     */
    protected function checkCsrfToken(): bool
    {
        if (!isset($_POST[CSRF_TOKEN_NAME]) || !isset($_SESSION[CSRF_TOKEN_NAME])) {
            return false;
        }

        return hash_equals($_SESSION[CSRF_TOKEN_NAME], $_POST[CSRF_TOKEN_NAME]);
    }

    /**
     * Gerar CSRF Token
     */
    protected function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION[CSRF_TOKEN_NAME] = $token;
        return $token;
    }
}