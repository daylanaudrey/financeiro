<?php
class Database {
    private $host = '127.0.0.1';
    private $port = '3307';
    private $database = 'dag_financeiro';
    private $username = 'root';
    private $password = 'root';
//    private $host = 'localhost';
//    private $port = '3306';
//    private $database = 'dagsol97_financeiro';
//    private $username = 'dagsol97_financeiro';
//    private $password = 'vk+DvtK6,fH6';
    private $connection;
    
    public function connect() {
        try {
            $this->connection = new PDO(
                "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            return $this->connection;
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Erro na conexão com o banco de dados");
        }
    }
    
    public function getConnection() {
        if (!$this->connection) {
            $this->connect();
        }
        return $this->connection;
    }
}