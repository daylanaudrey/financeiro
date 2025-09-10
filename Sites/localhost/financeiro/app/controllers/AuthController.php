<?php
require_once 'BaseController.php';

class AuthController extends BaseController {
    private $userModel;
    private $auditModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->auditModel = new AuditLog();
    }
    
    public function showLogin() {
        // Se já estiver logado, redirecionar para dashboard
        if ($this->isLoggedIn()) {
            $this->redirect(url('/'));
        }
        
        $data = [
            'title' => 'Login - Sistema Financeiro',
            'page' => 'login'
        ];
        
        $this->render('auth/login', $data);
    }
    
    public function login() {
        try {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);
            
            if (empty($email) || empty($password)) {
                $this->json([
                    'success' => false,
                    'message' => 'Email e senha são obrigatórios'
                ]);
            }
            
            $user = $this->userModel->findByEmail($email);
            
            if (!$user) {
                $this->auditModel->log([
                    'entity' => 'auth',
                    'action' => 'login',
                    'description' => "Tentativa de login com email inexistente: {$email}"
                ]);
                
                $this->json([
                    'success' => false,
                    'message' => 'Credenciais inválidas'
                ]);
            }
            
            if (!$this->userModel->verifyPassword($password, $user['password'])) {
                $this->auditModel->log([
                    'user_id' => $user['id'],
                    'entity' => 'auth',
                    'action' => 'login',
                    'description' => 'Tentativa de login com senha incorreta'
                ]);
                
                $this->json([
                    'success' => false,
                    'message' => 'Credenciais inválidas'
                ]);
            }
            
            if ($user['status'] !== 'ativo') {
                $this->auditModel->log([
                    'user_id' => $user['id'],
                    'entity' => 'auth',
                    'action' => 'login',
                    'description' => 'Tentativa de login com usuário inativo'
                ]);
                
                $this->json([
                    'success' => false,
                    'message' => 'Usuário inativo'
                ]);
            }
            
            // Login bem-sucedido
            $this->startUserSession($user, $remember);
            $this->userModel->updateLastLogin($user['id']);
            
            $userWithOrgs = $this->userModel->getUserWithOrganizations($user['id']);
            $orgId = !empty($userWithOrgs['organizations']) ? $userWithOrgs['organizations'][0]['id'] : null;
            
            $this->auditModel->logUserAction(
                $user['id'], 
                $orgId,
                'auth', 
                'login', 
                null, 
                null, 
                null, 
                'Login realizado com sucesso'
            );
            
            $this->json([
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'redirect' => url('/')
            ]);
            
        } catch (Exception $e) {
            error_log("Erro no login: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Erro interno. Tente novamente.'
            ]);
        }
    }
    
    public function logout() {
        $user = $this->getCurrentUser();
        
        if ($user) {
            $userWithOrgs = $this->userModel->getUserWithOrganizations($user['id']);
            $orgId = !empty($userWithOrgs['organizations']) ? $userWithOrgs['organizations'][0]['id'] : null;
            
            $this->auditModel->logUserAction(
                $user['id'], 
                $orgId,
                'auth', 
                'logout', 
                null, 
                null, 
                null, 
                'Logout realizado'
            );
        }
        
        $this->destroySession();
        $this->redirect(url('/login'));
    }
    
    public function showRegister() {
        $data = [
            'title' => 'Cadastro - Sistema Financeiro',
            'page' => 'register'
        ];
        
        $this->render('auth/register', $data);
    }
    
    public function register() {
        try {
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validações
            if (empty($nome) || empty($email) || empty($password)) {
                $this->json([
                    'success' => false,
                    'message' => 'Todos os campos são obrigatórios'
                ]);
            }
            
            if (strlen($password) < 6) {
                $this->json([
                    'success' => false,
                    'message' => 'A senha deve ter pelo menos 6 caracteres'
                ]);
            }
            
            if ($password !== $confirmPassword) {
                $this->json([
                    'success' => false,
                    'message' => 'As senhas não coincidem'
                ]);
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->json([
                    'success' => false,
                    'message' => 'Email inválido'
                ]);
            }
            
            // Verificar se email já existe
            $existingUser = $this->userModel->findByEmail($email);
            if ($existingUser) {
                $this->json([
                    'success' => false,
                    'message' => 'Este email já está cadastrado'
                ]);
            }
            
            // Criar usuário
            $userData = [
                'nome' => $nome,
                'email' => $email,
                'password' => $password,
                'role' => 'operador',
                'status' => 'ativo',
                'email_verified_at' => date('Y-m-d H:i:s')
            ];
            
            $userId = $this->userModel->createUser($userData);
            
            if ($userId) {
                $this->auditModel->log([
                    'user_id' => $userId,
                    'entity' => 'user',
                    'entity_id' => $userId,
                    'action' => 'create',
                    'description' => "Novo usuário cadastrado: {$email}"
                ]);
                
                $this->json([
                    'success' => true,
                    'message' => 'Cadastro realizado com sucesso! Você já pode fazer login.',
                    'redirect' => url('/login')
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => 'Erro ao criar usuário. Tente novamente.'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Erro no cadastro: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Erro interno. Tente novamente.'
            ]);
        }
    }
    
    private function startUserSession($user, $remember = false) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nome'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 dias
            // Salvar token no banco se necessário
        }
    }
    
    private function destroySession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        
        // Remover cookies
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
    }
    
    private function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            try {
                session_start();
            } catch (Exception $e) {
                return false;
            }
        }
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    private function getCurrentUser() {
        if (session_status() === PHP_SESSION_NONE) {
            try {
                session_start();
            } catch (Exception $e) {
                return null;
            }
        }
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'nome' => $_SESSION['user_name'],
                'email' => $_SESSION['user_email'],
                'role' => $_SESSION['user_role']
            ];
        }
        return null;
    }
}