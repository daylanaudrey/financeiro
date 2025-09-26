<?php

class DashboardLayoutModel
{
    private $db;

    public function __construct()
    {
        global $pdo;
        if (!$pdo) {
            // Se não há conexão global, criar uma nova
            require_once __DIR__ . '/../../config/database.php';
            $database = new Database();
            $this->db = $database->connect();
        } else {
            $this->db = $pdo;
        }
    }

    /**
     * Save user's dashboard layout preferences
     * @param int $userId
     * @param int $orgId 
     * @param array $widgetOrder
     * @return bool
     */
    public function saveUserLayout($userId, $orgId, $widgetOrder)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO dashboard_layout_preferences (user_id, organization_id, widget_order, updated_at)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                    widget_order = VALUES(widget_order),
                    updated_at = NOW()
            ");
            
            $widgetOrderJson = json_encode($widgetOrder);
            return $stmt->execute([$userId, $orgId, $widgetOrderJson]);
            
        } catch (PDOException $e) {
            error_log("Database error in saveUserLayout: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user's dashboard layout preferences
     * @param int $userId
     * @param int $orgId
     * @return array
     */
    public function getUserLayout($userId, $orgId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT widget_order, custom_settings 
                FROM dashboard_layout_preferences 
                WHERE user_id = ? AND organization_id = ?
            ");
            
            $stmt->execute([$userId, $orgId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return [
                    'widget_order' => json_decode($result['widget_order'], true),
                    'custom_settings' => $result['custom_settings'] ? json_decode($result['custom_settings'], true) : null
                ];
            } else {
                // Return default layout if no custom layout exists
                return $this->getDefaultLayout();
            }
            
        } catch (PDOException $e) {
            error_log("Database error in getUserLayout: " . $e->getMessage());
            return $this->getDefaultLayout();
        }
    }

    /**
     * Get default dashboard layout
     * @return array
     */
    public function getDefaultLayout()
    {
        return [
            'widget_order' => [
                'summary-cards',
                'account-balances',
                'credit-cards',
                'due-today',
                'due-dates',
                'comparative-data',
                'accounts-scheduled',
                'category-charts',
                'recent-transactions'
            ],
            'custom_settings' => null
        ];
    }

    /**
     * Check if user has custom layout
     * @param int $userId
     * @param int $orgId
     * @return bool
     */
    public function hasCustomLayout($userId, $orgId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 1 FROM dashboard_layout_preferences 
                WHERE user_id = ? AND organization_id = ?
            ");
            
            $stmt->execute([$userId, $orgId]);
            return $stmt->fetchColumn() !== false;
            
        } catch (PDOException $e) {
            error_log("Database error in hasCustomLayout: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete user's custom layout (revert to default)
     * @param int $userId
     * @param int $orgId
     * @return bool
     */
    public function deleteUserLayout($userId, $orgId)
    {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM dashboard_layout_preferences 
                WHERE user_id = ? AND organization_id = ?
            ");
            
            return $stmt->execute([$userId, $orgId]);
            
        } catch (PDOException $e) {
            error_log("Database error in deleteUserLayout: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Apply user's layout order to dashboard widgets (for server-side rendering)
     * @param array $widgets - Array of widget data keyed by widget ID
     * @param array $userOrder - User's preferred widget order
     * @return array - Reordered widgets array
     */
    public function applyLayoutOrder($widgets, $userOrder)
    {
        if (!is_array($userOrder) || empty($userOrder)) {
            return $widgets;
        }

        $orderedWidgets = [];
        
        // First, add widgets in user's preferred order
        foreach ($userOrder as $widgetId) {
            if (isset($widgets[$widgetId])) {
                $orderedWidgets[$widgetId] = $widgets[$widgetId];
            }
        }
        
        // Then add any remaining widgets that weren't in the user's order
        foreach ($widgets as $widgetId => $widgetData) {
            if (!isset($orderedWidgets[$widgetId])) {
                $orderedWidgets[$widgetId] = $widgetData;
            }
        }
        
        return $orderedWidgets;
    }
}