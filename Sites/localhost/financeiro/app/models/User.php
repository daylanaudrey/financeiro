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
        
        // Remove organization_name before saving to users table
        $organizationName = $data['organization_name'] ?? null;
        unset($data['organization_name']);
        
        $userId = $this->create($data);
        
        // Store organization name temporarily for verification process
        if ($userId && $organizationName) {
            $stmt = $this->db->prepare("
                UPDATE users SET temp_org_name = ? WHERE id = ?
            ");
            $stmt->execute([$organizationName, $userId]);
        }
        
        return $userId;
    }
    
    public function authenticate($email, $password) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.*, 
                       GROUP_CONCAT(CONCAT(uor.org_id, ':', uor.role) SEPARATOR '|') as org_roles
                FROM users u
                LEFT JOIN user_org_roles uor ON u.id = uor.user_id
                WHERE u.email = ? AND u.deleted_at IS NULL AND u.status = 'ativo'
                GROUP BY u.id
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Parse org_roles
                $orgRoles = [];
                if ($user['org_roles']) {
                    $roles = explode('|', $user['org_roles']);
                    foreach ($roles as $role) {
                        list($orgId, $roleType) = explode(':', $role);
                        $orgRoles[$orgId] = $roleType;
                    }
                }
                $user['org_roles'] = $orgRoles;
                
                // Log do login
                $this->logActivity($user['id'], 'login', 'Login realizado com sucesso');
                
                unset($user['password']);
                return $user;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Erro na autenticação: " . $e->getMessage());
            return false;
        }
    }
    
    public function isSuperAdmin($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 1 FROM users
                WHERE id = ? AND email = 'daylan@dagsolucaodigital.com.br' AND role = 'admin'
                AND email != 'admin@sistema.com'
            ");
            $stmt->execute([$userId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erro ao verificar super admin: " . $e->getMessage());
            return false;
        }
    }
    
    public function hasPermission($userId, $orgId, $module, $permission) {
        try {
            // Super admin tem todas as permissões
            if ($this->isSuperAdmin($userId)) {
                return true;
            }
            
            $stmt = $this->db->prepare("
                SELECT 1 FROM user_permissions up
                JOIN user_org_roles uor ON up.user_id = uor.user_id AND up.org_id = uor.org_id
                WHERE up.user_id = ? AND up.org_id = ? 
                  AND up.module = ? AND up.permission = ? AND up.granted = TRUE
            ");
            $stmt->execute([$userId, $orgId, $module, $permission]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erro ao verificar permissão: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUserPermissions($userId, $orgId) {
        try {
            $stmt = $this->db->prepare("
                SELECT module, permission, granted
                FROM user_permissions
                WHERE user_id = ? AND org_id = ? AND granted = TRUE
            ");
            $stmt->execute([$userId, $orgId]);
            
            $permissions = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!isset($permissions[$row['module']])) {
                    $permissions[$row['module']] = [];
                }
                $permissions[$row['module']][] = $row['permission'];
            }
            
            return $permissions;
        } catch (PDOException $e) {
            error_log("Erro ao buscar permissões: " . $e->getMessage());
            return [];
        }
    }
    
    public function getOrganizations($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT o.*, uor.role, os.status as subscription_status
                FROM organizations o
                JOIN user_org_roles uor ON o.id = uor.org_id
                LEFT JOIN organization_subscriptions os ON o.id = os.org_id 
                    AND os.status IN ('trial', 'active')
                WHERE uor.user_id = ? AND o.deleted_at IS NULL
                ORDER BY o.nome
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar organizações: " . $e->getMessage());
            return [];
        }
    }
    
    public function getAllOrganizations() {
        try {
            // Primeiro tenta com todas as tabelas (subscription system)
            $stmt = $this->db->prepare("
                SELECT o.*, 
                       COUNT(DISTINCT uor.user_id) as user_count,
                       os.status as subscription_status,
                       sp.nome as plan_name,
                       os.current_period_end,
                       os.trial_ends_at
                FROM organizations o
                LEFT JOIN user_org_roles uor ON o.id = uor.org_id
                LEFT JOIN organization_subscriptions os ON o.id = os.org_id 
                    AND os.status IN ('trial', 'active', 'suspended', 'expired')
                LEFT JOIN subscription_plans sp ON os.plan_id = sp.id
                WHERE o.deleted_at IS NULL
                GROUP BY o.id
                ORDER BY o.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar organizações com subscription: " . $e->getMessage());
            
            // Fallback: buscar apenas organizações básicas se tabelas de subscription não existem
            try {
                $stmt = $this->db->prepare("
                    SELECT o.*, 
                           COUNT(DISTINCT uor.user_id) as user_count,
                           'active' as subscription_status,
                           'Sistema Básico' as plan_name,
                           NULL as current_period_end,
                           NULL as trial_ends_at
                    FROM organizations o
                    LEFT JOIN user_org_roles uor ON o.id = uor.org_id
                    WHERE o.deleted_at IS NULL
                    GROUP BY o.id
                    ORDER BY o.created_at DESC
                ");
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e2) {
                error_log("Erro ao buscar organizações básicas: " . $e2->getMessage());
                return [];
            }
        }
    }
    
    public function addToOrganization($userId, $orgId, $role) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_org_roles (user_id, org_id, role, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$userId, $orgId, $role]);
            
            // As permissões são criadas automaticamente via trigger
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao adicionar usuário à organização: " . $e->getMessage());
            return false;
        }
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
    
    public function logActivity($userId, $action, $description, $entityId = null, $entity = 'user') {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO audit_logs (user_id, entity, entity_id, action, description, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            
            $stmt->execute([
                $userId, $entity, $entityId, $action, $description, $ipAddress, $userAgent
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao registrar log: " . $e->getMessage());
        }
    }
    
    public function findByVerificationToken($token) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM users 
                WHERE email_verification_token = ? 
                AND email_verified_at IS NULL 
                AND deleted_at IS NULL
            ");
            $stmt->execute([$token]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar usuário por token: " . $e->getMessage());
            return false;
        }
    }
    
    public function verifyUserEmail($userId) {
        try {
            $this->db->beginTransaction();
            
            // Buscar dados do usuário incluindo temp_org_name
            $stmt = $this->db->prepare("
                SELECT id, nome, email, temp_org_name 
                FROM users 
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                $this->db->rollBack();
                return false;
            }
            
            // Atualizar status do usuário
            $stmt = $this->db->prepare("
                UPDATE users 
                SET email_verified_at = NOW(), 
                    email_verification_token = NULL,
                    status = 'ativo',
                    temp_org_name = NULL,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
            
            // Criar organização automaticamente se temp_org_name existe
            if ($user['temp_org_name']) {
                $orgId = $this->createOrganizationForUser($userId, $user['temp_org_name'], $user['nome'], $user['email']);
                
                if ($orgId) {
                    // Associar usuário à organização como admin
                    $this->addToOrganization($userId, $orgId, 'admin');
                    error_log("Organização '{$user['temp_org_name']}' criada automaticamente para usuário {$userId}");
                }
            }
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Erro ao verificar email: " . $e->getMessage());
            return false;
        }
    }
    
    private function createOrganizationForUser($userId, $orgName, $userName, $userEmail) {
        try {
            // Criar a organização
            $stmt = $this->db->prepare("
                INSERT INTO organizations (nome, email, status, created_by, created_at) 
                VALUES (?, ?, 'ativo', ?, NOW())
            ");
            $stmt->execute([$orgName, $userEmail, $userId]);
            $orgId = $this->db->lastInsertId();
            
            if ($orgId) {
                // Log da criação automática da organização
                try {
                    require_once __DIR__ . '/AuditLog.php';
                    $auditModel = new AuditLog($this->db);
                    $auditModel->logUserAction(
                        $userId,
                        $orgId,
                        'organization',
                        'auto_create',
                        $orgId,
                        null,
                        [
                            'org_name' => $orgName,
                            'org_email' => $userEmail,
                            'trigger' => 'email_verification'
                        ],
                        "Organização '{$orgName}' criada automaticamente durante verificação de email do usuário {$userName}"
                    );
                } catch (Exception $e) {
                    error_log("Erro ao logar criação da organização: " . $e->getMessage());
                }
                
                error_log("Organização criada automaticamente: ID {$orgId}, Nome: {$orgName}, Usuário: {$userName}");
                return $orgId;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Erro ao criar organização para usuário: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAllOrganizationsWithUsers() {
        try {
            // Tenta buscar com subscription tables primeiro
            $stmt = $this->db->prepare("
                SELECT o.id, o.nome, o.created_at,
                       os.status as subscription_status,
                       sp.nome as plan_name,
                       COUNT(DISTINCT t.id) as transaction_count
                FROM organizations o
                LEFT JOIN organization_subscriptions os ON o.id = os.org_id AND os.status IN ('trial', 'active')
                LEFT JOIN subscription_plans sp ON os.plan_id = sp.id
                LEFT JOIN transactions t ON o.id = t.org_id AND t.deleted_at IS NULL
                WHERE o.deleted_at IS NULL
                GROUP BY o.id, o.nome, o.created_at, os.status, sp.nome
                ORDER BY o.nome ASC
            ");
            $stmt->execute();
            $organizations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar organizações com subscription: " . $e->getMessage());
            
            // Fallback: buscar sem subscription tables
            try {
                $stmt = $this->db->prepare("
                    SELECT o.id, o.nome, o.created_at,
                           'active' as subscription_status,
                           'Sistema Básico' as plan_name,
                           COUNT(DISTINCT t.id) as transaction_count
                    FROM organizations o
                    LEFT JOIN transactions t ON o.id = t.org_id AND t.deleted_at IS NULL
                    WHERE o.deleted_at IS NULL
                    GROUP BY o.id, o.nome, o.created_at
                    ORDER BY o.nome ASC
                ");
                $stmt->execute();
                $organizations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e2) {
                error_log("Erro ao buscar organizações básicas: " . $e2->getMessage());
                return [];
            }
        }
        
        // Para cada organização, buscar os usuários
        foreach ($organizations as &$org) {
            try {
                $stmt = $this->db->prepare("
                    SELECT u.id, u.nome, u.email, u.role, u.status, u.updated_at as last_login,
                           uor.role as org_role
                    FROM users u
                    JOIN user_org_roles uor ON u.id = uor.user_id
                    WHERE uor.org_id = ? AND u.deleted_at IS NULL
                    ORDER BY u.nome ASC
                ");
                $stmt->execute([$org['id']]);
                $org['users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Erro ao buscar usuários da organização {$org['id']}: " . $e->getMessage());
                $org['users'] = [];
            }
        }
        
        return $organizations;
    }
    
    public function updateVerificationToken($userId, $token) {
        $sql = "UPDATE users SET email_verification_token = ?, email_verification_sent_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$token, $userId]);
    }
    
    public function updateUser($userId, $data) {
        try {
            $fields = [];
            $values = [];
            
            foreach ($data as $key => $value) {
                $fields[] = "{$key} = ?";
                $values[] = $value;
            }
            
            $values[] = $userId;
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar usuário: " . $e->getMessage());
            return false;
        }
    }
    
    public function verifyUserPassword($userId, $password) {
        try {
            $stmt = $this->db->prepare("SELECT password FROM {$this->table} WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if ($user) {
                return password_verify($password, $user['password']);
            }
            return false;
        } catch (PDOException $e) {
            error_log("Erro ao verificar senha: " . $e->getMessage());
            return false;
        }
    }
    
    public function updatePassword($userId, $newPassword) {
        try {
            $hashedPassword = $this->hashPassword($newPassword);
            $stmt = $this->db->prepare("UPDATE {$this->table} SET password = ?, updated_at = NOW() WHERE id = ?");
            return $stmt->execute([$hashedPassword, $userId]);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar senha: " . $e->getMessage());
            return false;
        }
    }
}