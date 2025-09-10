<?php
require_once 'BaseController.php';
require_once 'AuthMiddleware.php';

class TransactionController extends BaseController {
    private $transactionModel;
    private $accountModel;
    private $auditModel;
    private $categoryModel;
    private $costCenterModel;
    
    public function __construct() {
        parent::__construct();
        $this->transactionModel = new Transaction();
        $this->accountModel = new Account();
        $this->auditModel = new AuditLog();
        $this->categoryModel = new Category();
        $this->costCenterModel = new CostCenter();
    }
    
    public function index() {
        $user = AuthMiddleware::requireAuth();
        
        // Por enquanto, usar org_id = 1
        $orgId = 1;
        
        // Parâmetros de paginação
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 25; // Reduzir para melhor performance
        $offset = ($page - 1) * $limit;
        
        // Parâmetros de filtro
        $filters = [
            'account_id' => $_GET['account_id'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'cost_center_id' => $_GET['cost_center_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'kind' => $_GET['kind'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => trim($_GET['search'] ?? ''),
            'vencidos' => $_GET['vencidos'] ?? false,
            'order_by' => $_GET['order_by'] ?? 'data_vencimento' // Ordenar por data de vencimento por padrão
        ];
        
        // Buscar transações com filtros
        $result = $this->transactionModel->getTransactionsWithFilters($orgId, $filters, $limit, $offset);
        $transactions = $result['transactions'];
        $totalRecords = $result['total'];
        $totals = $result['totals']; // Soma dos valores filtrados
        
        // Calcular paginação
        $totalPages = ceil($totalRecords / $limit);
        
        $accounts = $this->accountModel->getActiveAccountsByOrg($orgId);
        $categories = $this->categoryModel->getActiveCategories($orgId);
        $costCenters = $this->costCenterModel->getActiveCostCenters($orgId);
        
        // Calcular balanço do mês atual
        $currentYear = date('Y');
        $currentMonth = date('m');
        $monthlyBalance = $this->transactionModel->getMonthlyBalance($orgId, $currentYear, $currentMonth);
        
        $statusOptions = $this->transactionModel->getStatusOptions();
        $kindOptions = $this->transactionModel->getKindOptions();
        
        $data = [
            'title' => 'Lançamentos - Sistema Financeiro',
            'page' => 'transactions',
            'user' => $user,
            'transactions' => $transactions,
            'accounts' => $accounts,
            'categories' => $categories,
            'costCenters' => $costCenters,
            'monthlyBalance' => $monthlyBalance,
            'statusOptions' => $statusOptions,
            'kindOptions' => $kindOptions,
            'filters' => $filters,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalRecords' => $totalRecords,
                'limit' => $limit
            ],
            'totals' => $totals
        ];
        
        $this->render('layout', $data);
    }
    
    public function transfers() {
        // Verificar se está logado
        $user = AuthMiddleware::requireAuth();
        
        // Por enquanto, usar org_id = 1
        $orgId = 1;
        
        // Instanciar models necessários
        $accountModel = new Account();
        
        // Buscar contas ativas para os selects
        $accounts = $accountModel->getActiveAccountsByOrg($orgId);
        
        $data = [
            'title' => 'Transferências - Sistema Financeiro',
            'page' => 'transfers',
            'user' => $user,
            'accounts' => $accounts
        ];
        
        $this->render('layout', $data);
    }
    
    public function getTransfers() {
        try {
            $user = AuthMiddleware::requireAuth();
            $orgId = 1; // Por enquanto fixo
            
            // Buscar transferências (transações com transfer_pair_id)
            $transfers = $this->transactionModel->getTransfers($orgId);
            
            $this->json([
                'success' => true,
                'transfers' => $transfers
            ]);
            
        } catch (Exception $e) {
            error_log("EXCEPTION in TransactionController::getTransfers(): " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function create() {
        error_log("=== TRANSACTION CREATE START ===");
        error_log("POST data: " . json_encode($_POST));
        error_log("SESSION data: " . json_encode($_SESSION ?? []));
        
        try {
            error_log("Calling AuthMiddleware::requireAuth()");
            $user = AuthMiddleware::requireAuth();
            error_log("Auth successful, user: " . json_encode($user));
            
            $accountId = (int)($_POST['account_id'] ?? 0);
            $kind = $_POST['kind'] ?? '';
            $valor = floatval($_POST['valor'] ?? 0);
            $dataCompetencia = $_POST['data_competencia'] ?? '';
            $dataPagamento = !empty($_POST['data_pagamento']) ? $_POST['data_pagamento'] : null;
            $status = $_POST['status'] ?? 'confirmado';
            $categoryId = (int)($_POST['category_id'] ?? 0) ?: null;
            $contactId = (int)($_POST['contact_id'] ?? 0) ?: null;
            $descricao = trim($_POST['descricao'] ?? '');
            $observacoes = trim($_POST['observacoes'] ?? '') ?: null;
            
            // Campos de recorrência - tratar string vazia como NULL
            $recurrenceType = !empty($_POST['recurrence_type']) ? $_POST['recurrence_type'] : null;
            $recurrenceCount = (int)($_POST['recurrence_count'] ?? 1);
            $recurrenceEndDate = !empty($_POST['recurrence_end_date']) ? $_POST['recurrence_end_date'] : null;
            
            error_log("Parsed data - Account ID: $accountId, Kind: $kind, Valor: $valor, Desc: $descricao");
            
            // Validações
            error_log("Starting validations...");
            
            if (!$accountId) {
                error_log("VALIDATION FAILED: Account ID missing");
                $this->json(['success' => false, 'message' => 'Conta é obrigatória']);
                return;
            }
            
            if (empty($kind)) {
                error_log("VALIDATION FAILED: Kind missing");
                $this->json(['success' => false, 'message' => 'Tipo de lançamento é obrigatório']);
                return;
            }
            
            if ($valor <= 0) {
                error_log("VALIDATION FAILED: Invalid valor: $valor");
                $this->json(['success' => false, 'message' => 'Valor deve ser maior que zero']);
                return;
            }
            
            if (empty($dataCompetencia)) {
                error_log("VALIDATION FAILED: Data competencia missing");
                $this->json(['success' => false, 'message' => 'Data de competência é obrigatória']);
                return;
            }
            
            if (empty($descricao)) {
                error_log("VALIDATION FAILED: Descricao missing");
                $this->json(['success' => false, 'message' => 'Descrição é obrigatória']);
                return;
            }
            
            error_log("All validations passed!");
            
            // Verificar se a conta existe e é ativa
            error_log("Checking account with ID: $accountId");
            $account = $this->accountModel->findById($accountId);
            if (!$account || !$account['ativo']) {
                error_log("ACCOUNT VALIDATION FAILED: Account not found or inactive");
                $this->json(['success' => false, 'message' => 'Conta inválida ou inativa']);
                return;
            }
            error_log("Account found: " . json_encode($account));
            
            $transactionData = [
                'org_id' => 1, // Por enquanto fixo
                'account_id' => $accountId,
                'kind' => $kind,
                'valor' => $valor,
                'data_competencia' => $dataCompetencia,
                'data_pagamento' => $dataPagamento,
                'status' => $status,
                'category_id' => $categoryId,
                'contact_id' => $contactId,
                'descricao' => $descricao,
                'observacoes' => $observacoes,
                'recurrence_type' => $recurrenceType,
                'recurrence_count' => $recurrenceCount,
                'recurrence_end_date' => $recurrenceEndDate,
                'parent_transaction_id' => null,
                'recurrence_sequence' => 0,
                'created_by' => $user['id']
            ];
            
            error_log("Creating transaction with data: " . json_encode($transactionData));
            $transactionId = $this->transactionModel->createTransaction($transactionData);
            error_log("Transaction creation result - ID: " . ($transactionId ?: 'FAILED'));
            
            if ($transactionId) {
                // Gerar lançamentos recorrentes se configurado
                $recurringIds = [];
                if ($recurrenceType && $status === 'agendado') {
                    $recurringIds = $this->generateRecurringTransactions($transactionData, $transactionId);
                    
                    // Atualizar descrição do lançamento original para incluir sequência
                    if (!empty($recurringIds)) {
                        $newDescricao = $descricao . " (Recorrência 1/{$recurrenceCount})";
                        $this->transactionModel->update($transactionId, ['descricao' => $newDescricao]);
                    }
                }
                
                // Log da auditoria
                $auditMessage = "Lançamento criado: {$descricao}";
                if (!empty($recurringIds)) {
                    $auditMessage .= " (Gerados " . count($recurringIds) . " lançamentos recorrentes)";
                }
                
                $this->auditModel->logUserAction(
                    $user['id'],
                    1,
                    'transaction',
                    'create',
                    $transactionId,
                    null,
                    $transactionData,
                    $auditMessage
                );
                
                error_log("SUCCESS: Transaction created successfully with ID: $transactionId");
                
                $message = 'Lançamento criado com sucesso!';
                if (!empty($recurringIds)) {
                    $message .= ' Foram gerados ' . count($recurringIds) . ' lançamentos recorrentes.';
                }
                
                $this->json([
                    'success' => true,
                    'message' => $message,
                    'transaction_id' => $transactionId,
                    'recurring_ids' => $recurringIds
                ]);
            } else {
                error_log("ERROR: Transaction creation failed");
                $this->json(['success' => false, 'message' => 'Erro ao criar lançamento']);
            }
            
        } catch (Exception $e) {
            error_log("EXCEPTION in TransactionController::create(): " . $e->getMessage());
            error_log("Exception file: " . $e->getFile() . ":" . $e->getLine());
            error_log("Exception trace: " . $e->getTraceAsString());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
        
        error_log("=== TRANSACTION CREATE END ===");
    }
    
    public function transfer() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            // Dados da transferência
            $accountFromId = (int)($_POST['account_from'] ?? 0);
            $accountToId = (int)($_POST['account_to'] ?? 0);
            $valor = $this->convertBrazilianCurrencyToDecimal($_POST['valor'] ?? '0');
            $dataCompetencia = $_POST['data_competencia'] ?? date('Y-m-d');
            $descricao = trim($_POST['descricao'] ?? 'Transferência entre contas');
            $observacoes = trim($_POST['observacoes'] ?? '') ?: null;
            
            // Validações
            if (!$accountFromId || !$accountToId) {
                $this->json(['success' => false, 'message' => 'Contas de origem e destino são obrigatórias']);
                return;
            }
            
            if ($accountFromId === $accountToId) {
                $this->json(['success' => false, 'message' => 'Conta de origem deve ser diferente da conta de destino']);
                return;
            }
            
            if ($valor <= 0) {
                $this->json(['success' => false, 'message' => 'Valor deve ser maior que zero']);
                return;
            }
            
            // Verificar se as contas existem
            $accountModel = new Account();
            $accountFrom = $accountModel->findById($accountFromId);
            $accountTo = $accountModel->findById($accountToId);
            
            if (!$accountFrom || !$accountTo) {
                $this->json(['success' => false, 'message' => 'Uma ou ambas as contas não foram encontradas']);
                return;
            }
            
            // Criar par de transferências
            $transferId = $this->transactionModel->createTransfer($accountFromId, $accountToId, $valor, $dataCompetencia, $descricao, $observacoes, $user['id']);
            
            if ($transferId) {
                // Log da auditoria
                $this->auditModel->logUserAction(
                    $user['id'],
                    1,
                    'transaction',
                    'transfer',
                    $transferId,
                    null,
                    [
                        'account_from' => $accountFromId,
                        'account_to' => $accountToId,
                        'valor' => $valor,
                        'descricao' => $descricao
                    ],
                    "Transferência criada: {$accountFrom['nome']} → {$accountTo['nome']}"
                );
                
                $this->json([
                    'success' => true,
                    'message' => 'Transferência criada com sucesso!',
                    'transfer_id' => $transferId
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao criar transferência']);
            }
            
        } catch (Exception $e) {
            error_log("EXCEPTION in TransactionController::transfer(): " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function update() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            $transactionId = (int)($_POST['id'] ?? 0);
            $accountId = (int)($_POST['account_id'] ?? 0);
            $kind = $_POST['kind'] ?? '';
            $valor = floatval($_POST['valor'] ?? 0);
            $dataCompetencia = $_POST['data_competencia'] ?? '';
            $dataPagamento = !empty($_POST['data_pagamento']) ? $_POST['data_pagamento'] : null;
            $status = $_POST['status'] ?? 'confirmado';
            $categoryId = (int)($_POST['category_id'] ?? 0) ?: null;
            $contactId = (int)($_POST['contact_id'] ?? 0) ?: null;
            $descricao = trim($_POST['descricao'] ?? '');
            $observacoes = trim($_POST['observacoes'] ?? '') ?: null;
            
            if (!$transactionId) {
                $this->json(['success' => false, 'message' => 'ID do lançamento é obrigatório']);
            }
            
            // Buscar dados atuais para auditoria
            $oldData = $this->transactionModel->findById($transactionId);
            if (!$oldData) {
                $this->json(['success' => false, 'message' => 'Lançamento não encontrado']);
            }
            
            $updateData = [
                'account_id' => $accountId,
                'kind' => $kind,
                'valor' => $valor,
                'data_competencia' => $dataCompetencia,
                'data_pagamento' => $dataPagamento,
                'status' => $status,
                'category_id' => $categoryId,
                'contact_id' => $contactId,
                'descricao' => $descricao,
                'observacoes' => $observacoes
            ];
            
            $success = $this->transactionModel->updateTransaction($transactionId, $updateData);
            
            if ($success) {
                // Log da auditoria
                $this->auditModel->logUserAction(
                    $user['id'],
                    1,
                    'transaction',
                    'update',
                    $transactionId,
                    $oldData,
                    $updateData,
                    "Lançamento atualizado: {$descricao}"
                );
                
                $this->json(['success' => true, 'message' => 'Lançamento atualizado com sucesso!']);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao atualizar lançamento']);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar lançamento: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function delete() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            $transactionId = (int)($_POST['id'] ?? 0);
            
            if (!$transactionId) {
                $this->json(['success' => false, 'message' => 'ID do lançamento é obrigatório']);
            }
            
            $transaction = $this->transactionModel->findById($transactionId);
            if (!$transaction) {
                $this->json(['success' => false, 'message' => 'Lançamento não encontrado']);
            }
            
            $success = $this->transactionModel->deleteTransaction($transactionId);
            
            if ($success) {
                // Log da auditoria
                $this->auditModel->logUserAction(
                    $user['id'],
                    1,
                    'transaction',
                    'delete',
                    $transactionId,
                    $transaction,
                    null,
                    "Lançamento excluído: {$transaction['descricao']}"
                );
                
                $this->json(['success' => true, 'message' => 'Lançamento excluído com sucesso!']);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao excluir lançamento']);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao excluir lançamento: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function getTransaction() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            $transactionId = (int)($_GET['id'] ?? 0);
            
            if (!$transactionId) {
                $this->json(['success' => false, 'message' => 'ID do lançamento é obrigatório']);
            }
            
            $transaction = $this->transactionModel->findById($transactionId);
            
            if ($transaction) {
                $this->json(['success' => true, 'transaction' => $transaction]);
            } else {
                $this->json(['success' => false, 'message' => 'Lançamento não encontrado']);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao buscar lançamento: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    private function generateRecurringTransactions($baseTransactionData, $originalTransactionId) {
        $recurringIds = [];
        
        try {
            $recurrenceType = $baseTransactionData['recurrence_type'];
            $recurrenceCount = $baseTransactionData['recurrence_count'];
            $recurrenceEndDate = $baseTransactionData['recurrence_end_date'];
            $baseDate = new DateTime($baseTransactionData['data_competencia']);
            
            error_log("Generating recurring transactions - Type: $recurrenceType, Count: $recurrenceCount");
            
            // Mapear tipos de recorrência para períodos
            $periodMap = [
                'weekly' => '+1 week',
                'monthly' => '+1 month',
                'quarterly' => '+3 months',
                'biannual' => '+6 months',
                'yearly' => '+1 year'
            ];
            
            if (!isset($periodMap[$recurrenceType])) {
                error_log("Invalid recurrence type: $recurrenceType");
                return $recurringIds;
            }
            
            $period = $periodMap[$recurrenceType];
            $endDate = $recurrenceEndDate ? new DateTime($recurrenceEndDate) : null;
            
            // Gerar lançamentos futuros (exceto o primeiro que já foi criado)
            for ($i = 1; $i < $recurrenceCount; $i++) {
                $nextDate = clone $baseDate;
                for ($j = 0; $j < $i; $j++) {
                    $nextDate->modify($period);
                }
                
                // Verificar se não excedeu a data limite
                if ($endDate && $nextDate > $endDate) {
                    break;
                }
                
                // Criar dados para o próximo lançamento
                $nextTransactionData = $baseTransactionData;
                $nextTransactionData['data_competencia'] = $nextDate->format('Y-m-d');
                $nextTransactionData['parent_transaction_id'] = $originalTransactionId;
                $nextTransactionData['recurrence_sequence'] = $i + 1;
                
                // Remover campos de recorrência dos lançamentos filhos
                unset($nextTransactionData['recurrence_type']);
                unset($nextTransactionData['recurrence_count']);
                unset($nextTransactionData['recurrence_end_date']);
                
                // Ajustar descrição para indicar que é recorrente
                $sequenceNumber = $i + 1;
                $nextTransactionData['descricao'] = $baseTransactionData['descricao'] . " (Recorrência $sequenceNumber/$recurrenceCount)";
                
                error_log("Creating recurring transaction for date: " . $nextDate->format('Y-m-d'));
                
                // Criar o lançamento recorrente
                $recurringId = $this->transactionModel->createTransaction($nextTransactionData);
                
                if ($recurringId) {
                    $recurringIds[] = $recurringId;
                    error_log("Created recurring transaction ID: $recurringId");
                } else {
                    error_log("Failed to create recurring transaction for iteration $i");
                }
            }
            
            error_log("Generated " . count($recurringIds) . " recurring transactions");
            
        } catch (Exception $e) {
            error_log("Error generating recurring transactions: " . $e->getMessage());
        }
        
        return $recurringIds;
    }
    
    public function launch() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            $transactionId = (int)($_POST['id'] ?? 0);
            
            if (!$transactionId) {
                $this->json(['success' => false, 'message' => 'ID do lançamento é obrigatório']);
                return;
            }
            
            $transaction = $this->transactionModel->findById($transactionId);
            if (!$transaction) {
                $this->json(['success' => false, 'message' => 'Lançamento não encontrado']);
                return;
            }
            
            if ($transaction['status'] !== 'agendado') {
                $this->json(['success' => false, 'message' => 'Apenas lançamentos agendados podem ser lançados']);
                return;
            }
            
            // Parâmetros opcionais para ajustar valor e data
            $newValue = $_POST['valor'] ?? null;
            $paymentDate = $_POST['data_pagamento'] ?? date('Y-m-d');
            
            // Atualizar status para confirmado e definir data de pagamento
            $updateData = [
                'status' => 'confirmado',
                'data_pagamento' => $paymentDate
            ];
            
            // Se valor foi alterado, incluir no update
            if ($newValue && $newValue !== $transaction['valor']) {
                // Converter valor formatado brasileiro para decimal
                $valorNumerico = $this->convertBrazilianCurrencyToDecimal($newValue);
                if ($valorNumerico !== false) {
                    $updateData['valor'] = $valorNumerico;
                }
            }
            
            $success = $this->transactionModel->updateTransaction($transactionId, $updateData);
            
            if ($success) {
                // Log da auditoria
                $this->auditModel->logUserAction(
                    $user['id'],
                    1,
                    'transaction',
                    'launch',
                    $transactionId,
                    $transaction,
                    $updateData,
                    "Lançamento agendado executado: {$transaction['descricao']}"
                );
                
                $this->json(['success' => true, 'message' => 'Lançamento executado com sucesso!']);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao executar lançamento']);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao executar lançamento: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function confirm() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            $transactionId = (int)($_POST['id'] ?? 0);
            
            if (!$transactionId) {
                $this->json(['success' => false, 'message' => 'ID do lançamento é obrigatório']);
                return;
            }
            
            $transaction = $this->transactionModel->findById($transactionId);
            if (!$transaction) {
                $this->json(['success' => false, 'message' => 'Lançamento não encontrado']);
                return;
            }
            
            if ($transaction['status'] === 'confirmado') {
                $this->json(['success' => false, 'message' => 'Lançamento já está confirmado']);
                return;
            }
            
            // Data de confirmação (pagamento)
            $paymentDate = $_POST['data_pagamento'] ?? date('Y-m-d');
            
            // Parâmetro opcional para ajustar valor
            $newValue = $_POST['valor'] ?? null;
            
            // Atualizar status para confirmado e definir data de pagamento
            $updateData = [
                'status' => 'confirmado',
                'data_pagamento' => $paymentDate
            ];
            
            // Se valor foi alterado, incluir no update
            if ($newValue && $newValue !== $transaction['valor']) {
                // Converter valor formatado brasileiro para decimal
                $valorNumerico = $this->convertBrazilianCurrencyToDecimal($newValue);
                if ($valorNumerico !== false) {
                    $updateData['valor'] = $valorNumerico;
                }
            }
            
            $success = $this->transactionModel->updateTransaction($transactionId, $updateData);
            
            if ($success) {
                // Log da auditoria
                $this->auditModel->logUserAction(
                    $user['id'],
                    1,
                    'transaction',
                    'confirm',
                    $transaction,
                    $updateData,
                    "Lançamento confirmado: {$transaction['descricao']}"
                );
                
                $this->json(['success' => true, 'message' => 'Lançamento confirmado com sucesso!']);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao confirmar lançamento']);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao confirmar lançamento: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    private function convertBrazilianCurrencyToDecimal($value) {
        if (empty($value)) return false;
        
        // Remove "R$ " e espaços
        $cleaned = str_replace(['R$', ' '], '', $value);
        
        // Se tem vírgula, é formato brasileiro (ex: "1.234,56")
        if (strpos($cleaned, ',') !== false) {
            // Remove pontos (milhares) e substitui vírgula (decimal) por ponto
            $cleaned = str_replace('.', '', $cleaned);
            $cleaned = str_replace(',', '.', $cleaned);
        }
        
        $numericValue = floatval($cleaned);
        return $numericValue > 0 ? $numericValue : false;
    }
}