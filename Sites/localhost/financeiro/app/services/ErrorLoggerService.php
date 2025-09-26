<?php
require_once __DIR__ . '/../models/AuditLog.php';

class ErrorLoggerService {
    private static $instance = null;
    private $auditModel;
    private $db;
    
    private function __construct() {
        try {
            require_once __DIR__ . '/../../config/database.php';
            $database = new Database();
            $this->db = $database->connect();
            $this->auditModel = new AuditLog($this->db);
        } catch (Exception $e) {
            error_log("Erro ao inicializar ErrorLoggerService: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public static function init() {
        $instance = self::getInstance();
        
        // Configurar handler de erros PHP
        set_error_handler([$instance, 'handleError']);
        
        // Configurar handler de exceções
        set_exception_handler([$instance, 'handleException']);
        
        // Configurar handler de erros fatais
        register_shutdown_function([$instance, 'handleFatalError']);
    }
    
    public function handleError($severity, $message, $file = '', $line = 0) {
        // Não logar erros suprimidos com @
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $errorType = $this->getErrorType($severity);
        $errorMessage = "$errorType: $message in $file on line $line";
        
        // Log no arquivo PHP
        error_log($errorMessage);
        
        // Log na auditoria apenas para erros importantes
        if ($severity & (E_ERROR | E_WARNING | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR)) {
            $this->logToAudit('error', $errorMessage, [
                'severity' => $severity,
                'file' => $file,
                'line' => $line,
                'type' => $errorType
            ]);
        }
        
        return true;
    }
    
    public function handleException($exception) {
        $errorMessage = "Uncaught Exception: " . $exception->getMessage() . 
                       " in " . $exception->getFile() . 
                       " on line " . $exception->getLine();
        
        error_log($errorMessage);
        error_log("Stack trace: " . $exception->getTraceAsString());
        
        $this->logToAudit('exception', $errorMessage, [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
    
    public function handleFatalError() {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $errorMessage = "Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}";
            
            error_log($errorMessage);
            
            $this->logToAudit('fatal_error', $errorMessage, [
                'type' => $error['type'],
                'file' => $error['file'],
                'line' => $error['line']
            ]);
        }
    }
    
    public function logCustomError($type, $message, $context = []) {
        $fullMessage = "Custom Error [$type]: $message";
        error_log($fullMessage);
        
        $this->logToAudit($type, $message, $context);
    }
    
    public function logDatabaseError($query, $error, $params = []) {
        $message = "Database Error: $error";
        if ($query) {
            $message .= " | Query: $query";
        }
        if (!empty($params)) {
            $message .= " | Params: " . json_encode($params);
        }
        
        error_log($message);
        $this->logToAudit('database_error', $message, [
            'query' => $query,
            'params' => $params
        ]);
    }
    
    public function logAPIError($endpoint, $error, $request = []) {
        $message = "API Error at $endpoint: $error";
        error_log($message);
        
        $this->logToAudit('api_error', $message, [
            'endpoint' => $endpoint,
            'request' => $request
        ]);
    }
    
    public function logSecurityAlert($type, $message, $context = []) {
        $fullMessage = "Security Alert [$type]: $message";
        error_log($fullMessage);
        
        $this->logToAudit('security_alert', $message, array_merge($context, [
            'alert_type' => $type,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]));
    }
    
    private function logToAudit($action, $description, $context = []) {
        try {
            if (!$this->auditModel) {
                return;
            }
            
            $userId = $_SESSION['user_id'] ?? null;
            $orgId = $_SESSION['current_org_id'] ?? null;
            
            $this->auditModel->logUserAction(
                $userId,
                $orgId,
                'system',
                $action,
                null,
                json_encode($context),
                null,
                $description
            );
        } catch (Exception $e) {
            // Evitar loop infinito de erros
            error_log("Erro ao logar na auditoria: " . $e->getMessage());
        }
    }
    
    private function getErrorType($severity) {
        $errorTypes = [
            E_ERROR => 'Fatal Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict Notice',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated'
        ];
        
        return $errorTypes[$severity] ?? 'Unknown Error';
    }
}