<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/TeamNotificationService.php';

class DueDateReminderService {
    private $database;
    private $pdo;
    private $teamNotificationService;

    public function __construct() {
        $this->database = new Database();
        $this->pdo = $this->database->getConnection();
        $this->teamNotificationService = new TeamNotificationService();
    }

    /**
     * Processar todos os lembretes de vencimento
     */
    public function processAllReminders() {
        try {
            error_log("Iniciando processamento de lembretes de vencimento");

            // Buscar todas as organizações ativas
            $stmt = $this->pdo->query("
                SELECT DISTINCT id FROM organizations
                WHERE status = 'ativo' AND deleted_at IS NULL
            ");

            $organizations = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $totalReminders = 0;
            foreach ($organizations as $orgId) {
                $reminders = $this->processOrgReminders($orgId);
                $totalReminders += $reminders;
            }

            error_log("Processamento concluído. Total de lembretes enviados: {$totalReminders}");
            return $totalReminders;

        } catch (Exception $e) {
            error_log("Erro ao processar lembretes: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Processar lembretes para uma organização específica
     */
    public function processOrgReminders($orgId) {
        try {
            $remindersSent = 0;

            // Buscar usuários ativos da organização com suas preferências
            $stmt = $this->pdo->prepare("
                SELECT DISTINCT
                    u.id as user_id,
                    u.nome,
                    np.due_date_reminder_days_multiple,
                    np.remind_expenses,
                    np.remind_income,
                    np.enable_whatsapp_notifications,
                    np.notify_upcoming_due_dates
                FROM users u
                INNER JOIN user_org_roles uor ON u.id = uor.user_id
                LEFT JOIN notification_preferences np ON u.id = np.user_id AND np.org_id = ?
                WHERE uor.org_id = ?
                  AND u.status = 'ativo'
                  AND u.deleted_at IS NULL
                  AND COALESCE(np.notify_upcoming_due_dates, 1) = 1
            ");
            $stmt->execute([$orgId, $orgId]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($users as $user) {
                $userReminders = $this->processUserReminders($orgId, $user);
                $remindersSent += $userReminders;
            }

            return $remindersSent;

        } catch (Exception $e) {
            error_log("Erro ao processar lembretes da organização {$orgId}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Processar lembretes para um usuário específico
     */
    private function processUserReminders($orgId, $user) {
        try {
            $remindersSent = 0;

            // Obter dias de lembrete configurados
            $reminderDays = $this->parseReminderDays($user['due_date_reminder_days_multiple'] ?? '3,7');

            foreach ($reminderDays as $days) {
                $transactions = $this->getTransactionsDueInDays($orgId, $days, $user);

                if (!empty($transactions)) {
                    // Filtrar transações que ainda não receberam este lembrete
                    $newTransactions = $this->filterUnsentReminders($transactions, $user['user_id'], $days);

                    if (!empty($newTransactions)) {
                        // Enviar notificação
                        $result = $this->teamNotificationService->notifyUpcomingDue($orgId, $newTransactions);

                        if ($result && $result['whatsapp_sent'] > 0) {
                            // Marcar lembretes como enviados
                            $this->markRemindersAsSent($newTransactions, $user['user_id'], $orgId, $days);
                            $remindersSent++;

                            error_log("Lembrete de {$days} dias enviado para {$user['nome']}: " . count($newTransactions) . " transações");
                        }
                    }
                }
            }

            return $remindersSent;

        } catch (Exception $e) {
            error_log("Erro ao processar lembretes do usuário {$user['nome']}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Converter string de dias em array
     */
    private function parseReminderDays($daysString) {
        if (empty($daysString)) {
            return [3, 7]; // Padrão
        }

        $days = array_map('intval', explode(',', $daysString));
        return array_filter($days, function($day) {
            return $day > 0 && $day <= 365; // Validação básica
        });
    }

    /**
     * Buscar transações que vencem em X dias
     */
    private function getTransactionsDueInDays($orgId, $days, $user) {
        try {
            $targetDate = date('Y-m-d', strtotime("+{$days} days"));

            $conditions = [];
            $params = [$orgId, $targetDate];

            // Filtrar por tipo baseado nas preferências do usuário
            if (!$user['remind_expenses'] && !$user['remind_income']) {
                return []; // Usuário não quer nenhum tipo de lembrete
            } elseif (!$user['remind_expenses']) {
                $conditions[] = "t.kind = 'entrada'";
            } elseif (!$user['remind_income']) {
                $conditions[] = "t.kind = 'saida'";
            }

            $whereClause = !empty($conditions) ? 'AND ' . implode(' AND ', $conditions) : '';

            $stmt = $this->pdo->prepare("
                SELECT
                    t.id,
                    t.descricao,
                    t.valor,
                    t.kind,
                    t.data_competencia,
                    c.nome as categoria,
                    a.nome as conta
                FROM transactions t
                LEFT JOIN categories c ON t.category_id = c.id
                LEFT JOIN accounts a ON t.account_id = a.id
                WHERE t.org_id = ?
                  AND DATE(t.data_competencia) = ?
                  AND t.status != 'cancelado'
                  AND t.deleted_at IS NULL
                  {$whereClause}
                ORDER BY t.valor DESC
            ");

            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Erro ao buscar transações para {$days} dias: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Filtrar transações que ainda não receberam este lembrete
     */
    private function filterUnsentReminders($transactions, $userId, $reminderDays) {
        try {
            if (empty($transactions)) {
                return [];
            }

            $transactionIds = array_column($transactions, 'id');
            $placeholders = str_repeat('?,', count($transactionIds) - 1) . '?';

            $stmt = $this->pdo->prepare("
                SELECT transaction_id
                FROM due_date_reminder_sent
                WHERE user_id = ?
                  AND reminder_days = ?
                  AND transaction_id IN ({$placeholders})
            ");

            $params = array_merge([$userId, $reminderDays], $transactionIds);
            $stmt->execute($params);
            $sentIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Filtrar transações que ainda não foram enviadas
            return array_filter($transactions, function($transaction) use ($sentIds) {
                return !in_array($transaction['id'], $sentIds);
            });

        } catch (Exception $e) {
            error_log("Erro ao filtrar lembretes não enviados: " . $e->getMessage());
            return $transactions; // Em caso de erro, enviar todos
        }
    }

    /**
     * Marcar lembretes como enviados
     */
    private function markRemindersAsSent($transactions, $userId, $orgId, $reminderDays) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO due_date_reminder_sent
                (transaction_id, user_id, org_id, reminder_days)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE sent_at = CURRENT_TIMESTAMP
            ");

            foreach ($transactions as $transaction) {
                $stmt->execute([$transaction['id'], $userId, $orgId, $reminderDays]);
            }

        } catch (Exception $e) {
            error_log("Erro ao marcar lembretes como enviados: " . $e->getMessage());
        }
    }

    /**
     * Limpar histórico antigo de lembretes (manutenção)
     */
    public function cleanOldReminders($daysToKeep = 90) {
        try {
            $cutoffDate = date('Y-m-d', strtotime("-{$daysToKeep} days"));

            $stmt = $this->pdo->prepare("
                DELETE FROM due_date_reminder_sent
                WHERE sent_at < ?
            ");
            $stmt->execute([$cutoffDate]);

            $deletedCount = $stmt->rowCount();
            error_log("Limpeza de lembretes antigos: {$deletedCount} registros removidos");

            return $deletedCount;

        } catch (Exception $e) {
            error_log("Erro na limpeza de lembretes antigos: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Estatísticas de lembretes
     */
    public function getReminderStats($orgId, $days = 30) {
        try {
            $startDate = date('Y-m-d', strtotime("-{$days} days"));

            $stmt = $this->pdo->prepare("
                SELECT
                    COUNT(*) as total_reminders,
                    COUNT(DISTINCT user_id) as users_notified,
                    COUNT(DISTINCT transaction_id) as transactions_reminded,
                    AVG(reminder_days) as avg_reminder_days
                FROM due_date_reminder_sent
                WHERE org_id = ?
                  AND sent_at >= ?
            ");
            $stmt->execute([$orgId, $startDate]);

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Erro ao buscar estatísticas de lembretes: " . $e->getMessage());
            return null;
        }
    }
}
?>