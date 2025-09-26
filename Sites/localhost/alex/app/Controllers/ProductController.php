<?php

/**
 * ProductController
 * Gerencia CRUD de produtos
 */
class ProductController
{
    /**
     * Listar produtos
     */
    public function index(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('products.view');

        $filters = [
            'name' => $_GET['name'] ?? '',
            'ncm' => $_GET['ncm'] ?? '',
            'division_type' => $_GET['division_type'] ?? '',
            'is_active' => isset($_GET['is_active']) && $_GET['is_active'] !== '' ? (int)$_GET['is_active'] : null
        ];

        // Paginação
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20; // 20 produtos por página

        $products = Product::search($filters, $page, $perPage);
        $totalProducts = Product::countSearch($filters);
        $totalPages = ceil($totalProducts / $perPage);

        // Dados de paginação
        $pagination = [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalProducts,
            'per_page' => $perPage,
            'has_previous' => $page > 1,
            'has_next' => $page < $totalPages,
            'previous_page' => $page - 1,
            'next_page' => $page + 1,
            'start_item' => ($page - 1) * $perPage + 1,
            'end_item' => min($page * $perPage, $totalProducts)
        ];

        $data = [
            'title' => 'Gerenciar Produtos',
            'products' => $products,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $this->render('products/index', $data);
    }

    /**
     * Exibir formulário de criação
     */
    public function create(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('products.create');

        $data = [
            'title' => 'Cadastrar Produto',
            'product' => [],
            'action' => 'create'
        ];

        $this->render('products/form', $data);
    }

    /**
     * Processar criação do produto
     */
    public function store(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('products.create');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'products');
            exit;
        }

        $data = $this->validateProductData();

        if (!$data) {
            header('Location: ' . BASE_URL . 'products/create');
            exit;
        }

        try {
            $productId = Product::create($data);

            // Log de criação
            AuditLog::log('CREATE', 'products', $productId, null, $data);

            $_SESSION['success'] = 'Produto cadastrado com sucesso!';
            header('Location: ' . BASE_URL . 'products');
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao cadastrar produto: ' . $e->getMessage();
            header('Location: ' . BASE_URL . 'products/create');
        }
        exit;
    }

    /**
     * Exibir formulário de edição
     */
    public function edit(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('products.edit');

        $id = (int)($_GET['id'] ?? 0);

        if (!$id) {
            header('Location: ' . BASE_URL . 'products');
            exit;
        }

        $product = Product::findById($id);

        if (!$product) {
            $_SESSION['error'] = 'Produto não encontrado.';
            header('Location: ' . BASE_URL . 'products');
            exit;
        }

        $data = [
            'title' => 'Editar Produto',
            'product' => $product,
            'action' => 'edit'
        ];

        $this->render('products/form', $data);
    }

    /**
     * Processar atualização do produto
     */
    public function update(): void
    {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => 'Não autorizado']);
                return;
            }
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        if (!Permission::check('products.edit')) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => 'Permissão negada']);
                return;
            }
            $_SESSION['error'] = 'Você não tem permissão para editar produtos.';
            header('Location: ' . BASE_URL . 'products');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => 'Método não permitido']);
                return;
            }
            header('Location: ' . BASE_URL . 'products');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => 'ID do produto é obrigatório']);
                return;
            }
            header('Location: ' . BASE_URL . 'products');
            exit;
        }

        // Para auto-save, usar validação mais flexível
        if ($this->isAjaxRequest()) {
            $data = $this->validateAutoSaveData($id);
        } else {
            $data = $this->validateProductData($id);
        }

        if (!$data) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => 'Dados inválidos']);
                return;
            }
            header('Location: ' . BASE_URL . 'products/edit?id=' . $id);
            exit;
        }

        try {
            // Buscar dados antigos para log
            $oldProduct = Product::findById($id);

            // Para auto-save, usar updateField; para formulário completo, usar update
            if ($this->isAjaxRequest()) {
                // Remover campos desnecessários para auto-save
                unset($data['name']); // name não deve ser atualizado via auto-save
                Product::updateField($id, $data);
            } else {
                Product::update($id, $data);
            }

            // Log de atualização
            AuditLog::log('UPDATE', 'products', $id, $oldProduct, $data);

            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => true, 'message' => 'Produto atualizado com sucesso!']);
                return;
            }

            $_SESSION['success'] = 'Produto atualizado com sucesso!';
            header('Location: ' . BASE_URL . 'products');
        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => 'Erro ao atualizar produto: ' . $e->getMessage()]);
                return;
            }

            $_SESSION['error'] = 'Erro ao atualizar produto: ' . $e->getMessage();
            header('Location: ' . BASE_URL . 'products/edit?id=' . $id);
        }
        exit;
    }

    /**
     * Excluir produto
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

        if (!Permission::check('products.delete')) {
            $this->jsonResponse(['success' => false, 'message' => 'Permissão negada']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        try {
            // Buscar dados do produto antes de excluir para log
            $product = Product::findById($id);

            Product::delete($id);

            // Log de exclusão
            if ($product) {
                AuditLog::log('DELETE', 'products', $id, $product, null);
            }

            $this->jsonResponse(['success' => true, 'message' => 'Produto excluído com sucesso!']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao excluir produto: ' . $e->getMessage()]);
        }
    }

    /**
     * Validar dados do produto
     */
    private function validateProductData(?int $excludeId = null): ?array
    {
        $errors = [];

        $name = trim($_POST['name'] ?? '');
        $ncm = preg_replace('/\D/', '', trim($_POST['ncm'] ?? '')); // Remove tudo que não for dígito
        $description = trim($_POST['description'] ?? '');
        $rfb_min = $_POST['rfb_min'] ?? null;
        $rfb_max = $_POST['rfb_max'] ?? null;
        $weight_kg = $_POST['weight_kg'] ?? null;
        $unit = trim($_POST['unit'] ?? '');
        $division_type = $_POST['division_type'] ?? 'KG';
        $ii_rate = $_POST['ii_rate'] ?? 0;
        $ipi_rate = $_POST['ipi_rate'] ?? 0;
        $pis_rate = $_POST['pis_rate'] ?? 0;
        $cofins_rate = $_POST['cofins_rate'] ?? 0;
        $icms_rate = $_POST['icms_rate'] ?? 0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // Validações
        if (empty($name)) {
            $errors[] = 'Nome do produto é obrigatório.';
        }

        if (empty($ncm)) {
            $errors[] = 'NCM é obrigatório.';
        } elseif (!preg_match('/^\d{8}$/', $ncm)) {
            $errors[] = 'NCM deve conter exatamente 8 dígitos.';
        } elseif (Product::ncmExists($ncm, $excludeId)) {
            $errors[] = 'Este NCM já está cadastrado.';
        }

        if (!in_array($division_type, ['KG', 'QUANTIDADE'])) {
            $errors[] = 'Tipo de divisão inválido.';
        }

        // Função para converter valores com máscara para float
        $parseValue = function($value) {
            if ($value === null || $value === '') return null;
            // Remove pontos de milhares e substitui vírgula por ponto decimal
            $value = str_replace(['.', ','], ['', '.'], $value);
            return (float)$value;
        };

        // Validar valores numéricos
        if ($rfb_min !== null && $rfb_min !== '') {
            $rfb_min = $parseValue($rfb_min);
            if ($rfb_min < 0) {
                $errors[] = 'RFB mínimo deve ser um valor positivo.';
            }
        } else {
            $rfb_min = null;
        }

        if ($rfb_max !== null && $rfb_max !== '') {
            $rfb_max = $parseValue($rfb_max);
            if ($rfb_max < 0) {
                $errors[] = 'RFB máximo deve ser um valor positivo.';
            }
        } else {
            $rfb_max = null;
        }

        if ($rfb_min !== null && $rfb_max !== null && $rfb_min > $rfb_max) {
            $errors[] = 'RFB mínimo não pode ser maior que o RFB máximo.';
        }

        if ($weight_kg !== null && $weight_kg !== '') {
            $weight_kg = $parseValue($weight_kg);
            if ($weight_kg <= 0) {
                $errors[] = 'Peso deve ser um valor positivo.';
            }
        } else {
            $weight_kg = null;
        }

        // Validar alíquotas
        $rates = ['ii_rate', 'ipi_rate', 'pis_rate', 'cofins_rate', 'icms_rate'];
        foreach ($rates as $rate) {
            $$rate = $parseValue($$rate);
            if ($$rate < 0 || $$rate > 100) {
                $errors[] = 'Alíquotas devem estar entre 0% e 100%.';
                break;
            }
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            return null;
        }

        return [
            'name' => $name,
            'ncm' => $ncm,
            'description' => $description ?: null,
            'rfb_min' => $rfb_min,
            'rfb_max' => $rfb_max,
            'weight_kg' => $weight_kg,
            'unit' => $unit ?: null,
            'division_type' => $division_type,
            'ii_rate' => $ii_rate,
            'ipi_rate' => $ipi_rate,
            'pis_rate' => $pis_rate,
            'cofins_rate' => $cofins_rate,
            'icms_rate' => $icms_rate,
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

    /**
     * Verificar se é uma requisição AJAX
     */
    private function isAjaxRequest(): bool
    {
        return (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) || (
            isset($_SERVER['CONTENT_TYPE']) &&
            strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false
        );
    }

    /**
     * Buscar portos disponíveis para configuração (AJAX)
     */
    public function getPorts(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('products.view');

        if (!$this->isAjaxRequest()) {
            $this->jsonResponse(['success' => false, 'message' => 'Requisição inválida']);
            return;
        }

        try {
            $ports = Port::getAllActive();
            $this->jsonResponse(['success' => true, 'data' => $ports]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Salvar configuração RFB por porto (AJAX)
     */
    public function savePortConfig(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('products.edit');

        if (!$this->isAjaxRequest() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Requisição inválida']);
            return;
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        $portId = (int)($_POST['port_id'] ?? 0);
        $rfbMin = $_POST['rfb_min'] ?? null;
        $rfbMax = $_POST['rfb_max'] ?? null;
        $divisionType = $_POST['division_type'] ?? 'PC';

        if (!$productId || !$portId) {
            $this->jsonResponse(['success' => false, 'message' => 'Produto e porto são obrigatórios']);
            return;
        }

        // Função para converter valores monetários (mesma lógica do store)
        $parseValue = function($value) {
            if ($value === null || $value === '') return null;
            // Remove pontos de milhares e substitui vírgula por ponto decimal
            $value = str_replace(['.', ','], ['', '.'], $value);
            return (float)$value;
        };

        // Validar valores RFB
        $errors = [];

        if ($rfbMin !== null && $rfbMin !== '') {
            $rfbMin = $parseValue($rfbMin);
            if ($rfbMin < 0) {
                $errors[] = 'RFB mínimo deve ser um valor positivo.';
            }
        } else {
            $rfbMin = null;
        }

        if ($rfbMax !== null && $rfbMax !== '') {
            $rfbMax = $parseValue($rfbMax);
            if ($rfbMax < 0) {
                $errors[] = 'RFB máximo deve ser um valor positivo.';
            }
        } else {
            $rfbMax = null;
        }

        if ($rfbMin !== null && $rfbMax !== null && $rfbMin > $rfbMax) {
            $errors[] = 'RFB mínimo não pode ser maior que o RFB máximo.';
        }

        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'message' => implode(' ', $errors)]);
            return;
        }

        try {
            // Usar tabela port_product_configs original
            $sql = "INSERT INTO port_product_configs (port_id, product_id, rfb_min_override, rfb_max_override, division_type, created_at)
                    VALUES (:port_id, :product_id, :rfb_min_override, :rfb_max_override, :division_type, NOW())
                    ON DUPLICATE KEY UPDATE
                    rfb_min_override = VALUES(rfb_min_override),
                    rfb_max_override = VALUES(rfb_max_override),
                    division_type = VALUES(division_type),
                    updated_at = NOW()";

            $pdo = Database::getConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'port_id' => $portId,
                'product_id' => $productId,
                'rfb_min_override' => $rfbMin,
                'rfb_max_override' => $rfbMax,
                'division_type' => $divisionType
            ]);

            $this->jsonResponse(['success' => true, 'message' => 'Configuração salva com sucesso!']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao salvar configuração: ' . $e->getMessage()]);
        }
    }

    /**
     * Buscar configurações RFB de um produto por porto (AJAX)
     */
    public function getPortConfigs(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('products.view');

        if (!$this->isAjaxRequest()) {
            $this->jsonResponse(['success' => false, 'message' => 'Requisição inválida']);
            return;
        }

        $productId = (int)($_GET['product_id'] ?? 0);

        if (!$productId) {
            $this->jsonResponse(['success' => false, 'message' => 'ID do produto é obrigatório']);
            return;
        }

        try {
            $sql = "SELECT ppc.*, p.name as port_name
                    FROM port_product_configs ppc
                    LEFT JOIN ports p ON ppc.port_id = p.id
                    WHERE ppc.product_id = :product_id AND ppc.deleted = 0
                    ORDER BY p.name";

            $pdo = Database::getConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['product_id' => $productId]);
            $configs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Mapear campos para compatibilidade com o frontend
            $formattedConfigs = [];
            foreach ($configs as $config) {
                $formattedConfigs[] = [
                    'id' => $config['id'],
                    'port_id' => $config['port_id'],
                    'product_id' => $config['product_id'],
                    'rfb_min' => $config['rfb_min_override'],
                    'rfb_max' => $config['rfb_max_override'],
                    'division_type' => $config['division_type'],
                    'port_name' => $config['port_name']
                ];
            }

            $this->jsonResponse(['success' => true, 'data' => $formattedConfigs]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Remover configuração RFB por porto (AJAX)
     */
    public function deletePortConfig(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('products.edit');

        if (!$this->isAjaxRequest() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Requisição inválida']);
            return;
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        $portId = (int)($_POST['port_id'] ?? 0);

        if (!$productId || !$portId) {
            $this->jsonResponse(['success' => false, 'message' => 'Produto e porto são obrigatórios']);
            return;
        }

        try {
            $sql = "UPDATE port_product_configs
                    SET deleted = 1, deleted_at = NOW()
                    WHERE product_id = :product_id AND port_id = :port_id";

            $pdo = Database::getConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'product_id' => $productId,
                'port_id' => $portId
            ]);

            $this->jsonResponse(['success' => true, 'message' => 'Configuração removida com sucesso!']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao remover configuração: ' . $e->getMessage()]);
        }
    }

    /**
     * Validar dados para auto-save (campos específicos)
     */
    private function validateAutoSaveData(int $productId): ?array
    {
        $errors = [];
        $data = [];

        // Verificar se o produto existe
        $product = Product::findById($productId);
        if (!$product) {
            return null;
        }

        // Validar apenas os campos enviados
        if (isset($_POST['description'])) {
            $data['description'] = trim($_POST['description']);
        }

        if (isset($_POST['ncm'])) {
            $ncm = preg_replace('/\D/', '', trim($_POST['ncm'])); // Remove tudo que não for dígito
            if (!empty($ncm)) {
                if (!preg_match('/^\d{8}$/', $ncm)) {
                    $errors[] = 'NCM deve conter exatamente 8 dígitos.';
                } elseif (Product::ncmExists($ncm, $productId)) {
                    $errors[] = 'Este NCM já está cadastrado.';
                } else {
                    $data['ncm'] = $ncm;
                }
            }
        }

        // Função para converter valores com máscara para float
        $parseValue = function($value) {
            if ($value === null || $value === '') return null;
            // Remove pontos de milhares e substitui vírgula por ponto decimal
            $value = str_replace(['.', ','], ['', '.'], $value);
            return (float)$value;
        };

        // Validar alíquotas
        $taxFields = ['ii_rate', 'ipi_rate', 'pis_rate', 'cofins_rate', 'icms_rate'];
        foreach ($taxFields as $field) {
            if (isset($_POST[$field])) {
                $value = $parseValue($_POST[$field]);
                if ($value < 0 || $value > 100) {
                    $errors[] = "Alíquota deve estar entre 0 e 100%.";
                } else {
                    $data[$field] = $value;
                }
            }
        }

        if (isset($_POST['division_type'])) {
            $divisionType = $_POST['division_type'];
            if (in_array($divisionType, ['KG', 'QUANTIDADE'])) {
                $data['division_type'] = $divisionType;
            }
        }

        // Se há erros, retornar null
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            return null;
        }

        // Se não há dados para atualizar, retornar null
        if (empty($data)) {
            return null;
        }

        return $data;
    }

    /**
     * Buscar produtos por AJAX
     */
    public function search(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('products.view');

        if (!$this->isAjaxRequest()) {
            $this->jsonResponse(['success' => false, 'message' => 'Requisição inválida']);
            return;
        }

        $term = trim($_GET['term'] ?? '');

        if (empty($term)) {
            $this->jsonResponse(['success' => true, 'data' => []]);
            return;
        }

        try {
            $products = Product::searchByTerm($term);

            // Formatar dados para Select2
            $results = [];
            foreach ($products as $product) {
                // Prioridade: variant_description > description > name
                $displayDesc = '';
                if (!empty($product['variant_description'])) {
                    $displayDesc = $product['variant_description'];
                } elseif (!empty($product['description'])) {
                    $displayDesc = $product['description'];
                } elseif (!empty($product['name'])) {
                    $displayDesc = $product['name'];
                } else {
                    $displayDesc = 'Produto sem descrição';
                }

                $results[] = [
                    'id' => $product['id'],
                    'text' => $displayDesc . ' - NCM: ' . $product['ncm'],
                    'ncm' => $product['ncm'],
                    'description' => $displayDesc,
                    'division_type' => $product['division_type'],
                    'ii_rate' => $product['ii_rate'],
                    'ipi_rate' => $product['ipi_rate'],
                    'pis_rate' => $product['pis_rate'],
                    'cofins_rate' => $product['cofins_rate'],
                    'icms_rate' => $product['icms_rate'],
                    'rfb_min' => $product['rfb_min'] ?? '0',
                    'rfb_max' => $product['rfb_max'] ?? '0'
                ];
            }

            $this->jsonResponse(['success' => true, 'data' => $results]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}