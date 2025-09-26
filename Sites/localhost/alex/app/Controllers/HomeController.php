<?php

/**
 * HomeController
 * Controller da página inicial
 */
class HomeController
{
    /**
     * Página inicial
     */
    public function index(): void
    {
        $data = [
            'title' => 'Bem-vindo ao Sistema Aduaneiro'
        ];

        $this->render('home/index', $data);
    }

    /**
     * Dashboard
     */
    public function dashboard(): void
    {
        // Verificar se está logado
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // Atualizar taxa PTAX se necessário (primeiro acesso do dia)
        $this->updateExchangeRateIfNeeded();

        // Buscar estatísticas reais
        $stats = $this->getStats();

        $data = [
            'title' => 'Dashboard',
            'user_name' => $_SESSION['user_name'] ?? 'Usuário',
            'user_role' => $_SESSION['user_role'] ?? 'viewer',
            'stats' => $stats
        ];

        $this->render('dashboard/index', $data);
    }

    /**
     * Buscar estatísticas do sistema
     */
    private function getStats(): array
    {
        try {
            $pdo = Database::getConnection();

            // Contar produtos
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE deleted = 0 AND is_active = 1");
            $totalProducts = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Contar clientes
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM clients WHERE deleted = 0 AND is_active = 1");
            $totalClients = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Contar processos
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM processes WHERE deleted = 0");
            $totalProcesses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Contar processos pendentes (PRE EMBARQUE)
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM processes WHERE deleted = 0 AND status = 'PRE EMBARQUE'");
            $pendingProcesses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Contar portos
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM ports WHERE deleted = 0 AND is_active = 1");
            $totalPorts = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            return [
                'total_products' => $totalProducts,
                'total_clients' => $totalClients,
                'total_processes' => $totalProcesses,
                'pending_processes' => $pendingProcesses,
                'total_ports' => $totalPorts,
                'recent_processes' => []
            ];

        } catch (Exception $e) {
            // Em caso de erro, retornar valores padrão
            return [
                'total_products' => 0,
                'total_clients' => 0,
                'total_processes' => 0,
                'pending_processes' => 0,
                'total_ports' => 0,
                'recent_processes' => []
            ];
        }
    }

    /**
     * Atualizar taxa de câmbio PTAX se necessário
     */
    private function updateExchangeRateIfNeeded(): void
    {
        try {
            // Verificar se já atualizou hoje
            $lastUpdate = $_SESSION['last_exchange_rate_update'] ?? null;
            $today = date('Y-m-d');

            if ($lastUpdate === $today) {
                return; // Já atualizou hoje
            }

            // Tentar atualizar taxa
            $updated = ExchangeRate::updateDailyRate();

            if ($updated) {
                $_SESSION['last_exchange_rate_update'] = $today;
                $_SESSION['exchange_rate_status'] = 'updated';
            } else {
                $_SESSION['exchange_rate_status'] = 'failed';
            }

        } catch (Exception $e) {
            error_log("Erro ao atualizar taxa PTAX: " . $e->getMessage());
            $_SESSION['exchange_rate_status'] = 'error';
        }
    }

    /**
     * Renderizar view
     */
    private function render(string $view, array $data = []): void
    {
        extract($data);

        // Capturar o conteúdo da view
        ob_start();
        include APP_PATH . '/Views/' . $view . '.php';
        $content = ob_get_clean();

        // Incluir layout
        include APP_PATH . '/Views/layouts/main.php';
    }
}