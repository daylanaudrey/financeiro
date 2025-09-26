<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Middleware\AuthMiddleware;
use App\Models\User;
use App\Models\Product;
use App\Models\Client;
use App\Models\Process;

/**
 * DashboardController
 * Controller do Dashboard principal
 */
class DashboardController extends BaseController
{
    public function __construct()
    {
        // Verificar autenticação
        AuthMiddleware::require();
    }

    /**
     * Dashboard principal
     */
    public function index(): void
    {
        // Buscar estatísticas (por enquanto com valores default)
        $stats = [
            'total_products' => $this->getTotalProducts(),
            'total_clients' => $this->getTotalClients(),
            'total_processes' => $this->getTotalProcesses(),
            'pending_processes' => $this->getPendingProcesses(),
            'recent_processes' => $this->getRecentProcesses()
        ];

        $data = [
            'title' => 'Dashboard',
            'stats' => $stats,
            'user_name' => $_SESSION['user_name'],
            'user_role' => $_SESSION['user_role']
        ];

        $this->render('dashboard/index', $data);
    }

    /**
     * Obter total de produtos
     */
    private function getTotalProducts(): int
    {
        // Verificar se o Model existe, senão retornar 0
        if (!class_exists('App\Models\Product')) {
            return 0;
        }
        return Product::count(['deleted' => 0, 'is_active' => 1]);
    }

    /**
     * Obter total de importadores
     */
    private function getTotalClients(): int
    {
        // Verificar se o Model existe, senão retornar 0
        if (!class_exists('App\Models\Client')) {
            return 0;
        }
        return Client::count(['deleted' => 0, 'is_active' => 1]);
    }

    /**
     * Obter total de processos
     */
    private function getTotalProcesses(): int
    {
        // Verificar se o Model existe, senão retornar 0
        if (!class_exists('App\Models\Process')) {
            return 0;
        }
        return Process::count(['deleted' => 0]);
    }

    /**
     * Obter processos pendentes
     */
    private function getPendingProcesses(): int
    {
        // Verificar se o Model existe, senão retornar 0
        if (!class_exists('App\Models\Process')) {
            return 0;
        }
        return Process::count(['deleted' => 0, 'status' => 'DRAFT']);
    }

    /**
     * Obter processos recentes
     */
    private function getRecentProcesses(): array
    {
        // Por enquanto retornar array vazio
        return [];
    }
}