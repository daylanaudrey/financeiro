<?php
require_once 'BaseModel.php';

class Category extends BaseModel {
    protected $table = 'categories';
    
    public function getCategoriesByOrg($orgId) {
        $sql = "
            SELECT c.*, 
                   u.nome as created_by_name,
                   COUNT(t.id) as transaction_count
            FROM {$this->table} c
            LEFT JOIN users u ON c.created_by = u.id
            LEFT JOIN transactions t ON c.id = t.category_id AND t.deleted_at IS NULL
            WHERE c.org_id = ? AND c.deleted_at IS NULL
            GROUP BY c.id
            ORDER BY c.nome ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId]);
        return $stmt->fetchAll();
    }
    
    public function getCategoriesByType($orgId, $tipo = null) {
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
    
    public function getActiveCategories($orgId) {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE org_id = ? AND ativo = 1 AND deleted_at IS NULL
            ORDER BY nome ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId]);
        return $stmt->fetchAll();
    }
    
    public function createCategory($data) {
        return $this->create($data);
    }
    
    public function updateCategory($id, $data) {
        return $this->update($id, $data);
    }
    
    public function deleteCategory($id) {
        // Verificar se há transações vinculadas
        $sql = "SELECT COUNT(*) as count FROM transactions WHERE category_id = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            throw new Exception("Não é possível excluir esta categoria pois existem lançamentos vinculados a ela.");
        }
        
        return $this->delete($id);
    }
    
    public function getTypeOptions() {
        return [
            'receita' => ['nome' => 'Receita', 'cor' => '#28a745', 'icone' => 'fas fa-arrow-up'],
            'despesa' => ['nome' => 'Despesa', 'cor' => '#dc3545', 'icone' => 'fas fa-arrow-down'],
            'transferencia' => ['nome' => 'Transferência', 'cor' => '#17a2b8', 'icone' => 'fas fa-exchange-alt'],
            'geral' => ['nome' => 'Geral', 'cor' => '#6c757d', 'icone' => 'fas fa-tag']
        ];
    }
    
    public function createDefaultCategories($orgId, $userId) {
        $defaultCategories = [
            // Receitas
            ['nome' => 'Salário', 'tipo' => 'receita', 'cor' => '#28a745', 'icone' => 'fas fa-money-bill-wave'],
            ['nome' => 'Freelance', 'tipo' => 'receita', 'cor' => '#20c997', 'icone' => 'fas fa-laptop-code'],
            ['nome' => 'Investimentos', 'tipo' => 'receita', 'cor' => '#17a2b8', 'icone' => 'fas fa-chart-line'],
            ['nome' => 'Vendas', 'tipo' => 'receita', 'cor' => '#fd7e14', 'icone' => 'fas fa-shopping-cart'],
            
            // Despesas
            ['nome' => 'Alimentação', 'tipo' => 'despesa', 'cor' => '#dc3545', 'icone' => 'fas fa-utensils'],
            ['nome' => 'Transporte', 'tipo' => 'despesa', 'cor' => '#6f42c1', 'icone' => 'fas fa-car'],
            ['nome' => 'Moradia', 'tipo' => 'despesa', 'cor' => '#e83e8c', 'icone' => 'fas fa-home'],
            ['nome' => 'Saúde', 'tipo' => 'despesa', 'cor' => '#fd7e14', 'icone' => 'fas fa-heartbeat'],
            ['nome' => 'Educação', 'tipo' => 'despesa', 'cor' => '#20c997', 'icone' => 'fas fa-graduation-cap'],
            ['nome' => 'Lazer', 'tipo' => 'despesa', 'cor' => '#ffc107', 'icone' => 'fas fa-gamepad'],
            ['nome' => 'Compras', 'tipo' => 'despesa', 'cor' => '#6c757d', 'icone' => 'fas fa-shopping-bag'],
            ['nome' => 'Contas', 'tipo' => 'despesa', 'cor' => '#495057', 'icone' => 'fas fa-file-invoice-dollar']
        ];
        
        foreach ($defaultCategories as $category) {
            $this->create([
                'nome' => $category['nome'],
                'tipo' => $category['tipo'],
                'cor' => $category['cor'],
                'icone' => $category['icone'],
                'org_id' => $orgId,
                'created_by' => $userId,
                'ativo' => 1
            ]);
        }
    }

    /**
     * Busca categoria por nome e organização
     */
    public function findByName($name, $orgId) {
        $sql = "SELECT * FROM {$this->table} WHERE nome = ? AND org_id = ? AND deleted_at IS NULL LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name, $orgId]);
        return $stmt->fetch();
    }
}