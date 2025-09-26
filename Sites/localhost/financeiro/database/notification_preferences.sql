-- Tabela para preferências de notificação por usuário
CREATE TABLE IF NOT EXISTS notification_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    org_id INT NOT NULL,

    -- Tipos de notificação
    enable_desktop_notifications BOOLEAN DEFAULT TRUE,
    enable_email_notifications BOOLEAN DEFAULT TRUE,
    enable_sms_notifications BOOLEAN DEFAULT FALSE,
    enable_app_notifications BOOLEAN DEFAULT TRUE,

    -- Eventos específicos
    notify_new_transactions BOOLEAN DEFAULT TRUE,
    notify_upcoming_due_dates BOOLEAN DEFAULT TRUE,
    notify_low_balance BOOLEAN DEFAULT TRUE,
    notify_goal_reached BOOLEAN DEFAULT TRUE,
    notify_overdue_transactions BOOLEAN DEFAULT TRUE,
    notify_weekly_summary BOOLEAN DEFAULT TRUE,
    notify_monthly_summary BOOLEAN DEFAULT TRUE,

    -- Configurações de timing
    due_date_reminder_days INT DEFAULT 3, -- Quantos dias antes avisar sobre vencimentos
    low_balance_threshold DECIMAL(10,2) DEFAULT 100.00, -- Valor mínimo para alerta

    -- Configurações de horário (para evitar spam)
    quiet_hours_start TIME DEFAULT '22:00:00',
    quiet_hours_end TIME DEFAULT '08:00:00',
    enable_quiet_hours BOOLEAN DEFAULT TRUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (org_id) REFERENCES organizations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_org (user_id, org_id)
);

-- Índices para performance
CREATE INDEX idx_notification_prefs_user_org ON notification_preferences(user_id, org_id);
CREATE INDEX idx_notification_prefs_desktop ON notification_preferences(enable_desktop_notifications);

-- Tabela para histórico de notificações enviadas
CREATE TABLE IF NOT EXISTS notification_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    org_id INT NOT NULL,

    notification_type ENUM(
        'new_transaction',
        'upcoming_due',
        'low_balance',
        'goal_reached',
        'overdue_transaction',
        'weekly_summary',
        'monthly_summary',
        'system_alert'
    ) NOT NULL,

    delivery_method ENUM('desktop', 'email', 'sms', 'app') NOT NULL,

    title VARCHAR(255) NOT NULL,
    message TEXT,

    -- Dados relacionados
    related_entity_type VARCHAR(50), -- 'transaction', 'vault', 'account', etc
    related_entity_id INT,

    -- Status da entrega
    status ENUM('sent', 'delivered', 'failed', 'clicked') DEFAULT 'sent',

    -- Dados técnicos
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivered_at TIMESTAMP NULL,
    clicked_at TIMESTAMP NULL,
    error_message TEXT NULL,

    -- Metadados
    notification_data JSON, -- Para dados específicos da notificação

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (org_id) REFERENCES organizations(id) ON DELETE CASCADE
);

-- Índices para histórico
CREATE INDEX idx_notification_history_user_org ON notification_history(user_id, org_id);
CREATE INDEX idx_notification_history_type ON notification_history(notification_type);
CREATE INDEX idx_notification_history_status ON notification_history(status);
CREATE INDEX idx_notification_history_sent_at ON notification_history(sent_at);

-- Inserir preferências padrão para usuários existentes
INSERT INTO notification_preferences (user_id, org_id)
SELECT DISTINCT u.id, uor.org_id
FROM users u
JOIN user_org_roles uor ON u.id = uor.user_id
WHERE NOT EXISTS (
    SELECT 1 FROM notification_preferences np
    WHERE np.user_id = u.id AND np.org_id = uor.org_id
)
AND u.deleted_at IS NULL;