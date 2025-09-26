<?php
require_once 'BaseController.php';
require_once __DIR__ . '/../services/WhatsAppService.php';
require_once __DIR__ . '/../models/IntegrationConfig.php';

class IntegrationController extends BaseController {
    private $whatsAppService;
    private $integrationConfigModel;

    public function __construct() {
        parent::__construct();
        $this->whatsAppService = new WhatsAppService();
        $this->integrationConfigModel = new IntegrationConfig($this->db);
    }

    /**
     * Verifica se o usuário é superadmin
     */
    protected function requireSuperAdmin() {
        $user = AuthMiddleware::requireAuth();

        if (!isset($_SESSION['is_super_admin']) || $_SESSION['is_super_admin'] !== true) {
            if ($this->isAjax()) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Acesso negado. Apenas superadmin pode acessar esta funcionalidade.'
                ]);
                exit;
            } else {
                // Se está logado mas não é superadmin, volta para admin
                // Se não está logado, AuthMiddleware já redirecionou para login
                header('Location: ' . url('/admin'));
                exit;
            }
        }

        return $user;
    }

    private function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     * Página de configurações das integrações
     */
    public function index() {
        $user = $this->requireSuperAdmin();

        $this->render('admin/integrations', [
            'title' => 'Integrações - Painel Administrativo',
            'page' => 'integrations',
            'user' => $user
        ]);
    }

    /**
     * Testa conexão WhatsApp
     */
    public function testWhatsApp() {
        $this->requireSuperAdmin();

        try {
            $result = $this->whatsAppService->testConnection();
            return $this->jsonResponse($result);
        } catch (Exception $e) {
            return $this->handleError($e, 'Erro ao testar WhatsApp');
        }
    }

    /**
     * Envia mensagem de teste WhatsApp
     */
    public function sendTestWhatsApp() {
        $this->requireSuperAdmin();

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['phone']) || !isset($data['message'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Telefone e mensagem são obrigatórios'
                ]);
            }

            $result = $this->whatsAppService->sendMessage($data['phone'], $data['message']);

            return $this->jsonResponse([
                'success' => $result,
                'message' => $result ? 'Mensagem enviada com sucesso' : 'Erro ao enviar mensagem'
            ]);

        } catch (Exception $e) {
            return $this->handleError($e, 'Erro ao enviar mensagem de teste');
        }
    }

    /**
     * Configurações das integrações
     */
    public function getConfig() {
        $this->requireSuperAdmin();

        try {
            $orgId = 1; // Por enquanto sempre organização 1

            // Buscar configurações do banco de dados
            $whatsappConfigs = $this->integrationConfigModel->getConfigsByType($orgId, 'whatsapp');
            $n8nConfigs = $this->integrationConfigModel->getConfigsByType($orgId, 'n8n');

            // Por segurança, não retornar tokens reais completos
            $config = [
                'whatsapp' => [
                    'configured' => !empty($whatsappConfigs['token']) && !empty($whatsappConfigs['instance_id']),
                    'token_configured' => !empty($whatsappConfigs['token']),
                    'instance_configured' => !empty($whatsappConfigs['instance_id']),
                    'token' => !empty($whatsappConfigs['token']) ? substr($whatsappConfigs['token'], 0, 10) . '...' : '',
                    'instance_id' => $whatsappConfigs['instance_id'] ?? ''
                ],
                'n8n' => [
                    'webhook_url' => $n8nConfigs['webhook_url'] ?? url('/webhook/n8n/transaction'),
                    'test_url' => url('/webhook/test')
                ]
            ];

            return $this->jsonResponse([
                'success' => true,
                'config' => $config
            ]);

        } catch (Exception $e) {
            return $this->handleError($e, 'Erro ao buscar configurações');
        }
    }

    /**
     * Salva configurações das integrações
     */
    public function saveConfig() {
        $this->requireSuperAdmin();

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // TODO: Implementar salvamento seguro das configurações
            // Por enquanto, as configurações devem ser definidas via variáveis de ambiente

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Para segurança, configure as variáveis de ambiente WAPI_TOKEN e WAPI_INSTANCE_ID'
            ]);

        } catch (Exception $e) {
            return $this->handleError($e, 'Erro ao salvar configurações');
        }
    }

    /**
     * Salva configurações do WhatsApp
     */
    public function saveWhatsAppConfig() {
        $this->requireSuperAdmin();

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['token']) || !isset($data['instance_id'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Token e Instance ID são obrigatórios'
                ]);
            }

            $token = trim($data['token']);
            $instanceId = trim($data['instance_id']);

            if (empty($token) || empty($instanceId)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Token e Instance ID não podem estar vazios'
                ]);
            }

            $orgId = 1; // Por enquanto sempre organização 1

            // Salvar no banco de dados
            $configs = [
                'token' => $token,
                'instance_id' => $instanceId
            ];

            // Token deve ser criptografado para segurança
            $encryptedKeys = ['token'];

            $this->integrationConfigModel->saveConfigs($orgId, 'whatsapp', $configs, $encryptedKeys);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Configurações salvas com sucesso'
            ]);

        } catch (Exception $e) {
            return $this->handleError($e, 'Erro ao salvar configurações WhatsApp');
        }
    }

    /**
     * Busca configurações completas do WhatsApp (para edição)
     */
    public function getWhatsAppConfigForEdit() {
        $this->requireSuperAdmin();

        try {
            $orgId = 1; // Por enquanto sempre organização 1

            // Buscar configurações do banco de dados
            $whatsappConfigs = $this->integrationConfigModel->getConfigsByType($orgId, 'whatsapp');

            return $this->jsonResponse([
                'success' => true,
                'config' => [
                    'token' => $whatsappConfigs['token'] ?? '',
                    'instance_id' => $whatsappConfigs['instance_id'] ?? ''
                ]
            ]);

        } catch (Exception $e) {
            return $this->handleError($e, 'Erro ao buscar configurações para edição');
        }
    }

    /**
     * Salva configurações do MailerSend
     */
    public function saveEmailConfig() {
        $this->requireSuperAdmin();

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['api_key']) || !isset($data['from_email']) || !isset($data['from_name'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'API Key, Email Remetente e Nome Remetente são obrigatórios'
                ]);
            }

            $apiKey = trim($data['api_key']);
            $fromEmail = trim($data['from_email']);
            $fromName = trim($data['from_name']);

            if (empty($apiKey) || empty($fromEmail) || empty($fromName)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Todos os campos devem estar preenchidos'
                ]);
            }

            // Validar email
            if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Email remetente inválido'
                ]);
            }

            // Salvar configurações na tabela system_configs
            $this->updateSystemConfig('mailersend_api_key', $apiKey);
            $this->updateSystemConfig('mailersend_from_email', $fromEmail);
            $this->updateSystemConfig('mailersend_from_name', $fromName);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Configurações de email salvas com sucesso'
            ]);

        } catch (Exception $e) {
            return $this->handleError($e, 'Erro ao salvar configurações de email');
        }
    }

    /**
     * Busca configurações do MailerSend para edição
     */
    public function getEmailConfigForEdit() {
        $this->requireSuperAdmin();

        try {
            $apiKey = $this->getSystemConfig('mailersend_api_key');
            $fromEmail = $this->getSystemConfig('mailersend_from_email');
            $fromName = $this->getSystemConfig('mailersend_from_name');

            return $this->jsonResponse([
                'success' => true,
                'config' => [
                    'api_key' => $apiKey ?: '',
                    'from_email' => $fromEmail ?: 'noreply@dagsolucaodigital.com.br',
                    'from_name' => $fromName ?: 'DAG Sistema Financeiro'
                ]
            ]);

        } catch (Exception $e) {
            return $this->handleError($e, 'Erro ao buscar configurações de email');
        }
    }

    /**
     * Método helper para atualizar configurações do sistema
     */
    private function updateSystemConfig($key, $value) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO system_configs (key_name, key_value, created_at, updated_at)
                VALUES (?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                key_value = VALUES(key_value),
                updated_at = NOW()
            ");
            return $stmt->execute([$key, $value]);
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar configuração: " . $e->getMessage());
        }
    }

    /**
     * Método helper para buscar configurações do sistema
     */
    private function getSystemConfig($key) {
        try {
            $stmt = $this->db->prepare("SELECT key_value FROM system_configs WHERE key_name = ?");
            $stmt->execute([$key]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['key_value'] : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Envia notificação de transação via WhatsApp
     */
    public function sendTransactionNotification() {
        $this->requireSuperAdmin();
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['phone']) || !isset($data['transaction'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Telefone e dados da transação são obrigatórios'
                ]);
            }

            $result = $this->whatsAppService->sendTransactionNotification(
                $data['phone'],
                $data['transaction']
            );

            return $this->jsonResponse([
                'success' => $result,
                'message' => $result ? 'Notificação enviada com sucesso' : 'Erro ao enviar notificação'
            ]);

        } catch (Exception $e) {
            return $this->handleError($e, 'Erro ao enviar notificação');
        }
    }

    /**
     * Webhook de exemplo para testar N8N
     */
    public function testN8NWebhook() {
        $this->requireSuperAdmin();

        try {
            // Dados de exemplo para testar o webhook N8N
            $testData = [
                'valor' => '150,50',
                'descricao' => 'Teste N8N - Compra supermercado',
                'tipo' => 'saida',
                'categoria' => 'Alimentação',
                'conta' => 'Cartão Crédito',
                'data' => date('Y-m-d'),
                'observacoes' => 'Teste automatizado N8N'
            ];

            // Simular requisição para o webhook
            $webhookUrl = url('/webhook/n8n/transaction');

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $webhookUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($testData),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json'
                ],
                CURLOPT_TIMEOUT => 30
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $result = json_decode($response, true);

            return $this->jsonResponse([
                'success' => $httpCode === 200 && $result['success'],
                'message' => 'Teste N8N executado',
                'http_code' => $httpCode,
                'response' => $result,
                'test_data' => $testData
            ]);

        } catch (Exception $e) {
            return $this->handleError($e, 'Erro no teste N8N');
        }
    }
}
?>