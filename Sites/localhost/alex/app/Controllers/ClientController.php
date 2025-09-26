<?php

/**
 * ClientController
 * Gerencia CRUD de clientes
 */
class ClientController
{
    /**
     * Listar clientes
     */
    public function index(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('clients.view');

        $filters = [
            'name' => $_GET['name'] ?? '',
            'document' => $_GET['document'] ?? '',
            'type' => $_GET['type'] ?? '',
            'city' => $_GET['city'] ?? '',
            'state' => $_GET['state'] ?? '',
            'is_active' => isset($_GET['is_active']) && $_GET['is_active'] !== '' ? (int)$_GET['is_active'] : null
        ];

        $clients = Client::search($filters);

        $data = [
            'title' => 'Gerenciar Importadores',
            'clients' => $clients,
            'filters' => $filters
        ];

        $this->render('clients/index', $data);
    }

    /**
     * Exibir formulário de criação
     */
    public function create(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('clients.create');

        if ($_SESSION['user_role'] === 'viewer') {
            $_SESSION['error'] = 'Você não tem permissão para criar importadores.';
            header('Location: ' . BASE_URL . 'clients');
            exit;
        }

        $data = [
            'title' => 'Cadastrar Importador',
            'client' => [],
            'action' => 'create'
        ];

        $this->render('clients/form', $data);
    }

    /**
     * Processar criação do cliente
     */
    public function store(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('clients.create');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'clients');
            exit;
        }

        $data = $this->validateClientData();

        if (!$data) {
            header('Location: ' . BASE_URL . 'clients/create');
            exit;
        }

        try {
            Client::create($data);
            $_SESSION['success'] = 'Importador cadastrado com sucesso!';
            header('Location: ' . BASE_URL . 'clients');
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao cadastrar importador: ' . $e->getMessage();
            header('Location: ' . BASE_URL . 'clients/create');
        }
        exit;
    }

    /**
     * Exibir formulário de edição
     */
    public function edit(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('clients.edit');

        $id = (int)($_GET['id'] ?? 0);

        if (!$id) {
            header('Location: ' . BASE_URL . 'clients');
            exit;
        }

        $client = Client::findById($id);

        if (!$client) {
            $_SESSION['error'] = 'Importador não encontrado.';
            header('Location: ' . BASE_URL . 'clients');
            exit;
        }

        $data = [
            'title' => 'Editar Importador',
            'client' => $client,
            'action' => 'edit'
        ];

        $this->render('clients/form', $data);
    }

    /**
     * Processar atualização do cliente
     */
    public function update(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('clients.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'clients');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            header('Location: ' . BASE_URL . 'clients');
            exit;
        }

        $data = $this->validateClientData($id);

        if (!$data) {
            header('Location: ' . BASE_URL . 'clients/edit?id=' . $id);
            exit;
        }

        try {
            Client::update($id, $data);
            $_SESSION['success'] = 'Importador atualizado com sucesso!';
            header('Location: ' . BASE_URL . 'clients');
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar importador: ' . $e->getMessage();
            header('Location: ' . BASE_URL . 'clients/edit?id=' . $id);
        }
        exit;
    }

    /**
     * Excluir cliente
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

        if (!Permission::check('clients.delete')) {
            $this->jsonResponse(['success' => false, 'message' => 'Permissão negada']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        try {
            Client::delete($id);
            $this->jsonResponse(['success' => true, 'message' => 'Importador excluído com sucesso!']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao excluir importador: ' . $e->getMessage()]);
        }
    }

    /**
     * Validar dados do cliente
     */
    private function validateClientData(?int $excludeId = null): ?array
    {
        $errors = [];

        $type = 'PJ'; // Sempre Pessoa Jurídica
        $name = trim($_POST['name'] ?? '');
        $document = trim($_POST['document'] ?? '');
        $ie = trim($_POST['ie'] ?? '');
        $im = trim($_POST['im'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $number = trim($_POST['number'] ?? '');
        $complement = trim($_POST['complement'] ?? '');
        $neighborhood = trim($_POST['neighborhood'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $zip_code = trim($_POST['zip_code'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contact_name = trim($_POST['contact_name'] ?? '');
        $incoterm = trim($_POST['incoterm'] ?? '');
        $payment_terms = trim($_POST['payment_terms'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // Tipo sempre será PJ (Pessoa Jurídica)

        if (empty($name)) {
            $errors[] = 'Razão Social é obrigatória.';
        }

        if (empty($document)) {
            $errors[] = 'CNPJ é obrigatório.';
        } else {
            // Validar CNPJ
            $cleanDocument = preg_replace('/[^0-9]/', '', $document);

            if (!Client::validateCNPJ($cleanDocument)) {
                $errors[] = 'CNPJ inválido.';
            }

            // Verificar se documento já existe
            if (Client::documentExists($document, $excludeId)) {
                $errors[] = 'Este CNPJ já está cadastrado.';
            }
        }

        // Validar email se fornecido
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido.';
        }

        // Validar estado se fornecido
        if (!empty($state) && strlen($state) !== 2) {
            $errors[] = 'Estado deve ter 2 caracteres (UF).';
        }

        // Validar CEP se fornecido
        if (!empty($zip_code) && !preg_match('/^\d{5}-?\d{3}$/', $zip_code)) {
            $errors[] = 'CEP deve estar no formato 12345-678.';
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            return null;
        }

        return [
            'type' => $type,
            'name' => $name,
            'document' => $document,
            'ie' => $ie ?: null,
            'im' => $im ?: null,
            'address' => $address ?: null,
            'number' => $number ?: null,
            'complement' => $complement ?: null,
            'neighborhood' => $neighborhood ?: null,
            'city' => $city ?: null,
            'state' => strtoupper($state) ?: null,
            'zip_code' => $zip_code ?: null,
            'phone' => $phone ?: null,
            'mobile' => $mobile ?: null,
            'email' => $email ?: null,
            'contact_name' => $contact_name ?: null,
            'incoterm' => $incoterm ?: null,
            'payment_terms' => $payment_terms ?: null,
            'is_active' => $is_active
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