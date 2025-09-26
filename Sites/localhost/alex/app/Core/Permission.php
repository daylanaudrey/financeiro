<?php

/**
 * Sistema de Permissões
 * Gerencia permissões e controle de acesso no sistema
 */
class Permission
{
    /**
     * Permissões do sistema organizadas por módulo
     */
    private static array $permissions = [
        'users' => [
            'view' => 'Visualizar usuários',
            'create' => 'Criar usuários',
            'edit' => 'Editar usuários',
            'delete' => 'Excluir usuários'
        ],
        'products' => [
            'view' => 'Visualizar produtos',
            'create' => 'Criar produtos',
            'edit' => 'Editar produtos',
            'delete' => 'Excluir produtos'
        ],
        'clients' => [
            'view' => 'Visualizar importadores',
            'create' => 'Criar importadores',
            'edit' => 'Editar importadores',
            'delete' => 'Excluir importadores'
        ],
        'processes' => [
            'view' => 'Visualizar processos',
            'create' => 'Criar processos',
            'edit' => 'Editar processos',
            'delete' => 'Excluir processos'
        ],
        'process_items' => [
            'view' => 'Visualizar itens de processos',
            'create' => 'Adicionar itens a processos',
            'edit' => 'Editar itens de processos',
            'delete' => 'Excluir itens de processos'
        ],
        'reports' => [
            'view' => 'Visualizar relatórios',
            'export' => 'Exportar relatórios'
        ],
        'audit' => [
            'view' => 'Visualizar auditoria',
            'export' => 'Exportar logs de auditoria',
            'cleanup' => 'Limpar logs antigos'
        ],
        'ports' => [
            'view' => 'Visualizar portos',
            'create' => 'Criar portos',
            'edit' => 'Editar portos',
            'delete' => 'Excluir portos'
        ],
        'system' => [
            'admin' => 'Administração completa',
            'exchange_rates' => 'Gerenciar taxas de câmbio'
        ]
    ];

    /**
     * Permissões por role (padrão do sistema)
     */
    private static array $rolePermissions = [
        'admin' => [
            'users.*',
            'products.*',
            'clients.*',
            'processes.*',
            'process_items.*',
            'reports.*',
            'audit.*',
            'ports.*',
            'system.*'
        ],
        'operator' => [
            'products.*',
            'clients.*',
            'processes.*',
            'process_items.*',
            'ports.*',
            'reports.view',
            'reports.export'
        ],
        'viewer' => [
            'products.view',
            'clients.view',
            'processes.view',
            'process_items.view',
            'ports.view',
            'reports.view'
        ]
    ];

    /**
     * Verificar se usuário tem permissão
     */
    public static function check(string $permission, ?array $user = null): bool
    {
        if (!$user) {
            $user = self::getCurrentUser();
        }

        if (!$user) {
            return false;
        }

        // Admin sempre tem todas as permissões
        if ($user['role'] === 'admin') {
            return true;
        }

        // Verificar se usuário está ativo
        if (!$user['is_active']) {
            return false;
        }

        $userPermissions = self::getUserPermissions($user);

        // Verificar permissão exata
        if (in_array($permission, $userPermissions)) {
            return true;
        }

        // Verificar permissão com wildcard
        $permissionParts = explode('.', $permission);
        if (count($permissionParts) === 2) {
            $moduleWildcard = $permissionParts[0] . '.*';
            if (in_array($moduleWildcard, $userPermissions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verificar múltiplas permissões (AND)
     */
    public static function checkAll(array $permissions, ?array $user = null): bool
    {
        foreach ($permissions as $permission) {
            if (!self::check($permission, $user)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Verificar se tem alguma das permissões (OR)
     */
    public static function checkAny(array $permissions, ?array $user = null): bool
    {
        foreach ($permissions as $permission) {
            if (self::check($permission, $user)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Obter permissões do usuário
     */
    public static function getUserPermissions(?array $user = null): array
    {
        if (!$user) {
            $user = self::getCurrentUser();
        }

        if (!$user) {
            return [];
        }

        $userId = $user['id'];
        $role = $user['role'];

        // Admin sempre tem todas as permissões
        if ($role === 'admin') {
            return self::$rolePermissions['admin'];
        }

        try {
            $pdo = Database::getConnection();

            // Buscar permissões do role no banco
            $rolePermissions = [];
            $stmt = $pdo->prepare("SELECT CONCAT(module, '.', action) as permission
                                   FROM role_permissions
                                   WHERE role = ? AND allowed = 1");
            $stmt->execute([$role]);
            while ($row = $stmt->fetch()) {
                $rolePermissions[] = $row['permission'];
            }

            // Buscar permissões específicas do usuário
            $userPermissions = [];
            $stmt = $pdo->prepare("SELECT CONCAT(module, '.', action) as permission
                                   FROM user_permissions
                                   WHERE user_id = ? AND allowed = 1");
            $stmt->execute([$userId]);
            while ($row = $stmt->fetch()) {
                $userPermissions[] = $row['permission'];
            }

            // Combinar permissões do role + específicas do usuário
            return array_unique(array_merge($rolePermissions, $userPermissions));

        } catch (Exception $e) {
            // Fallback para permissões hardcoded se o banco falhar
            return self::$rolePermissions[$role] ?? [];
        }
    }

    /**
     * Obter usuário atual da sessão
     */
    private static function getCurrentUser(): ?array
    {
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'name' => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'role' => $_SESSION['user_role'] ?? '',
            'is_active' => true // Usuário logado sempre está ativo
        ];
    }

    /**
     * Verificar se é admin
     */
    public static function isAdmin(?array $user = null): bool
    {
        if (!$user) {
            $user = self::getCurrentUser();
        }

        return $user && $user['role'] === 'admin';
    }

    /**
     * Forçar redirect se não tiver permissão
     */
    public static function requirePermission(string $permission): void
    {
        if (!self::check($permission)) {
            self::showPermissionDenied($permission);
        }
    }

    /**
     * Exibir página de permissão negada
     */
    public static function showPermissionDenied(string $permission = null): void
    {
        http_response_code(403);

        $data = [
            'title' => 'Acesso Negado - Permissão Insuficiente',
            'required_permission' => $permission
        ];

        extract($data);

        // Capturar o conteúdo da view
        ob_start();
        include APP_PATH . '/Views/errors/permission_denied.php';
        $content = ob_get_clean();

        // Incluir layout
        include APP_PATH . '/Views/layouts/main.php';
        exit;
    }

    /**
     * Forçar redirect se não for admin
     */
    public static function requireAdmin(): void
    {
        if (!self::isAdmin()) {
            self::showPermissionDenied('system.admin');
        }
    }

    /**
     * Verificar se está logado
     */
    public static function requireAuth(): void
    {
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }

    /**
     * Obter todas as permissões do sistema
     */
    public static function getAllPermissions(): array
    {
        return self::$permissions;
    }

    /**
     * Obter permissões formatadas para exibição
     */
    public static function getPermissionsForDisplay(): array
    {
        $moduleNames = [
            'users' => 'Usuários',
            'products' => 'Produtos',
            'clients' => 'Clientes',
            'processes' => 'Processos',
            'process_items' => 'Itens de Processos',
            'reports' => 'Relatórios',
            'audit' => 'Auditoria',
            'ports' => 'Portos',
            'system' => 'Sistema'
        ];

        $display = [];
        foreach (self::$permissions as $module => $actions) {
            $display[$module] = [
                'name' => $moduleNames[$module] ?? ucfirst($module),
                'actions' => $actions
            ];
        }
        return $display;
    }

    /**
     * Verificar se role existe
     */
    public static function isValidRole(string $role): bool
    {
        return array_key_exists($role, self::$rolePermissions);
    }

    /**
     * Obter roles disponíveis
     */
    public static function getAvailableRoles(): array
    {
        return array_keys(self::$rolePermissions);
    }

    /**
     * Obter permissões de um role específico
     */
    public static function getRolePermissions(string $role): array
    {
        return self::$rolePermissions[$role] ?? [];
    }
}