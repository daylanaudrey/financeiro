<?php
require_once 'BaseController.php';
require_once 'AuthMiddleware.php';

class ReportController extends BaseController {
    private $transactionModel;
    private $accountModel;
    private $categoryModel;
    private $costCenterModel;
    
    public function __construct() {
        parent::__construct();
        $this->transactionModel = new Transaction();
        $this->accountModel = new Account();
        $this->categoryModel = new Category();
        $this->costCenterModel = new CostCenter();
    }
    
    public function index() {
        try {
            $user = AuthMiddleware::requireAuth();
            
            // Por enquanto, usar org_id = 1
            $orgId = 1;
            
            // Dados básicos para a página de relatórios
            $accounts = $this->accountModel->getActiveAccountsByOrg($orgId);
            $categories = $this->categoryModel->getCategoriesByOrg($orgId);
            $costCenters = $this->costCenterModel->getActiveCostCenters($orgId);
            
            // Dados para dashboard de relatórios
            $currentMonth = date('Y-m');
            $currentYear = date('Y');
            
            // Resumo mensal atual
            $monthlyBalance = $this->transactionModel->getMonthlyBalance($orgId, $currentYear, date('m'));
            
            // Últimas transações confirmadas
            $recentTransactions = $this->transactionModel->getConfirmedTransactionsByOrg($orgId, 10, 0);
            
            // Dados para gráfico de categorias (despesas do ano atual)
            $categoryExpenses = $this->transactionModel->getCategoryExpensesChart($orgId, $currentYear);
            
            // Dados para evolução mensal (últimos 12 meses)
            $monthlyEvolution = $this->transactionModel->getMonthlyEvolution($orgId, 12);
            
            $data = [
                'title' => 'Relatórios - Sistema Financeiro',
                'page' => 'reports',
                'user' => $user,
                'accounts' => $accounts,
                'categories' => $categories,
                'costCenters' => $costCenters,
                'monthlyBalance' => $monthlyBalance,
                'recentTransactions' => $recentTransactions,
                'categoryExpenses' => $categoryExpenses,
                'monthlyEvolution' => $monthlyEvolution,
                'currentMonth' => $currentMonth,
                'pageTitle' => 'Relatórios Financeiros'
            ];
            
            $this->render('layout', $data);
            
        } catch (Exception $e) {
            $this->handleError($e, 'Erro ao carregar relatórios');
        }
    }
    
    public function getBalanceReport() {
        try {
            $user = AuthMiddleware::requireAuth();
            $orgId = 1;
            
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-t');
            
            // Buscar saldos por conta
            $accountBalances = $this->accountModel->getBalanceByAccountType($orgId);
            
            // Buscar evolução mensal
            $monthlyEvolution = $this->getMonthlyEvolution($orgId, $startDate, $endDate);
            
            $this->jsonResponse([
                'success' => true,
                'accountBalances' => $accountBalances,
                'monthlyEvolution' => $monthlyEvolution
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function getCategoryReport() {
        try {
            $user = AuthMiddleware::requireAuth();
            $orgId = 1;
            
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-t');
            
            // Relatório por categoria
            $categoryReport = $this->getCategoryBreakdown($orgId, $startDate, $endDate);
            
            $this->jsonResponse([
                'success' => true,
                'categoryReport' => $categoryReport
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function getCategoriesData() {
        try {
            $user = AuthMiddleware::requireAuth();
            $orgId = 1;
            
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-t');
            $includeConfirmed = ($_GET['include_confirmed'] ?? '1') === '1';
            $includeScheduled = ($_GET['include_scheduled'] ?? '1') === '1';
            $includeDrafts = ($_GET['include_drafts'] ?? '0') === '1';
            $includeReceitas = ($_GET['include_receitas'] ?? '1') === '1';
            $includeDespesas = ($_GET['include_despesas'] ?? '1') === '1';
            
            // Buscar dados das categorias com filtros
            $categories = $this->getCategoriesWithFilters($orgId, $startDate, $endDate, [
                'includeConfirmed' => $includeConfirmed,
                'includeScheduled' => $includeScheduled,
                'includeDrafts' => $includeDrafts,
                'includeReceitas' => $includeReceitas,
                'includeDespesas' => $includeDespesas
            ]);
            
            $this->jsonResponse([
                'success' => true,
                'categories' => $categories
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Erro ao carregar dados das categorias: ' . $e->getMessage()
            ]);
        }
    }
    
    private function getMonthlyEvolution($orgId, $startDate, $endDate) {
        $sql = "
            SELECT 
                DATE_FORMAT(data_competencia, '%Y-%m') as mes,
                SUM(CASE WHEN kind IN ('entrada', 'transfer_in') AND status = 'confirmado' THEN valor ELSE 0 END) as receitas,
                SUM(CASE WHEN kind IN ('saida', 'transfer_out') AND status = 'confirmado' THEN valor ELSE 0 END) as despesas
            FROM transactions 
            WHERE org_id = ? 
            AND data_competencia BETWEEN ? AND ?
            AND deleted_at IS NULL
            GROUP BY DATE_FORMAT(data_competencia, '%Y-%m')
            ORDER BY mes
        ";
        
        $stmt = $this->transactionModel->db->prepare($sql);
        $stmt->execute([$orgId, $startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    private function getCategoryBreakdown($orgId, $startDate, $endDate) {
        $sql = "
            SELECT 
                c.nome as categoria_nome,
                c.cor as categoria_cor,
                t.kind as tipo,
                SUM(t.valor) as total,
                COUNT(t.id) as quantidade
            FROM transactions t
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.org_id = ? 
            AND t.data_competencia BETWEEN ? AND ?
            AND t.status = 'confirmado'
            AND t.deleted_at IS NULL
            GROUP BY c.id, c.nome, c.cor, t.kind
            ORDER BY total DESC
        ";
        
        $stmt = $this->transactionModel->db->prepare($sql);
        $stmt->execute([$orgId, $startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    private function getCategoriesWithFilters($orgId, $startDate, $endDate, $filters) {
        // Construir condições de status
        $statusConditions = [];
        if ($filters['includeConfirmed']) $statusConditions[] = "'confirmado'";
        if ($filters['includeScheduled']) $statusConditions[] = "'agendado'";
        if ($filters['includeDrafts']) $statusConditions[] = "'rascunho'";
        
        if (empty($statusConditions)) {
            return []; // Se nenhum status selecionado, retorna vazio
        }
        
        $statusFilter = "status IN (" . implode(',', $statusConditions) . ")";
        
        // Construir condições de tipo
        $kindConditions = [];
        if ($filters['includeReceitas']) $kindConditions[] = "'entrada'";
        if ($filters['includeDespesas']) $kindConditions[] = "'saida'";
        
        if (empty($kindConditions)) {
            return []; // Se nenhum tipo selecionado, retorna vazio
        }
        
        $kindFilter = "kind IN (" . implode(',', $kindConditions) . ")";
        
        $sql = "
            SELECT 
                COALESCE(c.nome, 'Sem categoria') as nome,
                COALESCE(c.cor, '#6c757d') as cor,
                SUM(t.valor) as total,
                COUNT(t.id) as transaction_count
            FROM transactions t
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.org_id = ? 
            AND t.data_competencia BETWEEN ? AND ?
            AND $statusFilter
            AND $kindFilter
            AND t.deleted_at IS NULL
            GROUP BY c.id, c.nome, c.cor
            HAVING total > 0
            ORDER BY total DESC
        ";
        
        $stmt = $this->transactionModel->db->prepare($sql);
        $stmt->execute([$orgId, $startDate, $endDate]);
        return $stmt->fetchAll();
    }
}