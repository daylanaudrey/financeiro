<?php
require_once 'BaseModel.php';

class Vault extends BaseModel {
    protected $table = 'vault_goals';
    
    public function getVaultsWithGoals($orgId = null) {
        $sql = "
            SELECT 
                vg.*,
                ROUND((vg.valor_atual / vg.valor_meta) * 100, 2) as progresso_percentual,
                CASE 
                    WHEN vg.data_meta IS NOT NULL THEN DATEDIFF(vg.data_meta, CURDATE())
                    ELSE NULL 
                END as dias_restantes,
                CASE 
                    WHEN vg.data_meta < CURDATE() AND NOT vg.concluido THEN 'atrasado'
                    WHEN vg.concluido THEN 'concluido'
                    WHEN vg.valor_atual >= vg.valor_meta * 0.8 THEN 'proximo'
                    ELSE 'em_andamento'
                END as status_meta
            FROM vault_goals vg
            WHERE vg.deleted_at IS NULL 
            " . ($orgId ? "AND vg.org_id = ?" : "") . "
            AND vg.ativo = TRUE
            ORDER BY 
                vg.concluido ASC,
                vg.prioridade DESC,
                vg.data_meta ASC,
                vg.created_at DESC
        ";
        
        $params = $orgId ? [$orgId] : [];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getVaultById($id) {
        $sql = "
            SELECT 
                vg.*,
                ROUND((vg.valor_atual / vg.valor_meta) * 100, 2) as progresso_percentual
            FROM vault_goals vg
            WHERE vg.id = ? AND vg.deleted_at IS NULL
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getVaultMovements($vaultGoalId) {
        $sql = "
            SELECT 
                vm.*,
                t.descricao as transaction_description,
                t.data_competencia
            FROM vault_movements vm
            INNER JOIN transactions t ON vm.transaction_id = t.id
            WHERE vm.vault_goal_id = ? 
            AND vm.deleted_at IS NULL
            ORDER BY vm.data_movimento DESC, vm.created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$vaultGoalId]);
        return $stmt->fetchAll();
    }
    
    public function createVaultGoal($data) {
        $requiredFields = ['titulo', 'valor_meta'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new InvalidArgumentException("Campo obrigatório não informado: {$field}");
            }
        }
        
        // Preparar dados para inserção
        $insertData = [
            'org_id' => $data['org_id'] ?? 1,
            'titulo' => $data['titulo'],
            'descricao' => $data['descricao'] ?? null,
            'valor_meta' => $this->convertBrazilianCurrencyToDecimal($data['valor_meta']),
            'data_meta' => !empty($data['data_meta']) ? $data['data_meta'] : null,
            'categoria' => $data['categoria'] ?? 'outros',
            'cor' => $data['cor'] ?? '#007bff',
            'icone' => $data['icone'] ?? 'fas fa-bullseye',
            'prioridade' => $data['prioridade'] ?? 'media',
            'created_by' => $data['created_by'] ?? null
        ];
        
        return $this->create($insertData);
    }
    
    public function updateVaultGoal($id, $data) {
        if (isset($data['valor_meta'])) {
            $data['valor_meta'] = $this->convertBrazilianCurrencyToDecimal($data['valor_meta']);
        }
        
        // Tratar campo data_meta - converter string vazia para null
        if (isset($data['data_meta']) && empty($data['data_meta'])) {
            $data['data_meta'] = null;
        }
        
        return $this->update($id, $data);
    }
    
    public function addMovement($vaultGoalId, $transactionId, $tipo, $valor, $descricao = null) {
        try {
            $this->db->beginTransaction();
            
            // Verificar se o vault goal existe
            $vault = $this->findById($vaultGoalId);
            if (!$vault) {
                throw new InvalidArgumentException("Vault goal não encontrado");
            }
            
            // Inserir movimento
            $movementData = [
                'vault_goal_id' => $vaultGoalId,
                'transaction_id' => $transactionId,
                'tipo' => $tipo,
                'valor' => is_string($valor) ? $this->convertBrazilianCurrencyToDecimal($valor) : $valor,
                'descricao' => $descricao,
                'data_movimento' => date('Y-m-d'),
                'created_by' => $_SESSION['user_id'] ?? null
            ];
            
            $stmt = $this->db->prepare("
                INSERT INTO vault_movements (vault_goal_id, transaction_id, tipo, valor, descricao, data_movimento, created_by) 
                VALUES (:vault_goal_id, :transaction_id, :tipo, :valor, :descricao, :data_movimento, :created_by)
            ");
            
            $success = $stmt->execute($movementData);
            if (!$success) {
                throw new Exception("Erro ao inserir movimento: " . implode(", ", $stmt->errorInfo()));
            }
            
            $movementId = $this->db->lastInsertId();
            $this->db->commit();
            
            // Se lastInsertId retornou 0, tentar buscar o ID do movimento inserido
            if ($movementId == 0) {
                $stmt = $this->db->prepare("
                    SELECT id FROM vault_movements 
                    WHERE vault_goal_id = ? AND transaction_id = ? AND tipo = ? 
                    ORDER BY created_at DESC LIMIT 1
                ");
                $stmt->execute([$vaultGoalId, $transactionId, $tipo]);
                $result = $stmt->fetch();
                $movementId = $result ? $result['id'] : 0;
            }
            
            return $movementId;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getVaultStatistics($orgId = null) {
        $sql = "
            SELECT 
                COUNT(*) as total_vaults,
                COUNT(CASE WHEN concluido = TRUE THEN 1 END) as concluidos,
                COUNT(CASE WHEN concluido = FALSE THEN 1 END) as em_andamento,
                SUM(valor_meta) as valor_total_metas,
                SUM(valor_atual) as valor_total_atual,
                ROUND(AVG((valor_atual / valor_meta) * 100), 2) as progresso_medio
            FROM vault_goals vg
            WHERE vg.deleted_at IS NULL 
            " . ($orgId ? "AND vg.org_id = ?" : "") . "
            AND vg.ativo = TRUE
        ";
        
        $params = $orgId ? [$orgId] : [];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    public function getVaultsByCategory($orgId = null) {
        $sql = "
            SELECT 
                vg.categoria,
                COUNT(*) as quantidade,
                SUM(vg.valor_meta) as valor_total_meta,
                SUM(vg.valor_atual) as valor_total_atual,
                ROUND(AVG((vg.valor_atual / vg.valor_meta) * 100), 2) as progresso_medio
            FROM vault_goals vg
            WHERE vg.deleted_at IS NULL 
            " . ($orgId ? "AND vg.org_id = ?" : "") . "
            AND vg.ativo = TRUE
            GROUP BY vg.categoria
            ORDER BY valor_total_meta DESC
        ";
        
        $params = $orgId ? [$orgId] : [];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    private function convertBrazilianCurrencyToDecimal($value) {
        if (is_numeric($value)) {
            return floatval($value);
        }
        
        $value = str_replace(['R$', ' ', '.'], '', $value);
        $value = str_replace(',', '.', $value);
        return floatval($value);
    }
}