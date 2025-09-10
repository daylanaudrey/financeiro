<?php
require_once 'BaseModel.php';

class AuditLog extends BaseModel {
    protected $table = 'audit_logs';
    
    public function log($data) {
        // Adicionar informações automáticas
        $data['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        // Se old_values ou new_values são arrays, converter para JSON
        if (isset($data['old_values']) && is_array($data['old_values'])) {
            $data['old_values'] = json_encode($data['old_values']);
        }
        if (isset($data['new_values']) && is_array($data['new_values'])) {
            $data['new_values'] = json_encode($data['new_values']);
        }
        
        return $this->create($data);
    }
    
    public function logUserAction($userId, $orgId, $entity, $action, $entityId = null, $oldValues = null, $newValues = null, $description = null) {
        return $this->log([
            'user_id' => $userId,
            'org_id' => $orgId,
            'entity' => $entity,
            'entity_id' => $entityId,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description
        ]);
    }
    
    public function getRecentLogs($orgId = null, $limit = 50) {
        $whereClause = $orgId ? "WHERE org_id = ?" : "WHERE 1=1";
        $params = $orgId ? [$orgId, $limit] : [$limit];
        
        $sql = "
            SELECT al.*, u.nome as user_name, u.email as user_email
            FROM {$this->table} al
            LEFT JOIN users u ON al.user_id = u.id
            {$whereClause}
            ORDER BY al.created_at DESC
            LIMIT ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getUserActivityLog($userId, $limit = 20) {
        $sql = "
            SELECT *
            FROM {$this->table}
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
    
    public function getEntityHistory($entity, $entityId) {
        $sql = "
            SELECT al.*, u.nome as user_name, u.email as user_email
            FROM {$this->table} al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE al.entity = ? AND al.entity_id = ?
            ORDER BY al.created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$entity, $entityId]);
        return $stmt->fetchAll();
    }
}