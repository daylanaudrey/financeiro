<?php
/**
 * Serviço ultrarrápido de notificações
 * Apenas adiciona à fila, processamento é 100% separado
 */

class FastNotificationService {
    private $queueFile;

    public function __construct() {
        $this->queueFile = __DIR__ . '/../../temp/fast_queue.json';
    }

    /**
     * Adiciona notificação à fila de forma ultrarrápida
     * Retorna em < 10ms sem fazer nenhum processamento
     */
    public function queueNotification($orgId, $type, $data) {
        $job = [
            'id' => uniqid('', true), // Mais rápido
            'org_id' => $orgId,
            'type' => $type,
            'data' => $data,
            'created' => time() // Timestamp simples
        ];

        // Escrita atômica ultrarrápida
        $this->appendToQueue($job);

        // Trigger de processamento assíncrono (sem aguardar)
        $this->triggerAsyncProcessing();

        return true; // Apenas confirma que foi adicionado
    }

    /**
     * Dispara processamento assíncrono sem aguardar resposta
     */
    private function triggerAsyncProcessing() {
        try {
            // Usar exec com & para executar em background
            $scriptPath = __DIR__ . '/../../process_fast_notifications.php';
            $command = "php " . escapeshellarg($scriptPath) . " > /dev/null 2>&1 &";

            exec($command);
        } catch (Exception $e) {
            // Falha no trigger não deve afetar o salvamento
            error_log("Async trigger failed: " . $e->getMessage());
        }
    }

    /**
     * Escrita atômica otimizada
     */
    private function appendToQueue($job) {
        $line = json_encode($job) . "\n";

        // Usar file_put_contents com LOCK_EX e FILE_APPEND
        file_put_contents($this->queueFile, $line, LOCK_EX | FILE_APPEND);
    }

    /**
     * Processa fila (para ser chamado por cron ou script separado)
     */
    public function processQueue() {
        if (!file_exists($this->queueFile)) {
            return ['processed' => 0];
        }

        $lines = file($this->queueFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (empty($lines)) {
            return ['processed' => 0];
        }

        $processed = 0;
        $failed = 0;

        foreach ($lines as $line) {
            try {
                $job = json_decode(trim($line), true);
                if ($job && is_array($job) && isset($job['type'])) {
                    if ($this->executeJob($job)) {
                        $processed++;
                        error_log("Fast queue: Job {$job['id']} processed successfully");
                    } else {
                        $failed++;
                        error_log("Fast queue: Job {$job['id']} failed");
                    }
                } else {
                    $failed++;
                    error_log("Fast queue: Invalid job format: " . substr($line, 0, 100));
                }
            } catch (Exception $e) {
                error_log("Fast queue job error: " . $e->getMessage());
                $failed++;
            }
        }

        // Limpar arquivo após processamento
        if ($processed > 0 || $failed > 0) {
            $backupFile = $this->queueFile . '.processed.' . date('Y-m-d_H-i-s');
            rename($this->queueFile, $backupFile);
            error_log("Fast queue: Processed {$processed} jobs, failed {$failed}. Backup: " . basename($backupFile));
        }

        return [
            'processed' => $processed,
            'failed' => $failed
        ];
    }

    /**
     * Executa um job individual
     */
    private function executeJob($job) {
        try {
            if ($job['type'] === 'whatsapp_notification') {
                return $this->sendWhatsAppNotification($job['data']);
            }
            return false;
        } catch (Exception $e) {
            error_log("Job execution error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envia notificação WhatsApp
     */
    private function sendWhatsAppNotification($data) {
        try {
            require_once __DIR__ . '/WhatsAppService.php';
            require_once __DIR__ . '/TeamNotificationService.php';

            // Se tem phone específico, usar diretamente
            if (isset($data['phone']) && $data['phone'] !== '5511999999999') {
                $whatsappService = new WhatsAppService();
                $message = "*{$data['title']}*\n\n{$data['message']}\n\n_DAG Financeiro_";

                $result = $whatsappService->sendMessageAsync($data['phone'], $message);
                return $result['success'] ?? false;
            }

            // Usar TeamNotificationService para buscar números reais e enviar
            $teamService = new TeamNotificationService();
            $orgId = $data['org_id'] ?? 1;

            // Usar dados da transação se disponíveis
            if (isset($data['transaction_data'])) {
                $result = $teamService->notifyNewTransaction($orgId, $data['transaction_data']);
            } else {
                // Fallback para dados básicos
                $notificationData = [
                    'id' => $data['transaction_id'] ?? null,
                    'descricao' => $data['message'],
                    'valor' => 0,
                    'kind' => strpos($data['title'], 'Receita') !== false ? 'entrada' : 'saida',
                    'data_competencia' => date('Y-m-d')
                ];

                $result = $teamService->notifyNewTransaction($orgId, $notificationData);
            }

            return !empty($result);

        } catch (Exception $e) {
            error_log("WhatsApp send error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtém estatísticas rápidas
     */
    public function getQueueCount() {
        if (!file_exists($this->queueFile)) {
            return 0;
        }

        $lines = file($this->queueFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return count($lines);
    }
}
?>