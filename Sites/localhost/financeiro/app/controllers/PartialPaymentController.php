<?php
require_once 'BaseController.php';
require_once __DIR__ . '/../models/PartialPayment.php';
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../models/Account.php';
require_once __DIR__ . '/AuthMiddleware.php';

class PartialPaymentController extends BaseController {
    private $partialPaymentModel;
    private $transactionModel;
    private $accountModel;

    public function __construct() {
        parent::__construct();
        $this->partialPaymentModel = new PartialPayment();
        $this->transactionModel = new Transaction();
        $this->accountModel = new Account();
    }

    /**
     * Registrar uma baixa parcial
     */
    public function register() {
        try {
            $sessionUser = AuthMiddleware::requireAuth();
            $orgId = $this->getCurrentOrgId();

            $data = json_decode(file_get_contents('php://input'), true);

            // Validações
            if (empty($data['transaction_id'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'ID da transação é obrigatório'
                ]);
            }

            if (empty($data['valor']) || $data['valor'] <= 0) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Valor da baixa é obrigatório e deve ser positivo'
                ]);
            }

            if (empty($data['account_id'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Conta para o pagamento é obrigatória'
                ]);
            }

            if (empty($data['data_pagamento'])) {
                $data['data_pagamento'] = date('Y-m-d');
            }

            // Adicionar dados do contexto
            $data['org_id'] = $orgId;
            $data['created_by'] = $sessionUser['id'];

            // Registrar baixa parcial
            $paymentId = $this->partialPaymentModel->registerPayment($data);

            if ($paymentId) {
                // Buscar dados atualizados da transação
                $transaction = $this->transactionModel->getById($data['transaction_id']);
                $payments = $this->partialPaymentModel->getPaymentsByTransaction($data['transaction_id']);
                $summary = $this->partialPaymentModel->getPaymentSummary($data['transaction_id']);

                // Log para auditoria
                error_log("Baixa parcial registrada: ID {$paymentId} para transação {$data['transaction_id']} - Valor: {$data['valor']}");

                // Enviar notificação de baixa parcial
                try {
                    require_once __DIR__ . '/../services/TeamNotificationService.php';
                    $teamService = new TeamNotificationService();

                    $tipo = $transaction['kind'] === 'entrada' ? 'Receita' : 'Despesa';
                    $icon = $transaction['kind'] === 'entrada' ? '💰' : '💸';
                    $title = "{$icon} Baixa Parcial - {$tipo}";

                    $valorPago = number_format($data['valor'], 2, ',', '.');
                    $saldoPendente = number_format($summary['saldo_pendente'], 2, ',', '.');
                    $message = "Baixa parcial registrada: {$transaction['descricao']}\n";
                    $message .= "💵 Valor pago: R$ {$valorPago}\n";
                    $message .= "📊 Saldo pendente: R$ {$saldoPendente}";

                    $teamService->sendToTeam(
                        $orgId,
                        'partial_payment',
                        $title,
                        $message,
                        'partial_payment',
                        $paymentId
                    );

                    error_log("Partial payment notification sent for payment ID: $paymentId");
                } catch (Exception $notificationError) {
                    error_log("Failed to send partial payment notification: " . $notificationError->getMessage());
                }

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Baixa parcial registrada com sucesso',
                    'payment_id' => $paymentId,
                    'transaction' => $transaction,
                    'payments' => $payments,
                    'summary' => $summary
                ]);
            }

            return $this->jsonResponse([
                'success' => false,
                'message' => 'Erro ao registrar baixa parcial'
            ]);

        } catch (Exception $e) {
            return $this->handleError($e, 'Erro ao registrar baixa parcial');
        }
    }

    /**
     * Listar baixas parciais de uma transação
     */
    public function listByTransaction($transactionId) {
        try {
            $sessionUser = AuthMiddleware::requireAuth();

            $payments = $this->partialPaymentModel->getPaymentsByTransaction($transactionId);
            $summary = $this->partialPaymentModel->getPaymentSummary($transactionId);

            return $this->jsonResponse([
                'success' => true,
                'payments' => $payments,
                'summary' => $summary
            ]);

        } catch (Exception $e) {
            return $this->handleError($e, 'Erro ao listar baixas parciais');
        }
    }

    /**
     * Cancelar uma baixa parcial
     */
    public function cancel($paymentId) {
        try {
            $sessionUser = AuthMiddleware::requireAuth();

            $result = $this->partialPaymentModel->cancelPayment($paymentId, $sessionUser['id']);

            if ($result) {
                error_log("Baixa parcial cancelada: ID {$paymentId} por usuário {$sessionUser['id']}");

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Baixa parcial cancelada com sucesso'
                ]);
            }

            return $this->jsonResponse([
                'success' => false,
                'message' => 'Erro ao cancelar baixa parcial'
            ]);

        } catch (Exception $e) {
            return $this->handleError($e, 'Erro ao cancelar baixa parcial');
        }
    }

    /**
     * Obter transações com baixas parciais pendentes
     */
    public function getPendingTransactions() {
        try {
            $sessionUser = AuthMiddleware::requireAuth();
            $orgId = $this->getCurrentOrgId();

            $transactions = $this->partialPaymentModel->getTransactionsWithPendingPayments($orgId);

            return $this->jsonResponse([
                'success' => true,
                'transactions' => $transactions
            ]);

        } catch (Exception $e) {
            return $this->handleError($e, 'Erro ao buscar transações pendentes');
        }
    }

    /**
     * Listar todas as baixas parciais com filtros
     */
    public function listAll() {
        try {
            $sessionUser = AuthMiddleware::requireAuth();
            $orgId = $this->getCurrentOrgId();

            // Parâmetros de filtro
            $transactionId = $_GET['transaction_id'] ?? null;
            $dateFrom = $_GET['date_from'] ?? null;
            $dateTo = $_GET['date_to'] ?? null;
            $userId = $_GET['user_id'] ?? null;
            $limit = (int)($_GET['limit'] ?? 100);

            // Query base
            $sql = "SELECT
                        pp.id as payment_id,
                        pp.valor,
                        pp.data_pagamento,
                        pp.descricao,
                        pp.created_at,
                        pp.transaction_id,
                        pp.created_by,
                        t.descricao as transaction_description,
                        t.valor_original as transaction_value,
                        t.status_pagamento as transaction_status,
                        a.nome as account_name,
                        u.nome as created_by_name
                    FROM partial_payments pp
                    INNER JOIN transactions t ON pp.transaction_id = t.id
                    LEFT JOIN accounts a ON pp.account_id = a.id
                    LEFT JOIN users u ON pp.created_by = u.id
                    WHERE pp.org_id = ? AND pp.deleted_at IS NULL";

            $params = [$orgId];

            // Aplicar filtros
            if ($transactionId) {
                $sql .= " AND pp.transaction_id = ?";
                $params[] = $transactionId;
            }

            if ($dateFrom) {
                $sql .= " AND pp.data_pagamento >= ?";
                $params[] = $dateFrom;
            }

            if ($dateTo) {
                $sql .= " AND pp.data_pagamento <= ?";
                $params[] = $dateTo;
            }

            if ($userId) {
                $sql .= " AND pp.created_by = ?";
                $params[] = $userId;
            }

            $sql .= " ORDER BY pp.created_at DESC LIMIT ?";
            $params[] = $limit;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $payments = $stmt->fetchAll();

            // Buscar resumo
            $summarySQL = "SELECT
                            COUNT(*) as total_payments,
                            SUM(pp.valor) as total_value,
                            COUNT(DISTINCT CASE WHEN t.status_pagamento = 'parcial' THEN t.id END) as partial_transactions,
                            COUNT(DISTINCT CASE WHEN t.status_pagamento = 'quitado' THEN t.id END) as paid_transactions
                           FROM partial_payments pp
                           INNER JOIN transactions t ON pp.transaction_id = t.id
                           WHERE pp.org_id = ? AND pp.deleted_at IS NULL";

            $summaryParams = [$orgId];

            if ($dateFrom) {
                $summarySQL .= " AND pp.data_pagamento >= ?";
                $summaryParams[] = $dateFrom;
            }

            if ($dateTo) {
                $summarySQL .= " AND pp.data_pagamento <= ?";
                $summaryParams[] = $dateTo;
            }

            $stmt = $this->db->prepare($summarySQL);
            $stmt->execute($summaryParams);
            $summary = $stmt->fetch();

            return $this->jsonResponse([
                'success' => true,
                'payments' => $payments,
                'summary' => $summary
            ]);

        } catch (Exception $e) {
            return $this->handleError($e, 'Erro ao listar baixas parciais');
        }
    }

    /**
     * Dashboard de baixas parciais
     */
    public function dashboard() {
        try {
            $sessionUser = AuthMiddleware::requireAuth();
            $orgId = $this->getCurrentOrgId();

            $dashboard = $this->partialPaymentModel->getPartialPaymentsDashboard($orgId);
            $pendingTransactions = $this->partialPaymentModel->getTransactionsWithPendingPayments($orgId, 10);

            return $this->jsonResponse([
                'success' => true,
                'dashboard' => $dashboard,
                'pending_transactions' => $pendingTransactions
            ]);

        } catch (Exception $e) {
            return $this->handleError($e, 'Erro ao carregar dashboard');
        }
    }

    /**
     * Habilitar/desabilitar baixa parcial em uma transação
     */
    public function togglePartialPayment() {
        try {
            $sessionUser = AuthMiddleware::requireAuth();

            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['transaction_id'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'ID da transação é obrigatório'
                ]);
            }

            $enable = $data['enable'] ?? false;

            // Atualizar transação
            $sql = "UPDATE transactions
                    SET permite_baixa_parcial = ?,
                        valor_original = CASE
                            WHEN valor_original IS NULL THEN valor
                            ELSE valor_original
                        END
                    WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$enable ? 1 : 0, $data['transaction_id']]);

            if ($result) {
                $action = $enable ? 'habilitada' : 'desabilitada';
                error_log("Baixa parcial {$action} para transação {$data['transaction_id']}");

                return $this->jsonResponse([
                    'success' => true,
                    'message' => "Baixa parcial {$action} com sucesso"
                ]);
            }

            return $this->jsonResponse([
                'success' => false,
                'message' => 'Erro ao atualizar configuração'
            ]);

        } catch (Exception $e) {
            return $this->handleError($e, 'Erro ao alterar configuração');
        }
    }
}
?>