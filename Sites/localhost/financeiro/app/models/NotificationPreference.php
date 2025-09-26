<?php
require_once 'BaseModel.php';

class NotificationPreference extends BaseModel {
    protected $table = 'notification_preferences';

    public function getUserPreferences($userId, $orgId, $createIfNotExists = true) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM {$this->table}
                WHERE user_id = ? AND org_id = ?
            ");
            $stmt->execute([$userId, $orgId]);
            $prefs = $stmt->fetch(PDO::FETCH_ASSOC);

            // Se não existir, criar com valores padrão (apenas uma vez)
            if (!$prefs && $createIfNotExists) {
                $created = $this->createDefaultPreferences($userId, $orgId);
                if ($created) {
                    // Buscar novamente sem criar (evita recursão)
                    return $this->getUserPreferences($userId, $orgId, false);
                } else {
                    // Se falhou ao criar, retornar defaults
                    return $this->getDefaultPreferences();
                }
            }

            return $prefs ?: $this->getDefaultPreferences();
        } catch (PDOException $e) {
            error_log("Erro ao buscar preferências de notificação: " . $e->getMessage());
            return $this->getDefaultPreferences();
        }
    }

    public function updateUserPreferences($userId, $orgId, $preferences) {
        try {
            // Verificar se já existe
            $existing = $this->getUserPreferences($userId, $orgId);

            if ($existing) {
                // Atualizar
                $fields = [];
                $values = [];

                foreach ($preferences as $key => $value) {
                    if ($key !== 'id' && $key !== 'user_id' && $key !== 'org_id' && $key !== 'created_at' && $key !== 'updated_at') {
                        $fields[] = "{$key} = ?";
                        $values[] = $value;
                    }
                }

                $values[] = $userId;
                $values[] = $orgId;

                $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE user_id = ? AND org_id = ?";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute($values);
            } else {
                // Criar novo
                $preferences['user_id'] = $userId;
                $preferences['org_id'] = $orgId;
                return $this->create($preferences);
            }
        } catch (PDOException $e) {
            error_log("Erro ao atualizar preferências de notificação: " . $e->getMessage());
            return false;
        }
    }

    public function createDefaultPreferences($userId, $orgId) {
        try {
            $defaultPrefs = [
                'user_id' => $userId,
                'org_id' => $orgId,
                'enable_desktop_notifications' => 1,
                'enable_email_notifications' => 1,
                'enable_sms_notifications' => 0,
                'enable_whatsapp_notifications' => 0,
                'enable_app_notifications' => 1,
                'notify_new_transactions' => 1,
                'notify_upcoming_due_dates' => 1,
                'notify_low_balance' => 1,
                'notify_goal_reached' => 1,
                'notify_overdue_transactions' => 1,
                'notify_weekly_summary' => 1,
                'notify_monthly_summary' => 1,
                'due_date_reminder_days' => 3,
                'low_balance_threshold' => 100.00,
                'quiet_hours_start' => '22:00:00',
                'quiet_hours_end' => '08:00:00',
                'enable_quiet_hours' => 1
            ];

            return $this->create($defaultPrefs);
        } catch (PDOException $e) {
            error_log("Erro ao criar preferências padrão: " . $e->getMessage());
            return false;
        }
    }

    public function getDefaultPreferences() {
        return [
            'enable_desktop_notifications' => true,
            'enable_email_notifications' => true,
            'enable_sms_notifications' => false,
            'enable_app_notifications' => true,
            'notify_new_transactions' => true,
            'notify_upcoming_due_dates' => true,
            'notify_low_balance' => true,
            'notify_goal_reached' => true,
            'notify_overdue_transactions' => true,
            'notify_weekly_summary' => true,
            'notify_monthly_summary' => true,
            'due_date_reminder_days' => 3,
            'low_balance_threshold' => 100.00,
            'quiet_hours_start' => '22:00:00',
            'quiet_hours_end' => '08:00:00',
            'enable_quiet_hours' => true
        ];
    }

    public function shouldSendNotification($userId, $orgId, $notificationType, $deliveryMethod = 'desktop') {
        try {
            $prefs = $this->getUserPreferences($userId, $orgId);
            if (!$prefs) {
                return false;
            }

            // Verificar se está em horário de silêncio
            if ($prefs['enable_quiet_hours'] && $this->isQuietHours($prefs)) {
                // Só enviar notificações urgentes em horário de silêncio
                $urgentTypes = ['overdue_transaction', 'low_balance'];
                if (!in_array($notificationType, $urgentTypes)) {
                    return false;
                }
            }

            // Verificar se o método de entrega está habilitado
            $methodField = "enable_{$deliveryMethod}_notifications";
            if (!$prefs[$methodField]) {
                return false;
            }

            // Verificar se o tipo de notificação está habilitado
            $typeField = "notify_{$notificationType}";
            if (isset($prefs[$typeField])) {
                return $prefs[$typeField];
            }

            return true;
        } catch (Exception $e) {
            error_log("Erro ao verificar se deve enviar notificação: " . $e->getMessage());
            return false;
        }
    }

    private function isQuietHours($prefs) {
        $currentTime = date('H:i:s');
        $quietStart = $prefs['quiet_hours_start'];
        $quietEnd = $prefs['quiet_hours_end'];

        // Se início é maior que fim, significa que passa da meia-noite
        if ($quietStart > $quietEnd) {
            return $currentTime >= $quietStart || $currentTime <= $quietEnd;
        } else {
            return $currentTime >= $quietStart && $currentTime <= $quietEnd;
        }
    }

    public function getUsersWithNotificationsEnabled($orgId, $notificationType, $deliveryMethod = 'desktop') {
        try {
            $stmt = $this->db->prepare("
                SELECT u.id, u.nome, u.email, np.*
                FROM users u
                JOIN user_org_roles uor ON u.id = uor.user_id
                LEFT JOIN {$this->table} np ON u.id = np.user_id AND np.org_id = ?
                WHERE uor.org_id = ?
                  AND u.deleted_at IS NULL
                  AND u.status = 'ativo'
                  AND (np.enable_{$deliveryMethod}_notifications IS NULL OR np.enable_{$deliveryMethod}_notifications = 1)
                  AND (np.notify_{$notificationType} IS NULL OR np.notify_{$notificationType} = 1)
            ");
            $stmt->execute([$orgId, $orgId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar usuários com notificações habilitadas: " . $e->getMessage());
            return [];
        }
    }

    public function getNotificationSummary($userId, $orgId) {
        try {
            $prefs = $this->getUserPreferences($userId, $orgId);
            if (!$prefs) {
                return ['total_enabled' => 0, 'methods' => [], 'types' => []];
            }

            $summary = [
                'total_enabled' => 0,
                'methods' => [
                    'desktop' => $prefs['enable_desktop_notifications'],
                    'email' => $prefs['enable_email_notifications'],
                    'sms' => $prefs['enable_sms_notifications'],
                    'app' => $prefs['enable_app_notifications']
                ],
                'types' => [
                    'new_transactions' => $prefs['notify_new_transactions'],
                    'upcoming_due_dates' => $prefs['notify_upcoming_due_dates'],
                    'low_balance' => $prefs['notify_low_balance'],
                    'goal_reached' => $prefs['notify_goal_reached'],
                    'overdue_transactions' => $prefs['notify_overdue_transactions'],
                    'weekly_summary' => $prefs['notify_weekly_summary'],
                    'monthly_summary' => $prefs['notify_monthly_summary']
                ]
            ];

            // Contar quantos estão habilitados
            $summary['total_enabled'] = array_sum($summary['methods']) + array_sum($summary['types']);

            return $summary;
        } catch (Exception $e) {
            error_log("Erro ao gerar resumo de notificações: " . $e->getMessage());
            return ['total_enabled' => 0, 'methods' => [], 'types' => []];
        }
    }
}