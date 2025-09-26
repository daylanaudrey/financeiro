<?php

/**
 * ProcessItemController
 * Gerencia CRUD de itens dos processos
 */
class ProcessItemController
{
    /**
     * Listar itens de um processo
     */
    public function index(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('process_items.view');

        $processId = (int)($_GET['process_id'] ?? 0);

        if (!$processId) {
            header('Location: ' . BASE_URL . 'processes');
            exit;
        }

        $process = Process::findById($processId);
        if (!$process) {
            $_SESSION['error'] = 'Processo não encontrado.';
            header('Location: ' . BASE_URL . 'processes');
            exit;
        }

        $items = ProcessItem::getByProcessId($processId);
        $totals = ProcessItem::getTotalsByProcessId($processId);

        $data = [
            'title' => 'Itens do Processo',
            'process' => $process,
            'items' => $items,
            'totals' => $totals
        ];

        $this->render('process_items/index', $data);
    }

    /**
     * Exibir formulário de criação
     */
    public function create(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('process_items.create');


        $processId = (int)($_GET['process_id'] ?? 0);

        if (!$processId) {
            header('Location: ' . BASE_URL . 'processes');
            exit;
        }

        $process = Process::findById($processId);
        if (!$process) {
            $_SESSION['error'] = 'Processo não encontrado.';
            header('Location: ' . BASE_URL . 'processes');
            exit;
        }

        $products = Product::getAllActive();

        // Debug - verificar se produtos chegaram
        error_log("Produtos carregados: " . count($products));
        foreach ($products as $product) {
            error_log("Produto: " . $product['description']);
        }

        $data = [
            'title' => 'Adicionar Item',
            'process' => $process,
            'products' => $products,
            'item' => [],
            'action' => 'create'
        ];

        $this->render('process_items/form', $data);
    }

    /**
     * Processar criação do item
     */
    public function store(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('process_items.create');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'processes');
            exit;
        }

        // Atualizar produto se foi alterado
        $this->updateProductIfChanged();

        $data = $this->validateItemData();

        if (!$data) {
            header('Location: ' . BASE_URL . 'process-items/create?process_id=' . ($_POST['process_id'] ?? 0));
            exit;
        }

        try {
            $itemId = ProcessItem::create($data);

            // Auto-salvar configuração RFB do porto se não existir (conforme tarefas.txt)

            // Recalcular fretes de todos os itens (peso total mudou)
            ProcessItem::recalculateAllFreights($data['process_id']);

            // Atualizar totais do processo
            ProcessItem::updateProcessTotals($data['process_id']);

            $_SESSION['success'] = 'Item adicionado com sucesso!';
            header('Location: ' . BASE_URL . 'process-items?process_id=' . $data['process_id']);
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao adicionar item: ' . $e->getMessage();
            header('Location: ' . BASE_URL . 'process-items/create?process_id=' . ($_POST['process_id'] ?? 0));
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
            header('Location: ' . BASE_URL . 'processes');
            exit;
        }

        Permission::requireAuth();
        Permission::requirePermission('process_items.edit');

        $item = ProcessItem::findById($id);

        if (!$item) {
            $_SESSION['error'] = 'Item não encontrado.';
            header('Location: ' . BASE_URL . 'processes');
            exit;
        }

        $process = Process::findById($item['process_id']);
        $products = Product::getAllActive();

        $data = [
            'title' => 'Editar Item',
            'process' => $process,
            'products' => $products,
            'item' => $item,
            'action' => 'edit'
        ];

        $this->render('process_items/form', $data);
    }

    /**
     * Processar atualização do item
     */
    public function update(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('process_items.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'processes');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            header('Location: ' . BASE_URL . 'processes');
            exit;
        }

        // Atualizar produto se foi alterado
        $this->updateProductIfChanged();

        $data = $this->validateItemData($id);

        if (!$data) {
            header('Location: ' . BASE_URL . 'process-items/edit?id=' . $id);
            exit;
        }

        try {
            ProcessItem::update($id, $data);

            // Auto-salvar configuração RFB do porto se não existir (conforme tarefas.txt)

            // Recalcular fretes de todos os itens (peso total mudou)
            ProcessItem::recalculateAllFreights($data['process_id']);

            // Atualizar totais do processo
            ProcessItem::updateProcessTotals($data['process_id']);

            $_SESSION['success'] = 'Item atualizado com sucesso!';
            header('Location: ' . BASE_URL . 'process-items?process_id=' . $data['process_id']);
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar item: ' . $e->getMessage();
            header('Location: ' . BASE_URL . 'process-items/edit?id=' . $id);
        }
        exit;
    }

    /**
     * Excluir item
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

        if (!Permission::check('process_items.delete')) {
            $this->jsonResponse(['success' => false, 'message' => 'Permissão negada']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        try {
            // Obter processo_id antes de excluir
            $item = ProcessItem::findById($id);
            if (!$item) {
                $this->jsonResponse(['success' => false, 'message' => 'Item não encontrado']);
                return;
            }

            $processId = $item['process_id'];

            ProcessItem::delete($id);

            // Recalcular frete de todos os itens restantes após exclusão
            ProcessItem::recalculateAllFreights($processId);

            // Atualizar totais do processo
            ProcessItem::updateProcessTotals($processId);

            $this->jsonResponse(['success' => true, 'message' => 'Item excluído com sucesso!']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao excluir item: ' . $e->getMessage()]);
        }
    }

    /**
     * Obter peso bruto total dos itens de um processo (para cálculo do Frete TTL/KG)
     */
    public function getTotalWeight()
    {
        header('Content-Type: application/json');

        try {
            $processId = (int)($_GET['process_id'] ?? 0);

            if (!$processId) {
                $this->jsonResponse(['success' => false, 'message' => 'Process ID é obrigatório.']);
                return;
            }

            $sql = "SELECT SUM(gross_weight) as total_gross_weight FROM process_items WHERE process_id = :process_id";
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['process_id' => $processId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $totalWeight = (float)($result['total_gross_weight'] ?? 0);

            $this->jsonResponse([
                'success' => true,
                'total_gross_weight' => $totalWeight
            ]);

        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao buscar peso total: ' . $e->getMessage()]);
        }
    }

    /**
     * Converter valor monetário formatado para float
     */
    private function parseMonetaryValue($value): float
    {
        if (empty($value)) return 0;

        // Converter para string se necessário
        $value = (string)$value;

        // Detectar formato: se tem vírgula após o último ponto, é formato americano
        $lastDot = strrpos($value, '.');
        $lastComma = strrpos($value, ',');

        if ($lastComma !== false && $lastComma > $lastDot) {
            // Formato brasileiro (1.222,22)
            // Remove pontos de milhar e substitui vírgula por ponto
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } else {
            // Formato americano (1,222.22) - caso ainda chegue assim
            // Remove vírgulas (separadores de milhar)
            $value = str_replace(',', '', $value);
        }

        return (float)$value;
    }

    /**
     * Validar dados do item
     */
    private function validateItemData(?int $excludeId = null): ?array
    {
        $errors = [];

        $processId = (int)($_POST['process_id'] ?? 0);
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = $this->parseMonetaryValue($_POST['quantity'] ?? 0);
        $unit = trim($_POST['unit'] ?? 'UN');

        // Usar parseMonetaryValue para campos com máscara
        $grossWeight = $this->parseMonetaryValue($_POST['gross_weight'] ?? 0);
        $weightDiscount = $this->parseMonetaryValue($_POST['weight_discount'] ?? 0);
        $netWeight = $this->parseMonetaryValue($_POST['net_weight'] ?? 0);
        $unitPriceUsd = $this->parseMonetaryValue($_POST['unit_price_usd'] ?? 0);
        $totalFobInput = $this->parseMonetaryValue($_POST['total_fob_input'] ?? 0);
        $freightTtlKg = $this->parseMonetaryValue($_POST['freight_ttl_kg'] ?? 0);
        $freightUsd = $this->parseMonetaryValue($_POST['freight_usd'] ?? 0);
        $insuranceUsd = $this->parseMonetaryValue($_POST['insurance_usd'] ?? 0);

        $notes = trim($_POST['notes'] ?? '');

        // Validações obrigatórias
        if (!$processId) {
            $errors[] = 'Processo é obrigatório.';
        }

        if (!$productId) {
            $errors[] = 'Produto é obrigatório.';
        }

        if ($quantity <= 0) {
            $errors[] = 'Quantidade deve ser maior que zero.';
        }

        // Não validar unitPriceUsd pois agora é calculado automaticamente

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            return null;
        }

        // Adicionar campos RFB e impostos se estiverem presentes
        $data = [
            'process_id' => $processId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'unit' => $unit,
            'gross_weight' => $grossWeight,
            'weight_discount' => $weightDiscount,
            'net_weight' => $netWeight,
            'unit_price_usd' => $unitPriceUsd,
            'total_fob_input' => $totalFobInput,
            'freight_ttl_kg' => $freightTtlKg,
            'freight_usd' => $freightUsd,
            'insurance_usd' => $insuranceUsd,
            'notes' => $notes ?: null
        ];

        // Adicionar valores RFB se estiverem presentes
        if (isset($_POST['rfb_used'])) {
            $data['rfb_used'] = $this->parseMonetaryValue($_POST['rfb_used']);
        }
        if (isset($_POST['rfb_margin'])) {
            $data['rfb_margin'] = $this->parseMonetaryValue($_POST['rfb_margin']);
        }
        if (isset($_POST['inv_used'])) {
            $data['inv_used'] = $this->parseMonetaryValue($_POST['inv_used']);
        }
        if (isset($_POST['rfb_option'])) {
            $data['rfb_option'] = $_POST['rfb_option'];
        }

        // Adicionar alíquotas de impostos editadas
        if (isset($_POST['ii_rate'])) {
            $data['ii_rate'] = $this->parseMonetaryValue($_POST['ii_rate']);
        }
        if (isset($_POST['ipi_rate'])) {
            $data['ipi_rate'] = $this->parseMonetaryValue($_POST['ipi_rate']);
        }
        if (isset($_POST['pis_rate'])) {
            $data['pis_rate'] = $this->parseMonetaryValue($_POST['pis_rate']);
        }
        if (isset($_POST['cofins_rate'])) {
            $data['cofins_rate'] = $this->parseMonetaryValue($_POST['cofins_rate']);
        }
        if (isset($_POST['icms_rate'])) {
            $data['icms_rate'] = $this->parseMonetaryValue($_POST['icms_rate']);
        }

        return $data;
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
     * Atualizar produto se informações foram alteradas
     * Nova lógica: Se a descrição for alterada, cria um produto filho (variação)
     */
    private function updateProductIfChanged(): void
    {
        if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
            return;
        }

        $productId = (int)$_POST['product_id'];

        // Buscar produto original
        $product = Product::findById($productId);
        if (!$product) {
            return;
        }

        // Verificar se a descrição foi alterada
        if (isset($_POST['product_description']) && !empty($_POST['product_description'])) {
            $newDescription = trim($_POST['product_description']);

            // Determinar a descrição atual do produto (considerando se é variação ou não)
            $currentDescription = $product['is_variant'] && !empty($product['variant_description'])
                ? $product['variant_description']
                : $product['description'];

            // Se a descrição é diferente da atual
            if ($newDescription !== $currentDescription) {
                // Verificar se já existe uma variação com esta descrição
                $existingVariant = Product::findByNCMAndDescription($product['ncm'], $newDescription);

                if ($existingVariant) {
                    // Usar a variação existente
                    $_POST['product_id'] = $existingVariant['id'];
                    error_log("Usando variação existente: ID {$existingVariant['id']} para descrição: {$newDescription}");
                } else {
                    // Determinar o produto pai (se o atual já for variação, usar o pai dele)
                    $parentProductId = $product['parent_id'] ?? $productId;

                    // Criar nova variação (produto filho)
                    try {
                        $variantId = Product::createVariant($parentProductId, $newDescription);

                        // Atualizar o POST para usar o novo produto filho
                        $_POST['product_id'] = $variantId;

                        // Log para auditoria
                        error_log("Produto variação criado: ID {$variantId} para produto pai {$parentProductId} com descrição: {$newDescription}");

                    } catch (Exception $e) {
                        error_log("Erro ao criar variação do produto: " . $e->getMessage());
                        error_log("Produto pai: {$parentProductId}, Nova descrição: {$newDescription}");
                        // Em caso de erro, continua usando o produto original
                    }
                }

                // Não atualizar a descrição do produto original
                return;
            }
        }

        // Para outras alterações (alíquotas, etc), atualizar apenas se não for uma variação
        if (!$product['is_variant']) {
            $updateData = [];

            if (isset($_POST['product_ncm']) && !empty($_POST['product_ncm'])) {
                $updateData['ncm'] = trim($_POST['product_ncm']);
            }

            if (isset($_POST['ii_rate'])) {
                $updateData['ii_rate'] = (float)$_POST['ii_rate'];
            }

            if (isset($_POST['ipi_rate'])) {
                $updateData['ipi_rate'] = (float)$_POST['ipi_rate'];
            }

            if (isset($_POST['pis_rate'])) {
                $updateData['pis_rate'] = (float)$_POST['pis_rate'];
            }

            if (isset($_POST['cofins_rate'])) {
                $updateData['cofins_rate'] = (float)$_POST['cofins_rate'];
            }

            if (isset($_POST['icms_rate'])) {
                $updateData['icms_rate'] = (float)$_POST['icms_rate'];
            }

            if (isset($_POST['product_division']) && !empty($_POST['product_division'])) {
                $updateData['division_type'] = $_POST['product_division'];
            }

            // Se há dados para atualizar, atualizar o produto
            if (!empty($updateData)) {
                try {
                    Product::update($productId, $updateData);
                } catch (Exception $e) {
                    // Log do erro mas continua o processo
                    error_log("Erro ao atualizar produto: " . $e->getMessage());
                }
            }
        }
    }

}