<?php
require_once 'BaseModel.php';

class Transaction extends BaseModel {
    protected $table = 'transactions';
    
    public function getTransactionsByOrg($orgId, $limit = 50, $offset = 0) {
        $sql = "
            SELECT t.*, 
                   a.nome as account_name, a.tipo as account_type,
                   c.nome as category_name, c.tipo as category_type, c.cor as category_color,
                   ct.nome as contact_name, ct.tipo as contact_type,
                   u.nome as created_by_name
            FROM {$this->table} t
            INNER JOIN accounts a ON t.account_id = a.id
            LEFT JOIN categories c ON t.category_id = c.id
            LEFT JOIN contacts ct ON t.contact_id = ct.id
            LEFT JOIN users u ON t.created_by = u.id
            WHERE t.org_id = ? AND t.deleted_at IS NULL
            ORDER BY t.id DESC, t.created_at DESC
            LIMIT ? OFFSET ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId, $limit, $offset]);
        return $stmt->fetchAll();
    }
    
    public function getConfirmedTransactionsByOrg($orgId, $limit = 50, $offset = 0) {
        $sql = "
            SELECT t.*, 
                   a.nome as account_name, a.tipo as account_type,
                   c.nome as category_name, c.tipo as category_type, c.cor as category_color,
                   ct.nome as contact_name, ct.tipo as contact_type,
                   u.nome as created_by_name
            FROM {$this->table} t
            INNER JOIN accounts a ON t.account_id = a.id
            LEFT JOIN categories c ON t.category_id = c.id
            LEFT JOIN contacts ct ON t.contact_id = ct.id
            LEFT JOIN users u ON t.created_by = u.id
            WHERE t.org_id = ? AND t.status = 'confirmado' AND t.deleted_at IS NULL
            ORDER BY t.id DESC, t.created_at DESC
            LIMIT ? OFFSET ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId, $limit, $offset]);
        return $stmt->fetchAll();
    }
    
    public function getRecentTransactionsWithUser($orgId, $limit = 10) {
        $sql = "
            SELECT t.*, 
                   a.nome as account_name, a.tipo as account_type,
                   c.nome as category_name, c.tipo as category_type, c.cor as category_color,
                   ct.nome as contact_name, ct.tipo as contact_type,
                   u.nome as created_by_name,
                   DATE(t.created_at) as created_date
            FROM {$this->table} t
            INNER JOIN accounts a ON t.account_id = a.id
            LEFT JOIN categories c ON t.category_id = c.id
            LEFT JOIN contacts ct ON t.contact_id = ct.id
            LEFT JOIN users u ON t.created_by = u.id
            WHERE t.org_id = ? AND t.deleted_at IS NULL
            ORDER BY t.created_at DESC, t.id DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId, $limit]);
        return $stmt->fetchAll();
    }
    
    public function getCategoryExpensesChart($orgId, $year) {
        $sql = "
            SELECT c.nome, c.cor, SUM(t.valor) as total
            FROM {$this->table} t
            INNER JOIN categories c ON t.category_id = c.id
            WHERE t.org_id = ? 
            AND t.kind IN ('saida', 'transfer_out') 
            AND t.status = 'confirmado'
            AND YEAR(t.data_competencia) = ?
            AND t.deleted_at IS NULL
            GROUP BY c.id, c.nome, c.cor
            HAVING total > 0
            ORDER BY total DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId, $year]);
        return $stmt->fetchAll();
    }
    
    public function getMonthlyEvolution($orgId, $months = 12) {
        $sql = "
            SELECT 
                YEAR(t.data_competencia) as year,
                MONTH(t.data_competencia) as month,
                SUM(CASE WHEN t.kind IN ('entrada', 'transfer_in') AND t.status = 'confirmado' THEN t.valor ELSE 0 END) as receitas,
                SUM(CASE WHEN t.kind IN ('saida', 'transfer_out') AND t.status = 'confirmado' THEN t.valor ELSE 0 END) as despesas
            FROM {$this->table} t
            WHERE t.org_id = ?
            AND t.data_competencia >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            AND t.deleted_at IS NULL
            GROUP BY YEAR(t.data_competencia), MONTH(t.data_competencia)
            ORDER BY year DESC, month DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId, $months, $months]);
        $results = $stmt->fetchAll();
        
        // Ordenar do mais antigo para o mais recente
        return array_reverse($results);
    }
    
    public function getTransactionsWithFilters($orgId, $filters = [], $limit = 25, $offset = 0) {
        // Base da query
        $baseSelect = "
            SELECT t.*, 
                   a.nome as account_name, a.tipo as account_type,
                   c.nome as category_name, c.tipo as category_type, c.cor as category_color,
                   ct.nome as contact_name, ct.tipo as contact_type,
                   cc.nome as cost_center_name, cc.codigo as cost_center_code, cc.cor as cost_center_color,
                   u.nome as created_by_name
            FROM {$this->table} t
            INNER JOIN accounts a ON t.account_id = a.id
            LEFT JOIN categories c ON t.category_id = c.id
            LEFT JOIN contacts ct ON t.contact_id = ct.id
            LEFT JOIN cost_centers cc ON t.cost_center_id = cc.id
            LEFT JOIN users u ON t.created_by = u.id
        ";
        
        $countSelect = "
            SELECT COUNT(*) as total 
            FROM {$this->table} t
            INNER JOIN accounts a ON t.account_id = a.id
            LEFT JOIN categories c ON t.category_id = c.id
            LEFT JOIN contacts ct ON t.contact_id = ct.id
            LEFT JOIN cost_centers cc ON t.cost_center_id = cc.id
        ";
        
        $totalsSelect = "
            SELECT 
                SUM(CASE WHEN t.kind IN ('entrada', 'transfer_in') THEN t.valor ELSE 0 END) as total_entradas,
                SUM(CASE WHEN t.kind IN ('saida', 'transfer_out') THEN t.valor ELSE 0 END) as total_saidas
            FROM {$this->table} t
            INNER JOIN accounts a ON t.account_id = a.id
            LEFT JOIN categories c ON t.category_id = c.id
            LEFT JOIN contacts ct ON t.contact_id = ct.id
            LEFT JOIN cost_centers cc ON t.cost_center_id = cc.id
        ";
        
        // Construir WHERE clause
        $whereConditions = ['t.org_id = ?'];
        $params = [$orgId];
        
        // Filtros
        if (!empty($filters['account_id'])) {
            $whereConditions[] = 't.account_id = ?';
            $params[] = $filters['account_id'];
        }
        
        if (!empty($filters['category_id'])) {
            $whereConditions[] = 't.category_id = ?';
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['cost_center_id'])) {
            $whereConditions[] = 't.cost_center_id = ?';
            $params[] = $filters['cost_center_id'];
        }
        
        if (!empty($filters['status'])) {
            $whereConditions[] = 't.status = ?';
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['kind'])) {
            $whereConditions[] = 't.kind = ?';
            $params[] = $filters['kind'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereConditions[] = 't.data_competencia >= ?';
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = 't.data_competencia <= ?';
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = '(t.descricao LIKE ? OR t.observacoes LIKE ?)';
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Filtro para vencidos
        if (!empty($filters['vencidos']) && $filters['vencidos'] === 'true') {
            $whereConditions[] = 't.data_competencia < CURDATE() AND t.status != "confirmado"';
        }
        
        $whereConditions[] = 't.deleted_at IS NULL';
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
        
        // Ordenação configurável
        $orderBy = 'ORDER BY ';
        if (!empty($filters['order_by'])) {
            switch ($filters['order_by']) {
                case 'data_vencimento':
                    $orderBy .= 't.data_competencia ASC';
                    break;
                case 'data_vencimento_desc':
                    $orderBy .= 't.data_competencia DESC';
                    break;
                case 'valor':
                    $orderBy .= 't.valor DESC';
                    break;
                case 'created_at':
                    $orderBy .= 't.created_at DESC';
                    break;
                default:
                    // Ordenação padrão: vencidos primeiro, depois por data de vencimento
                    $orderBy .= '
                        CASE 
                            WHEN t.data_competencia < CURDATE() AND t.status != "confirmado" THEN 0
                            ELSE 1 
                        END,
                        t.data_competencia ASC';
                    break;
            }
        } else {
            // Ordenação padrão: vencidos primeiro, depois por data de vencimento
            $orderBy .= '
                CASE 
                    WHEN t.data_competencia < CURDATE() AND t.status != "confirmado" THEN 0
                    ELSE 1 
                END,
                t.data_competencia ASC';
        }
        
        // Query principal com paginação
        $mainSql = $baseSelect . $whereClause . ' ' . $orderBy . ' LIMIT ? OFFSET ?';
        $stmt = $this->db->prepare($mainSql);
        $stmt->execute([...$params, $limit, $offset]);
        $transactions = $stmt->fetchAll();
        
        // Query para contar total
        $countSql = $countSelect . $whereClause;
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $totalResult = $stmt->fetch();
        
        // Query para totais
        $totalsSql = $totalsSelect . $whereClause;
        $stmt = $this->db->prepare($totalsSql);
        $stmt->execute($params);
        $totalsResult = $stmt->fetch();
        
        return [
            'transactions' => $transactions,
            'total' => $totalResult['total'] ?? 0,
            'totals' => [
                'entradas' => $totalsResult['total_entradas'] ?? 0,
                'saidas' => $totalsResult['total_saidas'] ?? 0,
                'saldo' => ($totalsResult['total_entradas'] ?? 0) - ($totalsResult['total_saidas'] ?? 0)
            ]
        ];
    }
    
    public function getTransactionsByAccount($accountId, $limit = 50) {
        $sql = "
            SELECT t.*, 
                   c.nome as category_name, c.cor as category_color,
                   ct.nome as contact_name
            FROM {$this->table} t
            LEFT JOIN categories c ON t.category_id = c.id
            LEFT JOIN contacts ct ON t.contact_id = ct.id
            WHERE t.account_id = ? AND t.deleted_at IS NULL
            ORDER BY t.data_competencia ASC, t.created_at ASC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$accountId, $limit]);
        return $stmt->fetchAll();
    }
    
    public function getMonthlyBalance($orgId, $year, $month) {
        $sql = "
            SELECT 
                SUM(CASE WHEN kind IN ('entrada', 'transfer_in') AND status = 'confirmado' THEN valor ELSE 0 END) as receitas,
                SUM(CASE WHEN kind IN ('saida', 'transfer_out') AND status = 'confirmado' THEN valor ELSE 0 END) as despesas,
                COUNT(*) as total_transactions
            FROM {$this->table}
            WHERE org_id = ? 
            AND YEAR(data_competencia) = ? 
            AND MONTH(data_competencia) = ?
            AND deleted_at IS NULL
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId, $year, $month]);
        return $stmt->fetch();
    }
    
    public function getMonthlyBalanceWithScheduled($orgId, $year, $month) {
        $sql = "
            SELECT 
                SUM(CASE WHEN kind IN ('entrada', 'transfer_in') AND status IN ('confirmado', 'agendado') THEN valor ELSE 0 END) as receitas,
                SUM(CASE WHEN kind IN ('saida', 'transfer_out') AND status IN ('confirmado', 'agendado') THEN valor ELSE 0 END) as despesas,
                COUNT(*) as total_transactions
            FROM {$this->table}
            WHERE org_id = ? 
            AND YEAR(data_competencia) = ? 
            AND MONTH(data_competencia) = ?
            AND deleted_at IS NULL
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId, $year, $month]);
        return $stmt->fetch();
    }
    
    public function getMonthlyBalanceByPersonType($orgId, $year, $month) {
        $sql = "
            SELECT 
                a.pessoa_tipo,
                SUM(CASE WHEN t.kind IN ('entrada', 'transfer_in') AND t.status IN ('confirmado', 'agendado') THEN t.valor ELSE 0 END) as receitas,
                SUM(CASE WHEN t.kind IN ('saida', 'transfer_out') AND t.status IN ('confirmado', 'agendado') THEN t.valor ELSE 0 END) as despesas,
                COUNT(*) as total_transactions
            FROM {$this->table} t
            INNER JOIN accounts a ON t.account_id = a.id
            WHERE t.org_id = ? 
            AND YEAR(t.data_competencia) = ? 
            AND MONTH(t.data_competencia) = ?
            AND t.deleted_at IS NULL
            GROUP BY a.pessoa_tipo
            ORDER BY a.pessoa_tipo
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId, $year, $month]);
        $results = $stmt->fetchAll();
        
        // Organizar por pessoa_tipo
        $organized = [];
        foreach ($results as $row) {
            $organized[$row['pessoa_tipo']] = $row;
        }
        
        return $organized;
    }
    
    public function getMonthlyBalanceByPersonTypeConfirmed($orgId, $year, $month) {
        $sql = "
            SELECT 
                a.pessoa_tipo,
                SUM(CASE WHEN t.kind IN ('entrada', 'transfer_in') AND t.status = 'confirmado' THEN t.valor ELSE 0 END) as receitas,
                SUM(CASE WHEN t.kind IN ('saida', 'transfer_out') AND t.status = 'confirmado' THEN t.valor ELSE 0 END) as despesas,
                COUNT(*) as total_transactions
            FROM {$this->table} t
            INNER JOIN accounts a ON t.account_id = a.id
            WHERE t.org_id = ? 
            AND YEAR(t.data_competencia) = ? 
            AND MONTH(t.data_competencia) = ?
            AND t.deleted_at IS NULL
            GROUP BY a.pessoa_tipo
            ORDER BY a.pessoa_tipo
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId, $year, $month]);
        $results = $stmt->fetchAll();
        
        // Organizar por pessoa_tipo
        $organized = [];
        foreach ($results as $row) {
            $organized[$row['pessoa_tipo']] = $row;
        }
        
        return $organized;
    }
    
    public function createTransaction($data) {
        // Começar transação do banco
        $this->db->beginTransaction();
        
        try {
            $transactionId = $this->create($data);
            
            if ($transactionId && $data['status'] === 'confirmado') {
                // Atualizar saldo da conta se confirmado
                $this->updateAccountBalance($data['account_id'], $data['kind'], $data['valor']);
            }
            
            $this->db->commit();
            return $transactionId;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function updateTransaction($id, $data) {
        $this->db->beginTransaction();
        
        try {
            // Buscar dados atuais
            $oldData = $this->findById($id);
            if (!$oldData) {
                throw new Exception("Transação não encontrada");
            }
            
            // Se mudou status para confirmado ou valor, recalcular saldo
            $recalculateBalance = false;
            if ($data['status'] !== $oldData['status'] || $data['valor'] != $oldData['valor']) {
                $recalculateBalance = true;
            }
            
            // Atualizar transação
            $success = $this->update($id, $data);
            
            if ($success && $recalculateBalance) {
                // Reverter saldo antigo se estava confirmado
                if ($oldData['status'] === 'confirmado') {
                    $this->reverseAccountBalance($oldData['account_id'], $oldData['kind'], $oldData['valor']);
                }
                
                // Aplicar novo saldo se está confirmado
                if ($data['status'] === 'confirmado') {
                    $accountId = $data['account_id'] ?? $oldData['account_id'];
                    $kind = $data['kind'] ?? $oldData['kind'];
                    $valor = $data['valor'] ?? $oldData['valor'];
                    $this->updateAccountBalance($accountId, $kind, $valor);
                }
            }
            
            $this->db->commit();
            return $success;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function deleteTransaction($id) {
        $this->db->beginTransaction();
        
        try {
            $transaction = $this->findById($id);
            if (!$transaction) {
                throw new Exception("Transação não encontrada");
            }
            
            // Reverter saldo se estava confirmado
            if ($transaction['status'] === 'confirmado') {
                $this->reverseAccountBalance($transaction['account_id'], $transaction['kind'], $transaction['valor']);
            }
            
            $success = $this->delete($id);
            
            $this->db->commit();
            return $success;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    private function updateAccountBalance($accountId, $kind, $valor) {
        $multiplier = in_array($kind, ['entrada', 'transfer_in']) ? 1 : -1;
        $amount = $valor * $multiplier;
        
        $sql = "UPDATE accounts SET saldo_atual = saldo_atual + ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$amount, $accountId]);
    }
    
    private function reverseAccountBalance($accountId, $kind, $valor) {
        $multiplier = in_array($kind, ['entrada', 'transfer_in']) ? -1 : 1;
        $amount = $valor * $multiplier;
        
        $sql = "UPDATE accounts SET saldo_atual = saldo_atual + ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$amount, $accountId]);
    }
    
    public function getStatusOptions() {
        return [
            'rascunho' => ['nome' => 'Rascunho', 'cor' => '#6c757d', 'icone' => 'fas fa-edit'],
            'agendado' => ['nome' => 'Agendado', 'cor' => '#ffc107', 'icone' => 'fas fa-clock'],
            'confirmado' => ['nome' => 'Confirmado', 'cor' => '#28a745', 'icone' => 'fas fa-check'],
            'cancelado' => ['nome' => 'Cancelado', 'cor' => '#dc3545', 'icone' => 'fas fa-times']
        ];
    }
    
    public function getKindOptions() {
        return [
            'entrada' => ['nome' => 'Receita', 'cor' => '#28a745', 'icone' => 'fas fa-arrow-up'],
            'saida' => ['nome' => 'Despesa', 'cor' => '#dc3545', 'icone' => 'fas fa-arrow-down'],
            'transfer_out' => ['nome' => 'Transferência (Saída)', 'cor' => '#17a2b8', 'icone' => 'fas fa-exchange-alt'],
            'transfer_in' => ['nome' => 'Transferência (Entrada)', 'cor' => '#6f42c1', 'icone' => 'fas fa-exchange-alt']
        ];
    }
    
    public function getUpcomingScheduledTransactions($orgId, $limit = 10) {
        $sql = "
            SELECT t.*, 
                   a.nome as account_name, a.tipo as account_type,
                   c.nome as category_name, c.cor as category_color
            FROM {$this->table} t
            INNER JOIN accounts a ON t.account_id = a.id
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.org_id = ? 
            AND t.status = 'agendado'
            AND t.deleted_at IS NULL
            ORDER BY t.data_competencia ASC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId, $limit]);
        return $stmt->fetchAll();
    }
    
    public function getCategoryExpensesByPersonType($orgId, $year = null, $month = null) {
        $dateFilter = '';
        $params = [$orgId];
        
        if ($year && $month) {
            $dateFilter = 'AND YEAR(t.data_competencia) = ? AND MONTH(t.data_competencia) = ?';
            $params[] = $year;
            $params[] = $month;
        } elseif ($year) {
            $dateFilter = 'AND YEAR(t.data_competencia) = ?';
            $params[] = $year;
        }
        
        $sql = "
            SELECT 
                a.pessoa_tipo,
                c.nome as category_name,
                c.cor as category_color,
                c.icone as category_icon,
                SUM(t.valor) as total_gasto
            FROM {$this->table} t
            INNER JOIN accounts a ON t.account_id = a.id
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.org_id = ? 
            AND t.kind IN ('saida', 'transfer_out')
            AND t.status = 'confirmado'
            AND t.deleted_at IS NULL
            {$dateFilter}
            GROUP BY a.pessoa_tipo, c.id, c.nome, c.cor, c.icone
            HAVING total_gasto > 0
            ORDER BY a.pessoa_tipo, total_gasto DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();
        
        // Organizar dados por pessoa_tipo
        $organized = ['PF' => [], 'PJ' => []];
        foreach ($results as $row) {
            $organized[$row['pessoa_tipo']][] = $row;
        }
        
        return $organized;
    }
    
    public function createTransfer($accountFromId, $accountToId, $valor, $dataCompetencia, $descricao, $observacoes, $userId) {
        // Começar transação do banco
        $this->db->beginTransaction();
        
        try {
            // Gerar ID único para o par de transferências
            $transferPairId = $this->generateTransferPairId();
            
            // Criar lançamento de saída (conta origem)
            $saida = [
                'org_id' => 1,
                'account_id' => $accountFromId,
                'kind' => 'transfer_out',
                'valor' => $valor,
                'data_competencia' => $dataCompetencia,
                'data_pagamento' => $dataCompetencia, // Transferência é sempre confirmada
                'status' => 'confirmado',
                'category_id' => null,
                'contact_id' => null,
                'descricao' => $descricao,
                'observacoes' => $observacoes,
                'transfer_pair_id' => $transferPairId,
                'created_by' => $userId
            ];
            
            $saidaId = $this->create($saida);
            if (!$saidaId) {
                throw new Exception("Erro ao criar lançamento de saída");
            }
            
            // Criar lançamento de entrada (conta destino)
            $entrada = [
                'org_id' => 1,
                'account_id' => $accountToId,
                'kind' => 'transfer_in',
                'valor' => $valor,
                'data_competencia' => $dataCompetencia,
                'data_pagamento' => $dataCompetencia, // Transferência é sempre confirmada
                'status' => 'confirmado',
                'category_id' => null,
                'contact_id' => null,
                'descricao' => $descricao,
                'observacoes' => $observacoes,
                'transfer_pair_id' => $transferPairId,
                'created_by' => $userId
            ];
            
            $entradaId = $this->create($entrada);
            if (!$entradaId) {
                throw new Exception("Erro ao criar lançamento de entrada");
            }
            
            // Atualizar saldos das contas (sempre confirmado)
            $this->updateAccountBalance($accountFromId, 'transfer_out', $valor);
            $this->updateAccountBalance($accountToId, 'transfer_in', $valor);
            
            $this->db->commit();
            return $transferPairId;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    private function generateTransferPairId() {
        // Gerar um ID único para o par de transferências
        $sql = "SELECT COALESCE(MAX(transfer_pair_id), 0) + 1 as next_id FROM {$this->table} WHERE transfer_pair_id IS NOT NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['next_id'];
    }
    
    public function getTransferPair($transferPairId) {
        $sql = "
            SELECT t.*, 
                   a.nome as account_name, a.tipo as account_type,
                   c.nome as category_name, c.cor as category_color
            FROM {$this->table} t
            INNER JOIN accounts a ON t.account_id = a.id
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.transfer_pair_id = ? AND t.deleted_at IS NULL
            ORDER BY t.kind DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$transferPairId]);
        return $stmt->fetchAll();
    }
    
    public function getTransfers($orgId, $limit = 50) {
        $sql = "
            (
                SELECT 
                    t1.transfer_pair_id,
                    t1.data_competencia,
                    t1.data_pagamento,
                    t1.valor,
                    t1.descricao,
                    t1.observacoes,
                    t1.created_at,
                    a1.nome as account_from_name,
                    a1.tipo as account_from_type,
                    a2.nome as account_to_name,
                    a2.tipo as account_to_type,
                    u.nome as created_by_name,
                    'transfer' as tipo_operacao
                FROM {$this->table} t1
                INNER JOIN {$this->table} t2 ON t1.transfer_pair_id = t2.transfer_pair_id
                INNER JOIN accounts a1 ON t1.account_id = a1.id
                INNER JOIN accounts a2 ON t2.account_id = a2.id
                LEFT JOIN users u ON t1.created_by = u.id
                WHERE t1.org_id = ? 
                AND t1.transfer_pair_id IS NOT NULL
                AND t1.kind = 'transfer_out'
                AND t2.kind = 'transfer_in'
                AND t1.deleted_at IS NULL
            )
            UNION ALL
            (
                SELECT 
                    NULL as transfer_pair_id,
                    tv.data_competencia,
                    tv.data_pagamento,
                    tv.valor,
                    tv.descricao,
                    tv.observacoes,
                    tv.created_at,
                    a1.nome as account_from_name,
                    a1.tipo as account_from_type,
                    vg.titulo as account_to_name,
                    'vault' as account_to_type,
                    u.nome as created_by_name,
                    'vault_deposit' as tipo_operacao
                FROM {$this->table} tv
                INNER JOIN accounts a1 ON tv.account_id = a1.id
                INNER JOIN vault_movements vm ON tv.id = vm.transaction_id
                INNER JOIN vault_goals vg ON vm.vault_goal_id = vg.id
                LEFT JOIN users u ON tv.created_by = u.id
                WHERE tv.org_id = ? 
                AND tv.kind = 'saida'
                AND tv.transfer_pair_id IS NULL
                AND vm.tipo = 'deposito'
                AND tv.deleted_at IS NULL
            )
            ORDER BY data_competencia DESC, created_at DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId, $orgId, $limit]);
        return $stmt->fetchAll();
    }
    
    public function getAccountTransactions($accountId, $startDate, $endDate, $limit, $offset) {
        $sql = "
            SELECT 
                t.*,
                c.nome as category_name,
                c.cor as category_color,
                cc.nome as cost_center_name,
                u.nome as created_by_name,
                -- Para transferências, buscar conta de destino
                CASE 
                    WHEN t.kind = 'transfer_out' THEN (
                        SELECT a2.nome FROM transactions t2 
                        INNER JOIN accounts a2 ON t2.account_id = a2.id 
                        WHERE t2.transfer_pair_id = t.transfer_pair_id 
                        AND t2.id != t.id LIMIT 1
                    )
                    WHEN t.kind = 'transfer_in' THEN (
                        SELECT a2.nome FROM transactions t2 
                        INNER JOIN accounts a2 ON t2.account_id = a2.id 
                        WHERE t2.transfer_pair_id = t.transfer_pair_id 
                        AND t2.id != t.id LIMIT 1
                    )
                    ELSE NULL
                END as transfer_account_name
            FROM transactions t
            LEFT JOIN categories c ON t.category_id = c.id
            LEFT JOIN cost_centers cc ON t.cost_center_id = cc.id
            LEFT JOIN users u ON t.created_by = u.id
            WHERE t.account_id = ?
            AND t.data_competencia BETWEEN ? AND ?
            AND t.deleted_at IS NULL
            ORDER BY t.data_competencia DESC, t.created_at DESC
            LIMIT ? OFFSET ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$accountId, $startDate, $endDate, $limit, $offset]);
        return $stmt->fetchAll();
    }
    
    public function getBalanceBeforeDate($accountId, $date) {
        $sql = "
            SELECT 
                COALESCE(SUM(
                    CASE 
                        WHEN kind IN ('entrada', 'transfer_in') THEN valor
                        WHEN kind IN ('saida', 'transfer_out') THEN -valor
                        ELSE 0
                    END
                ), 0) as balance
            FROM transactions
            WHERE account_id = ?
            AND data_competencia < ?
            AND status = 'confirmado'
            AND deleted_at IS NULL
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$accountId, $date]);
        $result = $stmt->fetch();
        return $result['balance'] ?? 0;
    }
    
    public function getAccountPeriodTotals($accountId, $startDate, $endDate) {
        $sql = "
            SELECT 
                SUM(CASE WHEN kind IN ('entrada', 'transfer_in') AND status = 'confirmado' THEN valor ELSE 0 END) as total_entradas,
                SUM(CASE WHEN kind IN ('saida', 'transfer_out') AND status = 'confirmado' THEN valor ELSE 0 END) as total_saidas,
                COUNT(*) as total_transactions,
                SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as pending_transactions
            FROM transactions
            WHERE account_id = ?
            AND data_competencia BETWEEN ? AND ?
            AND deleted_at IS NULL
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$accountId, $startDate, $endDate]);
        $result = $stmt->fetch();
        
        $result['saldo_liquido'] = ($result['total_entradas'] ?? 0) - ($result['total_saidas'] ?? 0);
        
        return $result;
    }
    
    public function countAccountTransactions($accountId, $startDate, $endDate) {
        $sql = "
            SELECT COUNT(*) as total
            FROM transactions
            WHERE account_id = ?
            AND data_competencia BETWEEN ? AND ?
            AND deleted_at IS NULL
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$accountId, $startDate, $endDate]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}