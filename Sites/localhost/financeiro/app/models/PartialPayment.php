<?php
require_once 'BaseModel.php';

class PartialPayment extends BaseModel {
    protected $table = 'partial_payments';

    /**
     * Registrar uma baixa parcial
     */
    public function registerPayment($data) {
        try {
            $this->db->beginTransaction();

            // Validar se a transação permite baixa parcial
            $transaction = $this->getTransaction($data['transaction_id']);
            if (!$transaction) {
                throw new Exception('Transação não encontrada');
            }

            // Verificar se o valor da baixa não excede o pendente
            $pendente = $transaction['valor_original'] - $transaction['valor_pago'];
            if ($data['valor'] > $pendente) {
                throw new Exception('Valor da baixa excede o valor pendente');
            }

            // Inserir baixa parcial
            $sql = "INSERT INTO {$this->table}
                    (org_id, transaction_id, account_id, valor, data_pagamento, descricao, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['org_id'],
                $data['transaction_id'],
                $data['account_id'],
                $data['valor'],
                $data['data_pagamento'],
                $data['descricao'] ?? null,
                $data['created_by']
            ]);

            if (!$result) {
                throw new Exception('Erro ao registrar baixa parcial');
            }

            $paymentId = $this->db->lastInsertId();

            // As triggers do banco se encarregam de:
            // - Atualizar valores da transação (valor_pago, valor_pendente, status_pagamento)
            // - Atualizar saldo da conta automaticamente
            // Não precisamos fazer isso manualmente para evitar duplicação

            $this->db->commit();
            return $paymentId;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Listar baixas parciais de uma transação
     */
    public function getPaymentsByTransaction($transactionId) {
        $sql = "SELECT pp.*, a.nome as account_name, u.nome as created_by_name
                FROM {$this->table} pp
                LEFT JOIN accounts a ON pp.account_id = a.id
                LEFT JOIN users u ON pp.created_by = u.id
                WHERE pp.transaction_id = ?
                  AND pp.deleted_at IS NULL
                ORDER BY pp.data_pagamento DESC, pp.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$transactionId]);
        return $stmt->fetchAll();
    }

    /**
     * Obter resumo de baixas parciais
     */
    public function getPaymentSummary($transactionId) {
        $sql = "SELECT
                    COUNT(*) as total_pagamentos,
                    COALESCE(SUM(valor), 0) as total_pago,
                    MIN(data_pagamento) as primeiro_pagamento,
                    MAX(data_pagamento) as ultimo_pagamento
                FROM {$this->table}
                WHERE transaction_id = ?
                  AND deleted_at IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$transactionId]);
        return $stmt->fetch();
    }

    /**
     * Cancelar baixa parcial
     */
    public function cancelPayment($paymentId, $userId) {
        try {
            $this->db->beginTransaction();

            // Obter dados da baixa
            $payment = $this->getById($paymentId);
            if (!$payment) {
                throw new Exception('Baixa parcial não encontrada');
            }

            // Soft delete da baixa
            $sql = "UPDATE {$this->table}
                    SET deleted_at = NOW()
                    WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$paymentId]);

            if (!$result) {
                throw new Exception('Erro ao cancelar baixa parcial');
            }

            // Atualizar status da transação
            $this->updateTransactionStatus($payment['transaction_id']);

            // Reverter saldo da conta
            $transaction = $this->getTransaction($payment['transaction_id']);
            $this->reverseAccountBalance($payment['account_id'], $payment['valor'], $transaction['kind']);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Obter transação
     */
    private function getTransaction($transactionId) {
        $sql = "SELECT t.*,
                       COALESCE(t.valor_original, t.valor) as valor_original,
                       COALESCE(t.valor_pago, 0) as valor_pago
                FROM transactions t
                WHERE t.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$transactionId]);
        return $stmt->fetch();
    }

    /**
     * Atualizar status da transação baseado nas baixas
     */
    private function updateTransactionStatus($transactionId) {
        // Calcular total pago
        $sql = "SELECT COALESCE(SUM(valor), 0) as total_pago
                FROM {$this->table}
                WHERE transaction_id = ?
                  AND deleted_at IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$transactionId]);
        $result = $stmt->fetch();
        $totalPago = $result['total_pago'];

        // Obter valor original
        $transaction = $this->getTransaction($transactionId);
        $valorOriginal = $transaction['valor_original'];

        // Atualizar transação
        $status = 'pendente';
        $statusPagamento = 'pendente';

        if ($totalPago >= $valorOriginal) {
            $status = 'confirmado';
            $statusPagamento = 'quitado';
        } elseif ($totalPago > 0) {
            $status = 'agendado';
            $statusPagamento = 'parcial';
        }

        $sql = "UPDATE transactions
                SET valor_pago = ?,
                    valor_pendente = ? - ?,
                    status = ?,
                    status_pagamento = ?
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $totalPago,
            $valorOriginal,
            $totalPago,
            $status,
            $statusPagamento,
            $transactionId
        ]);
    }

    /**
     * Atualizar saldo da conta após baixa
     */
    private function updateAccountBalance($accountId, $valor, $transactionKind) {
        if (in_array($transactionKind, ['entrada', 'transfer_in'])) {
            // Entrada: aumenta o saldo
            $sql = "UPDATE accounts SET saldo_atual = saldo_atual + ? WHERE id = ?";
        } else {
            // Saída: diminui o saldo
            $sql = "UPDATE accounts SET saldo_atual = saldo_atual - ? WHERE id = ?";
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$valor, $accountId]);
    }

    /**
     * Reverter saldo da conta após cancelamento
     */
    private function reverseAccountBalance($accountId, $valor, $transactionKind) {
        if (in_array($transactionKind, ['entrada', 'transfer_in'])) {
            // Reverter entrada: diminui o saldo
            $sql = "UPDATE accounts SET saldo_atual = saldo_atual - ? WHERE id = ?";
        } else {
            // Reverter saída: aumenta o saldo
            $sql = "UPDATE accounts SET saldo_atual = saldo_atual + ? WHERE id = ?";
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$valor, $accountId]);
    }

    /**
     * Obter transações com baixas parciais pendentes
     */
    public function getTransactionsWithPendingPayments($orgId, $limit = 20) {
        $sql = "SELECT
                    t.id,
                    t.descricao,
                    t.kind,
                    t.data_competencia,
                    COALESCE(t.valor_original, t.valor) as valor_total,
                    COALESCE(t.valor_pago, 0) as valor_pago,
                    COALESCE(t.valor_original, t.valor) - COALESCE(t.valor_pago, 0) as valor_pendente,
                    t.status_pagamento,
                    c.nome as category_name,
                    ct.nome as contact_name,
                    a.nome as account_name
                FROM transactions t
                LEFT JOIN categories c ON t.category_id = c.id
                LEFT JOIN contacts ct ON t.contact_id = ct.id
                LEFT JOIN accounts a ON t.account_id = a.id
                WHERE t.org_id = ?
                  AND t.permite_baixa_parcial = 1
                  AND t.status_pagamento IN ('pendente', 'parcial')
                  AND t.deleted_at IS NULL
                ORDER BY t.data_competencia ASC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Dashboard de baixas parciais
     */
    public function getPartialPaymentsDashboard($orgId) {
        // Total em baixas parciais
        $sql = "SELECT
                    COUNT(DISTINCT t.id) as total_transacoes,
                    SUM(COALESCE(t.valor_original, t.valor)) as valor_total,
                    SUM(COALESCE(t.valor_pago, 0)) as total_pago,
                    SUM(COALESCE(t.valor_original, t.valor) - COALESCE(t.valor_pago, 0)) as total_pendente,
                    COUNT(DISTINCT CASE WHEN t.status_pagamento = 'parcial' THEN t.id END) as qtd_parciais,
                    COUNT(DISTINCT CASE WHEN t.status_pagamento = 'quitado' THEN t.id END) as qtd_quitadas
                FROM transactions t
                WHERE t.org_id = ?
                  AND t.permite_baixa_parcial = 1
                  AND t.deleted_at IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId]);
        return $stmt->fetch();
    }
}
?>