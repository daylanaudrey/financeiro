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

    public function getTeamMembers($orgId) {
        $sql = "
            SELECT u.id, u.nome, u.email, u.telefone, u.status, u.created_at,
                   uor.role, uor.created_at as joined_at
            FROM users u
            INNER JOIN user_org_roles uor ON u.id = uor.user_id
            WHERE uor.org_id = ? AND u.deleted_at IS NULL
            ORDER BY u.nome
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId]);
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
    
    public function createWithTrial($data) {
        try {
            $this->db->beginTransaction();
            
            // Criar organização
            $orgId = $this->create($data);
            
            if ($orgId) {
                // Criar assinatura trial
                $stmt = $this->db->prepare("
                    INSERT INTO organization_subscriptions 
                    (org_id, plan_id, status, trial_ends_at, current_period_start, current_period_end)
                    VALUES (?, 1, 'trial', DATE_ADD(NOW(), INTERVAL 7 DAY), NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH))
                ");
                $stmt->execute([$orgId]);
                
                $this->db->commit();
                return $orgId;
            } else {
                $this->db->rollback();
                return false;
            }
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Erro ao criar organização com trial: " . $e->getMessage());
            return false;
        }
    }
    
    public function getSubscriptionInfo($orgId) {
        try {
            $stmt = $this->db->prepare("
                SELECT os.*, sp.nome as plan_name, sp.preco, sp.max_usuarios, sp.max_transacoes
                FROM organization_subscriptions os
                JOIN subscription_plans sp ON os.plan_id = sp.id
                WHERE os.org_id = ? AND os.status IN ('trial', 'active')
                ORDER BY os.created_at DESC
                LIMIT 1
            ");
            $stmt->execute([$orgId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar informações de assinatura: " . $e->getMessage());
            return false;
        }
    }
    
    public function canCreateUser($orgId) {
        try {
            // Buscar informações da assinatura atual
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(DISTINCT u.id) as user_count,
                    sp.max_usuarios,
                    os.status
                FROM organization_subscriptions os
                JOIN subscription_plans sp ON os.plan_id = sp.id
                LEFT JOIN user_org_roles uor ON os.org_id = uor.org_id
                LEFT JOIN users u ON uor.user_id = u.id AND u.deleted_at IS NULL
                WHERE os.org_id = ? AND os.status IN ('trial', 'active')
                GROUP BY sp.max_usuarios, os.status
            ");
            $stmt->execute([$orgId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result || !in_array($result['status'], ['trial', 'active'])) {
                return false;
            }
            
            // Se plano tem limite ilimitado (NULL), pode criar
            if ($result['max_usuarios'] === null) {
                return true;
            }
            
            // Verificar se está dentro do limite
            return $result['user_count'] < $result['max_usuarios'];
        } catch (PDOException $e) {
            error_log("Erro ao verificar limite de usuários: " . $e->getMessage());
            return false;
        }
    }
    
    public function canCreateTransaction($orgId) {
        try {
            // Buscar informações da assinatura atual
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(t.id) as transaction_count,
                    sp.max_transacoes,
                    os.status
                FROM organization_subscriptions os
                JOIN subscription_plans sp ON os.plan_id = sp.id
                LEFT JOIN transactions t ON os.org_id = t.org_id 
                    AND t.deleted_at IS NULL 
                    AND t.created_at >= os.current_period_start
                WHERE os.org_id = ? AND os.status IN ('trial', 'active')
                GROUP BY sp.max_transacoes, os.status
            ");
            $stmt->execute([$orgId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result || !in_array($result['status'], ['trial', 'active'])) {
                return false;
            }
            
            // Se plano tem limite ilimitado (NULL), pode criar
            if ($result['max_transacoes'] === null) {
                return true;
            }
            
            // Verificar se está dentro do limite
            return $result['transaction_count'] < $result['max_transacoes'];
        } catch (PDOException $e) {
            error_log("Erro ao verificar limite de transações: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUsageReport($orgId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM organization_usage_report 
                WHERE org_id = ?
            ");
            $stmt->execute([$orgId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar relatório de uso: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateUsage($orgId) {
        try {
            // Buscar ID da assinatura e início do período
            $stmt = $this->db->prepare("
                SELECT os.id, os.current_period_start 
                FROM organization_subscriptions os
                WHERE os.org_id = ? AND os.status IN ('trial', 'active')
                LIMIT 1
            ");
            $stmt->execute([$orgId]);
            $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$subscription) {
                return false;
            }
            
            // Contar usuários ativos
            $stmt = $this->db->prepare("
                SELECT COUNT(DISTINCT u.id) as user_count
                FROM user_org_roles uor
                JOIN users u ON uor.user_id = u.id
                WHERE uor.org_id = ? AND u.deleted_at IS NULL
            ");
            $stmt->execute([$orgId]);
            $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['user_count'] ?? 0;
            
            // Contar transações do período atual
            $stmt = $this->db->prepare("
                SELECT COUNT(t.id) as transaction_count
                FROM transactions t
                WHERE t.org_id = ? 
                  AND t.deleted_at IS NULL 
                  AND t.created_at >= ?
            ");
            $stmt->execute([$orgId, $subscription['current_period_start']]);
            $transactionCount = $stmt->fetch(PDO::FETCH_ASSOC)['transaction_count'] ?? 0;
            
            // Atualizar uso
            $stmt = $this->db->prepare("
                UPDATE organization_subscriptions 
                SET uso_usuarios = ?, uso_transacoes = ?, updated_at = NOW()
                WHERE id = ?
            ");
            return $stmt->execute([$userCount, $transactionCount, $subscription['id']]);
            
        } catch (PDOException $e) {
            error_log("Erro ao atualizar uso: " . $e->getMessage());
            return false;
        }
    }
    
    public function inviteUser($orgId, $email, $role, $invitedBy) {
        try {
            // Verificar se pode criar novo usuário
            if (!$this->canCreateUser($orgId)) {
                return ['success' => false, 'message' => 'Limite de usuários atingido para seu plano'];
            }
            
            // Gerar token único
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            $stmt = $this->db->prepare("
                INSERT INTO organization_invites (org_id, email, role, token, invited_by, expires_at)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$orgId, $email, $role, $token, $invitedBy, $expiresAt])) {
                return [
                    'success' => true, 
                    'message' => 'Convite enviado com sucesso',
                    'token' => $token
                ];
            } else {
                return ['success' => false, 'message' => 'Erro ao criar convite'];
            }
        } catch (PDOException $e) {
            error_log("Erro ao convidar usuário: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno do servidor'];
        }
    }
    
    public function acceptInvite($token, $userData) {
        try {
            $this->db->beginTransaction();
            
            // Verificar convite válido
            $stmt = $this->db->prepare("
                SELECT * FROM organization_invites 
                WHERE token = ? AND expires_at > NOW() AND accepted_at IS NULL
            ");
            $stmt->execute([$token]);
            $invite = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$invite) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Convite inválido ou expirado'];
            }
            
            // Verificar se ainda pode criar usuário
            if (!$this->canCreateUser($invite['org_id'])) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Limite de usuários atingido'];
            }
            
            // Criar usuário
            $userModel = new User($this->db);
            $userId = $userModel->createUser($userData);
            
            if (!$userId) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Erro ao criar usuário'];
            }
            
            // Associar à organização
            if (!$userModel->addToOrganization($userId, $invite['org_id'], $invite['role'])) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Erro ao associar usuário à organização'];
            }
            
            // Marcar convite como aceito
            $stmt = $this->db->prepare("
                UPDATE organization_invites 
                SET accepted_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$invite['id']]);
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Usuário criado e associado com sucesso'];
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Erro ao aceitar convite: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno do servidor'];
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