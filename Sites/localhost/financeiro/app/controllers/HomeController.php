<?php
require_once 'BaseController.php';
require_once 'AuthMiddleware.php';

class HomeController extends BaseController {
    
    public function index() {
        // Verificar se está logado
        $user = AuthMiddleware::requireAuth();
        
        // Por enquanto, usar org_id = 1
        $orgId = 1;
        
        // Instanciar models necessários
        $transactionModel = new Transaction();
        $accountModel = new Account();
        $categoryModel = new Category();
        
        // Calcular dados do dashboard
        $currentYear = date('Y');
        $currentMonth = date('m');
        
        // Saldo mensal por tipo de pessoa
        $monthlyBalanceByType = $transactionModel->getMonthlyBalanceByPersonTypeConfirmed($orgId, $currentYear, $currentMonth);
        $monthlyBalanceByTypeWithScheduled = $transactionModel->getMonthlyBalanceByPersonType($orgId, $currentYear, $currentMonth);
        // Saldo mensal (apenas confirmados para os cards principais)
        $monthlyBalance = $transactionModel->getMonthlyBalance($orgId, $currentYear, $currentMonth);
        
        // Saldo mensal incluindo agendados (para comparativos)
        $monthlyBalanceWithScheduled = $transactionModel->getMonthlyBalanceWithScheduled($orgId, $currentYear, $currentMonth);
        
        // Dados do mês anterior
        $previousMonth = $currentMonth - 1;
        $previousYear = $currentYear;
        if ($previousMonth < 1) {
            $previousMonth = 12;
            $previousYear--;
        }
        $previousMonthBalance = $transactionModel->getMonthlyBalance($orgId, $previousYear, $previousMonth);
        
        // Dados do próximo mês
        $nextMonth = $currentMonth + 1;
        $nextYear = $currentYear;
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear++;
        }
        $nextMonthBalance = $transactionModel->getMonthlyBalanceWithScheduled($orgId, $nextYear, $nextMonth);
        
        // Saldo total das contas ativas por tipo
        $accounts = $accountModel->getActiveAccountsByOrg($orgId);
        $accountBalancesByType = $accountModel->getTotalBalanceByType($orgId);
        $totalBalance = 0;
        foreach ($accounts as $account) {
            $totalBalance += $account['saldo_atual'];
        }
        
        // Categorias ativas para os modais
        $categories = $categoryModel->getActiveCategories($orgId);
        
        // Últimas transações (10 mais recentes)
        $recentTransactions = $transactionModel->getTransactionsByOrg($orgId, 10, 0);
        
        // Próximos agendamentos
        $upcomingTransactions = $transactionModel->getUpcomingScheduledTransactions($orgId, 5);
        
        // Dados para gráfico de categorias (ano atual)
        $categoryExpenses = $transactionModel->getCategoryExpensesByPersonType($orgId, $currentYear);
        
        $data = [
            'title' => 'Dashboard - Sistema Financeiro',
            'page' => 'dashboard',
            'user' => $user,
            'totalBalance' => $totalBalance,
            'monthlyIncome' => $monthlyBalance['receitas'] ?? 0,
            'monthlyExpenses' => $monthlyBalance['despesas'] ?? 0,
            'totalTransactions' => $monthlyBalance['total_transactions'] ?? 0,
            'monthlyBalanceByType' => $monthlyBalanceByType,
            'monthlyBalanceWithScheduled' => $monthlyBalanceWithScheduled,
            'monthlyBalanceByTypeWithScheduled' => $monthlyBalanceByTypeWithScheduled,
            'accountBalancesByType' => $accountBalancesByType,
            'previousMonthBalance' => $previousMonthBalance,
            'nextMonthBalance' => $nextMonthBalance,
            'recentTransactions' => $recentTransactions,
            'upcomingTransactions' => $upcomingTransactions,
            'accounts' => $accounts,
            'categories' => $categories,
            'categoryExpenses' => $categoryExpenses
        ];
        
        $this->render('layout', $data);
    }
    
    public function dashboard() {
        // Verificar se está logado
        $user = AuthMiddleware::requireAuth();
        
        // Por enquanto, usar org_id = 1
        $orgId = 1;
        
        // Instanciar models necessários
        $transactionModel = new Transaction();
        $accountModel = new Account();
        $categoryModel = new Category();
        
        // Calcular dados do dashboard
        $currentYear = date('Y');
        $currentMonth = date('m');
        
        // Saldo mensal por tipo de pessoa
        $monthlyBalanceByType = $transactionModel->getMonthlyBalanceByPersonTypeConfirmed($orgId, $currentYear, $currentMonth);
        $monthlyBalanceByTypeWithScheduled = $transactionModel->getMonthlyBalanceByPersonType($orgId, $currentYear, $currentMonth);
        // Saldo mensal (apenas confirmados para os cards principais)
        $monthlyBalance = $transactionModel->getMonthlyBalance($orgId, $currentYear, $currentMonth);
        
        // Saldo mensal incluindo agendados (para comparativos)
        $monthlyBalanceWithScheduled = $transactionModel->getMonthlyBalanceWithScheduled($orgId, $currentYear, $currentMonth);
        
        // Dados do mês anterior
        $previousMonth = $currentMonth - 1;
        $previousYear = $currentYear;
        if ($previousMonth < 1) {
            $previousMonth = 12;
            $previousYear--;
        }
        $previousMonthBalance = $transactionModel->getMonthlyBalance($orgId, $previousYear, $previousMonth);
        
        // Dados do próximo mês
        $nextMonth = $currentMonth + 1;
        $nextYear = $currentYear;
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear++;
        }
        $nextMonthBalance = $transactionModel->getMonthlyBalanceWithScheduled($orgId, $nextYear, $nextMonth);
        
        // Saldo total das contas ativas por tipo
        $accounts = $accountModel->getActiveAccountsByOrg($orgId);
        $accountBalancesByType = $accountModel->getTotalBalanceByType($orgId);
        $totalBalance = 0;
        foreach ($accounts as $account) {
            $totalBalance += $account['saldo_atual'];
        }
        
        // Categorias ativas para os modais
        $categories = $categoryModel->getActiveCategories($orgId);
        
        // Dados para gráfico de categorias (ano atual)
        $categoryExpenses = $transactionModel->getCategoryExpensesByPersonType($orgId, $currentYear);
        
        // Contar transações pendentes
        $pendingTransactions = count($transactionModel->getTransactionsByOrg($orgId, 10, 0));
        
        // Próximos agendamentos
        $upcomingTransactions = $transactionModel->getUpcomingScheduledTransactions($orgId, 5);
        
        $data = [
            'user' => $user,
            'totalBalance' => $totalBalance,
            'monthlyIncome' => $monthlyBalance['receitas'] ?? 0,
            'monthlyExpenses' => $monthlyBalance['despesas'] ?? 0,
            'totalTransactions' => $monthlyBalance['total_transactions'] ?? 0,
            'monthlyBalanceByType' => $monthlyBalanceByType,
            'monthlyBalanceWithScheduled' => $monthlyBalanceWithScheduled,
            'monthlyBalanceByTypeWithScheduled' => $monthlyBalanceByTypeWithScheduled,
            'accountBalancesByType' => $accountBalancesByType,
            'previousMonthBalance' => $previousMonthBalance,
            'nextMonthBalance' => $nextMonthBalance,
            'pendingTransactions' => $pendingTransactions,
            'upcomingTransactions' => $upcomingTransactions,
            'accounts' => $accounts,
            'categories' => $categories,
            'categoryExpenses' => $categoryExpenses
        ];
        
        $this->render('dashboard', $data);
    }
}