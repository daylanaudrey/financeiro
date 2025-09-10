<?php
class AuthMiddleware {
    
    public static function requireAuth() {
        error_log("=== AUTH MIDDLEWARE START ===");
        error_log("Session status: " . session_status() . " (1=disabled, 2=active, 3=none)");
        
        // Tentar iniciar sessão de forma segura
        if (session_status() === PHP_SESSION_NONE) {
            error_log("Starting new session...");
            
            // Configurar diretório de sessões se não estiver definido
            $sessionPath = session_save_path();
            if (empty($sessionPath)) {
                $tempDir = sys_get_temp_dir();
                error_log("Setting session save path to: " . $tempDir);
                session_save_path($tempDir);
            }
            
            // Configurar sessão para ser mais permissiva
            ini_set('session.cookie_lifetime', 3600);
            ini_set('session.gc_maxlifetime', 3600);
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 0); // Permitir HTTP para desenvolvimento
            
            try {
                session_start();
                error_log("Session started successfully. ID: " . session_id());
            } catch (Exception $e) {
                error_log("Session start failed: " . $e->getMessage());
                // Se falhar, limpar cookies e tentar novamente com ID novo
                if (isset($_COOKIE[session_name()])) {
                    setcookie(session_name(), '', time()-3600, '/');
                }
                session_regenerate_id(true);
                try {
                    session_start();
                    error_log("Session regenerated and started. New ID: " . session_id());
                } catch (Exception $e2) {
                    error_log("Second session start attempt failed: " . $e2->getMessage());
                    throw $e2;
                }
            }
        } else {
            error_log("Session already active. ID: " . session_id());
        }
        
        error_log("Session data: " . json_encode($_SESSION ?? []));
        
        error_log("Checking if user is logged in...");
        $isLoggedIn = self::isLoggedIn();
        error_log("Is logged in result: " . ($isLoggedIn ? 'YES' : 'NO'));
        
        if (!$isLoggedIn) {
            error_log("User not logged in, redirecting to login page");
            header('Location: ' . url('/login'));
            exit;
        }
        
        error_log("Getting current user...");
        $currentUser = self::getCurrentUser();
        error_log("Current user data: " . json_encode($currentUser));
        error_log("=== AUTH MIDDLEWARE END ===");
        
        return $currentUser;
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public static function getCurrentUser() {
        if (self::isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'nome' => $_SESSION['user_name'],
                'email' => $_SESSION['user_email'],
                'role' => $_SESSION['user_role']
            ];
        }
        return null;
    }
    
    public static function hasRole($requiredRole) {
        $user = self::getCurrentUser();
        if (!$user) return false;
        
        $roles = ['leitor', 'operador', 'financeiro', 'admin'];
        $userRoleIndex = array_search($user['role'], $roles);
        $requiredRoleIndex = array_search($requiredRole, $roles);
        
        return $userRoleIndex !== false && $requiredRoleIndex !== false && $userRoleIndex >= $requiredRoleIndex;
    }
    
    public static function requireRole($requiredRole) {
        $user = self::requireAuth();
        
        if (!self::hasRole($requiredRole)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Acesso negado. Permissão insuficiente.'
            ]);
            exit;
        }
        
        return $user;
    }
}