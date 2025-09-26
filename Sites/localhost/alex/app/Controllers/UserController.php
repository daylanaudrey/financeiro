<?php

/**
 * UserController
 * Gerencia CRUD de usuários - RESTRITO A ADMIN
 */
class UserController
{
    /**
     * Listar usuários
     */
    public function index(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('users.view');

        $filters = [
            'name' => $_GET['name'] ?? '',
            'email' => $_GET['email'] ?? '',
            'role' => $_GET['role'] ?? '',
            'is_active' => isset($_GET['is_active']) && $_GET['is_active'] !== '' ? (int)$_GET['is_active'] : null
        ];

        $users = User::getAll($filters);

        $data = [
            'title' => 'Gerenciar Usuários',
            'users' => $users,
            'filters' => $filters,
            'roleOptions' => User::getRoleOptions()
        ];

        $this->render('users/index', $data);
    }

    /**
     * Exibir formulário de criação
     */
    public function create(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('users.create');

        $data = [
            'title' => 'Cadastrar Usuário',
            'action' => 'create',
            'user' => [],
            'roleOptions' => User::getRoleOptions()
        ];

        $this->render('users/form', $data);
    }

    /**
     * Processar criação de usuário
     */
    public function store(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('users.create');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'users/create');
            exit;
        }

        $data = $this->validateUserData();

        if (!$data) {
            header('Location: ' . BASE_URL . 'users/create');
            exit;
        }

        try {
            $userId = User::create($data);

            // Log de criação
            AuditLog::log('CREATE', 'users', $userId, null, $data);

            $_SESSION['success'] = 'Usuário cadastrado com sucesso!';
            header('Location: ' . BASE_URL . 'users');
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao cadastrar usuário: ' . $e->getMessage();
            header('Location: ' . BASE_URL . 'users/create');
        }
        exit;
    }

    /**
     * Exibir formulário de edição
     */
    public function edit(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('users.edit');

        $id = (int)($_GET['id'] ?? 0);

        if (!$id) {
            header('Location: ' . BASE_URL . 'users');
            exit;
        }

        $user = User::findById($id);

        if (!$user) {
            $_SESSION['error'] = 'Usuário não encontrado.';
            header('Location: ' . BASE_URL . 'users');
            exit;
        }

        // Não permitir editar o próprio usuário
        if ($user['id'] == $_SESSION['user_id']) {
            $_SESSION['error'] = 'Você não pode editar seu próprio usuário. Use o perfil para isso.';
            header('Location: ' . BASE_URL . 'users');
            exit;
        }

        $data = [
            'title' => 'Editar Usuário',
            'action' => 'edit',
            'user' => $user,
            'roleOptions' => User::getRoleOptions()
        ];

        $this->render('users/form', $data);
    }

    /**
     * Processar atualização de usuário
     */
    public function update(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('users.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'users');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            $_SESSION['error'] = 'ID inválido.';
            header('Location: ' . BASE_URL . 'users');
            exit;
        }

        // Buscar dados antigos para log
        $oldUser = User::findById($id);

        if (!$oldUser) {
            $_SESSION['error'] = 'Usuário não encontrado.';
            header('Location: ' . BASE_URL . 'users');
            exit;
        }

        // Não permitir editar o próprio usuário
        if ($oldUser['id'] == $_SESSION['user_id']) {
            $_SESSION['error'] = 'Você não pode editar seu próprio usuário.';
            header('Location: ' . BASE_URL . 'users');
            exit;
        }

        $data = $this->validateUserData(true, $id);

        if (!$data) {
            header('Location: ' . BASE_URL . 'users/edit?id=' . $id);
            exit;
        }

        try {
            User::update($id, $data);

            // Log de atualização
            AuditLog::log('UPDATE', 'users', $id, $oldUser, $data);

            $_SESSION['success'] = 'Usuário atualizado com sucesso!';
            header('Location: ' . BASE_URL . 'users');
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar usuário: ' . $e->getMessage();
            header('Location: ' . BASE_URL . 'users/edit?id=' . $id);
        }
        exit;
    }

    /**
     * Excluir usuário
     */
    public function delete(): void
    {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            $this->jsonResponse(['success' => false, 'message' => 'Não autorizado']);
            return;
        }

        if (!Permission::check('users.delete')) {
            $this->jsonResponse(['success' => false, 'message' => 'Acesso negado']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método não permitido']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        // Buscar dados do usuário antes de excluir para log
        $user = User::findById($id);

        if (!$user) {
            $this->jsonResponse(['success' => false, 'message' => 'Usuário não encontrado']);
            return;
        }

        // Não permitir excluir o próprio usuário
        if ($user['id'] == $_SESSION['user_id']) {
            $this->jsonResponse(['success' => false, 'message' => 'Você não pode excluir seu próprio usuário']);
            return;
        }

        // Não permitir excluir outros admins
        if ($user['role'] === 'admin') {
            $this->jsonResponse(['success' => false, 'message' => 'Não é possível excluir outros administradores']);
            return;
        }

        try {
            User::delete($id);

            // Log de exclusão
            AuditLog::log('DELETE', 'users', $id, $user, null);

            $this->jsonResponse(['success' => true, 'message' => 'Usuário excluído com sucesso!']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao excluir usuário: ' . $e->getMessage()]);
        }
    }

    /**
     * Validar dados do usuário
     */
    private function validateUserData(bool $isUpdate = false, int $userId = null): ?array
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'operator';
        $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

        // Validações básicas
        if (empty($name)) {
            $_SESSION['error'] = 'Nome é obrigatório.';
            return null;
        }

        if (empty($email)) {
            $_SESSION['error'] = 'Email é obrigatório.';
            return null;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Email inválido.';
            return null;
        }

        // Verificar se o email já existe
        if (User::emailExists($email, $userId)) {
            $_SESSION['error'] = 'Este email já está sendo usado por outro usuário.';
            return null;
        }

        // Validar senha (obrigatória na criação)
        if (!$isUpdate && empty($password)) {
            $_SESSION['error'] = 'Senha é obrigatória.';
            return null;
        }

        if (!empty($password) && strlen($password) < 6) {
            $_SESSION['error'] = 'A senha deve ter pelo menos 6 caracteres.';
            return null;
        }

        // Validar role
        $validRoles = array_keys(User::getRoleOptions());
        if (!in_array($role, $validRoles)) {
            $_SESSION['error'] = 'Perfil de acesso inválido.';
            return null;
        }

        $data = [
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'is_active' => $isActive
        ];

        // Só incluir senha se foi fornecida
        if (!empty($password)) {
            $data['password'] = $password;
        }

        return $data;
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