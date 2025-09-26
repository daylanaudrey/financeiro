<?php
require_once 'BaseController.php';
require_once 'AuthMiddleware.php';
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../models/Account.php';
require_once __DIR__ . '/../models/AuditLog.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Contact.php';
require_once __DIR__ . '/../models/CreditCard.php';
require_once __DIR__ . '/../services/TeamNotificationService.php';

class TransactionController extends BaseController {
    private $transactionModel;
    private $accountModel;
    private $auditModel;
    private $categoryModel;
    private $costCenterModel;
    private $contactModel;
    private $creditCardModel;
    private $teamNotificationService;
    
    public function __construct() {
        parent::__construct();
        $this->transactionModel = new Transaction();
        $this->accountModel = new Account();
        $this->auditModel = new AuditLog();
        $this->categoryModel = new Category();
        $this->contactModel = new Contact();
        $this->creditCardModel = new CreditCard();
        $this->teamNotificationService = new TeamNotificationService();
    }
    
    public function index() {
        $user = AuthMiddleware::requireAuth();
        
        $orgId = $this->getCurrentOrgId();
        
        // Parâmetros de paginação
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 25; // Reduzir para melhor performance
        $offset = ($page - 1) * $limit;
        
        // Parâmetros de filtro
        $filters = [
            'account_id' => $_GET['account_id'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'contact_id' => $_GET['contact_id'] ?? '',
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
        $contacts = $this->contactModel->getContactsByOrg($orgId);
        
        // Calcular balanço do mês atual (incluindo baixas parciais)
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
            'contacts' => $contacts,
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
        
        $orgId = $this->getCurrentOrgId();
        
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
            $orgId = $this->getCurrentOrgId(); // Por enquanto fixo
            
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
            
            $paymentMethod = $_POST['payment_method'] ?? 'account';
            $accountId = (int)($_POST['account_id'] ?? 0);
            $creditCardId = (int)($_POST['credit_card_id'] ?? 0);
            $kind = $_POST['kind'] ?? '';
            $valor = floatval($_POST['valor'] ?? 0);
            $dataCompetencia = $_POST['data_competencia'] ?? '';
            $dataPagamento = !empty($_POST['data_pagamento']) ? $_POST['data_pagamento'] : null;
            $status = $_POST['status'] ?? 'confirmado';
            
            // Se o status é confirmado e data_pagamento não foi fornecida, usar data_competencia
            if ($status === 'confirmado' && !$dataPagamento) {
                $dataPagamento = $dataCompetencia;
            }
            
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
            
            if ($paymentMethod === 'credit_card') {
                if (!$creditCardId) {
                    error_log("VALIDATION FAILED: Credit card ID missing");
                    $this->json(['success' => false, 'message' => 'Cartão de crédito é obrigatório']);
                    return;
                }
                if ($kind === 'entrada') {
                    error_log("VALIDATION FAILED: Credit card cannot have income transaction");
                    $this->json(['success' => false, 'message' => 'Cartão de crédito só permite despesas']);
                    return;
                }
            } else {
                if (!$accountId) {
                    error_log("VALIDATION FAILED: Account ID missing");
                    $this->json(['success' => false, 'message' => 'Conta é obrigatória']);
                    return;
                }
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
            
            // Verificar conta ou cartão baseado no método de pagamento
            if ($paymentMethod === 'credit_card') {
                error_log("Checking credit card with ID: $creditCardId");
                $creditCard = $this->creditCardModel->findById($creditCardId);
                if (!$creditCard || !$creditCard['ativo']) {
                    error_log("CREDIT CARD VALIDATION FAILED: Card not found or inactive");
                    $this->json(['success' => false, 'message' => 'Cartão de crédito inválido ou inativo']);
                    return;
                }
                
                // Verificar limite disponível
                $limiteDisponivel = $creditCard['limite_total'] - $creditCard['limite_usado'];
                if ($valor > $limiteDisponivel) {
                    error_log("CREDIT CARD VALIDATION FAILED: Insufficient limit");
                    $this->json(['success' => false, 'message' => 'Limite insuficiente no cartão de crédito']);
                    return;
                }
                
                error_log("Credit card found: " . json_encode($creditCard));
                $accountId = null; // Para transações com cartão, account_id é nulo
            } else {
                error_log("Checking account with ID: $accountId");
                $account = $this->accountModel->findById($accountId);
                if (!$account || !$account['ativo']) {
                    error_log("ACCOUNT VALIDATION FAILED: Account not found or inactive");
                    $this->json(['success' => false, 'message' => 'Conta inválida ou inativa']);
                    return;
                }
                error_log("Account found: " . json_encode($account));
                $creditCardId = null; // Para transações normais, credit_card_id é nulo
            }
            
            $transactionData = [
                'org_id' => $this->getCurrentOrgId(),
                'account_id' => $accountId,
                'credit_card_id' => $creditCardId,
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
                    'transactions',
                    'create',
                    $transactionId,
                    null,
                    $transactionData,
                    $auditMessage
                );
                
                // Atualizar limite do cartão se for transação com cartão
                if ($paymentMethod === 'credit_card' && $creditCardId) {
                    $this->creditCardModel->updateLimiteUsado($creditCardId, $valor, 'add');
                    error_log("Credit card limit updated for card ID: $creditCardId");
                }
                
                error_log("SUCCESS: Transaction created successfully with ID: $transactionId");

                // Enviar notificações WhatsApp APENAS para transações confirmadas
                if ($status === 'confirmado') {
                    try {
                        $orgId = $this->getCurrentOrgId();
                        $transactionData = [
                            'id' => $transactionId,
                            'descricao' => $descricao,
                            'valor' => $valor,
                            'kind' => $kind,
                            'data_competencia' => $dataCompetencia,
                            'categoria' => $categoryId ? $this->categoryModel->findById($categoryId)['nome'] ?? null : null,
                            'conta' => $paymentMethod === 'account' && $accountId ?
                                      $this->accountModel->findById($accountId)['nome'] ?? null :
                                      ($paymentMethod === 'credit_card' && $creditCardId ?
                                       $this->creditCardModel->findById($creditCardId)['nome'] ?? null : null)
                        ];

                        // Enviar notificação com opção assíncrona (mas ainda processando diretamente)
                        $this->teamNotificationService->notifyNewTransaction($orgId, $transactionData);
                        error_log("WhatsApp notification sent for transaction ID: $transactionId");
                    } catch (Exception $notificationError) {
                        error_log("Failed to send WhatsApp notification: " . $notificationError->getMessage());
                        // Continuar mesmo se a notificação falhar
                    }
                } else {
                    error_log("Notification skipped - transaction status: $status (only confirmed transactions receive notifications)");
                }

                $message = 'Lançamento criado com sucesso!';
                if (!empty($recurringIds)) {
                    $message .= ' Foram gerados ' . count($recurringIds) . ' lançamentos recorrentes.';
                }
                
                error_log("=== TRANSACTION CREATE END - SUCCESS ===");
                $this->json([
                    'success' => true,
                    'message' => $message,
                    'transaction_id' => $transactionId,
                    'recurring_ids' => $recurringIds
                ]);
                return; // Terminar execução após enviar JSON
            } else {
                error_log("ERROR: Transaction creation failed");
                error_log("=== TRANSACTION CREATE END - FAILED ===");
                $this->json(['success' => false, 'message' => 'Erro ao criar lançamento']);
                return; // Terminar execução após enviar JSON
            }

        } catch (Exception $e) {
            error_log("EXCEPTION in TransactionController::create(): " . $e->getMessage());
            error_log("Exception file: " . $e->getFile() . ":" . $e->getLine());
            error_log("Exception trace: " . $e->getTraceAsString());
            error_log("=== TRANSACTION CREATE END - EXCEPTION ===");
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
            return; // Terminar execução após enviar JSON
        }
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
            
            // Obter org_id do usuário
            $orgId = $this->getCurrentOrgId();
            
            // Criar par de transferências
            $transferId = $this->transactionModel->createTransfer($accountFromId, $accountToId, $valor, $dataCompetencia, $descricao, $observacoes, $user['id'], $orgId);
            
            if ($transferId) {
                // Log da auditoria
                $this->auditModel->logUserAction(
                    $user['id'],
                    1,
                    'transactions',
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
            
            // Se account_id não foi enviado ou é 0, manter o valor atual
            if ($accountId === 0) {
                $currentData = $this->transactionModel->findById($transactionId);
                if ($currentData && $currentData['account_id']) {
                    $accountId = $currentData['account_id'];
                }
            }
            $kind = $_POST['kind'] ?? '';
            $valor = floatval($_POST['valor'] ?? 0);
            $dataCompetencia = $_POST['data_competencia'] ?? '';
            $dataPagamento = !empty($_POST['data_pagamento']) ? $_POST['data_pagamento'] : null;
            $status = $_POST['status'] ?? 'confirmado';
            
            // Se o status é confirmado e data_pagamento não foi fornecida, usar data_competencia
            if ($status === 'confirmado' && !$dataPagamento) {
                $dataPagamento = $dataCompetencia;
            }
            
            $categoryId = (int)($_POST['category_id'] ?? 0) ?: null;
            $contactId = (int)($_POST['contact_id'] ?? 0) ?: null;
            $descricao = trim($_POST['descricao'] ?? '');
            $observacoes = trim($_POST['observacoes'] ?? '') ?: null;
            
            // Se category_id não foi enviado corretamente, manter o valor atual
            if ($categoryId === null) {
                if (!isset($currentData)) {
                    $currentData = $this->transactionModel->findById($transactionId);
                }
                if ($currentData && $currentData['category_id']) {
                    $categoryId = $currentData['category_id'];
                }
            }
            
            // Se contact_id não foi enviado corretamente, manter o valor atual  
            if ($contactId === null) {
                if (!isset($currentData)) {
                    $currentData = $this->transactionModel->findById($transactionId);
                }
                if ($currentData && $currentData['contact_id']) {
                    $contactId = $currentData['contact_id'];
                }
            }
            
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
                try {
                    // Log da auditoria
                    $this->auditModel->logUserAction(
                        $user['id'],
                        1,
                        'transactions',
                        'update',
                        $transactionId,
                        $oldData,
                        $updateData,
                        "Lançamento atualizado: {$descricao}"
                    );
                } catch (Exception $auditError) {
                    error_log("Audit log failed: " . $auditError->getMessage());
                    // Continue mesmo se auditoria falhar
                }
                
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
                    'transactions',
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
    
    public function getCategoriesByType() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            $tipo = $_GET['tipo'] ?? '';
            $orgId = $this->getCurrentOrgId(); // Por enquanto fixo
            
            if (empty($tipo)) {
                $this->json(['success' => false, 'message' => 'Tipo é obrigatório']);
                return;
            }
            
            // Mapear tipo de transação para tipo de categoria
            $tipoCategoriaMap = [
                'entrada' => 'receita',
                'saida' => 'despesa'
            ];
            
            $tipoCategoria = $tipoCategoriaMap[$tipo] ?? null;
            
            if (!$tipoCategoria) {
                $this->json(['success' => false, 'message' => 'Tipo de transação inválido']);
                return;
            }
            
            $categories = $this->categoryModel->getCategoriesByType($orgId, $tipoCategoria);
            
            $this->json([
                'success' => true,
                'categories' => $categories
            ]);
            
        } catch (Exception $e) {
            error_log("Erro ao buscar categorias por tipo: " . $e->getMessage());
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
                    'transactions',
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
            
            // Data de confirmação (pagamento) - usar data de competência se não informada
            $paymentDate = $_POST['data_pagamento'] ?? null;
            if (empty($paymentDate)) {
                // Se não tem data de pagamento, usar data de competência
                $paymentDate = $transaction['data_competencia'] ?? date('Y-m-d');
            }
            
            // Parâmetro opcional para ajustar valor
            $newValue = $_POST['valor'] ?? null;
            
            // Parâmetro opcional para alterar conta de pagamento
            $newAccountId = $_POST['account_id'] ?? null;
            
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
                } else {
                    $this->json(['success' => false, 'message' => 'Valor inválido fornecido']);
                    return;
                }
            }
            
            // Se conta foi alterada, incluir no update
            if ($newAccountId && $newAccountId !== $transaction['account_id']) {
                $updateData['account_id'] = $newAccountId;
                error_log("Conta alterada na confirmação: {$transaction['account_id']} -> {$newAccountId}");
            }
            
            error_log("Dados para atualização na confirmação: " . json_encode($updateData));
            
            $success = $this->transactionModel->updateTransaction($transactionId, $updateData);
            
            if ($success) {
                // Log da auditoria
                $this->auditModel->logUserAction(
                    $user['id'],
                    1,
                    'transactions',
                    'update',
                    $transactionId,
                    $transaction,
                    $updateData,
                    "Lançamento confirmado: {$transaction['descricao']}"
                );

                // Enviar notificação de confirmação
                try {
                    $orgId = $this->getCurrentOrgId();

                    $transactionData = [
                        'id' => $transactionId,
                        'descricao' => $transaction['descricao'],
                        'valor' => $updateData['valor'] ?? $transaction['valor'],
                        'kind' => $transaction['kind'],
                        'data_competencia' => $transaction['data_competencia'],
                        'data_pagamento' => $paymentDate,
                        'categoria' => $transaction['category_id'] ? $this->categoryModel->findById($transaction['category_id'])['nome'] ?? null : null,
                        'conta' => $transaction['account_id'] ? $this->accountModel->findById($transaction['account_id'])['nome'] ?? null : null,
                        'action' => 'confirmed'
                    ];

                    // Buscar dados completos da transação para notificação
                    $transactionModel = new Transaction();
                    $fullTransaction = $transactionModel->findById($transactionId);

                    // Usar método padronizado para notificação de confirmação
                    $this->teamNotificationService->notifyTransactionConfirmed($orgId, $fullTransaction);

                    error_log("Confirmation notification sent for transaction ID: $transactionId");
                } catch (Exception $notificationError) {
                    error_log("Failed to send confirmation notification: " . $notificationError->getMessage());
                }

                error_log("Lançamento confirmado com sucesso - ID: {$transactionId}");
                $this->json(['success' => true, 'message' => 'Lançamento confirmado com sucesso!']);
            } else {
                error_log("Falha ao atualizar transação - ID: {$transactionId}");
                $this->json(['success' => false, 'message' => 'Erro ao confirmar lançamento']);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao confirmar lançamento: " . $e->getMessage() . " - Trace: " . $e->getTraceAsString());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor: ' . $e->getMessage()]);
        }
    }
    
    private function convertBrazilianCurrencyToDecimal($value) {
        if (empty($value)) {
            return false;
        }
        
        // Remove all non-numeric characters except comma and period
        $cleaned = preg_replace('/[^\d,.]/u', '', $value);
        
        // Handle Brazilian format (1.234,56 or 1234,56)
        if (strpos($cleaned, ',') !== false) {
            // If has both . and , then . is thousands separator
            if (strpos($cleaned, '.') !== false && strpos($cleaned, ',') !== false) {
                $cleaned = str_replace('.', '', $cleaned);
            }
            // Replace comma with period for decimal
            $cleaned = str_replace(',', '.', $cleaned);
        }
        
        // Convert to float
        $numericValue = floatval($cleaned);
        
        // Return the value if it's >= 0.01 (handles precision issues and rejects zero)
        return $numericValue >= 0.01 ? $numericValue : false;
    }
    
    public function getScheduled() {
        try {
            $user = AuthMiddleware::requireAuth();
            $orgId = $this->getCurrentOrgId();

            $transactions = $this->transactionModel->getScheduledTransactions($orgId);

            $this->json([
                'success' => true,
                'transactions' => $transactions
            ]);

        } catch (Exception $e) {
            error_log("Erro ao buscar agendados: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }

    public function partialPayment() {
        header('Content-Type: application/json');

        try {
            // Requer autenticação
            $user = AuthMiddleware::requireAuth();
            $orgId = $this->getCurrentOrgId();

            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input || !isset($input['transaction_id']) || !isset($input['valor_pago'])) {
                echo json_encode(['success' => false, 'message' => 'Dados incompletos para baixa parcial']);
                exit;
            }

            $transactionId = (int)$input['transaction_id'];
            $valorPagoInput = (float)$input['valor_pago'];
            $accountId = (int)$input['account_id'];

            if (!$valorPagoInput || $valorPagoInput <= 0) {
                echo json_encode(['success' => false, 'message' => 'Valor inválido para baixa parcial']);
                exit;
            }

            if (!$accountId) {
                echo json_encode(['success' => false, 'message' => 'Conta é obrigatória para baixa parcial']);
                exit;
            }

            // Buscar transação
            $transaction = $this->transactionModel->findById($transactionId);
            if (!$transaction) {
                echo json_encode(['success' => false, 'message' => 'Transação não encontrada']);
                exit;
            }

            // Verificar se é agendada
            if ($transaction['status'] !== 'agendado') {
                echo json_encode(['success' => false, 'message' => 'Apenas transações agendadas podem ter baixa parcial']);
                exit;
            }

            // Primeiro, habilitar baixa parcial na transação se ainda não estiver
            if (!$transaction['permite_baixa_parcial']) {
                require_once __DIR__ . '/../../config/database.php';
                $database = new Database();
                $pdo = $database->getConnection();

                $stmt = $pdo->prepare("UPDATE transactions
                                       SET permite_baixa_parcial = 1,
                                           valor_original = COALESCE(valor_original, valor),
                                           valor_pendente = COALESCE(valor_pendente, valor)
                                       WHERE id = ?");
                $stmt->execute([$transactionId]);

                // Recarregar transação
                $transaction = $this->transactionModel->findById($transactionId);
            }

            // Calcular saldo pendente
            $valorOriginal = $transaction['valor_original'] ?? $transaction['valor'];
            $valorJaPago = $transaction['valor_pago'] ?? 0;
            $saldoPendente = $valorOriginal - $valorJaPago;

            if ($saldoPendente <= 0) {
                echo json_encode(['success' => false, 'message' => 'Esta transação já foi totalmente paga']);
                exit;
            }

            if ($valorPagoInput > $saldoPendente) {
                echo json_encode(['success' => false, 'message' => 'O valor da baixa não pode ser maior que o saldo pendente de R$ ' . number_format($saldoPendente, 2, ',', '.')]);
                exit;
            }

            // Usar o novo sistema de baixas parciais
            require_once __DIR__ . '/../models/PartialPayment.php';
            $partialPaymentModel = new PartialPayment();

            // Dados para registrar a baixa parcial
            $paymentData = [
                'org_id' => $orgId,
                'transaction_id' => $transactionId,
                'account_id' => $accountId, // Usar conta selecionada pelo usuário
                'valor' => $valorPagoInput,
                'data_pagamento' => date('Y-m-d'),
                'descricao' => 'Baixa parcial por ' . $user['nome'] . ' (' . date('d/m/Y H:i') . ')',
                'created_by' => $user['id']
            ];

            // Registrar baixa parcial (as triggers atualizarão os saldos automaticamente)
            $paymentId = $partialPaymentModel->registerPayment($paymentData);

            if ($paymentId) {
                $novoSaldoPendente = $saldoPendente - $valorPagoInput;
                $message = "Baixa parcial realizada com sucesso! Valor pago: R$ " . number_format($valorPagoInput, 2, ',', '.') .
                          ". Saldo pendente: R$ " . number_format($novoSaldoPendente, 2, ',', '.');

                error_log("Baixa parcial registrada: ID {$paymentId} para transação {$transactionId} - Valor: {$valorPagoInput}");

                // Enviar notificação WhatsApp da baixa parcial
                try {
                    $orgId = $this->getCurrentOrgId();
                    $transactionModel = new Transaction();
                    $fullTransaction = $transactionModel->findById($transactionId);

                    $this->teamNotificationService->notifyPartialPayment($orgId, $fullTransaction, $valorPagoInput, $novoSaldoPendente);
                    error_log("Partial payment notification sent for transaction ID: $transactionId");
                } catch (Exception $notificationError) {
                    error_log("Failed to send partial payment notification: " . $notificationError->getMessage());
                }

                echo json_encode(['success' => true, 'message' => $message, 'payment_id' => $paymentId]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao processar baixa parcial']);
                exit;
            }

        } catch (Exception $e) {
            error_log("Erro ao processar baixa parcial: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor: ' . $e->getMessage()]);
            exit;
        }
    }

    public function filter() {
        $user = AuthMiddleware::requireAuth();
        $orgId = $this->getCurrentOrgId();

        try {
            $startDate = $_GET['start_date'] ?? '';
            $endDate = $_GET['end_date'] ?? '';
            $accountId = $_GET['account_id'] ?? '';
            $type = $_GET['type'] ?? '';

            if (empty($startDate) || empty($endDate)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Datas inicial e final são obrigatórias'
                ]);
                return;
            }

            $filters = [
                'date_from' => $startDate,
                'date_to' => $endDate,
                'account_id' => $accountId,
                'kind' => $type,
                'order_by' => 'data_competencia'
            ];

            $result = $this->transactionModel->getTransactionsWithFilters($orgId, $filters, 100, 0);
            $transactions = $result['transactions'];

            $this->jsonResponse([
                'success' => true,
                'transactions' => $transactions
            ]);

        } catch (Exception $e) {
            $this->handleError($e, 'Erro ao filtrar transações');
        }
    }

    // Método removido temporariamente - futuro: integração com WhatsApp
    // private function sendNewTransactionNotifications(...) { ... }

}