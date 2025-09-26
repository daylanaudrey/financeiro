<?php
require_once 'BaseModel.php';

class NotificationHistory extends BaseModel {
    protected $table = 'notification_history';

    // Sobrescrever o método create para usar sent_at e created_at
    public function create($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ({$columns}, sent_at, created_at) VALUES ({$placeholders}, NOW(), NOW())";

        $stmt = $this->db->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        try {
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            } else {
                // Log do erro para debug
                $errorInfo = $stmt->errorInfo();
                error_log("NotificationHistory create failed. Error: " . json_encode($errorInfo));
                error_log("SQL: " . $sql);
                error_log("Data: " . json_encode($data));
                return false;
            }
        } catch (Exception $e) {
            error_log("NotificationHistory create EXCEPTION: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Data: " . json_encode($data));
            throw $e; // Re-throw para que seja capturado no logNotification
        }
    }

    public function logNotification($userId, $orgId, $type, $deliveryMethod, $title, $message = null, $relatedEntityType = null, $relatedEntityId = null, $notificationData = null) {
        try {
            // Log detalhado dos parâmetros recebidos
            error_log("logNotification called with params: userId={$userId}, orgId={$orgId}, type={$type}, deliveryMethod={$deliveryMethod}");

            $data = [
                'user_id' => $userId,
                'org_id' => $orgId,
                'notification_type' => $type,
                'delivery_method' => $deliveryMethod,
                'title' => $title,
                'message' => $message,
                'related_entity_type' => $relatedEntityType,
                'related_entity_id' => $relatedEntityId,
                'status' => 'sent'
            ];

            if ($notificationData) {
                $data['notification_data'] = json_encode($notificationData);
            }

            error_log("logNotification data prepared: " . json_encode($data));

            $result = $this->create($data);

            error_log("logNotification create result: " . ($result ? $result : 'FALSE'));

            return $result;
        } catch (Exception $e) {
            // Capturar TODAS as exceções, não só PDOException
            error_log("Erro ao registrar notificação no histórico: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    public function markAsDelivered($notificationId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE {$this->table}
                SET status = 'delivered', delivered_at = NOW()
                WHERE id = ?
            ");
            return $stmt->execute([$notificationId]);
        } catch (PDOException $e) {
            error_log("Erro ao marcar notificação como entregue: " . $e->getMessage());
            return false;
        }
    }

    public function markAsClicked($notificationId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE {$this->table}
                SET status = 'clicked', clicked_at = NOW()
                WHERE id = ?
            ");
            return $stmt->execute([$notificationId]);
        } catch (PDOException $e) {
            error_log("Erro ao marcar notificação como clicada: " . $e->getMessage());
            return false;
        }
    }

    public function markAsFailed($notificationId, $errorMessage) {
        try {
            $stmt = $this->db->prepare("
                UPDATE {$this->table}
                SET status = 'failed', error_message = ?
                WHERE id = ?
            ");
            return $stmt->execute([$errorMessage, $notificationId]);
        } catch (PDOException $e) {
            error_log("Erro ao marcar notificação como falhada: " . $e->getMessage());
            return false;
        }
    }

    public function getUserNotifications($userId, $orgId, $limit = 50, $offset = 0) {
        try {
            $stmt = $this->db->prepare("
                SELECT nh.*,
                       CASE
                           WHEN nh.related_entity_type = 'transaction' THEN t.description
                           WHEN nh.related_entity_type = 'vault' THEN v.name
                           WHEN nh.related_entity_type = 'account' THEN a.name
                           ELSE NULL
                       END as related_entity_name
                FROM {$this->table} nh
                LEFT JOIN transactions t ON nh.related_entity_type = 'transaction' AND nh.related_entity_id = t.id
                LEFT JOIN vaults v ON nh.related_entity_type = 'vault' AND nh.related_entity_id = v.id
                LEFT JOIN accounts a ON nh.related_entity_type = 'account' AND nh.related_entity_id = a.id
                WHERE nh.user_id = ? AND nh.org_id = ?
                ORDER BY nh.sent_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$userId, $orgId, $limit, $offset]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar notificações do usuário: " . $e->getMessage());
            return [];
        }
    }

    public function getUnreadCount($userId, $orgId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM {$this->table}
                WHERE user_id = ? AND org_id = ? AND status = 'sent'
            ");
            $stmt->execute([$userId, $orgId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Erro ao contar notificações não lidas: " . $e->getMessage());
            return 0;
        }
    }

    public function getPendingNotifications($userId, $orgId, $limit = 20) {
        try {
            $stmt = $this->db->prepare("
                SELECT nh.*,
                       CASE
                           WHEN nh.related_entity_type = 'transaction' THEN t.description
                           WHEN nh.related_entity_type = 'vault' THEN v.name
                           WHEN nh.related_entity_type = 'account' THEN a.name
                           ELSE NULL
                       END as related_entity_name,
                       TIMESTAMPDIFF(MINUTE, nh.sent_at, NOW()) as minutes_ago
                FROM {$this->table} nh
                LEFT JOIN transactions t ON nh.related_entity_type = 'transaction' AND nh.related_entity_id = t.id
                LEFT JOIN vaults v ON nh.related_entity_type = 'vault' AND nh.related_entity_id = v.id
                LEFT JOIN accounts a ON nh.related_entity_type = 'account' AND nh.related_entity_id = a.id
                WHERE nh.user_id = ? AND nh.org_id = ? AND nh.status = 'sent'
                ORDER BY nh.sent_at DESC
                LIMIT ?
            ");
            $stmt->execute([$userId, $orgId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar notificações pendentes: " . $e->getMessage());
            return [];
        }
    }

    public function getRecentNotifications($userId, $orgId, $limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT nh.*,
                       CASE
                           WHEN nh.related_entity_type = 'transaction' THEN t.description
                           WHEN nh.related_entity_type = 'vault' THEN v.name
                           WHEN nh.related_entity_type = 'account' THEN a.name
                           ELSE NULL
                       END as related_entity_name,
                       TIMESTAMPDIFF(MINUTE, nh.sent_at, NOW()) as minutes_ago
                FROM {$this->table} nh
                LEFT JOIN transactions t ON nh.related_entity_type = 'transaction' AND nh.related_entity_id = t.id
                LEFT JOIN vaults v ON nh.related_entity_type = 'vault' AND nh.related_entity_id = v.id
                LEFT JOIN accounts a ON nh.related_entity_type = 'account' AND nh.related_entity_id = a.id
                WHERE nh.user_id = ? AND nh.org_id = ?
                  AND nh.sent_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY nh.sent_at DESC
                LIMIT ?
            ");
            $stmt->execute([$userId, $orgId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar notificações recentes: " . $e->getMessage());
            return [];
        }
    }

    public function getNotificationStats($userId, $orgId, $days = 30) {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    notification_type,
                    delivery_method,
                    status,
                    COUNT(*) as count,
                    DATE(sent_at) as date
                FROM {$this->table}
                WHERE user_id = ? AND org_id = ?
                  AND sent_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY notification_type, delivery_method, status, DATE(sent_at)
                ORDER BY sent_at DESC
            ");
            $stmt->execute([$userId, $orgId, $days]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar estatísticas de notificações: " . $e->getMessage());
            return [];
        }
    }

    public function markAllAsRead($userId, $orgId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE {$this->table}
                SET status = 'delivered', delivered_at = NOW()
                WHERE user_id = ? AND org_id = ? AND status = 'sent'
            ");
            return $stmt->execute([$userId, $orgId]);
        } catch (PDOException $e) {
            error_log("Erro ao marcar todas as notificações como lidas: " . $e->getMessage());
            return false;
        }
    }

    public function deleteOldNotifications($days = 90) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM {$this->table}
                WHERE sent_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            return $stmt->execute([$days]);
        } catch (PDOException $e) {
            error_log("Erro ao deletar notificações antigas: " . $e->getMessage());
            return false;
        }
    }

    public function getNotificationById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT nh.*,
                       CASE
                           WHEN nh.related_entity_type = 'transaction' THEN t.description
                           WHEN nh.related_entity_type = 'vault' THEN v.name
                           WHEN nh.related_entity_type = 'account' THEN a.name
                           ELSE NULL
                       END as related_entity_name
                FROM {$this->table} nh
                LEFT JOIN transactions t ON nh.related_entity_type = 'transaction' AND nh.related_entity_id = t.id
                LEFT JOIN vaults v ON nh.related_entity_type = 'vault' AND nh.related_entity_id = v.id
                LEFT JOIN accounts a ON nh.related_entity_type = 'account' AND nh.related_entity_id = a.id
                WHERE nh.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar notificação por ID: " . $e->getMessage());
            return null;
        }
    }
}