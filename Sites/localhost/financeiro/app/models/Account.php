<?php
require_once 'BaseModel.php';

class Account extends BaseModel {
    protected $table = 'accounts';
    
    public function getAccountsByOrg($orgId) {
        $sql = "
            SELECT a.*, u.nome as created_by_name
            FROM {$this->table} a
            LEFT JOIN users u ON a.created_by = u.id
            WHERE a.org_id = ? AND a.deleted_at IS NULL
            ORDER BY a.pessoa_tipo ASC, a.ativo DESC, a.nome ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId]);
        return $stmt->fetchAll();
    }
    
    public function getAccountsByOrgAndType($orgId, $pessoaTipo) {
        $sql = "
            SELECT a.*, u.nome as created_by_name
            FROM {$this->table} a
            LEFT JOIN users u ON a.created_by = u.id
            WHERE a.org_id = ? AND a.pessoa_tipo = ? AND a.deleted_at IS NULL
            ORDER BY a.ativo DESC, a.nome ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId, $pessoaTipo]);
        return $stmt->fetchAll();
    }
    
    public function getTotalBalanceByType($orgId) {
        $sql = "
            SELECT pessoa_tipo, SUM(saldo_atual) as total, COUNT(*) as quantidade
            FROM {$this->table}
            WHERE org_id = ? AND ativo = 1 AND deleted_at IS NULL
            GROUP BY pessoa_tipo
            ORDER BY pessoa_tipo ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId]);
        return $stmt->fetchAll();
    }
    
    public function getActiveAccountsByOrg($orgId) {
        $sql = "
            SELECT *
            FROM {$this->table}
            WHERE org_id = ? AND ativo = 1 AND deleted_at IS NULL
            ORDER BY nome ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId]);
        return $stmt->fetchAll();
    }
    
    public function getAccountsByType($orgId, $tipo) {
        $sql = "
            SELECT a.*, u.nome as created_by_name
            FROM {$this->table} a
            LEFT JOIN users u ON a.created_by = u.id
            WHERE a.org_id = ? AND a.tipo = ? AND a.deleted_at IS NULL
            ORDER BY a.ativo DESC, a.nome ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId, $tipo]);
        return $stmt->fetchAll();
    }
    
    public function getTotalBalance($orgId) {
        $sql = "
            SELECT SUM(saldo_atual) as total
            FROM {$this->table}
            WHERE org_id = ? AND ativo = 1 AND deleted_at IS NULL
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    public function getBalanceByAccountType($orgId) {
        $sql = "
            SELECT tipo, SUM(saldo_atual) as total, COUNT(*) as quantidade
            FROM {$this->table}
            WHERE org_id = ? AND ativo = 1 AND deleted_at IS NULL
            GROUP BY tipo
            ORDER BY total DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId]);
        return $stmt->fetchAll();
    }
    
    public function updateBalance($accountId, $newBalance) {
        $sql = "UPDATE {$this->table} SET saldo_atual = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$newBalance, $accountId]);
    }
    
    public function adjustBalance($accountId, $amount) {
        $sql = "UPDATE {$this->table} SET saldo_atual = saldo_atual + ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$amount, $accountId]);
    }
    
    public function recalculateBalance($accountId) {
        // Buscar saldo inicial da conta
        $account = $this->findById($accountId);
        if (!$account) {
            return false;
        }
        
        $saldoInicial = $account['saldo_inicial'];
        
        // Somar todas as transações CONFIRMADAS
        $sql = "
            SELECT 
                SUM(CASE WHEN kind IN ('entrada', 'transfer_in') THEN valor ELSE 0 END) as entradas,
                SUM(CASE WHEN kind IN ('saida', 'transfer_out') THEN valor ELSE 0 END) as saidas
            FROM transactions 
            WHERE account_id = ? 
            AND status = 'confirmado' 
            AND deleted_at IS NULL
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$accountId]);
        $result = $stmt->fetch();
        
        $entradas = $result['entradas'] ?? 0;
        $saidas = $result['saidas'] ?? 0;
        $saldoCalculado = $saldoInicial + $entradas - $saidas;
        
        // Atualizar saldo atual
        $updateSql = "UPDATE {$this->table} SET saldo_atual = ?, updated_at = NOW() WHERE id = ?";
        $updateStmt = $this->db->prepare($updateSql);
        return $updateStmt->execute([$saldoCalculado, $accountId]);
    }
    
    public function getAccountTypes() {
        return [
            'corrente' => ['nome' => 'Conta Corrente', 'icone' => 'fas fa-university', 'cor' => '#007bff'],
            'poupanca' => ['nome' => 'Conta Poupança', 'icone' => 'fas fa-piggy-bank', 'cor' => '#28a745'],
            'carteira' => ['nome' => 'Carteira/Dinheiro', 'icone' => 'fas fa-wallet', 'cor' => '#ffc107'],
            'cartao_prepago' => ['nome' => 'Cartão Pré-pago', 'icone' => 'fas fa-credit-card', 'cor' => '#17a2b8'],
            'vault' => ['nome' => 'Cofre/Reserva', 'icone' => 'fas fa-vault', 'cor' => '#6f42c1']
        ];
    }
    
    public function createAccount($data) {
        // Se não informou saldo inicial, usar 0
        if (!isset($data['saldo_inicial'])) {
            $data['saldo_inicial'] = 0;
        }
        
        // Saldo atual começa igual ao inicial
        $data['saldo_atual'] = $data['saldo_inicial'];
        
        // Cor padrão se não informada
        if (!isset($data['cor'])) {
            $types = $this->getAccountTypes();
            $data['cor'] = $types[$data['tipo']]['cor'] ?? '#007bff';
        }
        
        return $this->create($data);
    }
}