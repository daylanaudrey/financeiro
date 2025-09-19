<?php

class DashboardApiController extends BaseController
{
    private $dashboardLayoutModel;

    public function __construct()
    {
        parent::__construct();
        
        // Ensure session is started for authentication
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->dashboardLayoutModel = new DashboardLayoutModel();
    }

    /**
     * Save user's dashboard layout preferences
     * POST /api/dashboard/save-layout
     */
    public function saveLayout()
    {
        try {
            // Ensure user is authenticated
            if (!$this->isAuthenticated()) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            // Get request data
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['widget_order'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Dados inválidos'
                ], 400);
            }

            $widgetOrder = $input['widget_order'];
            
            // Validate widget order is an array
            if (!is_array($widgetOrder)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Ordem dos widgets deve ser um array'
                ], 400);
            }

            // Validate that all widgets are known widget IDs
            $validWidgets = [
                'summary-cards',
                'account-balances',
                'credit-cards',
                'due-today',
                'due-dates',
                'comparative-data',
                'accounts-scheduled',
                'category-charts',
                'recent-transactions'
            ];

            foreach ($widgetOrder as $widgetId) {
                if (!in_array($widgetId, $validWidgets)) {
                    return $this->jsonResponse([
                        'success' => false,
                        'message' => "Widget inválido: {$widgetId}"
                    ], 400);
                }
            }

            $userId = $_SESSION['user_id'];
            $orgId = 1; // Currently using fixed org ID as specified in CLAUDE.md
            
            // Save layout preferences
            $result = $this->dashboardLayoutModel->saveUserLayout($userId, $orgId, $widgetOrder);
            
            if ($result) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Layout salvo com sucesso'
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Erro ao salvar layout'
                ], 500);
            }

        } catch (Exception $e) {
            error_log("Error saving dashboard layout: " . $e->getMessage());
            return $this->handleError($e, 'Erro interno do servidor');
        }
    }

    /**
     * Reset user's dashboard layout to default
     * POST /api/dashboard/reset-layout
     */
    public function resetLayout()
    {
        try {
            if (!$this->isAuthenticated()) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            $userId = $_SESSION['user_id'];
            $orgId = 1; // Currently using fixed org ID

            // Default widget order
            $defaultOrder = [
                'summary-cards',
                'account-balances',
                'credit-cards',
                'due-today',
                'due-dates',
                'comparative-data',
                'accounts-scheduled',
                'category-charts',
                'recent-transactions'
            ];

            $result = $this->dashboardLayoutModel->saveUserLayout($userId, $orgId, $defaultOrder);
            
            if ($result) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Layout restaurado para o padrão'
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Erro ao restaurar layout'
                ], 500);
            }

        } catch (Exception $e) {
            error_log("Error resetting dashboard layout: " . $e->getMessage());
            return $this->handleError($e, 'Erro interno do servidor');
        }
    }

    /**
     * Get user's dashboard layout preferences
     * GET /api/dashboard/get-layout
     */
    public function getLayout()
    {
        try {
            if (!$this->isAuthenticated()) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            $userId = $_SESSION['user_id'];
            $orgId = 1; // Currently using fixed org ID

            $layout = $this->dashboardLayoutModel->getUserLayout($userId, $orgId);
            
            return $this->jsonResponse([
                'success' => true,
                'layout' => $layout
            ]);

        } catch (Exception $e) {
            error_log("Error getting dashboard layout: " . $e->getMessage());
            return $this->handleError($e, 'Erro interno do servidor');
        }
    }

    /**
     * Check if user is authenticated
     */
    private function isAuthenticated()
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}