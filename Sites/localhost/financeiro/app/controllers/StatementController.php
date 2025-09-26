<?php
require_once 'BaseController.php';
require_once 'AuthMiddleware.php';

class StatementController extends BaseController {
    private $transactionModel;
    private $accountModel;
    
    public function __construct() {
        parent::__construct();
        $this->transactionModel = new Transaction();
        $this->accountModel = new Account();
    }
    
    public function index() {
        try {
            $user = AuthMiddleware::requireAuth();
            $orgId = $this->getCurrentOrgId();
            
            $accountId = (int)($_GET['account_id'] ?? 0);
            
            if (!$accountId) {
                header('Location: ' . url('/accounts'));
                exit;
            }
            
            // Buscar dados da conta
            $account = $this->accountModel->findById($accountId);
            if (!$account || $account['org_id'] != $orgId) {
                header('Location: ' . url('/accounts'));
                exit;
            }
            
            // Garantir que temos o saldo atual da conta
            if (!isset($account['saldo_atual'])) {
                $account['saldo'] = $account['saldo_inicial'] ?? 0;
            } else {
                $account['saldo'] = $account['saldo_atual'];
            }
            
            // Parâmetros de filtro
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-t');
            $page = (int)($_GET['page'] ?? 1);
            $perPage = 50;
            $offset = ($page - 1) * $perPage;
            
            // Buscar transações da conta
            $transactions = $this->transactionModel->getAccountTransactions($accountId, $startDate, $endDate, $perPage, $offset);
            
            // Calcular saldo inicial (antes do período)
            $initialBalance = $this->transactionModel->getBalanceBeforeDate($accountId, $startDate);
            
            // Calcular totais do período
            $periodTotals = $this->transactionModel->getAccountPeriodTotals($accountId, $startDate, $endDate);
            
            // Calcular total de registros para paginação
            $totalRecords = $this->transactionModel->countAccountTransactions($accountId, $startDate, $endDate);
            $totalPages = ceil($totalRecords / $perPage);
            
            // Renderizar a view statements dentro de um buffer
            ob_start();
            $pageData = [
                'user' => $user,
                'account' => $account,
                'transactions' => $transactions,
                'initialBalance' => $initialBalance,
                'periodTotals' => $periodTotals,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalRecords' => $totalRecords
            ];
            extract($pageData);
            include __DIR__ . '/../views/statements.php';
            $content = ob_get_clean();
            
            $data = [
                'title' => 'Extrato da Conta - Sistema Financeiro',
                'page' => 'statements',
                'user' => $user,
                'content' => $content,
                'pageTitle' => 'Extrato - ' . $account['nome']
            ];
            
            $this->render('layout', $data);
            
        } catch (Exception $e) {
            $this->handleError($e, 'Erro ao carregar extrato');
        }
    }
    
    public function export() {
        try {
            $user = AuthMiddleware::requireAuth();
            $orgId = $this->getCurrentOrgId();
            
            $accountId = (int)($_GET['account_id'] ?? 0);
            $format = $_GET['format'] ?? 'pdf';
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-t');
            
            if (!$accountId) {
                $this->jsonResponse(['success' => false, 'message' => 'Conta não informada']);
                return;
            }
            
            $account = $this->accountModel->findById($accountId);
            if (!$account || $account['org_id'] != $orgId) {
                $this->jsonResponse(['success' => false, 'message' => 'Conta não encontrada']);
                return;
            }
            
            // Garantir que temos o saldo atual da conta
            if (!isset($account['saldo_atual'])) {
                $account['saldo'] = $account['saldo_inicial'] ?? 0;
            } else {
                $account['saldo'] = $account['saldo_atual'];
            }
            
            // Buscar todas as transações do período (sem limite)
            $transactions = $this->transactionModel->getAccountTransactions($accountId, $startDate, $endDate, 10000, 0);
            $initialBalance = $this->transactionModel->getBalanceBeforeDate($accountId, $startDate);
            $periodTotals = $this->transactionModel->getAccountPeriodTotals($accountId, $startDate, $endDate);
            
            if ($format === 'csv') {
                $this->exportToCSV($account, $transactions, $initialBalance, $periodTotals, $startDate, $endDate);
            } else {
                // PDF export pode ser implementado futuramente
                $this->jsonResponse(['success' => false, 'message' => 'Formato não suportado']);
            }
            
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    private function exportToCSV($account, $transactions, $initialBalance, $periodTotals, $startDate, $endDate) {
        $filename = 'extrato_' . strtolower(str_replace(' ', '_', $account['nome'])) . '_' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeçalho do extrato
        fputcsv($output, ['EXTRATO BANCÁRIO'], ';');
        fputcsv($output, [''], ';');
        fputcsv($output, ['Conta:', $account['nome']], ';');
        fputcsv($output, ['Período:', date('d/m/Y', strtotime($startDate)) . ' a ' . date('d/m/Y', strtotime($endDate))], ';');
        fputcsv($output, ['Saldo Inicial:', 'R$ ' . number_format($initialBalance, 2, ',', '.')], ';');
        fputcsv($output, [''], ';');
        
        // Cabeçalhos das colunas
        fputcsv($output, [
            'Data',
            'Descrição', 
            'Categoria',
            'Tipo',
            'Status',
            'Valor',
            'Saldo'
        ], ';');
        
        // Dados das transações
        $runningBalance = $initialBalance;
        foreach ($transactions as $transaction) {
            $valor = $transaction['valor'];
            $isCredit = in_array($transaction['kind'], ['entrada', 'transfer_in']);
            
            if ($transaction['status'] === 'confirmado') {
                $runningBalance += $isCredit ? $valor : -$valor;
            }
            
            fputcsv($output, [
                date('d/m/Y', strtotime($transaction['data_competencia'])),
                $transaction['descricao'],
                $transaction['category_name'] ?: 'Sem categoria',
                $this->getTransactionTypeLabel($transaction['kind']),
                ucfirst($transaction['status']),
                ($isCredit ? '' : '-') . 'R$ ' . number_format($valor, 2, ',', '.'),
                'R$ ' . number_format($runningBalance, 2, ',', '.')
            ], ';');
        }
        
        // Resumo
        fputcsv($output, [''], ';');
        fputcsv($output, ['RESUMO DO PERÍODO'], ';');
        fputcsv($output, ['Total de Entradas:', 'R$ ' . number_format($periodTotals['total_entradas'], 2, ',', '.')], ';');
        fputcsv($output, ['Total de Saídas:', 'R$ ' . number_format($periodTotals['total_saidas'], 2, ',', '.')], ';');
        fputcsv($output, ['Saldo Líquido:', 'R$ ' . number_format($periodTotals['saldo_liquido'], 2, ',', '.')], ';');
        fputcsv($output, ['Total de Transações:', $periodTotals['total_transactions']], ';');
        
        fclose($output);
        exit;
    }
    
    private function getTransactionTypeLabel($kind) {
        $types = [
            'entrada' => 'Receita',
            'saida' => 'Despesa',
            'transfer_in' => 'Transferência Recebida',
            'transfer_out' => 'Transferência Enviada'
        ];
        
        return $types[$kind] ?? ucfirst($kind);
    }
}