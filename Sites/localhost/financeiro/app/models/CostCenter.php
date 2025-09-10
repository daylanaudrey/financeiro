<?php
require_once 'BaseModel.php';

class CostCenter extends BaseModel {
    protected $table = 'cost_centers';
    
    public function getCostCentersByOrg($orgId) {
        $sql = "
            SELECT 
                cc.*,
                parent.nome as parent_name,
                u.nome as created_by_name,
                (SELECT COUNT(*) FROM cost_centers cc2 WHERE cc2.parent_id = cc.id AND cc2.deleted_at IS NULL) as children_count,
                (SELECT COUNT(*) FROM transactions t WHERE t.cost_center_id = cc.id AND t.deleted_at IS NULL) as transactions_count
            FROM {$this->table} cc
            LEFT JOIN cost_centers parent ON cc.parent_id = parent.id
            LEFT JOIN users u ON cc.created_by = u.id
            WHERE cc.org_id = ? AND cc.deleted_at IS NULL
            ORDER BY cc.nivel ASC, cc.caminho ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId]);
        return $stmt->fetchAll();
    }
    
    public function getCostCenterHierarchy($orgId) {
        $sql = "
            SELECT 
                cc.*,
                parent.nome as parent_name
            FROM {$this->table} cc
            LEFT JOIN cost_centers parent ON cc.parent_id = parent.id
            WHERE cc.org_id = ? AND cc.deleted_at IS NULL AND cc.ativo = TRUE
            ORDER BY cc.caminho ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId]);
        $results = $stmt->fetchAll();
        
        // Organizar em árvore hierárquica
        return $this->buildHierarchy($results);
    }
    
    public function getActiveCostCenters($orgId) {
        $sql = "
            SELECT cc.*
            FROM {$this->table} cc
            WHERE cc.org_id = ? AND cc.ativo = TRUE AND cc.deleted_at IS NULL
            ORDER BY cc.nivel ASC, cc.nome ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orgId]);
        return $stmt->fetchAll();
    }
    
    public function getCostCenterUsage($costCenterId, $startDate = null, $endDate = null) {
        $sql = "
            SELECT 
                COUNT(*) as total_transactions,
                SUM(CASE WHEN t.kind IN ('entrada', 'transfer_in') THEN t.valor ELSE 0 END) as total_receitas,
                SUM(CASE WHEN t.kind IN ('saida', 'transfer_out') THEN t.valor ELSE 0 END) as total_despesas,
                SUM(CASE WHEN t.kind IN ('entrada', 'transfer_in') THEN t.valor ELSE -t.valor END) as saldo
            FROM transactions t
            WHERE t.cost_center_id = ?
            AND t.deleted_at IS NULL
            AND t.status = 'confirmado'
        ";
        
        $params = [$costCenterId];
        
        if ($startDate) {
            $sql .= " AND t.data_competencia >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND t.data_competencia <= ?";
            $params[] = $endDate;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    public function createCostCenter($data) {
        // Validação de campos obrigatórios
        $requiredFields = ['org_id', 'codigo', 'nome'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new InvalidArgumentException("Campo obrigatório não informado: {$field}");
            }
        }
        
        // Verificar se código já existe na organização
        $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE org_id = ? AND codigo = ? AND deleted_at IS NULL");
        $stmt->execute([$data['org_id'], $data['codigo']]);
        if ($stmt->fetch()) {
            throw new InvalidArgumentException("Código já existe nesta organização");
        }
        
        // Preparar dados para inserção
        $insertData = [
            'org_id' => $data['org_id'],
            'codigo' => strtoupper($data['codigo']),
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
            'parent_id' => !empty($data['parent_id']) ? $data['parent_id'] : null,
            'ativo' => isset($data['ativo']) ? (int)$data['ativo'] : 1,
            'created_by' => $data['created_by'] ?? null
        ];
        
        // Usar BaseModel que agora lança exceções apropriadas
        return $this->create($insertData);
    }
    
    private function createWithoutTriggers($data) {
        try {
            // Abordagem que evita triggers completamente usando transação
            $this->db->beginTransaction();
            
            // Desabilitar temporariamente checagem de chaves estrangeiras se necessário
            $this->db->exec("SET foreign_key_checks = 0");
            
            // Calcular nivel e caminho manualmente
            $nivel = 1;
            $caminho = '/' . $data['codigo'];
            
            if (!empty($data['parent_id'])) {
                $parentStmt = $this->db->prepare("SELECT nivel, caminho FROM {$this->table} WHERE id = ?");
                $parentStmt->execute([$data['parent_id']]);
                $parent = $parentStmt->fetch();
                
                if ($parent) {
                    $nivel = $parent['nivel'] + 1;
                    $caminho = $parent['caminho'] . '/' . $data['codigo'];
                }
            }
            
            // Inserção simples com todos os campos calculados
            $sql = "INSERT INTO {$this->table} (
                org_id, codigo, nome, descricao, parent_id, nivel, caminho, 
                ativo, created_by, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                $data['org_id'],
                $data['codigo'],
                $data['nome'],
                $data['descricao'],
                $data['parent_id'],
                $nivel,
                $caminho,
                $data['ativo'],
                $data['created_by']
            ]);
            
            // Reabilitar checagem de chaves estrangeiras
            $this->db->exec("SET foreign_key_checks = 1");
            
            if ($success) {
                $id = $this->db->lastInsertId();
                $this->db->commit();
                return $id;
            }
            
            $this->db->rollback();
            return false;
            
        } catch (Exception $e) {
            $this->db->rollback();
            $this->db->exec("SET foreign_key_checks = 1"); // Garantir que seja reabilitado
            throw $e;
        }
    }

    private function createDirectInsert($data) {
        // Inserção direta sem acionar triggers
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}, created_at, nivel, caminho) VALUES ({$placeholders}, NOW(), 1, CONCAT('/', UPPER(:codigo_path)))";
        
        // Adicionar campo adicional para caminho
        $data['codigo_path'] = $data['codigo'];
        
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute($data)) {
            return $this->db->lastInsertId();
        } else {
            $errorInfo = $stmt->errorInfo();
            error_log("SQL execution failed for cost_center direct insert. Error: " . json_encode($errorInfo));
            throw new Exception("Erro ao criar centro de custo: " . $errorInfo[2]);
        }
    }
    
    public function updateCostCenter($id, $data) {
        // Se está alterando o código, verificar duplicatas
        if (isset($data['codigo'])) {
            $current = $this->findById($id);
            if (!$current) {
                throw new InvalidArgumentException("Centro de custo não encontrado");
            }
            
            $data['codigo'] = strtoupper($data['codigo']);
            
            if ($data['codigo'] !== $current['codigo']) {
                $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE org_id = ? AND codigo = ? AND id != ? AND deleted_at IS NULL");
                $stmt->execute([$current['org_id'], $data['codigo'], $id]);
                if ($stmt->fetch()) {
                    throw new InvalidArgumentException("Código já existe nesta organização");
                }
            }
        }
        
        // Update simples sem usar BaseModel para evitar conflitos com triggers
        $allowedFields = ['codigo', 'nome', 'descricao', 'parent_id', 'ativo'];
        $updateFields = [];
        $updateValues = [];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $updateFields[] = "{$field} = ?";
                if ($field === 'codigo') {
                    $updateValues[] = strtoupper($value);
                } else {
                    $updateValues[] = $value;
                }
            }
        }
        
        if (empty($updateFields)) {
            return true; // Nada para atualizar
        }
        
        $updateValues[] = $id;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($updateValues);
    }
    
    private function createCostCenterDirect($data) {
        // Método direto que evita completamente os triggers
        try {
            // Usar transação para garantir consistência
            $this->db->beginTransaction();
            
            // Método 1: Tentar inserção simples com apenas campos básicos
            $sql = "INSERT INTO {$this->table} (org_id, codigo, nome, ativo, created_at) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                $data['org_id'],
                $data['codigo'],
                $data['nome'],
                $data['ativo']
            ]);
            
            if ($success) {
                $id = $this->db->lastInsertId();
                
                // Debug: verificar se o ID foi gerado
                error_log("[DEBUG] Cost Center criado - Success: " . ($success ? 'true' : 'false') . ", ID: " . $id);
                
                if ($id && $id > 0) {
                    // Atualizar campos opcionais em comandos separados para evitar triggers
                    if (!empty($data['descricao'])) {
                        $updateStmt = $this->db->prepare("UPDATE {$this->table} SET descricao = ? WHERE id = ?");
                        $updateStmt->execute([$data['descricao'], $id]);
                    }
                    
                    if (!empty($data['parent_id'])) {
                        $updateStmt = $this->db->prepare("UPDATE {$this->table} SET parent_id = ? WHERE id = ?");
                        $updateStmt->execute([$data['parent_id'], $id]);
                    }
                    
                    if (!empty($data['created_by'])) {
                        $updateStmt = $this->db->prepare("UPDATE {$this->table} SET created_by = ? WHERE id = ?");
                        $updateStmt->execute([$data['created_by'], $id]);
                    }
                    
                    $this->db->commit();
                    return $id;
                } else {
                    error_log("[ERROR] Cost Center - ID não foi gerado corretamente");
                    $this->db->rollback();
                    return false;
                }
            }
            
            $this->db->rollback();
            return false;
            
        } catch (Exception $e) {
            $this->db->rollback();
            
            // Fallback final: criar um registro com o mínimo possível
            if (strpos($e->getMessage(), '1442') !== false) {
                return $this->createCostCenterMinimal($data);
            }
            
            throw $e;
        }
    }
    
    private function createCostCenterMinimal($data) {
        // Fallback extremo: inserção manual com campos essenciais apenas
        try {
            $sql = "INSERT IGNORE INTO {$this->table} SET org_id = ?, codigo = ?, nome = ?, ativo = 1";
            $stmt = $this->db->prepare($sql);
            
            if ($stmt->execute([$data['org_id'], $data['codigo'], $data['nome']])) {
                return $this->db->lastInsertId() ?: $this->getIdByCode($data['org_id'], $data['codigo']);
            }
            
            return false;
            
        } catch (Exception $e) {
            // Último recurso: buscar se já existe
            return $this->getIdByCode($data['org_id'], $data['codigo']);
        }
    }
    
    private function insertCostCenterSimple($data) {
        // Método mais simples usando BaseModel
        error_log("[DEBUG] Usando método insertCostCenterSimple");
        
        $cleanData = [
            'org_id' => $data['org_id'],
            'codigo' => strtoupper($data['codigo']),
            'nome' => $data['nome'],
            'ativo' => $data['ativo']
        ];
        
        // Adicionar campos opcionais apenas se não estão vazios
        if (!empty($data['descricao'])) {
            $cleanData['descricao'] = $data['descricao'];
        }
        
        if (!empty($data['parent_id'])) {
            $cleanData['parent_id'] = $data['parent_id'];
        }
        
        if (!empty($data['created_by'])) {
            $cleanData['created_by'] = $data['created_by'];
        }
        
        error_log("[DEBUG] Dados limpos: " . json_encode($cleanData));
        
        // Usar método create do BaseModel
        $id = $this->create($cleanData);
        
        error_log("[DEBUG] ID retornado pelo create: " . ($id ?: 'false'));
        
        return $id;
    }
    
    private function getIdByCode($orgId, $codigo) {
        $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE org_id = ? AND codigo = ?");
        $stmt->execute([$orgId, $codigo]);
        $result = $stmt->fetch();
        return $result ? $result['id'] : false;
    }
    
    public function getCostCenterReport($orgId, $startDate, $endDate) {
        $sql = "
            SELECT 
                cc.id,
                cc.codigo,
                cc.nome,
                cc.nivel,
                cc.caminho,
                COUNT(t.id) as total_transacoes,
                SUM(CASE WHEN t.kind IN ('entrada', 'transfer_in') THEN t.valor ELSE 0 END) as receitas,
                SUM(CASE WHEN t.kind IN ('saida', 'transfer_out') THEN t.valor ELSE 0 END) as despesas,
                SUM(CASE WHEN t.kind IN ('entrada', 'transfer_in') THEN t.valor ELSE -t.valor END) as saldo
            FROM cost_centers cc
            LEFT JOIN transactions t ON cc.id = t.cost_center_id 
                AND t.deleted_at IS NULL 
                AND t.status = 'confirmado'
                AND t.data_competencia BETWEEN ? AND ?
            WHERE cc.org_id = ? AND cc.deleted_at IS NULL
            GROUP BY cc.id, cc.codigo, cc.nome, cc.nivel, cc.caminho
            ORDER BY cc.caminho ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate, $orgId]);
        return $stmt->fetchAll();
    }
    
    public function canDelete($id) {
        // Verificar se tem filhos
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE parent_id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        if ($result['count'] > 0) {
            return ['can_delete' => false, 'reason' => 'Centro de custo possui subcategorias'];
        }
        
        // Verificar se tem transações
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM transactions WHERE cost_center_id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        if ($result['count'] > 0) {
            return ['can_delete' => false, 'reason' => 'Centro de custo possui transações vinculadas'];
        }
        
        return ['can_delete' => true, 'reason' => ''];
    }
    
    public function getParentOptions($orgId, $excludeId = null) {
        $sql = "
            SELECT id, codigo, nome, nivel, caminho
            FROM {$this->table}
            WHERE org_id = ? AND deleted_at IS NULL
        ";
        $params = [$orgId];
        
        if ($excludeId) {
            $sql .= " AND id != ? AND caminho NOT LIKE (SELECT CONCAT(caminho, '.%') FROM {$this->table} WHERE id = ?)";
            $params[] = $excludeId;
            $params[] = $excludeId;
        }
        
        $sql .= " ORDER BY caminho ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    private function buildHierarchy($items) {
        $hierarchy = [];
        $references = [];
        
        // Primeiro, criar referências de todos os items
        foreach ($items as $item) {
            $item['children'] = [];
            $references[$item['id']] = $item;
        }
        
        // Depois, construir a hierarquia
        foreach ($references as $item) {
            if ($item['parent_id']) {
                $references[$item['parent_id']]['children'][] = &$references[$item['id']];
            } else {
                $hierarchy[] = &$references[$item['id']];
            }
        }
        
        return $hierarchy;
    }
    
    public function getIndentedName($costCenter) {
        $indent = str_repeat('— ', max(0, $costCenter['nivel'] - 1));
        return $indent . $costCenter['nome'];
    }
}