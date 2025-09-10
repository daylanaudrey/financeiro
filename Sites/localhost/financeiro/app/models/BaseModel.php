<?php
class BaseModel {
    protected $db;
    protected $table;
    
    public function __construct() {
        require_once __DIR__ . '/../../config/database.php';
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function findAll() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE deleted_at IS NULL ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}, created_at) VALUES ({$placeholders}, NOW())";
        
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute($data)) {
            return $this->db->lastInsertId();
        } else {
            $errorInfo = $stmt->errorInfo();
            error_log("SQL execution failed for table {$this->table}. Error: " . json_encode($errorInfo));
            
            // Lançar exceção com mensagem detalhada do erro
            $sqlErrorMessage = $errorInfo[2] ?? 'Erro desconhecido no banco de dados';
            throw new Exception($sqlErrorMessage);
        }
    }
    
    public function update($id, $data) {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE {$this->table} SET {$setClause}, updated_at = NOW() WHERE id = :id";
        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute($data)) {
            return true;
        } else {
            $errorInfo = $stmt->errorInfo();
            error_log("SQL update failed for table {$this->table}. Error: " . json_encode($errorInfo));
            
            // Lançar exceção com mensagem detalhada do erro
            $sqlErrorMessage = $errorInfo[2] ?? 'Erro desconhecido no banco de dados';
            throw new Exception($sqlErrorMessage);
        }
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET deleted_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }
}