<?php

/**
 * PermissionController
 * Gerencia permissões de perfis e usuários
 */
class PermissionController
{
    /**
     * Listar perfis e suas permissões
     */
    public function roles(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('system.admin');

        $roles = ['admin', 'operator', 'viewer'];
        $modules = Permission::getPermissionsForDisplay();
        $rolePermissions = [];

        // Buscar permissões de cada role
        foreach ($roles as $role) {
            $rolePermissions[$role] = $this->getRolePermissions($role);
        }

        $data = [
            'title' => 'Gerenciar Permissões dos Perfis',
            'roles' => $roles,
            'modules' => $modules,
            'rolePermissions' => $rolePermissions
        ];

        $this->render('permissions/roles', $data);
    }

    /**
     * Atualizar permissões de um perfil
     */
    public function updateRole(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('system.admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método não permitido']);
            return;
        }

        $role = $_POST['role'] ?? '';
        $permissions = $_POST['permissions'] ?? [];

        if (!in_array($role, ['admin', 'operator', 'viewer'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Perfil inválido']);
            return;
        }

        // Admin sempre tem todas as permissões
        if ($role === 'admin') {
            $this->jsonResponse(['success' => false, 'message' => 'Não é possível alterar permissões do administrador']);
            return;
        }

        try {
            $pdo = Database::getConnection();
            $pdo->beginTransaction();

            // Remover permissões antigas
            $stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role = ?");
            $stmt->execute([$role]);

            // Inserir novas permissões
            $stmt = $pdo->prepare("INSERT INTO role_permissions (role, module, action, allowed) VALUES (?, ?, ?, 1)");

            foreach ($permissions as $permission) {
                if (strpos($permission, '.') !== false) {
                    list($module, $action) = explode('.', $permission, 2);
                    $stmt->execute([$role, $module, $action]);
                }
            }

            $pdo->commit();

            // Log de auditoria
            AuditLog::log('UPDATE', 'role_permissions', 0, ['role' => $role], ['permissions' => $permissions]);

            $this->jsonResponse(['success' => true, 'message' => 'Permissões atualizadas com sucesso']);

        } catch (Exception $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao atualizar permissões: ' . $e->getMessage()]);
        }
    }

    /**
     * Listar permissões de um usuário específico
     */
    public function userPermissions(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('system.admin');

        $userId = (int)($_GET['user_id'] ?? 0);

        if (!$userId) {
            header('Location: ' . BASE_URL . 'users');
            exit;
        }

        $user = User::findById($userId);

        if (!$user) {
            $_SESSION['error'] = 'Usuário não encontrado';
            header('Location: ' . BASE_URL . 'users');
            exit;
        }

        $modules = Permission::getPermissionsForDisplay();
        $userPermissions = $this->getUserPermissions($userId);
        $rolePermissions = $this->getRolePermissions($user['role']);

        $data = [
            'title' => 'Permissões do Usuário: ' . $user['name'],
            'user' => $user,
            'modules' => $modules,
            'userPermissions' => $userPermissions,
            'rolePermissions' => $rolePermissions
        ];

        $this->render('permissions/user', $data);
    }

    /**
     * Atualizar permissões específicas de um usuário
     */
    public function updateUserPermissions(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('system.admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método não permitido']);
            return;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        $permissions = $_POST['permissions'] ?? [];

        if (!$userId) {
            $this->jsonResponse(['success' => false, 'message' => 'Usuário inválido']);
            return;
        }

        $user = User::findById($userId);

        if (!$user) {
            $this->jsonResponse(['success' => false, 'message' => 'Usuário não encontrado']);
            return;
        }

        // Não permitir alterar permissões de admin
        if ($user['role'] === 'admin') {
            $this->jsonResponse(['success' => false, 'message' => 'Não é possível alterar permissões de administradores']);
            return;
        }

        try {
            $pdo = Database::getConnection();
            $pdo->beginTransaction();

            // Remover permissões antigas do usuário
            $stmt = $pdo->prepare("DELETE FROM user_permissions WHERE user_id = ?");
            $stmt->execute([$userId]);

            // Inserir novas permissões customizadas
            if (!empty($permissions)) {
                $stmt = $pdo->prepare("INSERT INTO user_permissions (user_id, module, action, allowed, created_by) VALUES (?, ?, ?, 1, ?)");

                foreach ($permissions as $permission) {
                    if (strpos($permission, '.') !== false) {
                        list($module, $action) = explode('.', $permission, 2);
                        $stmt->execute([$userId, $module, $action, $_SESSION['user_id']]);
                    }
                }
            }

            $pdo->commit();

            // Log de auditoria
            AuditLog::log('UPDATE', 'user_permissions', $userId, ['user_id' => $userId], ['permissions' => $permissions]);

            $this->jsonResponse(['success' => true, 'message' => 'Permissões do usuário atualizadas com sucesso']);

        } catch (Exception $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao atualizar permissões: ' . $e->getMessage()]);
        }
    }

    /**
     * Buscar permissões de um role
     */
    private function getRolePermissions(string $role): array
    {
        $sql = "SELECT CONCAT(module, '.', action) as permission
                FROM role_permissions
                WHERE role = ? AND allowed = 1";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$role]);

        $permissions = [];
        while ($row = $stmt->fetch()) {
            $permissions[] = $row['permission'];
        }

        return $permissions;
    }

    /**
     * Buscar permissões específicas de um usuário
     */
    private function getUserPermissions(int $userId): array
    {
        $sql = "SELECT CONCAT(module, '.', action) as permission
                FROM user_permissions
                WHERE user_id = ? AND allowed = 1";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);

        $permissions = [];
        while ($row = $stmt->fetch()) {
            $permissions[] = $row['permission'];
        }

        return $permissions;
    }

    /**
     * Resposta JSON
     */
    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Renderizar view
     */
    private function render(string $view, array $data = []): void
    {
        extract($data);

        // Capturar o conteúdo da view
        ob_start();
        include APP_PATH . '/Views/' . $view . '.php';
        $content = ob_get_clean();

        // Incluir layout
        include APP_PATH . '/Views/layouts/main.php';
    }
}