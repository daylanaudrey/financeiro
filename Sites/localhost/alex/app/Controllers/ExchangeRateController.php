<?php

/**
 * ExchangeRateController
 * Controller para gerenciar taxas de câmbio PTAX
 */
class ExchangeRateController
{
    /**
     * Listar histórico de taxas
     */
    public function index(): void
    {
        Permission::requireAuth();
        Permission::requirePermission('system.exchange_rates');

        // Buscar histórico de taxas (últimos 30 dias)
        $rates = ExchangeRate::getHistory(30, 'USD');

        // Buscar taxa atual
        $currentRate = ExchangeRate::getCurrentRate('USD');

        $data = [
            'title' => 'Taxa de Câmbio PTAX',
            'rates' => $rates,
            'current_rate' => $currentRate,
            'last_update' => $_SESSION['last_exchange_rate_update'] ?? null,
            'update_status' => $_SESSION['exchange_rate_status'] ?? null
        ];

        $this->render('exchange_rates/index', $data);
    }

    /**
     * Atualizar taxa PTAX manualmente
     */
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método não permitido']);
            return;
        }

        // Verificar permissões
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            $this->jsonResponse(['success' => false, 'message' => 'Não autorizado']);
            return;
        }

        if (!Permission::check('system.exchange_rates')) {
            $this->jsonResponse(['success' => false, 'message' => 'Permissão negada']);
            return;
        }

        try {
            // Forçar atualização da taxa
            $updated = ExchangeRate::updateDailyRate();

            if ($updated) {
                $_SESSION['last_exchange_rate_update'] = date('Y-m-d');
                $_SESSION['exchange_rate_status'] = 'updated';

                // Buscar taxa atualizada
                $currentRate = ExchangeRate::getCurrentRate('USD');
                $rate = $currentRate ? $currentRate['rate'] : null;

                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Taxa PTAX atualizada com sucesso!',
                    'rate' => $rate,
                    'formatted_rate' => $rate ? 'R$ ' . number_format($rate, 4, ',', '.') : 'N/A'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Não foi possível atualizar a taxa PTAX. Tente novamente.'
                ]);
            }

        } catch (Exception $e) {
            error_log("Erro ao atualizar taxa PTAX: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Erro interno ao atualizar taxa: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Buscar taxa atual do dia anterior (AJAX)
     */
    public function getCurrent(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(['success' => false, 'message' => 'Método não permitido']);
            return;
        }

        // Verificar se está logado
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            $this->jsonResponse(['success' => false, 'message' => 'Não autorizado']);
            return;
        }

        try {
            $currentRate = ExchangeRate::getCurrentRate('USD');

            if ($currentRate) {
                $this->jsonResponse([
                    'success' => true,
                    'rate' => $currentRate['rate'],
                    'date' => $currentRate['date'],
                    'formatted_rate' => 'R$ ' . number_format($currentRate['rate'], 4, ',', '.')
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Taxa não encontrada'
                ]);
            }

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Erro ao buscar taxa: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Buscar taxa por data (AJAX)
     */
    public function getByDate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(['success' => false, 'message' => 'Método não permitido']);
            return;
        }

        // Verificar se está logado
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            $this->jsonResponse(['success' => false, 'message' => 'Não autorizado']);
            return;
        }

        $date = $_GET['date'] ?? null;

        if (!$date) {
            $this->jsonResponse(['success' => false, 'message' => 'Data é obrigatória']);
            return;
        }

        try {
            $rate = ExchangeRate::getRateByDate($date, 'USD');

            if ($rate) {
                $this->jsonResponse([
                    'success' => true,
                    'rate' => $rate
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Taxa não encontrada para a data especificada'
                ]);
            }

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Erro ao buscar taxa: ' . $e->getMessage()
            ]);
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

    /**
     * Resposta JSON
     */
    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}