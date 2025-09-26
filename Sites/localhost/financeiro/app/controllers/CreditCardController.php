<?php
require_once 'BaseController.php';
require_once 'AuthMiddleware.php';
require_once __DIR__ . '/../models/CreditCard.php';

class CreditCardController extends BaseController {
    private $creditCardModel;
    
    public function __construct() {
        parent::__construct();
        $this->creditCardModel = new CreditCard();
    }
    
    public function index() {
        // Verificar se está logado
        $user = AuthMiddleware::requireAuth();
        $orgId = $this->getCurrentOrgId();
        
        // Buscar cartões da organização
        $creditCards = $this->creditCardModel->getByOrganization($orgId);
        
        // Buscar totais de faturas de cartão de crédito (mês atual)
        require_once __DIR__ . '/../models/Transaction.php';
        $transactionModel = new Transaction();
        $currentYear = date('Y');
        $currentMonth = date('m');
        $creditCardInvoices = $transactionModel->getCreditCardInvoiceTotals($orgId, $currentYear, $currentMonth);
        
        // Buscar contas ativas para o modal de pagamento
        require_once __DIR__ . '/../models/Account.php';
        $accountModel = new Account();
        $accounts = $accountModel->getActiveAccountsByOrg($orgId);
        
        $data = [
            'title' => 'Cartões de Crédito - Sistema Financeiro',
            'page' => 'credit-cards',
            'user' => $user,
            'creditCards' => $creditCards,
            'creditCardInvoices' => $creditCardInvoices,
            'accounts' => $accounts
        ];
        
        
        $this->render('layout', $data);
    }
    
    public function create() {
        try {
            $user = AuthMiddleware::requireAuth();
            $orgId = $this->getCurrentOrgId();
            
            $nome = trim($_POST['nome'] ?? '');
            $bandeira = trim($_POST['bandeira'] ?? '');
            $limiteTotal = floatval($_POST['limite_total'] ?? 0);
            $diaVencimento = intval($_POST['dia_vencimento'] ?? 0);
            $diaFechamento = intval($_POST['dia_fechamento'] ?? 0);
            $banco = trim($_POST['banco'] ?? '') ?: null;
            $ultimosDigitos = trim($_POST['ultimos_digitos'] ?? '') ?: null;
            $cor = trim($_POST['cor'] ?? '#6c757d');
            $observacoes = trim($_POST['observacoes'] ?? '') ?: null;
            
            // Validações
            if (empty($nome) || empty($bandeira)) {
                $this->json(['success' => false, 'message' => 'Nome e bandeira são obrigatórios']);
                return;
            }
            
            if ($limiteTotal <= 0) {
                $this->json(['success' => false, 'message' => 'Limite deve ser maior que zero']);
                return;
            }
            
            if ($diaVencimento < 1 || $diaVencimento > 31) {
                $this->json(['success' => false, 'message' => 'Dia de vencimento deve estar entre 1 e 31']);
                return;
            }
            
            if ($diaFechamento < 1 || $diaFechamento > 31) {
                $this->json(['success' => false, 'message' => 'Dia de fechamento deve estar entre 1 e 31']);
                return;
            }
            
            if ($ultimosDigitos && !preg_match('/^\d{4}$/', $ultimosDigitos)) {
                $this->json(['success' => false, 'message' => 'Últimos dígitos devem conter exatamente 4 números']);
                return;
            }
            
            // Criar cartão
            $cardData = [
                'org_id' => $orgId,
                'nome' => $nome,
                'bandeira' => $bandeira,
                'limite_total' => $limiteTotal,
                'dia_vencimento' => $diaVencimento,
                'dia_fechamento' => $diaFechamento,
                'banco' => $banco,
                'ultimos_digitos' => $ultimosDigitos,
                'cor' => $cor,
                'observacoes' => $observacoes,
                'created_by' => $user['id']
            ];
            
            $cardId = $this->creditCardModel->createCard($cardData);
            
            if ($cardId) {
                $this->json([
                    'success' => true,
                    'message' => 'Cartão de crédito criado com sucesso!',
                    'card_id' => $cardId
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao criar cartão de crédito']);
            }
            
        } catch (Exception $e) {
            error_log("EXCEPTION in CreditCardController::create(): " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function getCard() {
        try {
            $user = AuthMiddleware::requireAuth();
            $cardId = intval($_GET['id'] ?? 0);
            
            if (!$cardId) {
                $this->json(['success' => false, 'message' => 'ID do cartão é obrigatório']);
                return;
            }
            
            $card = $this->creditCardModel->findById($cardId);
            
            if (!$card) {
                $this->json(['success' => false, 'message' => 'Cartão não encontrado']);
                return;
            }
            
            // Verificar se pertence à organização do usuário
            $orgId = $this->getCurrentOrgId();
            if ($card['org_id'] != $orgId) {
                $this->json(['success' => false, 'message' => 'Cartão não encontrado']);
                return;
            }
            
            $this->json(['success' => true, 'card' => $card]);
            
        } catch (Exception $e) {
            error_log("EXCEPTION in CreditCardController::getCard(): " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function update() {
        try {
            $user = AuthMiddleware::requireAuth();
            $cardId = intval($_POST['id'] ?? 0);
            
            if (!$cardId) {
                $this->json(['success' => false, 'message' => 'ID do cartão é obrigatório']);
                return;
            }
            
            // Verificar se o cartão existe e pertence à organização
            $card = $this->creditCardModel->findById($cardId);
            $orgId = $this->getCurrentOrgId();
            
            if (!$card || $card['org_id'] != $orgId) {
                $this->json(['success' => false, 'message' => 'Cartão não encontrado']);
                return;
            }
            
            $nome = trim($_POST['nome'] ?? '');
            $bandeira = trim($_POST['bandeira'] ?? '');
            $limiteTotal = floatval($_POST['limite_total'] ?? 0);
            $diaVencimento = intval($_POST['dia_vencimento'] ?? 0);
            $diaFechamento = intval($_POST['dia_fechamento'] ?? 0);
            $banco = trim($_POST['banco'] ?? '') ?: null;
            $ultimosDigitos = trim($_POST['ultimos_digitos'] ?? '') ?: null;
            $cor = trim($_POST['cor'] ?? '#6c757d');
            $observacoes = trim($_POST['observacoes'] ?? '') ?: null;
            $ativo = intval($_POST['ativo'] ?? 1);
            
            // Validações (mesmo do create)
            if (empty($nome) || empty($bandeira)) {
                $this->json(['success' => false, 'message' => 'Nome e bandeira são obrigatórios']);
                return;
            }
            
            if ($limiteTotal <= 0) {
                $this->json(['success' => false, 'message' => 'Limite deve ser maior que zero']);
                return;
            }
            
            if ($ultimosDigitos && !preg_match('/^\d{4}$/', $ultimosDigitos)) {
                $this->json(['success' => false, 'message' => 'Últimos dígitos devem conter exatamente 4 números']);
                return;
            }
            
            $updateData = [
                'nome' => $nome,
                'bandeira' => $bandeira,
                'limite_total' => $limiteTotal,
                'dia_vencimento' => $diaVencimento,
                'dia_fechamento' => $diaFechamento,
                'banco' => $banco,
                'ultimos_digitos' => $ultimosDigitos,
                'cor' => $cor,
                'observacoes' => $observacoes,
                'ativo' => $ativo
            ];
            
            $success = $this->creditCardModel->updateCard($cardId, $updateData);
            
            if ($success) {
                $this->json(['success' => true, 'message' => 'Cartão atualizado com sucesso!']);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao atualizar cartão']);
            }
            
        } catch (Exception $e) {
            error_log("EXCEPTION in CreditCardController::update(): " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function delete() {
        try {
            $user = AuthMiddleware::requireAuth();
            $cardId = intval($_POST['id'] ?? 0);
            
            if (!$cardId) {
                $this->json(['success' => false, 'message' => 'ID do cartão é obrigatório']);
                return;
            }
            
            // Verificar se o cartão existe e pertence à organização
            $card = $this->creditCardModel->findById($cardId);
            $orgId = $this->getCurrentOrgId();
            
            if (!$card || $card['org_id'] != $orgId) {
                $this->json(['success' => false, 'message' => 'Cartão não encontrado']);
                return;
            }
            
            $success = $this->creditCardModel->deleteCard($cardId);
            
            if ($success) {
                $this->json(['success' => true, 'message' => 'Cartão excluído com sucesso!']);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao excluir cartão. Verifique se não há transações vinculadas.']);
            }
            
        } catch (Exception $e) {
            error_log("EXCEPTION in CreditCardController::delete(): " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function getStatistics() {
        try {
            $user = AuthMiddleware::requireAuth();
            $cardId = intval($_GET['card_id'] ?? 0);
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-t');
            
            if (!$cardId) {
                $this->json(['success' => false, 'message' => 'ID do cartão é obrigatório']);
                return;
            }
            
            // Verificar se o cartão pertence à organização
            $card = $this->creditCardModel->findById($cardId);
            $orgId = $this->getCurrentOrgId();
            
            if (!$card || $card['org_id'] != $orgId) {
                $this->json(['success' => false, 'message' => 'Cartão não encontrado']);
                return;
            }
            
            $statistics = $this->creditCardModel->getCardStatistics($cardId, $startDate, $endDate);
            $transactions = $this->creditCardModel->getCardTransactions($cardId, $startDate, $endDate, 10);
            
            $this->json([
                'success' => true,
                'statistics' => $statistics,
                'transactions' => $transactions,
                'card' => $card
            ]);
            
        } catch (Exception $e) {
            error_log("EXCEPTION in CreditCardController::getStatistics(): " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function getActiveCards() {
        try {
            $user = AuthMiddleware::requireAuth();
            $orgId = $this->getCurrentOrgId();
            
            $cards = $this->creditCardModel->getActiveByOrganization($orgId);
            
            $this->json(['success' => true, 'cards' => $cards]);
            
        } catch (Exception $e) {
            error_log("EXCEPTION in CreditCardController::getActiveCards(): " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function payCard() {
        try {
            $user = AuthMiddleware::requireAuth();
            $cardId = (int)($_POST['card_id'] ?? 0);
            $valor = floatval($_POST['valor'] ?? 0);
            $accountId = (int)($_POST['account_id'] ?? 0);
            $dataCompetencia = $_POST['data_competencia'] ?? '';
            $dataPagamento = $_POST['data_pagamento'] ?? null;
            $observacoes = trim($_POST['observacoes'] ?? '') ?: null;
            
            // Validações
            if (!$cardId) {
                $this->json(['success' => false, 'message' => 'Cartão é obrigatório']);
                return;
            }
            
            if (!$accountId) {
                $this->json(['success' => false, 'message' => 'Conta para débito é obrigatória']);
                return;
            }
            
            if ($valor <= 0) {
                $this->json(['success' => false, 'message' => 'Valor deve ser maior que zero']);
                return;
            }
            
            if (empty($dataCompetencia)) {
                $this->json(['success' => false, 'message' => 'Data de competência é obrigatória']);
                return;
            }
            
            // Verificar se o cartão pertence à organização
            $card = $this->creditCardModel->findById($cardId);
            $orgId = $this->getCurrentOrgId();
            
            if (!$card || $card['org_id'] != $orgId) {
                $this->json(['success' => false, 'message' => 'Cartão não encontrado']);
                return;
            }
            
            // Verificar se o valor não é maior que o limite usado
            if ($valor > $card['limite_usado']) {
                $this->json(['success' => false, 'message' => 'Valor não pode ser maior que o limite usado (R$ ' . number_format($card['limite_usado'], 2, ',', '.') . ')']);
                return;
            }
            
            // Criar transação de pagamento
            require_once __DIR__ . '/../models/Transaction.php';
            require_once __DIR__ . '/../models/Account.php';
            
            $transactionModel = new Transaction();
            $accountModel = new Account();
            
            // Verificar se a conta existe
            $account = $accountModel->findById($accountId);
            if (!$account || !$account['ativo']) {
                $this->json(['success' => false, 'message' => 'Conta inválida ou inativa']);
                return;
            }
            
            $transactionData = [
                'org_id' => $orgId,
                'account_id' => $accountId,
                'credit_card_id' => null, // Pagamento não vai para o cartão
                'kind' => 'saida',
                'valor' => $valor,
                'data_competencia' => $dataCompetencia,
                'data_pagamento' => $dataPagamento,
                'status' => 'confirmado',
                'category_id' => null, // Podemos criar uma categoria específica depois
                'contact_id' => null,
                'descricao' => "Pagamento Cartão {$card['nome']}",
                'observacoes' => $observacoes,
                'recurrence_type' => null,
                'recurrence_count' => 1,
                'recurrence_end_date' => null,
                'parent_transaction_id' => null,
                'recurrence_sequence' => 0,
                'created_by' => $user['id']
            ];
            
            $transactionId = $transactionModel->createTransaction($transactionData);
            
            if ($transactionId) {
                // Liberar limite do cartão
                $success = $this->creditCardModel->updateLimiteUsado($cardId, $valor, 'subtract');
                
                if ($success) {
                    $this->json([
                        'success' => true,
                        'message' => 'Pagamento registrado e limite liberado com sucesso!',
                        'transaction_id' => $transactionId
                    ]);
                } else {
                    $this->json(['success' => false, 'message' => 'Transação criada, mas erro ao liberar limite']);
                }
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao registrar pagamento']);
            }
            
        } catch (Exception $e) {
            error_log("EXCEPTION in CreditCardController::payCard(): " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
}