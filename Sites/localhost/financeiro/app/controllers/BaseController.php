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

        // Verificar se é uma chamada de API
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $isApiCall = strpos($uri, '/api/') !== false;

        if ($isApiCall) {
            // Para APIs, retornar JSON
            $errorData = [
                'success' => false,
                'message' => $message
            ];

            if (ini_get('display_errors')) {
                $errorData['debug'] = [
                    'error' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine()
                ];
            }

            return $this->jsonResponse($errorData);
        } else {
            // Para páginas web, retornar HTML
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
    
    protected function requireAuth() {
        session_start();
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            $this->redirect('/login');
        }
    }
    
    protected function requireSuperAdmin() {
        $this->requireAuth();
        if (!isset($_SESSION['is_super_admin']) || !$_SESSION['is_super_admin']) {
            $this->redirect('/dashboard');
        }
    }
    
    protected function hasPermission($module, $permission) {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Super admin tem todas as permissões
        if (isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin']) {
            return true;
        }
        
        $orgId = $_SESSION['current_org_id'] ?? null;
        if (!$orgId) {
            return false;
        }
        
        $userModel = new User($this->db);
        return $userModel->hasPermission($_SESSION['user_id'], $orgId, $module, $permission);
    }
    
    protected function getCurrentOrgId() {
        session_start();
        
        if (isset($_SESSION['current_org_id'])) {
            return $_SESSION['current_org_id'];
        }
        
        $userId = $_SESSION['user_id'] ?? null;
        $userEmail = $_SESSION['user_email'] ?? null;
        
        if (!$userId || !$userEmail) {
            return 1; // Fallback se não houver dados na sessão
        }
        
        try {
            // Buscar organização baseada no email do usuário
            $stmt = $this->db->prepare("SELECT id FROM organizations WHERE email = ? LIMIT 1");
            $stmt->execute([$userEmail]);
            $org = $stmt->fetch();
            
            if ($org) {
                $_SESSION['current_org_id'] = $org['id'];
                return $org['id'];
            }
            
            // Se não encontrar por email, buscar a primeira organização do usuário
            // Assumindo que há uma tabela de relacionamento ou usar org_id de alguma tabela
            $stmt = $this->db->prepare("
                SELECT DISTINCT org_id 
                FROM accounts 
                WHERE created_by = ? OR user_id = ? 
                LIMIT 1
            ");
            $stmt->execute([$userId, $userId]);
            $userOrg = $stmt->fetch();
            
            if ($userOrg) {
                $_SESSION['current_org_id'] = $userOrg['org_id'];
                return $userOrg['org_id'];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao buscar org_id: " . $e->getMessage());
        }
        
        return 1; // Fallback final
    }
    
    protected function isSuperAdmin() {
        session_start();
        return isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'];
    }
}