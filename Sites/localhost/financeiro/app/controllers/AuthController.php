<?php
require_once 'BaseController.php';
require_once __DIR__ . '/../services/EmailService.php';

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
        
        // Verificar se veio do mobile e armazenar na sessão
        if (isset($_GET['mobile']) && $_GET['mobile'] == '1') {
            $_SESSION['return_to_mobile'] = 'true';
        }
        
        $data = [
            'title' => 'Login - Sistema Financeiro',
            'page' => 'login',
            'is_mobile' => isset($_GET['mobile'])
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
            
            // Usar novo método de autenticação
            $user = $this->userModel->authenticate($email, $password);
            
            if (!$user) {
                $this->auditModel->log([
                    'entity' => 'auth',
                    'action' => 'login',
                    'description' => "Tentativa de login inválida: {$email}"
                ]);
                
                $this->json([
                    'success' => false,
                    'message' => 'Credenciais inválidas'
                ]);
            }
            
            // Verificar se o email foi verificado (exceto para super admin)
            if (!$this->userModel->isSuperAdmin($user['id']) && !$user['email_verified_at']) {
                $this->json([
                    'success' => false,
                    'message' => 'Você precisa verificar seu email antes de fazer login. Verifique sua caixa de entrada.',
                    'show_resend' => true
                ]);
            }
            
            // Login bem-sucedido - salvar dados na sessão
            $this->startUserSession($user, $remember);
            $this->userModel->updateLastLogin($user['id']);
            
            // Verificar se é mobile/PWA
            $isMobile = isset($_GET['mobile']) && $_GET['mobile'] == '1';
            $isMobileUA = strpos($_SERVER['HTTP_USER_AGENT'] ?? '', 'Mobile') !== false;
            $isPWA = isset($_SERVER['HTTP_SEC_FETCH_MODE']) && $_SERVER['HTTP_SEC_FETCH_MODE'] === 'navigate';
            $returnToMobile = isset($_SESSION['return_to_mobile']) && $_SESSION['return_to_mobile'] == 'true';
            
            // Definir URL de redirecionamento baseada no tipo de usuário
            $redirectUrl = url('/');
            
            // Se for mobile/PWA, redirecionar para mobile - mas apenas se solicitado explicitamente
            if ($isMobile || $returnToMobile) {
                $redirectUrl = url('/mobile');
                // Limpar flag de retorno ao mobile
                unset($_SESSION['return_to_mobile']);
            }
            // Se for super admin, redirecionar para painel administrativo
            elseif ($this->userModel->isSuperAdmin($user['id'])) {
                $redirectUrl = url('/admin');
            }
            
            // Verificar se há URL de redirecionamento salva (tem prioridade)
            if (isset($_SESSION['redirect_after_login'])) {
                $redirectUrl = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
            }
            
            $this->json([
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'redirect' => $redirectUrl
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
        try {
            $user = $this->getCurrentUser();
            
            if ($user) {
                // Verificar se o usuário ainda existe no banco antes de fazer log
                $existingUser = $this->userModel->findByEmail($user['email']);
                if ($existingUser) {
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
            }
        } catch (Exception $e) {
            // Se houver erro no log, apenas continue com o logout
            error_log("Erro no logout: " . $e->getMessage());
        }
        
        $this->destroySession();
        
        // Verificar se veio do mobile ou PWA
        $isMobile = isset($_GET['mobile']) && $_GET['mobile'] == '1';
        
        if ($isMobile) {
            $this->redirect(url('/login?mobile=1'));
        } else {
            $this->redirect(url('/login'));
        }
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
            $organizationName = trim($_POST['organization_name'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validações
            if (empty($nome) || empty($email) || empty($organizationName) || empty($password)) {
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
            
            // Gerar token de verificação
            $verificationToken = bin2hex(random_bytes(32));
            
            // Criar usuário (sem verificação inicial)
            $userData = [
                'nome' => $nome,
                'email' => $email,
                'password' => $password,
                'role' => 'operador',
                'status' => 'pendente', // Status pendente até verificar email
                'email_verification_token' => $verificationToken,
                'email_verification_sent_at' => date('Y-m-d H:i:s'),
                'organization_name' => $organizationName
            ];
            
            $userId = $this->userModel->createUser($userData);
            
            if ($userId) {
                // Tentar enviar email de verificação (não falha se der erro)
                $emailSent = $this->sendVerificationEmail($email, $nome, $verificationToken);
                
                $this->auditModel->log([
                    'user_id' => $userId,
                    'entity' => 'user',
                    'entity_id' => $userId,
                    'action' => 'create',
                    'description' => "Novo usuário cadastrado (pendente verificação): {$email}"
                ]);
                
                $message = 'Cadastro realizado com sucesso!';
                if ($emailSent) {
                    $message .= ' Verifique seu email para ativar sua conta.';
                } else {
                    $message .= ' Houve um problema ao enviar o email de verificação, mas sua conta foi criada. Entre em contato conosco se precisar de ajuda.';
                }
                
                $this->json([
                    'success' => true,
                    'message' => $message,
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
        $_SESSION['user_tipo'] = $user['role']; // Adicionar user_tipo para compatibilidade
        $_SESSION['org_roles'] = $user['org_roles'] ?? [];
        $_SESSION['is_super_admin'] = $this->userModel->isSuperAdmin($user['id']);
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        // Definir organização atual (primeira disponível ou null para super admin)
        if (!empty($user['org_roles'])) {
            $firstOrgId = array_key_first($user['org_roles']);
            $_SESSION['current_org_id'] = $firstOrgId;
        } else {
            $_SESSION['current_org_id'] = null;
        }
        
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 dias
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
    
    private function sendVerificationEmail($email, $nome, $token) {
        try {
            $emailService = new EmailService();
            $verificationUrl = absoluteUrl("verify-email?token=$token");
            
            $subject = 'Verificação de Email - Sistema Financeiro';
            $content = "
                <h2>Bem-vindo, $nome!</h2>
                <p>Obrigado por se cadastrar no Sistema Financeiro.</p>
                <p>Para ativar sua conta, clique no link abaixo:</p>
                <p style='margin: 20px 0;'>
                    <a href='$verificationUrl' style='background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                        Verificar Email
                    </a>
                </p>
                <p>Se você não conseguir clicar no botão, copie e cole este link no seu navegador:</p>
                <p><a href='$verificationUrl'>$verificationUrl</a></p>
                <p><small>Este link é válido por 24 horas.</small></p>
            ";
            
            return $emailService->sendEmail($email, $subject, $content);
        } catch (Exception $e) {
            error_log("Erro ao enviar email de verificação: " . $e->getMessage());
            return false;
        }
    }
    
    public function verifyEmail() {
        try {
            $token = $_GET['token'] ?? '';
            
            if (empty($token)) {
                $this->render('auth/verification_error', [
                    'title' => 'Token Inválido',
                    'message' => 'Token de verificação não fornecido.'
                ]);
                return;
            }
            
            // Buscar usuário pelo token
            $user = $this->userModel->findByVerificationToken($token);
            
            if (!$user) {
                $this->render('auth/verification_error', [
                    'title' => 'Token Inválido',
                    'message' => 'Token de verificação inválido ou expirado.'
                ]);
                return;
            }
            
            // Verificar se o token não expirou (24 horas)
            $sentAt = new DateTime($user['email_verification_sent_at']);
            $now = new DateTime();
            $hoursDiff = $now->diff($sentAt)->h + ($now->diff($sentAt)->days * 24);
            
            if ($hoursDiff > 24) {
                $this->render('auth/verification_error', [
                    'title' => 'Token Expirado',
                    'message' => 'Token de verificação expirado. Solicite um novo email de verificação.'
                ]);
                return;
            }
            
            // Verificar email
            $this->userModel->verifyUserEmail($user['id']);
            
            // Criar organização pessoal para o usuário
            $this->createPersonalOrganization($user);
            
            $this->auditModel->log([
                'user_id' => $user['id'],
                'entity' => 'user',
                'entity_id' => $user['id'],
                'action' => 'verify',
                'description' => "Email verificado e organização pessoal criada: {$user['email']}"
            ]);
            
            $this->render('auth/verification_success', [
                'title' => 'Email Verificado',
                'message' => 'Sua conta foi ativada com sucesso! Você já pode fazer login.',
                'user_name' => $user['nome']
            ]);
            
        } catch (Exception $e) {
            error_log("Erro na verificação de email: " . $e->getMessage());
            $this->render('auth/verification_error', [
                'title' => 'Erro na Verificação',
                'message' => 'Ocorreu um erro durante a verificação. Tente novamente.'
            ]);
        }
    }
    
    private function createPersonalOrganization($user) {
        try {
            $orgModel = new Organization();
            
            // Criar organização pessoal
            $orgData = [
                'nome' => $user['temp_org_name'] ?? "Sistema de " . $user['nome'],
                'pessoa_tipo' => 'PF',
                'email' => $user['email'],
                'status' => 'ativa'
            ];
            
            $orgId = $orgModel->createWithOwner($orgData, $user['id']);
            
            if ($orgId) {
                // Criar assinatura trial
                $db = $orgModel->db ?? (new Database())->getConnection();
                $stmt = $db->prepare("
                    INSERT INTO organization_subscriptions 
                    (org_id, plan_id, status, trial_ends_at, current_period_start, current_period_end)
                    VALUES (?, 1, 'trial', DATE_ADD(NOW(), INTERVAL 7 DAY), NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH))
                ");
                $stmt->execute([$orgId]);
                
                // Limpar temp_org_name
                $db = $orgModel->db ?? (new Database())->getConnection();
                $stmt = $db->prepare("UPDATE users SET temp_org_name = NULL WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                // Criar categorias padrão
                $categoryModel = new Category();
                $categoryModel->createDefaultCategories($orgId, $user['id']);
                
                // Log da criação da organização
                $this->auditModel->log([
                    'user_id' => $user['id'],
                    'entity' => 'organization',
                    'entity_id' => $orgId,
                    'action' => 'create',
                    'description' => "Organização pessoal criada automaticamente: " . $orgData['nome']
                ]);
            }
        } catch (Exception $e) {
            error_log("Erro ao criar organização pessoal: " . $e->getMessage());
            // Não falha o processo de verificação se der erro na criação da org
        }
    }
    
    public function resendVerificationEmail() {
        try {
            $email = $_POST['email'] ?? '';
            
            if (empty($email)) {
                $this->json([
                    'success' => false,
                    'message' => 'Email é obrigatório'
                ]);
            }
            
            // Buscar usuário
            $user = $this->userModel->findByEmail($email);
            if (!$user) {
                $this->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado'
                ]);
            }
            
            // Verificar se já está verificado
            if ($user['email_verified_at']) {
                $this->json([
                    'success' => false,
                    'message' => 'Este email já foi verificado'
                ]);
            }
            
            // Gerar novo token
            $verificationToken = bin2hex(random_bytes(32));
            
            // Atualizar token no banco
            $this->userModel->updateVerificationToken($user['id'], $verificationToken);
            
            // Enviar email
            $emailSent = $this->sendVerificationEmail($email, $user['nome'], $verificationToken);
            
            if ($emailSent) {
                $this->json([
                    'success' => true,
                    'message' => 'Email de verificação reenviado com sucesso!'
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => 'Erro ao enviar email. Tente novamente.'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao reenviar email: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Erro interno. Tente novamente.'
            ]);
        }
    }
}