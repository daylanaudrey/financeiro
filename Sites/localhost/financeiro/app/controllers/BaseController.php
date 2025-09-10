<?php
class BaseController {
    protected $db;
    
    public function __construct() {
        require_once __DIR__ . '/../../config/database.php';
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    protected function render($view, $data = []) {
        extract($data);
        $viewPath = __DIR__ . "/../views/{$view}.php";
        
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            throw new Exception("View {$view} não encontrada");
        }
    }
    
    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }
    
    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function handleError($exception, $message = 'Erro interno') {
        error_log($exception->getMessage());
        
        if (ini_get('display_errors')) {
            echo "<h1>Erro na aplicação</h1>";
            echo "<p><strong>Erro:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
            echo "<p><strong>Arquivo:</strong> " . $exception->getFile() . "</p>";
            echo "<p><strong>Linha:</strong> " . $exception->getLine() . "</p>";
        } else {
            echo "<h1>{$message}</h1>";
            echo "<p>Tente novamente em alguns instantes.</p>";
        }
    }
}