<?php

namespace App\Middleware;

/**
 * AuthMiddleware
 * Middleware para verificar autenticação
 */
class AuthMiddleware
{
    /**
     * Verificar se usuário está autenticado
     */
    public static function check(): bool
    {
        // Verificar sessão
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return false;
        }

        // Verificar timeout da sessão (120 minutos)
        if (isset($_SESSION['login_time'])) {
            $sessionTimeout = SESSION_TIMEOUT * 60; // Converter para segundos
            if ((time() - $_SESSION['login_time']) > $sessionTimeout) {
                self::destroySession();
                return false;
            }
        }

        // Atualizar tempo da sessão
        $_SESSION['login_time'] = time();

        return true;
    }

    /**
     * Requerer autenticação
     * Redireciona para login se não estiver autenticado
     */
    public static function require(): void
    {
        if (!self::check()) {
            $_SESSION['login_error'] = 'Você precisa fazer login para acessar esta página.';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }

    /**
     * Requerer papel específico
     */
    public static function requireRole(string $role): void
    {
        self::require();

        $userRole = $_SESSION['user_role'] ?? null;

        // Admin tem acesso a tudo
        if ($userRole === 'admin') {
            return;
        }

        // Verificar papel específico
        if ($userRole !== $role) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }
    }

    /**
     * Requerer admin
     */
    public static function requireAdmin(): void
    {
        self::require();

        if ($_SESSION['user_role'] !== 'admin') {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }
    }

    /**
     * Verificar se é admin
     */
    public static function isAdmin(): bool
    {
        return self::check() && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Verificar se tem permissão para ação
     */
    public static function canAccess(string $module, string $action = 'view'): bool
    {
        if (!self::check()) {
            return false;
        }

        $role = $_SESSION['user_role'] ?? null;

        // Admin pode tudo
        if ($role === 'admin') {
            return true;
        }

        // Regras por papel
        $permissions = [
            'operator' => [
                'products' => ['view', 'create', 'edit'],
                'clients' => ['view', 'create', 'edit'],
                'processes' => ['view', 'create', 'edit'],
                'reports' => ['view']
            ],
            'viewer' => [
                'products' => ['view'],
                'clients' => ['view'],
                'processes' => ['view'],
                'reports' => ['view']
            ]
        ];

        return isset($permissions[$role][$module]) &&
               in_array($action, $permissions[$role][$module]);
    }

    /**
     * Destruir sessão
     */
    private static function destroySession(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}