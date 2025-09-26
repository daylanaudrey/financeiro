<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/NotificationHistory.php';
require_once __DIR__ . '/../models/NotificationPreference.php';
require_once __DIR__ . '/WhatsAppService.php';

class TeamNotificationService {
    private $userModel;
    private $notificationHistory;
    private $notificationPreference;
    private $whatsAppService;

    public function __construct() {
        $this->userModel = new User();
        $this->notificationHistory = new NotificationHistory();
        $this->notificationPreference = new NotificationPreference();
        $this->whatsAppService = new WhatsAppService();
    }

    /**
     * Enviar notificação para toda a equipe
     */
    public function sendToTeam($orgId, $notificationType, $title, $message, $relatedEntityType = null, $relatedEntityId = null, $options = []) {
        try {
            error_log("Enviando notificação para equipe: orgId={$orgId}, tipo={$notificationType}");

            // Buscar todos os usuários ativos da organização
            $teamMembers = $this->getTeamMembers($orgId);

            $results = [
                'whatsapp_sent' => 0,
                'whatsapp_failed' => 0,
                'app_sent' => 0,
                'email_sent' => 0,
                'email_failed' => 0,
                'total_members' => count($teamMembers)
            ];

            // Verificar se deve pular WhatsApp completamente
            $skipWhatsApp = isset($options['skip_whatsapp']) && $options['skip_whatsapp'] === true;
            $useAsyncWhatsApp = isset($options['async_whatsapp']) && $options['async_whatsapp'] === true;

            foreach ($teamMembers as $member) {
                $preferences = $this->notificationPreference->getUserPreferences($member['id'], $orgId);

                // Verificar se o usuário quer receber este tipo de notificação
                if (!$this->shouldSendNotification($preferences, $notificationType)) {
                    continue;
                }

                // Enviar por WhatsApp se habilitado, usuário tem número E não foi solicitado para pular
                if (!$skipWhatsApp && $preferences['enable_whatsapp_notifications'] && !empty($member['whatsapp_number'])) {
                    // Escolher método de envio baseado na opção
                    if ($useAsyncWhatsApp) {
                        $whatsappResult = $this->sendWhatsAppNotificationAsync($member, $title, $message);
                    } else {
                        $whatsappResult = $this->sendWhatsAppNotification($member, $title, $message);
                    }

                    if ($whatsappResult['success']) {
                        $results['whatsapp_sent']++;
                        $status = 'sent';
                    } else {
                        $results['whatsapp_failed']++;
                        $status = 'failed';
                    }

                    // Log da notificação com dados de auditoria detalhados
                    $notificationData = [
                        'whatsapp_audit' => $whatsappResult['audit_data'] ?? $whatsappResult,
                        'member_name' => $whatsappResult['member_name'],
                        'member_phone' => $whatsappResult['member_phone'],
                        'async_mode' => $useAsyncWhatsApp
                    ];

                    $notificationId = $this->notificationHistory->logNotification(
                        $member['id'],
                        $orgId,
                        $notificationType,
                        'whatsapp',
                        $title,
                        $message,
                        $relatedEntityType,
                        $relatedEntityId,
                        $notificationData
                    );

                    // Se falhou, atualizar o status da notificação
                    if (!$whatsappResult['success'] && $notificationId) {
                        $errorMessage = $whatsappResult['audit_data']['error_message'] ?? $whatsappResult['error'] ?? 'Erro desconhecido';
                        $this->notificationHistory->markAsFailed($notificationId, $errorMessage);
                    }
                } elseif ($skipWhatsApp) {
                    // Log informativo quando WhatsApp foi pulado intencionalmente
                    error_log("WhatsApp pulado para {$member['nome']} conforme solicitado");
                }

                // Enviar notificação no sistema se habilitado
                if ($preferences['enable_app_notifications']) {
                    $this->notificationHistory->logNotification(
                        $member['id'],
                        $orgId,
                        $notificationType,
                        'app',
                        $title,
                        $message,
                        $relatedEntityType,
                        $relatedEntityId
                    );
                    $results['app_sent']++;
                }

                // Enviar por Email se habilitado
                if ($preferences['enable_email_notifications'] && !empty($member['email'])) {
                    $emailResult = $this->sendEmailNotification($member, $title, $message, $notificationType, $relatedEntityType, $relatedEntityId);

                    if ($emailResult['success']) {
                        $results['email_sent']++;
                    } else {
                        $results['email_failed']++;
                    }

                    // Log da notificação por email
                    $notificationId = $this->notificationHistory->logNotification(
                        $member['id'],
                        $orgId,
                        $notificationType,
                        'email',
                        $title,
                        $message,
                        $relatedEntityType,
                        $relatedEntityId,
                        $emailResult
                    );

                    // Se falhou, atualizar o status da notificação
                    if (!$emailResult['success'] && $notificationId) {
                        $errorMessage = $emailResult['error'] ?? 'Erro desconhecido no envio de email';
                        $this->notificationHistory->markAsFailed($notificationId, $errorMessage);
                    }
                }
            }

            error_log("Notificação enviada: " . json_encode($results));
            return $results;

        } catch (Exception $e) {
            error_log("Erro ao enviar notificação para equipe: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar membros ativos da equipe
     */
    private function getTeamMembers($orgId) {
        try {
            // Usar conexão direta ao banco
            require_once __DIR__ . '/../../config/database.php';
            $database = new Database();
            $pdo = $database->getConnection();

            $stmt = $pdo->prepare("
                SELECT u.id, u.nome, u.email, u.whatsapp_number, uor.role
                FROM users u
                INNER JOIN user_org_roles uor ON u.id = uor.user_id
                WHERE uor.org_id = ?
                  AND u.status = 'ativo'
                  AND u.deleted_at IS NULL
                ORDER BY u.nome
            ");
            $stmt->execute([$orgId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Erro ao buscar membros da equipe: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verificar se deve enviar notificação baseado nas preferências
     */
    private function shouldSendNotification($preferences, $notificationType) {
        $typeMapping = [
            'new_transaction' => 'notify_new_transactions',
            'transaction_confirmed' => 'notify_new_transactions',
            'partial_payment' => 'notify_new_transactions',
            'due_date' => 'notify_upcoming_due_dates',
            'low_balance' => 'notify_low_balance',
            'goal_reached' => 'notify_goal_reached',
            'overdue' => 'notify_overdue_transactions',
            'weekly_summary' => 'notify_weekly_summary',
            'monthly_summary' => 'notify_monthly_summary'
        ];

        $prefKey = $typeMapping[$notificationType] ?? null;
        if (!$prefKey) {
            return true; // Se não tem mapeamento, enviar por padrão
        }

        return isset($preferences[$prefKey]) && $preferences[$prefKey] == 1;
    }

    /**
     * Enviar notificação WhatsApp para um membro (assíncrono)
     */
    private function sendWhatsAppNotificationAsync($member, $title, $message) {
        try {
            $fullMessage = "*{$title}*\n\n{$message}\n\n_DAG Financeiro_";

            $asyncResult = $this->whatsAppService->sendMessageAsync($member['whatsapp_number'], $fullMessage);

            // Log detalhado
            $logMessage = "WhatsApp ASYNC para {$member['nome']} ({$member['whatsapp_number']})";
            if ($asyncResult['success']) {
                $logMessage .= " - ENVIADO ASSINCRONAMENTE";
                error_log($logMessage);
            } else {
                $logMessage .= " - FALHA - " . ($asyncResult['error'] ?? 'Erro desconhecido');
                error_log($logMessage);
            }

            // Retornar dados para logging
            return [
                'success' => $asyncResult['success'],
                'audit_data' => $asyncResult,
                'member_name' => $member['nome'],
                'member_phone' => $member['whatsapp_number'],
                'async' => true
            ];

        } catch (Exception $e) {
            error_log("Erro ao enviar WhatsApp async para {$member['nome']}: " . $e->getMessage());
            return [
                'success' => false,
                'audit_data' => ['error_message' => $e->getMessage()],
                'member_name' => $member['nome'],
                'member_phone' => $member['whatsapp_number'],
                'async' => true
            ];
        }
    }

    /**
     * Enviar notificação WhatsApp para um membro (síncrono)
     */
    private function sendWhatsAppNotification($member, $title, $message) {
        try {
            $fullMessage = "*{$title}*\n\n{$message}\n\n_DAG Financeiro_";

            $auditResult = $this->whatsAppService->sendMessage($member['whatsapp_number'], $fullMessage);

            // Log detalhado com informações de auditoria
            $logMessage = "WhatsApp para {$member['nome']} ({$member['whatsapp_number']})";
            if ($auditResult['success']) {
                $logMessage .= " - SUCESSO - MessageID: " . $auditResult['message_id'];
                error_log($logMessage);
            } else {
                $logMessage .= " - FALHA - " . $auditResult['error_message'];
                error_log($logMessage);
            }

            // Retornar dados de auditoria para logging
            return [
                'success' => $auditResult['success'],
                'audit_data' => $auditResult,
                'member_name' => $member['nome'],
                'member_phone' => $member['whatsapp_number']
            ];

        } catch (Exception $e) {
            error_log("Erro ao enviar WhatsApp para {$member['nome']}: " . $e->getMessage());
            return [
                'success' => false,
                'audit_data' => ['error_message' => $e->getMessage()],
                'member_name' => $member['nome'],
                'member_phone' => $member['whatsapp_number']
            ];
        }
    }

    /**
     * Enviar notificação por Email para um membro
     */
    private function sendEmailNotification($member, $title, $message, $notificationType = null, $relatedEntityType = null, $relatedEntityId = null) {
        try {
            require_once __DIR__ . '/EmailService.php';
            $emailService = new EmailService();

            // Se for lembrete de vencimento, usar template específico
            if ($notificationType === 'upcoming_due' && !empty($relatedEntityId)) {
                // $relatedEntityId contém os dados das transações para lembretes
                $transactions = is_string($relatedEntityId) ? json_decode($relatedEntityId, true) : $relatedEntityId;

                if (is_array($transactions) && !empty($transactions)) {
                    // Calcular dias até vencimento baseado na primeira transação
                    $firstTransaction = reset($transactions);
                    $daysAhead = ceil((strtotime($firstTransaction['data_competencia']) - strtotime(date('Y-m-d'))) / (60 * 60 * 24));

                    $result = $emailService->sendDueDateReminderEmail(
                        $member['email'],
                        $member['nome'],
                        $transactions,
                        $daysAhead
                    );
                } else {
                    // Fallback para template genérico
                    $result = $emailService->sendEmail($member['email'], $title, $message);
                }
            } else {
                // Usar template genérico para outros tipos
                $result = $emailService->sendEmail($member['email'], $title, $message);
            }

            $auditData = [
                'method' => 'email',
                'status' => $result ? 'sent' : 'failed',
                'timestamp' => date('Y-m-d H:i:s'),
                'email_to' => $member['email'],
                'member_name' => $member['nome'],
                'notification_type' => $notificationType
            ];

            if ($result) {
                error_log("Email enviado com sucesso para {$member['nome']} ({$member['email']})");
            } else {
                error_log("Falha ao enviar email para {$member['nome']} ({$member['email']})");
                $auditData['error_message'] = 'Falha no EmailService';
            }

            return [
                'success' => $result,
                'member_name' => $member['nome'],
                'member_email' => $member['email'],
                'audit_data' => $auditData
            ];

        } catch (Exception $e) {
            error_log("Erro ao enviar notificação Email para {$member['nome']}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'audit_data' => [
                    'error_message' => $e->getMessage(),
                    'error_type' => 'exception',
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ];
        }
    }

    /**
     * Notificação de nova transação
     */
    public function notifyNewTransaction($orgId, $transaction) {
        // Melhor diferenciação entre tipos de transação
        if ($transaction['kind'] === 'entrada') {
            $tipo = 'Receita';
            $icon = '✅';
            $titleIcon = '💰';
            $valorIcon = '📈';
            $corEmoji = '🟢';
        } else {
            $tipo = 'Despesa';
            $icon = '❌';
            $titleIcon = '💸';
            $valorIcon = '📉';
            $corEmoji = '🔴';
        }

        $valor = 'R$ ' . number_format($transaction['valor'], 2, ',', '.');

        $title = "{$titleIcon} Nova {$tipo} {$corEmoji}";

        // Formato melhorado da mensagem
        $message = "{$icon} *{$transaction['descricao']}*\n";
        $message .= "{$valorIcon} Valor: *{$valor}*\n";
        $message .= "📅 Data: " . date('d/m/Y', strtotime($transaction['data_competencia'])) . "\n";

        if (!empty($transaction['categoria'])) {
            $message .= "🏷️ Categoria: {$transaction['categoria']}\n";
        }

        if (!empty($transaction['conta'])) {
            $message .= "🏪 Conta: {$transaction['conta']}\n";
        }

        // Adicionar linha divisória para melhor separação
        $message .= "────────────────";

        // Usar envio assíncrono por padrão para melhor performance
        $options = ['async_whatsapp' => true];
        return $this->sendToTeam($orgId, 'new_transaction', $title, $message, 'transaction', $transaction['id'] ?? null, $options);
    }

    /**
     * Notificar baixa parcial
     */
    public function notifyPartialPayment($orgId, $transaction, $valorPago, $saldoPendente) {
        // Mesmo padrão visual das transações normais
        if ($transaction['kind'] === 'entrada') {
            $tipo = 'Receita';
            $icon = '✅';
            $titleIcon = '💰';
            $valorIcon = '📈';
            $corEmoji = '🟢';
        } else {
            $tipo = 'Despesa';
            $icon = '❌';
            $titleIcon = '💸';
            $valorIcon = '📉';
            $corEmoji = '🔴';
        }

        $valorPagoFormatado = 'R$ ' . number_format($valorPago, 2, ',', '.');
        $saldoPendenteFormatado = 'R$ ' . number_format($saldoPendente, 2, ',', '.');

        $title = "💴 Baixa Parcial {$corEmoji}";

        // Formato similar às transações normais
        $message = "{$icon} *{$transaction['descricao']}*\n";
        $message .= "💴 Valor Pago: *{$valorPagoFormatado}*\n";
        $message .= "⏳ Saldo Pendente: *{$saldoPendenteFormatado}*\n";
        $message .= "📅 Data: " . date('d/m/Y') . "\n";

        if (!empty($transaction['categoria_nome'])) {
            $message .= "🏷️ Categoria: {$transaction['categoria_nome']}\n";
        }

        if (!empty($transaction['account_name'])) {
            $message .= "🏪 Conta: {$transaction['account_name']}\n";
        }

        // Adicionar linha divisória para melhor separação
        $message .= "────────────────";

        // Usar envio assíncrono por padrão para melhor performance
        $options = ['async_whatsapp' => true];
        return $this->sendToTeam($orgId, 'partial_payment', $title, $message, 'transaction', $transaction['id'] ?? null, $options);
    }

    /**
     * Notificar confirmação de lançamento
     */
    public function notifyTransactionConfirmed($orgId, $transaction) {
        // Mesmo padrão visual das transações normais
        if ($transaction['kind'] === 'entrada') {
            $tipo = 'Receita';
            $icon = '✅';
            $titleIcon = '💰';
            $valorIcon = '📈';
            $corEmoji = '🟢';
        } else {
            $tipo = 'Despesa';
            $icon = '❌';
            $titleIcon = '💸';
            $valorIcon = '📉';
            $corEmoji = '🔴';
        }

        $valor = 'R$ ' . number_format($transaction['valor'], 2, ',', '.');

        $title = "✅ {$tipo} Confirmada {$corEmoji}";

        // Formato similar às transações normais
        $message = "{$icon} *{$transaction['descricao']}*\n";
        $message .= "{$valorIcon} Valor: *{$valor}*\n";
        $message .= "📅 Data: " . date('d/m/Y', strtotime($transaction['data_competencia'])) . "\n";

        if (!empty($transaction['categoria_nome'])) {
            $message .= "🏷️ Categoria: {$transaction['categoria_nome']}\n";
        }

        if (!empty($transaction['account_name'])) {
            $message .= "🏪 Conta: {$transaction['account_name']}\n";
        }

        // Adicionar linha divisória para melhor separação
        $message .= "────────────────";

        // Usar envio assíncrono por padrão para melhor performance
        $options = ['async_whatsapp' => true];
        return $this->sendToTeam($orgId, 'transaction_confirmed', $title, $message, 'transaction', $transaction['id'] ?? null, $options);
    }

    /**
     * Notificação de vencimento
     */
    public function notifyUpcomingDue($orgId, $transactions) {
        if (empty($transactions)) {
            return false;
        }

        // Separar por tipo de vencimento
        $despesas = [];
        $receitas = [];

        foreach ($transactions as $transaction) {
            if ($transaction['kind'] === 'entrada') {
                $receitas[] = $transaction;
            } else {
                $despesas[] = $transaction;
            }
        }

        $title = "⏰ Contas a Vencer";
        $message = "";

        // Seção de despesas a pagar
        if (!empty($despesas)) {
            $message .= "🔴 *DESPESAS A PAGAR* (" . count($despesas) . ")\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

            foreach ($despesas as $despesa) {
                $valor = 'R$ ' . number_format($despesa['valor'], 2, ',', '.');
                $data = date('d/m/Y', strtotime($despesa['data_competencia']));
                $diasVencimento = $this->calcularDiasVencimento($despesa['data_competencia']);

                $urgencia = $diasVencimento <= 1 ? '🚨' : ($diasVencimento <= 3 ? '⚠️' : '📋');

                $message .= "{$urgencia} *{$despesa['descricao']}*\n";
                $message .= "💸 {$valor} | 📅 {$data}";

                if ($diasVencimento == 0) {
                    $message .= " (HOJE!)";
                } elseif ($diasVencimento == 1) {
                    $message .= " (AMANHÃ)";
                } else {
                    $message .= " ({$diasVencimento} dias)";
                }

                $message .= "\n\n";
            }
        }

        // Seção de receitas a receber
        if (!empty($receitas)) {
            if (!empty($despesas)) {
                $message .= "\n";
            }

            $message .= "🟢 *RECEITAS A RECEBER* (" . count($receitas) . ")\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

            foreach ($receitas as $receita) {
                $valor = 'R$ ' . number_format($receita['valor'], 2, ',', '.');
                $data = date('d/m/Y', strtotime($receita['data_competencia']));
                $diasVencimento = $this->calcularDiasVencimento($receita['data_competencia']);

                $message .= "💰 *{$receita['descricao']}*\n";
                $message .= "📈 {$valor} | 📅 {$data}";

                if ($diasVencimento == 0) {
                    $message .= " (HOJE!)";
                } elseif ($diasVencimento == 1) {
                    $message .= " (AMANHÃ)";
                } else {
                    $message .= " ({$diasVencimento} dias)";
                }

                $message .= "\n\n";
            }
        }

        $message .= "🔔 Mantenha suas finanças em dia!";

        // Passar todas as transações como dados adicionais para o template de email
        $allTransactions = array_merge($despesas, $receitas);

        // Usar envio assíncrono por padrão para melhor performance
        $options = ['async_whatsapp' => true];
        return $this->sendToTeam($orgId, 'upcoming_due', $title, $message, 'transactions', $allTransactions, $options);
    }

    /**
     * Calcular dias até o vencimento
     */
    private function calcularDiasVencimento($dataVencimento) {
        $hoje = new DateTime();
        $vencimento = new DateTime($dataVencimento);
        $diferenca = $hoje->diff($vencimento);

        if ($vencimento < $hoje) {
            return -$diferenca->days; // Vencido
        }

        return $diferenca->days;
    }

    /**
     * Notificação de saldo baixo
     */
    public function notifyLowBalance($orgId, $account, $balance) {
        $title = "⚠️ Saldo Baixo";
        $valor = 'R$ ' . number_format($balance, 2, ',', '.');
        $message = "A conta *{$account['nome']}* está com saldo baixo:\n\n";
        $message .= "💰 Saldo atual: {$valor}\n";
        $message .= "📊 Considere fazer um aporte ou revisar os gastos.";

        // Usar envio assíncrono por padrão para melhor performance
        $options = ['async_whatsapp' => true];
        return $this->sendToTeam($orgId, 'low_balance', $title, $message, 'account', $account['id'] ?? null, $options);
    }
}
?>