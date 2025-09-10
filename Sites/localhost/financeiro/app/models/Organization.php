<?php
require_once 'BaseModel.php';

class Organization extends BaseModel {
    protected $table = 'organizations';
    
    public function getUserOrganizations($userId) {
        $sql = "
            SELECT o.*, uor.role, uor.created_at as joined_at
            FROM {$this->table} o
            INNER JOIN user_org_roles uor ON o.id = uor.org_id
            WHERE uor.user_id = ? AND o.deleted_at IS NULL
            ORDER BY o.nome
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function getUserRole($userId, $orgId) {
        $sql = "
            SELECT role 
            FROM user_org_roles 
            WHERE user_id = ? AND org_id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $orgId]);
        $result = $stmt->fetch();
        return $result ? $result['role'] : null;
    }
    
    public function addUserToOrg($userId, $orgId, $role = 'financeiro') {
        $sql = "
            INSERT INTO user_org_roles (user_id, org_id, role, created_at)
            VALUES (?, ?, ?, NOW())
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId, $orgId, $role]);
    }
    
    public function removeUserFromOrg($userId, $orgId) {
        $sql = "DELETE FROM user_org_roles WHERE user_id = ? AND org_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId, $orgId]);
    }
    
    public function getOrgUsers($orgId) {
        $sql = "
            SELECT u.*, uor.role, uor.created_at as joined_at
            FROM users u
            INNER JOIN user_org_roles uor ON u.id = uor.user_id
            WHERE uor.org_id = ? AND u.deleted_at IS NULL
            ORDER BY u.nome
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId]);
        return $stmt->fetchAll();
    }
    
    public function createWithOwner($data, $ownerId) {
        $this->db->beginTransaction();
        
        try {
            // Criar organização
            $orgId = $this->create($data);
            if (!$orgId) {
                throw new Exception("Erro ao criar organização");
            }
            
            // Adicionar criador como admin
            $this->addUserToOrg($ownerId, $orgId, 'admin');
            
            $this->db->commit();
            return $orgId;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getRoles() {
        return [
            'admin' => [
                'nome' => 'Administrador',
                'descricao' => 'Acesso total: gerencia usuários, contas, e todas as funcionalidades',
                'cor' => '#dc3545'
            ],
            'financeiro' => [
                'nome' => 'Financeiro',
                'descricao' => 'Gerencia lançamentos, recorrências, cadastros. Sem gerenciar usuários',
                'cor' => '#0d6efd'
            ],
            'operador' => [
                'nome' => 'Operador',
                'descricao' => 'Cria lançamentos e vê saldos. Sem excluir ou relatórios detalhados',
                'cor' => '#198754'
            ],
            'leitor' => [
                'nome' => 'Leitor',
                'descricao' => 'Somente leitura: extratos e relatórios',
                'cor' => '#6c757d'
            ]
        ];
    }
}