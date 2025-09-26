<?php
require_once 'BaseController.php';
require_once 'AuthMiddleware.php';
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../models/Account.php';
require_once __DIR__ . '/../models/Category.php';

class MobileController extends BaseController {
    private $transactionModel;
    private $accountModel;
    private $categoryModel;
    private $contactModel;
    
    public function __construct() {
        parent::__construct();
        $this->transactionModel = new Transaction();
        $this->accountModel = new Account();
        $this->categoryModel = new Category();
        $this->contactModel = new Contact();
    }
    
    public function index() {
        $user = AuthMiddleware::requireAuth();
        
        $orgId = 1; // Por enquanto sempre org 1
        
        // Buscar dados necessários para mobile
        $accounts = $this->accountModel->getActiveAccountsByOrg($orgId);
        $categories = $this->categoryModel->getActiveCategories($orgId);
        $contacts = $this->contactModel->getContactsByOrg($orgId);
        
        // Buscar últimos 50 lançamentos com informações do usuário
        $recentTransactions = $this->transactionModel->getRecentTransactionsWithUser($orgId, 50);

        // Buscar transações que vencem ontem, hoje e amanhã
        $transactionsDueYesterday = $this->transactionModel->getDueByDateTransactions($orgId, date('Y-m-d', strtotime('-1 day')), 20);
        $transactionsDueToday = $this->transactionModel->getDueTodayTransactions($orgId, 20);
        $transactionsDueTomorrow = $this->transactionModel->getDueByDateTransactions($orgId, date('Y-m-d', strtotime('+1 day')), 20);

        // Calcular saldo total
        $totalBalance = 0;
        foreach ($accounts as $account) {
            $totalBalance += $account['saldo_atual'];
        }
        
        // Calcular balanço do mês atual
        $currentYear = date('Y');
        $currentMonth = date('m');
        $monthlyBalance = $this->transactionModel->getMonthlyBalance($orgId, $currentYear, $currentMonth);
        
        $data = [
            'title' => 'Mobile - Sistema Financeiro',
            'page' => 'mobile',
            'user' => $user,
            'accounts' => $accounts,
            'categories' => $categories,
            'contacts' => $contacts,
            'recentTransactions' => $recentTransactions,
            'transactionsDueYesterday' => $transactionsDueYesterday,
            'transactionsDueToday' => $transactionsDueToday,
            'transactionsDueTomorrow' => $transactionsDueTomorrow,
            'totalBalance' => $totalBalance,
            'monthlyBalance' => $monthlyBalance
        ];
        
        $this->render('mobile', $data);
    }
    
    public function searchTransactions() {
        $user = AuthMiddleware::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método não permitido']);
            return;
        }
        
        $searchTerm = trim($_POST['search'] ?? '');
        
        if (empty($searchTerm)) {
            $this->jsonResponse(['success' => false, 'message' => 'Termo de pesquisa obrigatório']);
            return;
        }
        
        try {
            $orgId = 1; // Por enquanto sempre org 1
            
            // Buscar transações que correspondem ao termo de pesquisa
            $transactions = $this->transactionModel->searchTransactions($orgId, $searchTerm);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Pesquisa realizada com sucesso',
                'data' => [
                    'transactions' => $transactions,
                    'count' => count($transactions)
                ]
            ]);
            
        } catch (Exception $e) {
            error_log("Erro na pesquisa mobile: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false, 
                'message' => 'Erro interno do servidor'
            ]);
        }
    }
}