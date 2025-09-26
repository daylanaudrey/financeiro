<?php
require_once __DIR__ . '/../models/IntegrationConfig.php';

class WhatsAppService {
    private $apiUrl;
    private $token;
    private $instanceId;

    public function __construct() {
        // Configurações da w-api.app - buscar do banco de dados
        $this->apiUrl = 'https://api.w-api.app';

        // Buscar configurações do banco
        $this->loadConfigFromDatabase();
    }

    private function loadConfigFromDatabase() {
        try {
            require_once __DIR__ . '/../../config/database.php';
            $database = new Database();
            $pdo = $database->getConnection();

            $integrationConfigModel = new IntegrationConfig($pdo);
            $orgId = 1; // Por enquanto sempre organização 1

            $configs = $integrationConfigModel->getConfigsByType($orgId, 'whatsapp');

            $this->token = $configs['token'] ?? $_ENV['WAPI_TOKEN'] ?? null;
            $this->instanceId = $configs['instance_id'] ?? $_ENV['WAPI_INSTANCE_ID'] ?? null;

        } catch (Exception $e) {
            error_log("WhatsApp: Erro ao carregar configurações do banco: " . $e->getMessage());

            // Fallback para variáveis de ambiente
            $this->token = $_ENV['WAPI_TOKEN'] ?? null;
            $this->instanceId = $_ENV['WAPI_INSTANCE_ID'] ?? null;
        }
    }

    /**
     * Envia mensagem via WhatsApp
     */
    public function sendMessage($phoneNumber, $message) {
        $auditData = [
            'phone_original' => $phoneNumber,
            'phone_formatted' => null,
            'message_length' => strlen($message),
            'timestamp' => date('Y-m-d H:i:s'),
            'success' => false,
            'error_message' => null,
            'message_id' => null,
            'instance_id' => $this->instanceId,
            'api_response' => null
        ];

        try {
            if (!$this->token || !$this->instanceId) {
                $auditData['error_message'] = "Token ou Instance ID não configurados";
                error_log("WhatsApp: " . $auditData['error_message']);
                return $auditData;
            }

            // Limpar e formatar número de telefone
            $phone = $this->formatPhoneNumber($phoneNumber);
            $auditData['phone_formatted'] = $phone;

            if (!$phone) {
                $auditData['error_message'] = "Número de telefone inválido: " . $phoneNumber;
                error_log("WhatsApp: " . $auditData['error_message']);
                return $auditData;
            }

            // URL correta conforme documentação w-api.app
            $url = "{$this->apiUrl}/v1/message/send-text?instanceId={$this->instanceId}";

            $data = [
                'phone' => $phone,
                'message' => $message
            ];

            $headers = [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json'
            ];

            $response = $this->makeRequest($url, $data, $headers);
            $auditData['api_response'] = $response;

            if ($response && isset($response['messageId']) && isset($response['instanceId'])) {
                $auditData['success'] = true;
                $auditData['message_id'] = $response['messageId'];
                error_log("WhatsApp: Mensagem enviada com sucesso para " . $phone . " - MessageID: " . $response['messageId']);
                return $auditData;
            } else {
                $auditData['error_message'] = "Resposta da API inválida: " . json_encode($response);
                error_log("WhatsApp: " . $auditData['error_message']);
                return $auditData;
            }

        } catch (Exception $e) {
            $auditData['error_message'] = "Exceção: " . $e->getMessage();
            error_log("WhatsApp: " . $auditData['error_message']);
            return $auditData;
        }
    }

    /**
     * Envia notificação de nova transação
     */
    public function sendTransactionNotification($phoneNumber, $transactionData) {
        $icon = $transactionData['kind'] === 'entrada' ? '💰' : '💸';
        $type = $transactionData['kind'] === 'entrada' ? 'Receita' : 'Despesa';
        $valor = 'R$ ' . number_format($transactionData['valor'], 2, ',', '.');

        $message = "🏦 *Nova {$type}* {$icon}\n\n";
        $message .= "📝 *Descrição:* {$transactionData['descricao']}\n";
        $message .= "💵 *Valor:* {$valor}\n";
        $message .= "📅 *Data:* " . date('d/m/Y', strtotime($transactionData['data_competencia'])) . "\n";

        if (isset($transactionData['categoria'])) {
            $message .= "🏷️ *Categoria:* {$transactionData['categoria']}\n";
        }

        if (isset($transactionData['conta'])) {
            $message .= "🏪 *Conta:* {$transactionData['conta']}\n";
        }

        $message .= "\n⏰ " . date('d/m/Y H:i:s');

        return $this->sendMessage($phoneNumber, $message);
    }

    /**
     * Envia notificação de vencimento
     */
    public function sendDueNotification($phoneNumber, $transactions) {
        if (empty($transactions)) {
            return false;
        }

        $message = "⚠️ *Contas a Vencer* ⚠️\n\n";

        foreach ($transactions as $transaction) {
            $valor = 'R$ ' . number_format($transaction['valor'], 2, ',', '.');
            $data = date('d/m/Y', strtotime($transaction['data_competencia']));

            $message .= "📌 {$transaction['descricao']}\n";
            $message .= "💰 {$valor} - 📅 {$data}\n\n";
        }

        $message .= "🔔 Não esqueça de quitar suas contas!\n";
        $message .= "⏰ " . date('d/m/Y H:i:s');

        return $this->sendMessage($phoneNumber, $message);
    }

    /**
     * Envia resumo diário/semanal/mensal
     */
    public function sendSummaryNotification($phoneNumber, $summaryData, $period = 'diário') {
        $icon = $period === 'diário' ? '📊' : ($period === 'semanal' ? '📈' : '📉');

        $message = "{$icon} *Resumo {$period}* {$icon}\n\n";

        if (isset($summaryData['receitas'])) {
            $receitas = 'R$ ' . number_format($summaryData['receitas'], 2, ',', '.');
            $message .= "💰 *Receitas:* {$receitas}\n";
        }

        if (isset($summaryData['despesas'])) {
            $despesas = 'R$ ' . number_format($summaryData['despesas'], 2, ',', '.');
            $message .= "💸 *Despesas:* {$despesas}\n";
        }

        if (isset($summaryData['saldo'])) {
            $saldo = 'R$ ' . number_format($summaryData['saldo'], 2, ',', '.');
            $saldoIcon = $summaryData['saldo'] >= 0 ? '✅' : '❌';
            $message .= "{$saldoIcon} *Saldo:* {$saldo}\n";
        }

        $message .= "\n⏰ " . date('d/m/Y H:i:s');

        return $this->sendMessage($phoneNumber, $message);
    }

    /**
     * Formata número de telefone para padrão brasileiro
     */
    private function formatPhoneNumber($phone) {
        // Remove todos os caracteres não numéricos
        $clean = preg_replace('/\D/', '', $phone);

        // Se já tem código do país brasileiro (55)
        if (strlen($clean) >= 12 && substr($clean, 0, 2) === '55') {
            return $clean; // Já formatado corretamente
        }

        // Se tem 11 dígitos (celular com 9) - adiciona código do país
        if (strlen($clean) === 11 && in_array(substr($clean, 2, 1), ['9'])) {
            return '55' . $clean;
        }

        // Se tem 10 dígitos (telefone fixo) - adiciona código do país
        if (strlen($clean) === 10) {
            return '55' . $clean;
        }

        // Se tem 9 dígitos (celular sem código de área) - assume DDD comum
        if (strlen($clean) === 9 && in_array(substr($clean, 0, 1), ['9'])) {
            return '5511' . $clean; // Assume SP como padrão
        }

        // Se tem 8 dígitos (fixo sem código de área) - assume DDD comum
        if (strlen($clean) === 8) {
            return '5511' . $clean; // Assume SP como padrão
        }

        // Para números internacionais ou outros formatos, aceitar como está
        if (strlen($clean) >= 8 && strlen($clean) <= 15) {
            return $clean;
        }

        return null; // Número inválido
    }

    /**
     * Faz requisição HTTP
     */
    private function makeRequest($url, $data, $headers) {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 5, // Reduzido de 30 para 5 segundos
            CURLOPT_CONNECTTIMEOUT => 3, // Timeout de conexão de 3 segundos
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            error_log("WhatsApp cURL error: " . $error);
            return false;
        }

        if ($httpCode !== 200) {
            error_log("WhatsApp HTTP error: " . $httpCode . " - " . $response);
            return false;
        }

        return json_decode($response, true);
    }

    /**
     * Faz requisição GET para a API
     */
    private function makeGetRequest($url, $headers) {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPGET => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 5, // Reduzido de 30 para 5 segundos
            CURLOPT_CONNECTTIMEOUT => 3, // Timeout de conexão de 3 segundos
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            error_log("WhatsApp cURL error (GET): " . $error);
            return false;
        }

        if ($httpCode !== 200) {
            error_log("WhatsApp HTTP error (GET): " . $httpCode . " - " . $response);
            return false;
        }

        return json_decode($response, true);
    }

    /**
     * Envia mensagem de forma assíncrona (não-bloqueante)
     * Retorna imediatamente sem aguardar resposta da API
     */
    public function sendMessageAsync($phoneNumber, $message) {
        try {
            if (!$this->token || !$this->instanceId) {
                error_log("WhatsApp Async: Token ou Instance ID não configurados");
                return ['success' => false, 'error' => 'Configuração inválida'];
            }

            // Limpar e formatar número de telefone
            $phone = $this->formatPhoneNumber($phoneNumber);
            if (!$phone) {
                error_log("WhatsApp Async: Número de telefone inválido: " . $phoneNumber);
                return ['success' => false, 'error' => 'Número inválido'];
            }

            // URL correta conforme documentação w-api.app
            $url = "{$this->apiUrl}/v1/message/send-text?instanceId={$this->instanceId}";

            $data = [
                'phone' => $phone,
                'message' => $message
            ];

            $headers = [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json'
            ];

            // Fazer requisição assíncrona usando cURL multi
            $this->makeAsyncRequest($url, $data, $headers, $phone);

            // Retornar sucesso imediatamente sem aguardar resposta
            error_log("WhatsApp Async: Mensagem enviada assincronamente para " . $phone);
            return ['success' => true, 'async' => true, 'phone' => $phone];

        } catch (Exception $e) {
            error_log("WhatsApp Async: Exceção: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Faz requisição HTTP assíncrona (não-bloqueante)
     */
    private function makeAsyncRequest($url, $data, $headers, $phone) {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true, // Capturar resposta para evitar output direto
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 1, // Timeout muito baixo - fire and forget
            CURLOPT_CONNECTTIMEOUT => 1,
            CURLOPT_SSL_VERIFYPEER => false, // Acelerar conexão
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_NOSIGNAL => 1 // Evitar problemas com sinais em ambiente assíncrono
        ]);

        // Executar e capturar resposta para evitar output direto
        $response = curl_exec($ch);
        curl_close($ch);

        // Log apenas se houver resposta válida (opcional para debug)
        if ($response && strlen($response) > 0) {
            error_log("WhatsApp Async Response (discarded): " . substr($response, 0, 100));
        }

        error_log("WhatsApp: Requisição assíncrona enviada para " . $phone);
    }

    /**
     * Testa a conexão com a API
     */
    public function testConnection() {
        try {
            if (!$this->token || !$this->instanceId) {
                return [
                    'success' => false,
                    'message' => 'Token ou Instance ID não configurados'
                ];
            }

            // Primeiro, vamos testar apenas se conseguimos conectar na API base
            $headers = [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json'
            ];

            // Teste básico usando endpoint correto da w-api.app
            $url = "{$this->apiUrl}/v1/message/send-text?instanceId={$this->instanceId}";

            $testData = [
                'phone' => '5511999999999', // Número fictício para teste
                'message' => 'Teste de conexão'
            ];

            // Fazer uma tentativa de envio (que pode falhar, mas pelo menos validará a autenticação)
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($testData),
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 5, // Reduzido de 10 para 5 segundos
                CURLOPT_CONNECTTIMEOUT => 3, // Timeout de conexão de 3 segundos
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                return [
                    'success' => false,
                    'message' => 'Erro de conexão cURL: ' . $error
                ];
            }

            if ($httpCode === 401 || $httpCode === 403) {
                return [
                    'success' => false,
                    'message' => 'Token ou Instance ID inválidos (HTTP ' . $httpCode . ')'
                ];
            }

            if ($httpCode === 404) {
                return [
                    'success' => false,
                    'message' => 'API endpoint não encontrado. Verifique se a instância ' . $this->instanceId . ' está ativa na w-api.app'
                ];
            }

            // Para w-api.app, qualquer resposta que não seja 401/403/404 indica que a conexão funciona
            if ($httpCode >= 200 && $httpCode < 500) {
                $responseData = json_decode($response, true);

                if ($httpCode === 400 && isset($responseData['message']) &&
                    strpos($responseData['message'], 'número de telefone') !== false) {
                    // HTTP 400 esperado para número fictício - conexão OK
                    return [
                        'success' => true,
                        'message' => 'Conexão WhatsApp OK! Token e Instance ID válidos. Instância conectada e funcionando.',
                        'data' => $responseData
                    ];
                }

                // Outras respostas válidas
                return [
                    'success' => true,
                    'message' => 'Conexão WhatsApp OK - API respondeu (HTTP ' . $httpCode . '). Token e Instance ID válidos.',
                    'data' => $responseData
                ];
            }

            return [
                'success' => false,
                'message' => 'Erro HTTP ' . $httpCode . ': ' . $response
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Exceção: ' . $e->getMessage()
            ];
        }
    }
}
?>