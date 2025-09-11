<?php
require_once 'BaseController.php';
require_once 'AuthMiddleware.php';

class MobileController extends BaseController {
    private $transactionModel;
    private $accountModel;
    private $categoryModel;
    
    public function __construct() {
        parent::__construct();
        $this->transactionModel = new Transaction();
        $this->accountModel = new Account();
        $this->categoryModel = new Category();
    }
    
    public function index() {
        $user = AuthMiddleware::requireAuth();
        
        // Por enquanto, usar org_id = 1
        $orgId = 1;
        
        // Buscar dados necessários para mobile
        $accounts = $this->accountModel->getActiveAccountsByOrg($orgId);
        $categories = $this->categoryModel->getActiveCategories($orgId);
        
        // Buscar últimos 10 lançamentos com informações do usuário
        $recentTransactions = $this->transactionModel->getRecentTransactionsWithUser($orgId, 10);
        
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
            'recentTransactions' => $recentTransactions,
            'totalBalance' => $totalBalance,
            'monthlyBalance' => $monthlyBalance
        ];
        
        $this->render('mobile', $data);
    }
}