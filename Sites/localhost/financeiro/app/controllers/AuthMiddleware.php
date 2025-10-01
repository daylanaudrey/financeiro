<?php
class AuthMiddleware {
    
    public static function requireAuth() {
        // Auth middleware started
        
        // Tentar iniciar sessão de forma segura
        if (session_status() === PHP_SESSION_NONE) {
            // Starting new session
            
            // Configurar diretório de sessões se não estiver definido
            $sessionPath = session_save_path();
            if (empty($sessionPath)) {
                $tempDir = sys_get_temp_dir();
                // Setting session save path
                session_save_path($tempDir);
            }
            
            // Configurar sessão para ser mais permissiva
            ini_set('session.cookie_lifetime', 3600); // 1 hora
            ini_set('session.gc_maxlifetime', 7200); // 2 horas para coleta de lixo
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 0); // Permitir HTTP para desenvolvimento
            ini_set('session.gc_probability', 1); // Aumentar probabilidade de limpeza
            ini_set('session.gc_divisor', 100);
            
            try {
                session_start();
                // Session started successfully
            } catch (Exception $e) {
                // Session start failed, will retry
                // Se falhar, limpar cookies e tentar novamente com ID novo
                if (isset($_COOKIE[session_name()])) {
                    setcookie(session_name(), '', time()-3600, '/');
                }
                session_regenerate_id(true);
                try {
                    session_start();
                    // Session regenerated and started
                } catch (Exception $e2) {
                    // Second session start attempt failed
                    throw $e2;
                }
            }
        } else {
            // Session already active
        }
        
        $isLoggedIn = self::isLoggedIn();
        
        if (!$isLoggedIn) {
            // Verificar se é uma requisição AJAX/API
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                     strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
            $isApiCall = strpos($_SERVER['REQUEST_URI'], '/api/') !== false;

            if ($isAjax || $isApiCall) {
                // Para requisições AJAX/API, retornar JSON
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Usuário não autenticado',
                    'error' => 'authentication_required'
                ]);
                exit;
            }

            // User not logged in, redirecting to login page

            // Capturar a URL atual para redirecionamento pós-login
            $currentUrl = $_SERVER['REQUEST_URI'];

            // Detectar se é acesso mobile/PWA
            $isMobileRequest = ($currentUrl === '/mobile' ||
                              str_contains($currentUrl, '/mobile') ||
                              self::isMobileUserAgent());

            // Evitar loops infinitos - não salvar URLs de auth
            if (!in_array($currentUrl, ['/login', '/register', '/logout']) &&
                !str_contains($currentUrl, '/login') &&
                !str_contains($currentUrl, '/logout') &&
                !str_contains($currentUrl, '/auth/')) {

                // Para mobile, salvar flag especial em vez da URL completa
                if ($isMobileRequest) {
                    $_SESSION['return_to_mobile'] = 'true';
                } else {
                    $_SESSION['redirect_after_login'] = $currentUrl;
                }
            }

            // Redirecionar para login com parâmetro mobile se necessário
            if ($isMobileRequest) {
                header('Location: ' . url('/login?mobile=1'));
            } else {
                header('Location: ' . url('/login'));
            }
            exit;
        }
        
        $currentUser = self::getCurrentUser();
        
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

    private static function isMobileUserAgent() {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return false;
        }

        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $mobileKeywords = [
            'Mobile', 'Android', 'iPhone', 'iPad', 'iPod',
            'Windows Phone', 'BlackBerry', 'webOS'
        ];

        foreach ($mobileKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                return true;
            }
        }

        return false;
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