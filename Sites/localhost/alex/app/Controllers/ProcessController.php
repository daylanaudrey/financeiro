<?php

/**
 * ProcessController
 * Gerencia CRUD de processos de importação
 */
class ProcessController
{
    /**
     * Listar processos
     */
    public function index(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('processes.view');

        $filters = [
            'code' => $_GET['code'] ?? '',
            'client_id' => $_GET['client_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'type' => $_GET['type'] ?? '',
            'modal' => $_GET['modal'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];

        $processes = Process::search($filters);
        $clients = Client::getAllActive(); // Para o filtro de importadores

        $data = [
            'title' => 'Gerenciar Processos',
            'processes' => $processes,
            'clients' => $clients,
            'filters' => $filters,
            'status_options' => Process::getStatusOptions(),
            'type_options' => Process::getTypeOptions(),
            'modal_options' => Process::getModalOptions()
        ];

        $this->render('processes/index', $data);
    }

    /**
     * Exibir formulário de criação
     */
    public function create(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('processes.create');

        $clients = Client::getAllActive();
        $nextCode = Process::getNextProcessNumber();

        // Buscar taxa PTAX atual
        $currentRate = ExchangeRate::getCurrentRate('USD');
        $exchangeRate = $currentRate ? $currentRate['rate'] : 5.00; // Fallback

        $ports = Port::getAllActive();

        $data = [
            'title' => 'Cadastrar Processo',
            'process' => [
                'code' => $nextCode,
                'exchange_rate' => $exchangeRate
            ],
            'clients' => $clients,
            'ports' => $ports,
            'status_options' => Process::getStatusOptions(),
            'type_options' => Process::getTypeOptions(),
            'modal_options' => Process::getModalOptions(),
            'incoterm_options' => Process::getIncotermOptions(),
            'current_ptax_rate' => $exchangeRate,
            'action' => 'create'
        ];

        $this->render('processes/form', $data);
    }

    /**
     * Processar criação do processo
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'processes');
            exit;
        }

        Permission::requireAuth();
        Permission::requirePermission('processes.create');

        $data = $this->validateProcessData();

        if (!$data) {
            header('Location: ' . BASE_URL . 'processes/create');
            exit;
        }

        try {
            $processId = Process::create($data);
            $_SESSION['success'] = 'Processo cadastrado com sucesso! Agora adicione os itens.';
            header('Location: ' . BASE_URL . 'process-items/create?process_id=' . $processId);
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao cadastrar processo: ' . $e->getMessage();
            header('Location: ' . BASE_URL . 'processes/create');
        }
        exit;
    }

    /**
     * Exibir formulário de edição
     */
    public function edit(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('processes.edit');

        $id = (int)($_GET['id'] ?? 0);

        if (!$id) {
            header('Location: ' . BASE_URL . 'processes');
            exit;
        }

        $process = Process::findById($id);

        if (!$process) {
            $_SESSION['error'] = 'Processo não encontrado.';
            header('Location: ' . BASE_URL . 'processes');
            exit;
        }

        $clients = Client::getAllActive();

        // Buscar taxa PTAX atual (apenas para referência, não altera se processo finalizado)
        $currentRate = ExchangeRate::getCurrentRate('USD');
        $currentPtaxRate = $currentRate ? $currentRate['rate'] : 5.00;

        $ports = Port::getAllActive();

        $data = [
            'title' => 'Editar Processo',
            'process' => $process,
            'clients' => $clients,
            'ports' => $ports,
            'status_options' => Process::getStatusOptions(),
            'type_options' => Process::getTypeOptions(),
            'modal_options' => Process::getModalOptions(),
            'incoterm_options' => Process::getIncotermOptions(),
            'current_ptax_rate' => $currentPtaxRate,
            'action' => 'edit'
        ];

        $this->render('processes/form', $data);
    }

    /**
     * Processar atualização do processo
     */
    public function update(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('processes.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'processes');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            header('Location: ' . BASE_URL . 'processes');
            exit;
        }

        $data = $this->validateProcessData($id);

        if (!$data) {
            header('Location: ' . BASE_URL . 'processes/edit?id=' . $id);
            exit;
        }

        try {
            Process::update($id, $data);
            $_SESSION['success'] = 'Processo atualizado com sucesso!';
            header('Location: ' . BASE_URL . 'processes');
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar processo: ' . $e->getMessage();
            header('Location: ' . BASE_URL . 'processes/edit?id=' . $id);
        }
        exit;
    }

    /**
     * Excluir processo
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

        if (!Permission::check('processes.delete')) {
            $this->jsonResponse(['success' => false, 'message' => 'Permissão negada']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        try {
            Process::delete($id);
            $this->jsonResponse(['success' => true, 'message' => 'Processo excluído com sucesso!']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao excluir processo: ' . $e->getMessage()]);
        }
    }

    /**
     * Validar dados do processo
     */
    private function validateProcessData(?int $excludeId = null): ?array
    {
        $errors = [];

        $code = trim($_POST['code'] ?? '');
        $client_id = (int)($_POST['client_id'] ?? 0);
        $type = $_POST['type'] ?? '';
        $status = $_POST['status'] ?? '';
        $process_date = $_POST['process_date'] ?? '';
        $arrival_date = $_POST['arrival_date'] ?? '';
        $clearance_date = $_POST['clearance_date'] ?? '';
        $estimated_arrival_date = $_POST['estimated_arrival_date'] ?? '';
        $confirmed_arrival_date = $_POST['confirmed_arrival_date'] ?? '';
        $free_time_days = (int)($_POST['free_time_days'] ?? 7);
        $modal = $_POST['modal'] ?? '';
        $container_number = trim($_POST['container_number'] ?? '');
        $bl_number = trim($_POST['bl_number'] ?? '');
        $incoterm = $_POST['incoterm'] ?? '';

        // Função auxiliar para converter valores monetários formatados
        $parseMonetaryValue = function($value) {
            if (empty($value)) return 0;

            // Detectar formato: se tem vírgula após o último ponto, é formato americano
            $lastDot = strrpos($value, '.');
            $lastComma = strrpos($value, ',');

            if ($lastComma !== false && $lastComma > $lastDot) {
                // Formato brasileiro (32.323.232,32)
                // Remove pontos de milhar e substitui vírgula por ponto
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                // Formato americano (32,323,232.32) - caso ainda chegue assim
                // Remove vírgulas (separadores de milhar)
                $value = str_replace(',', '', $value);
            }

            return (float)$value;
        };

        $total_fob_usd = $parseMonetaryValue($_POST['total_fob_usd'] ?? 0);
        $total_freight_usd = $parseMonetaryValue($_POST['total_freight_usd'] ?? 0);
        $total_insurance_usd = $parseMonetaryValue($_POST['total_insurance_usd'] ?? 0);
        $exchange_rate = (float)($_POST['exchange_rate'] ?? 0);
        $destination_port_id = !empty($_POST['destination_port_id']) ? (int)$_POST['destination_port_id'] : null;
        $notes = trim($_POST['notes'] ?? '');

        // Validações obrigatórias
        if (empty($code)) {
            $errors[] = 'Código do processo é obrigatório.';
        } else {
            // Verificar se código já existe
            if (Process::codeExists($code, $excludeId)) {
                $errors[] = 'Este código de processo já existe.';
            }
        }

        if (!$client_id) {
            $errors[] = 'Importador é obrigatório.';
        }

        if (empty($type) || !in_array($type, ['NUMERARIO', 'MAPA'])) {
            $errors[] = 'Tipo de processo é obrigatório.';
        }

        if (empty($status) || !array_key_exists($status, Process::getStatusOptions())) {
            $errors[] = 'Status é obrigatório.';
        }

        if (empty($process_date)) {
            $errors[] = 'Data do processo é obrigatória.';
        }

        if (empty($modal) || !array_key_exists($modal, Process::getModalOptions())) {
            $errors[] = 'Modal de transporte é obrigatório.';
        }

        if (empty($incoterm) || !array_key_exists($incoterm, Process::getIncotermOptions())) {
            $errors[] = 'Incoterm é obrigatório.';
        }

        if ($exchange_rate <= 0) {
            $errors[] = 'Taxa de câmbio deve ser maior que zero.';
        }

        // Validar datas
        // Removida validação que impedia data de chegada anterior ao processo
        // pois em importações é comum a mercadoria chegar antes do processo ser registrado

        if (!empty($clearance_date) && !empty($arrival_date)) {
            if (strtotime($clearance_date) < strtotime($arrival_date)) {
                $errors[] = 'Data de desembaraço não pode ser anterior à data de chegada.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            return null;
        }

        // Calcular valores
        $total_cif_usd = Process::calculateCifUsd($total_fob_usd, $total_freight_usd, $total_insurance_usd);
        $total_cif_brl = Process::calculateCifBrl($total_cif_usd, $exchange_rate);

        return [
            'code' => $code,
            'client_id' => $client_id,
            'type' => $type,
            'status' => $status,
            'process_date' => $process_date,
            'arrival_date' => $arrival_date ?: null,
            'clearance_date' => $clearance_date ?: null,
            'estimated_arrival_date' => $estimated_arrival_date ?: null,
            'confirmed_arrival_date' => $confirmed_arrival_date ?: null,
            'free_time_days' => $free_time_days,
            'modal' => $modal,
            'destination_port_id' => $destination_port_id,
            'container_number' => $container_number ?: null,
            'bl_number' => $bl_number ?: null,
            'incoterm' => $incoterm,
            'total_fob_usd' => $total_fob_usd,
            'total_freight_usd' => $total_freight_usd,
            'total_insurance_usd' => $total_insurance_usd,
            'total_cif_usd' => $total_cif_usd,
            'exchange_rate' => $exchange_rate,
            'total_cif_brl' => $total_cif_brl,
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