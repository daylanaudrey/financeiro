<?php
require_once 'BaseModel.php';

class Subscription extends BaseModel {
    protected $table = 'organization_subscriptions';
    
    public function getAllSubscriptions() {
        try {
            $stmt = $this->db->prepare("
                SELECT os.*, os.status as subscription_status, o.nome as org_name, sp.nome as plan_name, sp.preco,
                       COUNT(DISTINCT uor.user_id) as user_count,
                       COUNT(DISTINCT t.id) as transaction_count
                FROM organization_subscriptions os
                JOIN organizations o ON os.org_id = o.id
                JOIN subscription_plans sp ON os.plan_id = sp.id
                LEFT JOIN user_org_roles uor ON o.id = uor.org_id
                LEFT JOIN transactions t ON o.id = t.org_id 
                    AND t.deleted_at IS NULL 
                    AND t.created_at >= os.current_period_start
                WHERE o.deleted_at IS NULL
                GROUP BY os.id
                ORDER BY os.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar assinaturas: " . $e->getMessage());
            return [];
        }
    }
    
    public function getAllPlans() {
        try {
            $stmt = $this->db->prepare("
                SELECT sp.id, sp.nome, sp.slug, sp.preco, sp.max_usuarios, sp.max_transacoes, 
                       sp.trial_days, sp.ativo, sp.created_at, sp.updated_at,
                       COUNT(os.id) as subscription_count
                FROM subscription_plans sp
                LEFT JOIN organization_subscriptions os ON sp.id = os.plan_id 
                    AND os.status IN ('trial', 'active')
                WHERE sp.ativo = TRUE
                GROUP BY sp.id, sp.nome, sp.slug, sp.preco, sp.max_usuarios, sp.max_transacoes, 
                         sp.trial_days, sp.ativo, sp.created_at, sp.updated_at
                ORDER BY sp.preco ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar planos: " . $e->getMessage());
            return [];
        }
    }
    
    public function getSubscriptionByOrg($orgId) {
        try {
            $stmt = $this->db->prepare("
                SELECT os.*, sp.*,
                       sp.nome as plan_name,
                       sp.max_usuarios,
                       sp.max_transacoes
                FROM organization_subscriptions os
                JOIN subscription_plans sp ON os.plan_id = sp.id
                WHERE os.org_id = ? AND os.status IN ('trial', 'active')
                ORDER BY os.created_at DESC
                LIMIT 1
            ");
            $stmt->execute([$orgId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar assinatura: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateSubscription($orgId, $planId, $status) {
        try {
            $this->db->beginTransaction();
            
            // Cancelar assinatura atual
            $stmt = $this->db->prepare("
                UPDATE organization_subscriptions 
                SET status = 'cancelled', cancelled_at = NOW(), updated_at = NOW()
                WHERE org_id = ? AND status IN ('trial', 'active')
            ");
            $stmt->execute([$orgId]);
            
            // Criar nova assinatura
            $stmt = $this->db->prepare("
                INSERT INTO organization_subscriptions 
                (org_id, plan_id, status, current_period_start, current_period_end)
                VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH))
            ");
            $stmt->execute([$orgId, $planId, $status]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Erro ao atualizar assinatura: " . $e->getMessage());
            return false;
        }
    }
    
    public function changePlan($orgId, $newPlanId) {
        try {
            $this->db->beginTransaction();
            
            // Buscar assinatura atual
            $currentSubscription = $this->getSubscriptionByOrg($orgId);
            
            if (!$currentSubscription) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Assinatura não encontrada'];
            }
            
            // Verificar se o novo plano comporta o uso atual
            $usageReport = $this->getUsageReport($orgId);
            $newPlan = $this->getPlan($newPlanId);
            
            if ($newPlan['max_usuarios'] && $usageReport['uso_usuarios'] > $newPlan['max_usuarios']) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Novo plano não comporta quantidade atual de usuários'];
            }
            
            if ($newPlan['max_transacoes'] && $usageReport['uso_transacoes'] > $newPlan['max_transacoes']) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Novo plano não comporta quantidade atual de transações'];
            }
            
            // Atualizar plano
            $stmt = $this->db->prepare("
                UPDATE organization_subscriptions 
                SET plan_id = ?, updated_at = NOW()
                WHERE org_id = ? AND status IN ('trial', 'active')
            ");
            $stmt->execute([$newPlanId, $orgId]);
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Plano alterado com sucesso'];
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Erro ao alterar plano: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno do servidor'];
        }
    }
    
    public function getPlan($planId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM subscription_plans WHERE id = ? AND ativo = TRUE");
            $stmt->execute([$planId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar plano: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUsageReport($orgId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM organization_usage_report WHERE org_id = ?");
            $stmt->execute([$orgId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar relatório de uso: " . $e->getMessage());
            return false;
        }
    }
    
    public function createPayment($subscriptionId, $valor, $dueDate, $paymentMethod = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO subscription_payments 
                (subscription_id, valor, due_date, payment_method, status)
                VALUES (?, ?, ?, ?, 'pending')
            ");
            return $stmt->execute([$subscriptionId, $valor, $dueDate, $paymentMethod]);
        } catch (PDOException $e) {
            error_log("Erro ao criar cobrança: " . $e->getMessage());
            return false;
        }
    }
    
    public function markPaymentAsPaid($paymentId, $externalId = null) {
        try {
            $stmt = $this->db->prepare("
                UPDATE subscription_payments 
                SET status = 'paid', paid_at = NOW(), external_id = ?, updated_at = NOW()
                WHERE id = ?
            ");
            return $stmt->execute([$externalId, $paymentId]);
        } catch (PDOException $e) {
            error_log("Erro ao marcar pagamento como pago: " . $e->getMessage());
            return false;
        }
    }
    
    public function expireTrials() {
        try {
            $stmt = $this->db->prepare("CALL ExpireTrials()");
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao expirar trials: " . $e->getMessage());
            return false;
        }
    }
    
    public function renewSubscriptions() {
        try {
            $stmt = $this->db->prepare("CALL RenewSubscriptions()");
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao renovar assinaturas: " . $e->getMessage());
            return false;
        }
    }
    
    public function getRevenueReport($startDate = null, $endDate = null) {
        try {
            $startDate = $startDate ?? date('Y-m-01');
            $endDate = $endDate ?? date('Y-m-t');
            
            $stmt = $this->db->prepare("
                SELECT 
                    sp.nome as plan_name,
                    COUNT(DISTINCT os.id) as subscription_count,
                    SUM(sp.preco) as total_revenue,
                    AVG(sp.preco) as avg_revenue
                FROM subscription_payments p
                JOIN organization_subscriptions os ON p.subscription_id = os.id
                JOIN subscription_plans sp ON os.plan_id = sp.id
                WHERE p.status = 'paid' 
                  AND p.paid_at BETWEEN ? AND ?
                GROUP BY sp.id, sp.nome
                ORDER BY total_revenue DESC
            ");
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao gerar relatório de receita: " . $e->getMessage());
            return [];
        }
    }
}