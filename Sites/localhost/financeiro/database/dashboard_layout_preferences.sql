-- Create table for storing user dashboard layout preferences
CREATE TABLE IF NOT EXISTS dashboard_layout_preferences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    organization_id INT NOT NULL,
    widget_order JSON NOT NULL DEFAULT '[]',
    custom_settings JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_org (user_id, organization_id)
);

-- Insert default layout order for existing users (if any)
-- This sets a default order for all dashboard widgets
INSERT IGNORE INTO dashboard_layout_preferences (user_id, organization_id, widget_order)
SELECT u.id, 1, JSON_ARRAY(
    'summary-cards',
    'account-balances',
    'credit-cards', 
    'comparative-data',
    'accounts-scheduled',
    'category-charts',
    'recent-transactions'
)
FROM users u
WHERE EXISTS (SELECT 1 FROM organizations WHERE id = 1);