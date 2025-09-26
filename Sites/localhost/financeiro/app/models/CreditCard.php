<?php
require_once 'BaseModel.php';

class CreditCard extends BaseModel {
    protected $table = 'credit_cards';
    
    public function getByOrganization($orgId) {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, u.nome as created_by_name,
                       (c.limite_total - c.limite_usado) as limite_disponivel,
                       ROUND((c.limite_usado / c.limite_total) * 100, 2) as percentual_uso
                FROM {$this->table} c
                LEFT JOIN users u ON c.created_by = u.id
                WHERE c.org_id = ? AND c.deleted_at IS NULL
                ORDER BY c.ativo DESC, c.nome ASC
            ");
            $stmt->execute([$orgId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar cartões: " . $e->getMessage());
            return [];
        }
    }
    
    public function getActiveByOrganization($orgId) {
        try {
            $stmt = $this->db->prepare("
                SELECT *, (limite_total - limite_usado) as limite_disponivel
                FROM {$this->table}
                WHERE org_id = ? AND ativo = 1 AND deleted_at IS NULL
                ORDER BY nome ASC
            ");
            $stmt->execute([$orgId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar cartões ativos: " . $e->getMessage());
            return [];
        }
    }
    
    public function createCard($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO {$this->table} (
                    org_id, nome, bandeira, limite_total, dia_vencimento, dia_fechamento,
                    banco, ultimos_digitos, cor, observacoes, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['org_id'],
                $data['nome'],
                $data['bandeira'],
                $data['limite_total'],
                $data['dia_vencimento'],
                $data['dia_fechamento'],
                $data['banco'],
                $data['ultimos_digitos'],
                $data['cor'],
                $data['observacoes'],
                $data['created_by']
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erro ao criar cartão: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateCard($id, $data) {
        try {
            $stmt = $this->db->prepare("
                UPDATE {$this->table} SET
                    nome = ?, bandeira = ?, limite_total = ?, dia_vencimento = ?,
                    dia_fechamento = ?, banco = ?, ultimos_digitos = ?, cor = ?,
                    observacoes = ?, ativo = ?, updated_at = NOW()
                WHERE id = ? AND deleted_at IS NULL
            ");
            
            return $stmt->execute([
                $data['nome'],
                $data['bandeira'],
                $data['limite_total'],
                $data['dia_vencimento'],
                $data['dia_fechamento'],
                $data['banco'],
                $data['ultimos_digitos'],
                $data['cor'],
                $data['observacoes'],
                $data['ativo'],
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar cartão: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateLimiteUsado($cardId, $valor, $operacao = 'add') {
        try {
            $this->db->beginTransaction();
            
            // Buscar limite atual
            $stmt = $this->db->prepare("
                SELECT limite_total, limite_usado 
                FROM {$this->table} 
                WHERE id = ? AND deleted_at IS NULL
            ");
            $stmt->execute([$cardId]);
            $card = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$card) {
                throw new Exception("Cartão não encontrado");
            }
            
            // Calcular novo limite usado
            $novoLimiteUsado = $operacao === 'add' 
                ? $card['limite_usado'] + $valor
                : $card['limite_usado'] - $valor;
                
            // Verificar se não excede o limite
            if ($novoLimiteUsado > $card['limite_total']) {
                throw new Exception("Limite do cartão excedido");
            }
            
            if ($novoLimiteUsado < 0) {
                $novoLimiteUsado = 0;
            }
            
            // Atualizar limite usado
            $stmt = $this->db->prepare("
                UPDATE {$this->table} 
                SET limite_usado = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$novoLimiteUsado, $cardId]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Erro ao atualizar limite do cartão: " . $e->getMessage());
            return false;
        }
    }
    
    public function getCardStatistics($cardId, $startDate = null, $endDate = null) {
        try {
            $whereDate = '';
            $params = [$cardId];
            
            if ($startDate && $endDate) {
                $whereDate = " AND t.data_competencia BETWEEN ? AND ?";
                $params[] = $startDate;
                $params[] = $endDate;
            }
            
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_transacoes,
                    SUM(CASE WHEN t.kind = 'saida' THEN t.valor ELSE 0 END) as total_gastos,
                    AVG(CASE WHEN t.kind = 'saida' THEN t.valor ELSE NULL END) as ticket_medio,
                    MAX(CASE WHEN t.kind = 'saida' THEN t.valor ELSE 0 END) as maior_gasto
                FROM transactions t
                WHERE t.credit_card_id = ? AND t.deleted_at IS NULL {$whereDate}
            ");
            
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erro ao buscar estatísticas do cartão: " . $e->getMessage());
            return [];
        }
    }
    
    public function getCardTransactions($cardId, $startDate = null, $endDate = null, $limit = 10) {
        try {
            $whereDate = '';
            $params = [$cardId];
            
            if ($startDate && $endDate) {
                $whereDate = " AND t.data_competencia BETWEEN ? AND ?";
                $params[] = $startDate;
                $params[] = $endDate;
            }
            
            $params[] = $limit;
            
            $stmt = $this->db->prepare("
                SELECT t.*, c.nome as categoria_nome, c.cor as categoria_cor
                FROM transactions t
                LEFT JOIN categories c ON t.category_id = c.id
                WHERE t.credit_card_id = ? AND t.deleted_at IS NULL {$whereDate}
                ORDER BY t.data_competencia DESC, t.created_at DESC
                LIMIT ?
            ");
            
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erro ao buscar transações do cartão: " . $e->getMessage());
            return [];
        }
    }
    
    public function deleteCard($id) {
        try {
            // Verificar se há transações vinculadas ao cartão
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM transactions 
                WHERE credit_card_id = ? AND deleted_at IS NULL
            ");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                throw new Exception("Não é possível excluir cartão com transações vinculadas");
            }
            
            // Soft delete
            $stmt = $this->db->prepare("
                UPDATE {$this->table} 
                SET deleted_at = NOW() 
                WHERE id = ?
            ");
            
            return $stmt->execute([$id]);
            
        } catch (Exception $e) {
            error_log("Erro ao excluir cartão: " . $e->getMessage());
            return false;
        }
    }
}