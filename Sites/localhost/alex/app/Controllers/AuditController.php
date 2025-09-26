<?php

/**
 * AuditController
 * Gerencia logs de auditoria - ACESSO RESTRITO A ADMIN
 */
class AuditController
{
    /**
     * Listar logs de auditoria
     */
    public function index(): void
    {
        // Verificar se está logado
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // RESTRITO APENAS A ADMIN
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = 'Acesso negado. Apenas administradores podem acessar os logs de auditoria.';
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        // Filtros de busca
        $filters = [
            'action' => $_GET['action'] ?? '',
            'table_name' => $_GET['table_name'] ?? '',
            'user_id' => !empty($_GET['user_id']) ? (int)$_GET['user_id'] : null,
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? ''
        ];

        // Paginação
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;

        // Buscar logs
        $logs = AuditLog::getAll(
            $filters['action'] ?: null,
            $filters['table_name'] ?: null,
            $filters['user_id'],
            $filters['start_date'] ?: null,
            $filters['end_date'] ?: null,
            $limit,
            $offset
        );

        // Contar total para paginação
        $totalLogs = AuditLog::count(
            $filters['action'] ?: null,
            $filters['table_name'] ?: null,
            $filters['user_id'],
            $filters['start_date'] ?: null,
            $filters['end_date'] ?: null
        );

        $totalPages = ceil($totalLogs / $limit);

        // Buscar opções para filtros
        $uniqueActions = AuditLog::getUniqueActions();
        $uniqueTables = AuditLog::getUniqueTables();
        $users = User::getAllActive();

        $data = [
            'title' => 'Logs de Auditoria',
            'logs' => $logs,
            'filters' => $filters,
            'uniqueActions' => $uniqueActions,
            'uniqueTables' => $uniqueTables,
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalLogs' => $totalLogs,
            'limit' => $limit
        ];

        $this->render('audit/index', $data);
    }

    /**
     * Visualizar detalhes de um log específico
     */
    public function show(int $id): void
    {
        // Verificar se está logado
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // RESTRITO APENAS A ADMIN
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = 'Acesso negado. Apenas administradores podem acessar os logs de auditoria.';
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        $log = AuditLog::findById($id);

        if (!$log) {
            $_SESSION['error'] = 'Log de auditoria não encontrado.';
            header('Location: ' . BASE_URL . 'audit');
            exit;
        }

        $data = [
            'title' => 'Detalhes do Log de Auditoria',
            'log' => $log
        ];

        $this->render('audit/show', $data);
    }

    /**
     * Visualizar histórico de um registro específico
     */
    public function history(): void
    {
        // Verificar se está logado
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // RESTRITO APENAS A ADMIN
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = 'Acesso negado. Apenas administradores podem acessar os logs de auditoria.';
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        $tableName = $_GET['table'] ?? '';
        $recordId = !empty($_GET['id']) ? (int)$_GET['id'] : 0;

        if (!$tableName || !$recordId) {
            $_SESSION['error'] = 'Parâmetros inválidos para visualizar histórico.';
            header('Location: ' . BASE_URL . 'audit');
            exit;
        }

        $logs = AuditLog::getByRecord($tableName, $recordId);

        $data = [
            'title' => "Histórico - {$tableName} #{$recordId}",
            'logs' => $logs,
            'tableName' => $tableName,
            'recordId' => $recordId
        ];

        $this->render('audit/history', $data);
    }

    /**
     * Limpeza de logs antigos
     */
    public function cleanup(): void
    {
        // Verificar se está logado
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // RESTRITO APENAS A ADMIN
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = 'Acesso negado. Apenas administradores podem gerenciar logs de auditoria.';
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $daysToKeep = max(30, (int)($_POST['days_to_keep'] ?? 365));

            try {
                $deletedCount = AuditLog::cleanup($daysToKeep);

                // Log da limpeza
                AuditLog::log('CLEANUP', 'audit_logs', null, null, [
                    'days_to_keep' => $daysToKeep,
                    'deleted_count' => $deletedCount
                ]);

                $_SESSION['success'] = "Limpeza concluída! {$deletedCount} logs antigos foram removidos.";
            } catch (Exception $e) {
                $_SESSION['error'] = 'Erro ao realizar limpeza: ' . $e->getMessage();
            }

            header('Location: ' . BASE_URL . 'audit');
            exit;
        }

        $data = [
            'title' => 'Limpeza de Logs de Auditoria'
        ];

        $this->render('audit/cleanup', $data);
    }

    /**
     * Exportar logs para CSV
     */
    public function export(): void
    {
        // Verificar se está logado
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // RESTRITO APENAS A ADMIN
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = 'Acesso negado. Apenas administradores podem exportar logs de auditoria.';
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        // Aplicar mesmos filtros da listagem
        $filters = [
            'action' => $_GET['action'] ?? '',
            'table_name' => $_GET['table_name'] ?? '',
            'user_id' => !empty($_GET['user_id']) ? (int)$_GET['user_id'] : null,
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? ''
        ];

        // Buscar todos os logs (sem limite para exportação)
        $logs = AuditLog::getAll(
            $filters['action'] ?: null,
            $filters['table_name'] ?: null,
            $filters['user_id'],
            $filters['start_date'] ?: null,
            $filters['end_date'] ?: null,
            10000, // Limite máximo
            0
        );

        // Log da exportação
        AuditLog::log('EXPORT', 'audit_logs', null, null, [
            'filters' => $filters,
            'exported_count' => count($logs)
        ]);

        // Gerar CSV
        $filename = 'audit_logs_' . date('Y-m-d_H-i-s') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // Cabeçalho CSV
        fputcsv($output, [
            'ID',
            'Usuário',
            'Email',
            'Ação',
            'Tabela',
            'ID do Registro',
            'IP',
            'Data/Hora',
            'Valores Antigos',
            'Valores Novos'
        ]);

        // Dados
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['id'],
                $log['user_name'] ?? 'Sistema',
                $log['user_email'] ?? '',
                $log['action'],
                $log['table_name'],
                $log['record_id'],
                $log['ip_address'],
                $log['created_at'],
                $log['old_values'],
                $log['new_values']
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Renderizar view
     */
    private function render(string $view, array $data = []): void
    {
        extract($data);

        // Capturar o conteúdo da view
        ob_start();
        include __DIR__ . '/../Views/' . $view . '.php';
        $content = ob_get_clean();

        // Incluir layout principal
        include __DIR__ . '/../Views/layouts/main.php';
    }
}