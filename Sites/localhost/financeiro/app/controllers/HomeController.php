<?php
require_once 'BaseController.php';
require_once 'AuthMiddleware.php';
require_once __DIR__ . '/../models/CreditCard.php';

class HomeController extends BaseController {
    
    public function index() {
        // Verificar se está logado
        $user = AuthMiddleware::requireAuth();
        
        $orgId = $this->getCurrentOrgId();
        
        // Instanciar models necessários
        $transactionModel = new Transaction();
        $accountModel = new Account();
        $categoryModel = new Category();
        $creditCardModel = new CreditCard();
        $dashboardLayoutModel = new DashboardLayoutModel();
        
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

        // Buscar transações que vencem ontem, hoje e amanhã
        $transactionsDueYesterday = $transactionModel->getDueByDateTransactions($orgId, date('Y-m-d', strtotime('-1 day')), 20);
        $transactionsDueToday = $transactionModel->getDueTodayTransactions($orgId, 20);
        $transactionsDueTomorrow = $transactionModel->getDueByDateTransactions($orgId, date('Y-m-d', strtotime('+1 day')), 20);
        
        // Dados para gráfico de categorias (ano atual)
        $categoryExpenses = $transactionModel->getCategoryExpensesByPersonType($orgId, $currentYear);
        
        // Buscar cartões de crédito ativos
        $creditCards = $creditCardModel->getByOrganization($orgId);
        
        // Buscar totais de faturas de cartão de crédito (mês atual)
        $creditCardInvoices = $transactionModel->getCreditCardInvoiceTotals($orgId, $currentYear, $currentMonth);
        
        // Buscar contatos ativos para o modal de lançamento
        $contactModel = new Contact();
        $contacts = $contactModel->getActiveContacts($orgId);

        // Buscar vencimentos do dia (sempre inicializar array vazio se método não existir)
        $dueTodayTransactions = [];
        if (method_exists($transactionModel, 'getDueTodayTransactions')) {
            $dueTodayTransactions = $transactionModel->getDueTodayTransactions($orgId, 8);
        }
        
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
            'transactionsDueYesterday' => $transactionsDueYesterday,
            'transactionsDueToday' => $transactionsDueToday,
            'transactionsDueTomorrow' => $transactionsDueTomorrow,
            'accounts' => $accounts,
            'categories' => $categories,
            'categoryExpenses' => $categoryExpenses,
            'creditCards' => $creditCards,
            'creditCardInvoices' => $creditCardInvoices,
            'contacts' => $contacts,
            'dueTodayTransactions' => $dueTodayTransactions
        ];
        
        // Get user's dashboard layout preferences
        $userId = $_SESSION['user_id'];
        $userLayout = $dashboardLayoutModel->getUserLayout($userId, $orgId);
        $data['userLayout'] = $userLayout;
        
        $this->render('layout', $data);
    }
    
    public function dashboard() {
        // Verificar se está logado
        $user = AuthMiddleware::requireAuth();
        
        $orgId = $this->getCurrentOrgId();
        
        // Instanciar models necessários
        $transactionModel = new Transaction();
        $accountModel = new Account();
        $categoryModel = new Category();
        $dashboardLayoutModel = new DashboardLayoutModel();
        
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

        // Buscar transações que vencem ontem, hoje e amanhã
        $transactionsDueYesterday = $transactionModel->getDueByDateTransactions($orgId, date('Y-m-d', strtotime('-1 day')), 20);
        $transactionsDueToday = $transactionModel->getDueTodayTransactions($orgId, 20);
        $transactionsDueTomorrow = $transactionModel->getDueByDateTransactions($orgId, date('Y-m-d', strtotime('+1 day')), 20);

        // Buscar vencimentos do dia (sempre inicializar array vazio se método não existir)
        $dueTodayTransactions = [];
        if (method_exists($transactionModel, 'getDueTodayTransactions')) {
            $dueTodayTransactions = $transactionModel->getDueTodayTransactions($orgId, 8);
        }
        
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
            'transactionsDueYesterday' => $transactionsDueYesterday,
            'transactionsDueToday' => $transactionsDueToday,
            'transactionsDueTomorrow' => $transactionsDueTomorrow,
            'accounts' => $accounts,
            'categories' => $categories,
            'categoryExpenses' => $categoryExpenses,
            'dueTodayTransactions' => $dueTodayTransactions
        ];
        
        // Get user's dashboard layout preferences
        $userId = $_SESSION['user_id'];
        $userLayout = $dashboardLayoutModel->getUserLayout($userId, $orgId);
        $data['userLayout'] = $userLayout;
        
        $this->render('dashboard', $data);
    }

    public function debugDueToday() {
        if (($_GET['secret'] ?? '') !== 'dag_debug_2025') {
            die('Acesso negado');
        }

        $orgId = 1;
        $transactionModel = new Transaction();

        // Verificar se método existe
        if (!method_exists($transactionModel, 'getDueTodayTransactions')) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Método getDueTodayTransactions não existe']);
            return;
        }

        $dueTodayTransactions = $transactionModel->getDueTodayTransactions($orgId, 8);

        header('Content-Type: application/json');
        echo json_encode([
            'has_method' => true,
            'count' => count($dueTodayTransactions),
            'should_show_card' => !empty($dueTodayTransactions),
            'transactions' => $dueTodayTransactions,
            'current_date' => date('Y-m-d'),
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_PRETTY_PRINT);
    }
}