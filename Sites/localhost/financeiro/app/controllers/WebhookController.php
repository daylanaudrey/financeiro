<?php
require_once 'BaseController.php';
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../models/Account.php';
require_once __DIR__ . '/../models/Category.php';

class WebhookController extends BaseController {
    private $transactionModel;
    private $accountModel;
    private $categoryModel;

    public function __construct() {
        parent::__construct();
        $this->transactionModel = new Transaction();
        $this->accountModel = new Account();
        $this->categoryModel = new Category();
    }

    /**
     * Endpoint para receber dados do N8N
     * POST /webhook/n8n/transaction
     */
    public function n8nTransaction() {
        try {
            // Verificar método
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Método não permitido'
                ]);
            }

            // Ler dados JSON do body
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            if (!$data) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Dados JSON inválidos'
                ]);
            }

            // Log dos dados recebidos
            error_log("N8N Webhook recebido: " . json_encode($data));

            // Validar campos obrigatórios
            $required = ['valor', 'descricao', 'tipo'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return $this->jsonResponse([
                        'success' => false,
                        'message' => "Campo obrigatório ausente: {$field}"
                    ]);
                }
            }

            // Processar dados do N8N
            $transactionData = $this->processN8NData($data);

            if (!$transactionData) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Erro ao processar dados do N8N'
                ]);
            }

            // Criar transação
            $transactionId = $this->transactionModel->create($transactionData);

            if ($transactionId) {
                error_log("Transação criada via N8N: ID {$transactionId}");

                // Enviar notificação WhatsApp (se configurado)
                $this->sendWhatsAppNotification($transactionId, $transactionData);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Transação criada com sucesso',
                    'transaction_id' => $transactionId,
                    'data' => $transactionData
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Erro ao criar transação'
                ]);
            }

        } catch (Exception $e) {
            error_log("Erro no webhook N8N: " . $e->getMessage());
            return $this->handleError($e, 'Erro interno no webhook');
        }
    }

    /**
     * Processa e valida dados vindos do N8N
     */
    private function processN8NData($data) {
        try {
            // Valor - remover caracteres não numéricos e converter
            $valor = $this->parseValue($data['valor']);
            if ($valor <= 0) {
                throw new Exception("Valor inválido: " . $data['valor']);
            }

            // Tipo - entrada ou saída
            $kind = strtolower($data['tipo']);
            if (!in_array($kind, ['entrada', 'saida'])) {
                throw new Exception("Tipo inválido: " . $data['tipo']);
            }

            // Buscar ou criar conta padrão
            $accountId = $this->getOrCreateAccount($data['conta'] ?? 'N8N Import');

            // Buscar ou criar categoria padrão
            $categoryId = $this->getOrCreateCategory($data['categoria'] ?? 'Automático');

            // Data da competência
            $dataCompetencia = $data['data'] ?? date('Y-m-d');
            if (!$this->isValidDate($dataCompetencia)) {
                $dataCompetencia = date('Y-m-d');
            }

            return [
                'org_id' => 1, // Sempre org 1
                'account_id' => $accountId,
                'kind' => $kind,
                'valor' => $valor,
                'data_competencia' => $dataCompetencia,
                'data_pagamento' => null,
                'status' => 'confirmado',
                'category_id' => $categoryId,
                'contact_id' => null,
                'descricao' => $data['descricao'],
                'observacoes' => $data['observacoes'] ?? 'Importado via N8N',
                'recurrence_type' => null,
                'recurrence_count' => 1,
                'recurrence_end_date' => null,
                'parent_transaction_id' => null,
                'recurrence_sequence' => 0,
                'created_by' => 1 // Sistema
            ];

        } catch (Exception $e) {
            error_log("Erro ao processar dados N8N: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Converte valor string para float
     */
    private function parseValue($value) {
        // Remover caracteres não numéricos exceto vírgula e ponto
        $cleaned = preg_replace('/[^\d,.]/', '', $value);

        // Converter vírgula para ponto
        $cleaned = str_replace(',', '.', $cleaned);

        return floatval($cleaned);
    }

    /**
     * Busca ou cria uma conta
     */
    private function getOrCreateAccount($accountName) {
        // Buscar conta existente
        $existing = $this->accountModel->findByName($accountName, 1); // org_id = 1

        if ($existing) {
            return $existing['id'];
        }

        // Criar nova conta
        $accountData = [
            'org_id' => 1,
            'nome' => $accountName,
            'tipo' => 'corrente',
            'banco' => 'N8N Import',
            'moeda' => 'BRL',
            'saldo_inicial' => 0,
            'saldo_atual' => 0,
            'ativo' => 1,
            'cor' => '#007bff',
            'created_by' => 1
        ];

        return $this->accountModel->create($accountData);
    }

    /**
     * Busca ou cria uma categoria
     */
    private function getOrCreateCategory($categoryName) {
        // Buscar categoria existente
        $existing = $this->categoryModel->findByName($categoryName, 1); // org_id = 1

        if ($existing) {
            return $existing['id'];
        }

        // Criar nova categoria
        $categoryData = [
            'org_id' => 1,
            'nome' => $categoryName,
            'tipo' => 'geral',
            'cor' => '#28a745',
            'ativo' => 1,
            'created_by' => 1
        ];

        return $this->categoryModel->create($categoryData);
    }

    /**
     * Valida formato de data
     */
    private function isValidDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Envia notificação WhatsApp (placeholder para implementação futura)
     */
    private function sendWhatsAppNotification($transactionId, $transactionData) {
        // TODO: Implementar integração com w-api.app
        error_log("WhatsApp notification queued for transaction: {$transactionId}");
    }

    /**
     * Endpoint para testar webhook
     * GET /webhook/test
     */
    public function test() {
        return $this->jsonResponse([
            'success' => true,
            'message' => 'Webhook endpoint funcionando',
            'timestamp' => date('Y-m-d H:i:s'),
            'endpoints' => [
                'POST /webhook/n8n/transaction' => 'Criar transação via N8N',
                'GET /webhook/test' => 'Testar webhook'
            ]
        ]);
    }
}
?>