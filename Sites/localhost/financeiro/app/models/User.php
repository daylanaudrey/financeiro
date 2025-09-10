<?php
require_once 'BaseModel.php';

class User extends BaseModel {
    protected $table = 'users';
    
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ? AND deleted_at IS NULL");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    public function verifyPassword($password, $hashedPassword) {
        return password_verify($password, $hashedPassword);
    }
    
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    public function createUser($data) {
        // Handle both 'senha' and 'password' for compatibility
        if (isset($data['senha'])) {
            $data['password'] = $this->hashPassword($data['senha']);
            unset($data['senha']);
        } elseif (isset($data['password']) && !str_starts_with($data['password'], '$2y$')) {
            $data['password'] = $this->hashPassword($data['password']);
        }
        
        return $this->create($data);
    }
    
    public function getUserWithOrganizations($userId) {
        $sql = "
            SELECT u.*, 
                   GROUP_CONCAT(CONCAT(o.id, ':', o.nome, ':', ur.role) SEPARATOR '|') as organizations
            FROM users u
            LEFT JOIN user_org_roles ur ON u.id = ur.user_id
            LEFT JOIN organizations o ON ur.org_id = o.id AND o.deleted_at IS NULL
            WHERE u.id = ? AND u.deleted_at IS NULL
            GROUP BY u.id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if ($user && $user['organizations']) {
            $orgs = [];
            foreach (explode('|', $user['organizations']) as $orgData) {
                if ($orgData) {
                    [$id, $nome, $role] = explode(':', $orgData);
                    $orgs[] = ['id' => $id, 'nome' => $nome, 'role' => $role];
                }
            }
            $user['organizations'] = $orgs;
        } else {
            $user['organizations'] = [];
        }
        
        return $user;
    }
    
    public function updateLastLogin($userId) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$userId]);
    }
}