<?php
require_once 'BaseController.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Organization.php';
require_once __DIR__ . '/../models/Subscription.php';
require_once __DIR__ . '/../services/EmailService.php';

class AdminController extends BaseController {
    private $userModel;
    private $organizationModel;
    private $subscriptionModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User($this->db);
        $this->organizationModel = new Organization($this->db);
        $this->subscriptionModel = new Subscription($this->db);
        
        // Verificar se usuário está logado e é super admin
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }

        // Verificação adicional: bloquear especificamente admin@sistema.com
        $user = $this->userModel->findById($_SESSION['user_id']);
        if ($user && $user['email'] === 'admin@sistema.com') {
            error_log("Tentativa de acesso negada para usuário de teste: admin@sistema.com");
            $this->redirect('/dashboard');
        }

        if (!$this->userModel->isSuperAdmin($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }
    }
    
    public function index() {
        try {
            $data = [
                'organizations' => $this->userModel->getAllOrganizations(),
                'stats' => $this->getSystemStats()
            ];
            
            $this->render('admin/dashboard', $data);
        } catch (Exception $e) {
            $this->handleError($e, 'Erro ao carregar painel administrativo');
        }
    }
    
    public function organizations() {
        try {
            $organizations = $this->userModel->getAllOrganizations();
            $this->render('admin/organizations', ['organizations' => $organizations]);
        } catch (Exception $e) {
            $this->handleError($e, 'Erro ao carregar organizações');
        }
    }
    
    public function organizationsUsers() {
        try {
            $organizations = $this->userModel->getAllOrganizationsWithUsers();
            $this->render('admin/organizations_users', ['organizations' => $organizations]);
        } catch (Exception $e) {
            $this->handleError($e, 'Erro ao carregar organizações e usuários');
        }
    }
    
    public function subscriptions() {
        try {
            $subscriptions = $this->subscriptionModel->getAllSubscriptions();
            $plans = $this->subscriptionModel->getAllPlans();
            
            $data = [
                'subscriptions' => $subscriptions,
                'plans' => $plans
            ];
            
            $this->render('admin/subscriptions', $data);
        } catch (Exception $e) {
            $this->handleError($e, 'Erro ao carregar assinaturas');
        }
    }
    
    public function updateSubscription() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método não permitido']);
        }
        
        try {
            $orgId = $_POST['org_id'] ?? null;
            $planId = $_POST['plan_id'] ?? null;
            $status = $_POST['status'] ?? null;
            
            if (!$orgId || !$planId || !$status) {
                $this->jsonResponse(['success' => false, 'message' => 'Dados obrigatórios não informados']);
            }
            
            $result = $this->subscriptionModel->updateSubscription($orgId, $planId, $status);
            
            if ($result) {
                $this->userModel->logActivity($_SESSION['user_id'], 'update', "Assinatura atualizada - Org: $orgId", $orgId, 'subscription');
                $this->jsonResponse(['success' => true, 'message' => 'Assinatura atualizada com sucesso']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Erro ao atualizar assinatura']);
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function systemConfig() {
        try {
            $configs = $this->getSystemConfigs();
            $this->render('admin/system_config', ['configs' => $configs]);
        } catch (Exception $e) {
            $this->handleError($e, 'Erro ao carregar configurações');
        }
    }
    
    public function updateSystemConfig() {
        // Log simples para ver se chega aqui
        file_put_contents('/tmp/debug_admin.log', "updateSystemConfig chamado\n", FILE_APPEND);
        
        $this->jsonResponse(['success' => true, 'message' => 'Método chamado com sucesso']);
    }
    
    public function auditLogs() {
        try {
            $page = $_GET['page'] ?? 1;
            $limit = 50;
            $offset = ($page - 1) * $limit;
            
            $logs = $this->getAuditLogs($limit, $offset);
            $totalLogs = $this->getTotalAuditLogs();
            $totalPages = ceil($totalLogs / $limit);
            
            $data = [
                'logs' => $logs,
                'currentPage' => $page,
                'totalPages' => $totalPages
            ];
            
            $this->render('admin/audit_logs', $data);
        } catch (Exception $e) {
            $this->handleError($e, 'Erro ao carregar logs de auditoria');
        }
    }
    
    private function getSystemStats() {
        try {
            // Tenta buscar com tabelas de subscription primeiro
            $stmt = $this->db->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM organizations WHERE deleted_at IS NULL) as total_organizations,
                    (SELECT COUNT(*) FROM users WHERE deleted_at IS NULL) as total_users,
                    (SELECT COUNT(*) FROM organization_subscriptions WHERE status = 'active') as active_subscriptions,
                    (SELECT COUNT(*) FROM organization_subscriptions WHERE status = 'trial') as trial_subscriptions,
                    (SELECT COUNT(*) FROM transactions WHERE deleted_at IS NULL AND DATE(created_at) = CURDATE()) as today_transactions,
                    (SELECT COALESCE(SUM(sp.preco), 0) FROM organization_subscriptions os JOIN subscription_plans sp ON os.plan_id = sp.id WHERE os.status = 'active') as monthly_revenue
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Garantir que todos os valores existam e não sejam NULL
            return [
                'total_organizations' => (int)($result['total_organizations'] ?? 0),
                'total_users' => (int)($result['total_users'] ?? 0),
                'active_subscriptions' => (int)($result['active_subscriptions'] ?? 0),
                'trial_subscriptions' => (int)($result['trial_subscriptions'] ?? 0),
                'today_transactions' => (int)($result['today_transactions'] ?? 0),
                'monthly_revenue' => (float)($result['monthly_revenue'] ?? 0)
            ];
        } catch (PDOException $e) {
            error_log("Erro ao buscar estatísticas com subscription: " . $e->getMessage());
            
            // Fallback: buscar estatísticas básicas sem subscription
            try {
                $stmt = $this->db->prepare("
                    SELECT 
                        (SELECT COUNT(*) FROM organizations WHERE deleted_at IS NULL) as total_organizations,
                        (SELECT COUNT(*) FROM users WHERE deleted_at IS NULL) as total_users,
                        (SELECT COUNT(*) FROM transactions WHERE deleted_at IS NULL AND DATE(created_at) = CURDATE()) as today_transactions
                ");
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                return [
                    'total_organizations' => (int)($result['total_organizations'] ?? 0),
                    'total_users' => (int)($result['total_users'] ?? 0),
                    'active_subscriptions' => (int)($result['total_organizations'] ?? 0), // Assume todas ativas
                    'trial_subscriptions' => 0,
                    'today_transactions' => (int)($result['today_transactions'] ?? 0),
                    'monthly_revenue' => 0
                ];
            } catch (PDOException $e2) {
                error_log("Erro ao buscar estatísticas básicas: " . $e2->getMessage());
                return [
                    'total_organizations' => 0,
                    'total_users' => 0,
                    'active_subscriptions' => 0,
                    'trial_subscriptions' => 0,
                    'today_transactions' => 0,
                    'monthly_revenue' => 0
                ];
            }
        }
    }
    
    private function getSystemConfigs() {
        try {
            $stmt = $this->db->prepare("SELECT key_name, key_value FROM system_configs ORDER BY key_name");
            $stmt->execute();
            $configsArray = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Converter array para formato key => value
            $configs = [];
            foreach ($configsArray as $config) {
                $configs[$config['key_name']] = $config['key_value'];
            }
            
            return $configs;
        } catch (PDOException $e) {
            error_log("Erro ao buscar configurações: " . $e->getMessage());
            return [];
        }
    }
    
    private function updateConfig($key, $value) {
        try {
            $stmt = $this->db->prepare("
                UPDATE system_configs 
                SET key_value = ?, updated_at = NOW() 
                WHERE key_name = ?
            ");
            return $stmt->execute([$value, $key]);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar configuração: " . $e->getMessage());
            return false;
        }
    }
    
    private function getAuditLogs($limit, $offset) {
        try {
            $stmt = $this->db->prepare("
                SELECT al.*, u.nome as user_name, u.email, o.nome as org_name
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.id
                LEFT JOIN organizations o ON al.org_id = o.id
                ORDER BY al.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar logs: " . $e->getMessage());
            return [];
        }
    }
    
    private function getTotalAuditLogs() {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM audit_logs");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Erro ao contar logs: " . $e->getMessage());
            return 0;
        }
    }
    
    public function testEmailConfig() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método não permitido']);
        }
        
        try {
            $emailService = new EmailService();
            $result = $emailService->testConnection();
            
            $this->userModel->logActivity($_SESSION['user_id'], 'test', 'Teste de configuração de email', null, 'email_config');
            $this->jsonResponse($result);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function sendTestEmail() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método não permitido']);
        }
        
        try {
            $emailTo = $_POST['email'] ?? null;
            
            if (!$emailTo || !filter_var($emailTo, FILTER_VALIDATE_EMAIL)) {
                $this->jsonResponse(['success' => false, 'message' => 'Email inválido']);
            }
            
            $emailService = new EmailService();
            $subject = 'Teste de Configuração - Sistema Financeiro';
            $content = "
                <h2>Teste de Email</h2>
                <p>Este é um email de teste enviado pelo Sistema Financeiro.</p>
                <p>Se você recebeu este email, significa que a configuração do MailerSend está funcionando corretamente!</p>
                <p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>
                <p><strong>Enviado por:</strong> {$_SESSION['user_name']} ({$_SESSION['user_email']})</p>
            ";
            
            $result = $emailService->sendEmail($emailTo, $subject, $content);
            
            if ($result) {
                $this->userModel->logActivity($_SESSION['user_id'], 'send', "Email teste enviado para: $emailTo", null, 'email_test');
                $this->jsonResponse(['success' => true, 'message' => 'Email de teste enviado com sucesso!']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Falha ao enviar email de teste']);
            }
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
}