<?php
require_once 'BaseModel.php';

class Contact extends BaseModel {
    protected $table = 'contacts';
    
    public function getContactsByOrg($orgId) {
        $sql = "
            SELECT c.*, 
                   u.nome as created_by_name,
                   COUNT(t.id) as transaction_count
            FROM {$this->table} c
            LEFT JOIN users u ON c.created_by = u.id
            LEFT JOIN transactions t ON c.id = t.contact_id AND t.deleted_at IS NULL
            WHERE c.org_id = ? AND c.deleted_at IS NULL
            GROUP BY c.id
            ORDER BY c.nome ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId]);
        return $stmt->fetchAll();
    }
    
    public function getContactsByType($orgId, $tipo = null) {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE org_id = ? AND deleted_at IS NULL
        ";
        $params = [$orgId];
        
        if ($tipo) {
            $sql .= " AND tipo = ?";
            $params[] = $tipo;
        }
        
        $sql .= " ORDER BY nome ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getActiveContacts($orgId) {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE org_id = ? AND ativo = 1 AND deleted_at IS NULL
            ORDER BY nome ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId]);
        return $stmt->fetchAll();
    }
    
    public function createContact($data) {
        return $this->create($data);
    }
    
    public function updateContact($id, $data) {
        return $this->update($id, $data);
    }
    
    public function deleteContact($id) {
        // Verificar se há transações vinculadas
        $sql = "SELECT COUNT(*) as count FROM transactions WHERE contact_id = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            throw new Exception("Não é possível excluir este contato pois existem lançamentos vinculados a ele.");
        }
        
        return $this->delete($id);
    }
    
    public function getTypeOptions() {
        return [
            'cliente' => ['nome' => 'Cliente', 'cor' => '#28a745', 'icone' => 'fas fa-user-plus'],
            'fornecedor' => ['nome' => 'Fornecedor', 'cor' => '#dc3545', 'icone' => 'fas fa-truck'],
            'funcionario' => ['nome' => 'Funcionário', 'cor' => '#17a2b8', 'icone' => 'fas fa-user-tie'],
            'parceiro' => ['nome' => 'Parceiro', 'cor' => '#ffc107', 'icone' => 'fas fa-handshake'],
            'outros' => ['nome' => 'Outros', 'cor' => '#6c757d', 'icone' => 'fas fa-user']
        ];
    }
}