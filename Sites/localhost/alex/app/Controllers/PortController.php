<?php

/**
 * PortController
 * Gerencia CRUD de portos
 */
class PortController
{
    /**
     * Listar portos
     */
    public function index(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('ports.view');

        $filters = [
            'name' => $_GET['name'] ?? '',
            'city' => $_GET['city'] ?? '',
            'state' => $_GET['state'] ?? '',
            'is_active' => isset($_GET['is_active']) ? (int)$_GET['is_active'] : null
        ];

        $ports = Port::search($filters);

        $data = [
            'title' => 'Gerenciar Portos',
            'ports' => $ports,
            'filters' => $filters
        ];

        $this->render('ports/index', $data);
    }

    /**
     * Exibir formulário de criação
     */
    public function create(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('ports.create');

        $data = [
            'title' => 'Cadastrar Porto',
            'port' => [],
            'action' => 'create'
        ];

        $this->render('ports/form', $data);
    }

    /**
     * Processar criação do porto
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'ports');
            exit;
        }

        Permission::requireAuth();
        Permission::requirePermission('ports.create');

        $data = $this->validatePortData();

        if (!$data) {
            header('Location: ' . BASE_URL . 'ports/create');
            exit;
        }

        try {
            Port::create($data);
            $_SESSION['success'] = 'Porto cadastrado com sucesso!';
            header('Location: ' . BASE_URL . 'ports');
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao cadastrar porto: ' . $e->getMessage();
            header('Location: ' . BASE_URL . 'ports/create');
        }
        exit;
    }

    /**
     * Exibir formulário de edição
     */
    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);

        if (!$id) {
            header('Location: ' . BASE_URL . 'ports');
            exit;
        }

        Permission::requireAuth();
        Permission::requirePermission('ports.edit');

        $port = Port::findById($id);

        if (!$port) {
            $_SESSION['error'] = 'Porto não encontrado.';
            header('Location: ' . BASE_URL . 'ports');
            exit;
        }

        $data = [
            'title' => 'Editar Porto',
            'port' => $port,
            'action' => 'edit'
        ];

        $this->render('ports/form', $data);
    }

    /**
     * Processar atualização do porto
     */
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'ports');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            header('Location: ' . BASE_URL . 'ports');
            exit;
        }

        Permission::requireAuth();
        Permission::requirePermission('ports.edit');

        $data = $this->validatePortData($id);

        if (!$data) {
            header('Location: ' . BASE_URL . 'ports/edit?id=' . $id);
            exit;
        }

        try {
            Port::update($id, $data);
            $_SESSION['success'] = 'Porto atualizado com sucesso!';
            header('Location: ' . BASE_URL . 'ports');
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar porto: ' . $e->getMessage();
            header('Location: ' . BASE_URL . 'ports/edit?id=' . $id);
        }
        exit;
    }

    /**
     * Excluir porto
     */
    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método não permitido']);
            return;
        }

        // Verificar permissões
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            $this->jsonResponse(['success' => false, 'message' => 'Não autorizado']);
            return;
        }

        if (!Permission::check('ports.delete')) {
            $this->jsonResponse(['success' => false, 'message' => 'Permissão negada']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        try {
            Port::delete($id);
            $this->jsonResponse(['success' => true, 'message' => 'Porto excluído com sucesso!']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao excluir porto: ' . $e->getMessage()]);
        }
    }

    /**
     * Validar dados do porto
     */
    private function validatePortData(?int $excludeId = null): ?array
    {
        $errors = [];

        $name = trim($_POST['name'] ?? '');
        $prefix = trim(strtoupper($_POST['prefix'] ?? ''));
        $customs_code = trim($_POST['customs_code'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $country = trim($_POST['country'] ?? 'Brasil');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $notes = trim($_POST['notes'] ?? '');

        // Validações
        if (empty($name)) {
            $errors[] = 'Nome do porto é obrigatório.';
        }

        if (empty($prefix)) {
            $errors[] = 'Prefixo do porto é obrigatório.';
        } elseif (strlen($prefix) > 10) {
            $errors[] = 'Prefixo deve ter no máximo 10 caracteres.';
        } elseif (Port::prefixExists($prefix, $excludeId)) {
            $errors[] = 'Este prefixo já está em uso.';
        }

        if (empty($city)) {
            $errors[] = 'Cidade é obrigatória.';
        }

        // Validar código de recinto alfandegário se fornecido
        if (!empty($customs_code) && strlen($customs_code) > 20) {
            $errors[] = 'Código de recinto alfandegário deve ter no máximo 20 caracteres.';
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            return null;
        }

        return [
            'name' => $name,
            'prefix' => $prefix,
            'customs_code' => $customs_code ?: null,
            'city' => $city,
            'state' => $state ?: null,
            'country' => $country,
            'is_active' => $is_active,
            'notes' => $notes ?: null
        ];
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


    /**
     * Resposta JSON
     */
    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}