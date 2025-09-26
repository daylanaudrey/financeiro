<?php
require_once 'BaseController.php';
require_once 'AuthMiddleware.php';
require_once __DIR__ . '/../models/NotificationHistory.php';
require_once __DIR__ . '/../models/NotificationPreference.php';

class NotificationController extends BaseController {
    private $notificationHistory;
    private $notificationPreference;

    public function __construct() {
        parent::__construct();
        $this->notificationHistory = new NotificationHistory();
        $this->notificationPreference = new NotificationPreference();
    }

    public function recent() {
        try {
            $sessionUser = AuthMiddleware::requireAuth();
            $userId = $sessionUser['id'];
            $orgId = 1; // Por enquanto sempre org 1

            $notifications = $this->notificationHistory->getRecentNotifications($userId, $orgId, 10);
            $unreadCount = $this->notificationHistory->getUnreadCount($userId, $orgId);

            return $this->jsonResponse([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);

        } catch (Exception $e) {
            error_log("Erro ao buscar notificações recentes: " . $e->getMessage());
            return $this->handleError($e, 'Erro ao carregar notificações');
        }
    }


    public function markAsRead($id) {
        try {
            $sessionUser = AuthMiddleware::requireAuth();
            $userId = $sessionUser['id'];
            $orgId = 1; // Por enquanto sempre org 1

            // Verificar se a notificação pertence ao usuário
            $notification = $this->notificationHistory->getNotificationById($id);
            if (!$notification || $notification['user_id'] != $userId || $notification['org_id'] != $orgId) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Notificação não encontrada'
                ]);
            }

            $result = $this->notificationHistory->markAsDelivered($id);

            return $this->jsonResponse([
                'success' => $result,
                'message' => $result ? 'Notificação marcada como lida' : 'Erro ao marcar como lida'
            ]);

        } catch (Exception $e) {
            error_log("Erro ao marcar notificação como lida: " . $e->getMessage());
            return $this->handleError($e, 'Erro ao marcar notificação como lida');
        }
    }

    public function markAllAsRead() {
        try {
            $sessionUser = AuthMiddleware::requireAuth();
            $userId = $sessionUser['id'];
            $orgId = 1; // Por enquanto sempre org 1

            $result = $this->notificationHistory->markAllAsRead($userId, $orgId);

            return $this->jsonResponse([
                'success' => $result,
                'message' => $result ? 'Todas as notificações foram marcadas como lidas' : 'Erro ao marcar notificações como lidas'
            ]);

        } catch (Exception $e) {
            error_log("Erro ao marcar todas as notificações como lidas: " . $e->getMessage());
            return $this->handleError($e, 'Erro ao marcar todas as notificações como lidas');
        }
    }

    public function getHistory() {
        try {
            $sessionUser = AuthMiddleware::requireAuth();
            $userId = $sessionUser['id'];
            $orgId = 1; // Por enquanto sempre org 1

            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 50);
            $offset = ($page - 1) * $limit;

            $notifications = $this->notificationHistory->getUserNotifications($userId, $orgId, $limit, $offset);

            return $this->jsonResponse([
                'success' => true,
                'notifications' => $notifications,
                'page' => $page,
                'limit' => $limit
            ]);

        } catch (Exception $e) {
            error_log("Erro ao buscar histórico de notificações: " . $e->getMessage());
            return $this->handleError($e, 'Erro ao carregar histórico');
        }
    }

    public function getPreferences() {
        try {
            $sessionUser = AuthMiddleware::requireAuth();
            $userId = $sessionUser['id'];
            $orgId = 1; // Por enquanto sempre org 1

            $preferences = $this->notificationPreference->getUserPreferences($userId, $orgId);
            $summary = $this->notificationPreference->getNotificationSummary($userId, $orgId);

            return $this->jsonResponse([
                'success' => true,
                'preferences' => $preferences,
                'summary' => $summary
            ]);

        } catch (Exception $e) {
            error_log("Erro ao buscar preferências de notificação: " . $e->getMessage());
            return $this->handleError($e, 'Erro ao carregar preferências');
        }
    }

    public function updatePreferences() {
        try {
            $sessionUser = AuthMiddleware::requireAuth();
            $userId = $sessionUser['id'];
            $orgId = 1; // Por enquanto sempre org 1

            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Dados inválidos'
                ]);
            }

            // Validar dados de entrada
            $allowedFields = [
                'enable_desktop_notifications',
                'enable_email_notifications',
                'enable_sms_notifications',
                'enable_whatsapp_notifications',
                'enable_app_notifications',
                'notify_new_transactions',
                'notify_upcoming_due_dates',
                'notify_low_balance',
                'notify_goal_reached',
                'notify_overdue_transactions',
                'notify_weekly_summary',
                'notify_monthly_summary',
                'due_date_reminder_days',
                'low_balance_threshold',
                'quiet_hours_start',
                'quiet_hours_end',
                'enable_quiet_hours'
            ];

            $preferences = [];
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $preferences[$field] = $input[$field];
                }
            }

            $result = $this->notificationPreference->updateUserPreferences($userId, $orgId, $preferences);

            return $this->jsonResponse([
                'success' => $result,
                'message' => $result ? 'Preferências atualizadas com sucesso' : 'Erro ao atualizar preferências'
            ]);

        } catch (Exception $e) {
            error_log("Erro ao atualizar preferências de notificação: " . $e->getMessage());
            return $this->handleError($e, 'Erro ao salvar preferências');
        }
    }

    // Alias para compatibilidade com as rotas
    public function savePreferences() {
        return $this->updatePreferences();
    }

    public function getStats() {
        try {
            $sessionUser = AuthMiddleware::requireAuth();
            $userId = $sessionUser['id'];
            $orgId = 1; // Por enquanto sempre org 1

            $days = intval($_GET['days'] ?? 30);
            $stats = $this->notificationHistory->getNotificationStats($userId, $orgId, $days);

            return $this->jsonResponse([
                'success' => true,
                'stats' => $stats,
                'period_days' => $days
            ]);

        } catch (Exception $e) {
            error_log("Erro ao buscar estatísticas de notificações: " . $e->getMessage());
            return $this->handleError($e, 'Erro ao carregar estatísticas');
        }
    }

    // Endpoint para sistemas externos enviarem notificações
    public function send() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $required = ['user_id', 'org_id', 'type', 'title'];
            foreach ($required as $field) {
                if (!isset($input[$field])) {
                    return $this->jsonResponse([
                        'success' => false,
                        'message' => "Campo obrigatório: {$field}"
                    ]);
                }
            }

            $userId = $input['user_id'];
            $orgId = $input['org_id'];
            $type = $input['type'];
            $title = $input['title'];
            $message = $input['message'] ?? null;
            $relatedEntityType = $input['related_entity_type'] ?? null;
            $relatedEntityId = $input['related_entity_id'] ?? null;
            $notificationData = $input['notification_data'] ?? null;

            // Verificar se o usuário aceita este tipo de notificação
            if (!$this->notificationPreference->shouldSendNotification($userId, $orgId, $type, 'app')) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Notificação bloqueada pelas preferências do usuário',
                    'blocked' => true
                ]);
            }

            // Registrar no histórico
            $notificationId = $this->notificationHistory->logNotification(
                $userId,
                $orgId,
                $type,
                'app',
                $title,
                $message,
                $relatedEntityType,
                $relatedEntityId,
                $notificationData
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Notificação enviada com sucesso',
                'notification_id' => $notificationId
            ]);

        } catch (Exception $e) {
            error_log("Erro ao enviar notificação: " . $e->getMessage());
            return $this->handleError($e, 'Erro ao enviar notificação');
        }
    }

    // Endpoint para verificar notificações pendentes (usado pelo JS)
    public function pending() {
        try {
            $sessionUser = AuthMiddleware::requireAuth();
            $userId = $sessionUser['id'];
            $orgId = 1; // Por enquanto sempre org 1

            // Buscar notificações dos últimos 5 minutos que ainda não foram entregues
            $notifications = $this->notificationHistory->getUserNotifications($userId, $orgId, 10, 0);
            $recentNotifications = array_filter($notifications, function($n) {
                return $n['minutes_ago'] <= 5 && $n['status'] === 'sent';
            });

            return $this->jsonResponse([
                'success' => true,
                'notifications' => array_values($recentNotifications)
            ]);

        } catch (Exception $e) {
            error_log("Erro ao buscar notificações pendentes: " . $e->getMessage());
            return $this->handleError($e, 'Erro ao carregar notificações pendentes');
        }
    }

    // Endpoint para auditoria de notificações WhatsApp (admin)
    public function auditLog() {
        try {
            $sessionUser = AuthMiddleware::requireAuth();
            $userId = $sessionUser['id'];
            $orgId = 1; // Por enquanto sempre org 1

            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 50);
            $deliveryMethod = $_GET['delivery_method'] ?? null;
            $status = $_GET['status'] ?? null;
            $offset = ($page - 1) * $limit;

            $auditLogs = $this->getAuditLogs($orgId, $limit, $offset, $deliveryMethod, $status);

            return $this->jsonResponse([
                'success' => true,
                'audit_logs' => $auditLogs,
                'page' => $page,
                'limit' => $limit
            ]);

        } catch (Exception $e) {
            error_log("Erro ao buscar logs de auditoria: " . $e->getMessage());
            return $this->handleError($e, 'Erro ao carregar logs de auditoria');
        }
    }

    private function getAuditLogs($orgId, $limit, $offset, $deliveryMethod = null, $status = null) {
        try {
            $whereConditions = ['nh.org_id = ?'];
            $params = [$orgId];

            if ($deliveryMethod) {
                $whereConditions[] = 'nh.delivery_method = ?';
                $params[] = $deliveryMethod;
            }

            if ($status) {
                $whereConditions[] = 'nh.status = ?';
                $params[] = $status;
            }

            $whereClause = implode(' AND ', $whereConditions);

            // Usar query simplificada para evitar problemas com JOINs
            $stmt = $this->db->prepare("
                SELECT nh.*,
                       u.nome as user_name,
                       u.email as user_email
                FROM notification_history nh
                LEFT JOIN users u ON nh.user_id = u.id
                WHERE {$whereClause}
                ORDER BY nh.sent_at DESC
                LIMIT ? OFFSET ?
            ");

            $params[] = $limit;
            $params[] = $offset;

            $stmt->execute($params);
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Decodificar notification_data para facilitar visualização
            foreach ($logs as &$log) {
                if ($log['notification_data']) {
                    $log['notification_data_decoded'] = json_decode($log['notification_data'], true);
                }
                // Adicionar tempo relativo
                if ($log['sent_at']) {
                    $log['sent_at_relative'] = $this->getRelativeTime($log['sent_at']);
                }
            }

            return $logs;

        } catch (Exception $e) {
            error_log("Erro ao buscar logs de auditoria: " . $e->getMessage());
            return [];
        }
    }

    private function getRelativeTime($datetime) {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;

        if ($diff < 60) {
            return $diff . " segundos atrás";
        } elseif ($diff < 3600) {
            return floor($diff / 60) . " minutos atrás";
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . " horas atrás";
        } else {
            return floor($diff / 86400) . " dias atrás";
        }
    }

    /**
     * Processa notificações em background (não requer autenticação)
     */
    public function processBackground() {
        try {
            // Não requer autenticação para permitir processamento em background
            require_once __DIR__ . '/../services/BackgroundJobService.php';
            $backgroundJobService = new BackgroundJobService();

            $result = $backgroundJobService->processQueue();

            // Log do resultado sem retornar resposta (fire and forget)
            error_log("Background notification processing completed: " . json_encode($result));

            // Retornar resposta mínima
            header('Content-Type: application/json');
            echo json_encode(['processed' => $result['processed']]);
            exit;

        } catch (Exception $e) {
            error_log("Background notification processing error: " . $e->getMessage());

            // Retornar erro mínimo
            header('Content-Type: application/json');
            echo json_encode(['error' => 'processing_failed']);
            exit;
        }
    }
}