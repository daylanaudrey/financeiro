<?php
/**
 * Serviço para executar tarefas em background (não-bloqueantes)
 * Usado especialmente para envio de notificações WhatsApp
 */

class BackgroundJobService {
    private $queueFile;

    public function __construct() {
        $this->queueFile = __DIR__ . '/../../temp/notification_queue.json';

        // Criar diretório temp se não existir
        $tempDir = dirname($this->queueFile);
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
    }

    /**
     * Adiciona um job de notificação à fila para execução em background
     */
    public function queueNotification($type, $orgId, $data, $options = []) {
        $job = [
            'id' => uniqid('job_', true),
            'type' => $type,
            'org_id' => $orgId,
            'data' => $data,
            'options' => $options,
            'created_at' => date('Y-m-d H:i:s'),
            'status' => 'pending',
            'attempts' => 0,
            'max_attempts' => 3
        ];

        // Carregar fila atual
        $queue = $this->loadQueue();

        // Adicionar novo job
        $queue[] = $job;

        // Salvar fila
        $this->saveQueue($queue);

        // Executar jobs imediatamente em background (não-bloqueante)
        $this->triggerBackgroundProcessing();

        return $job['id'];
    }

    /**
     * Dispara processamento em background sem aguardar
     */
    private function triggerBackgroundProcessing() {
        // Usar curl para chamar o processador em background
        $processorUrl = $this->getBaseUrl() . '/process-notifications';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $processorUrl,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_TIMEOUT => 1, // Timeout mínimo - fire and forget
            CURLOPT_CONNECTTIMEOUT => 1,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_NOSIGNAL => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => false
        ]);

        // Executar e fechar imediatamente
        curl_exec($ch);
        curl_close($ch);

        error_log("Background notification processor triggered");
    }

    /**
     * Processa todos os jobs pendentes na fila
     */
    public function processQueue() {
        $queue = $this->loadQueue();
        $processedJobs = [];

        foreach ($queue as $index => $job) {
            if ($job['status'] !== 'pending') {
                continue;
            }

            try {
                $result = $this->executeJob($job);

                if ($result['success']) {
                    $job['status'] = 'completed';
                    $job['completed_at'] = date('Y-m-d H:i:s');
                    $job['result'] = $result;
                } else {
                    $job['attempts']++;

                    if ($job['attempts'] >= $job['max_attempts']) {
                        $job['status'] = 'failed';
                        $job['failed_at'] = date('Y-m-d H:i:s');
                        $job['error'] = $result['error'] ?? 'Max attempts reached';
                    } else {
                        $job['status'] = 'retry';
                        $job['retry_at'] = date('Y-m-d H:i:s', strtotime('+5 minutes'));
                    }
                }

                $processedJobs[] = $job['id'];
                $queue[$index] = $job;

            } catch (Exception $e) {
                error_log("Job processing error: " . $e->getMessage());
                $job['status'] = 'failed';
                $job['failed_at'] = date('Y-m-d H:i:s');
                $job['error'] = $e->getMessage();
                $queue[$index] = $job;
            }
        }

        // Salvar fila atualizada
        $this->saveQueue($queue);

        // Limpar jobs antigos (mais de 24h)
        $this->cleanOldJobs();

        return [
            'processed' => count($processedJobs),
            'jobs' => $processedJobs
        ];
    }

    /**
     * Executa um job específico
     */
    private function executeJob($job) {
        switch ($job['type']) {
            case 'notification':
                return $this->executeNotificationJob($job);

            default:
                return ['success' => false, 'error' => 'Unknown job type: ' . $job['type']];
        }
    }

    /**
     * Executa job de notificação
     */
    private function executeNotificationJob($job) {
        try {
            require_once __DIR__ . '/TeamNotificationService.php';
            $teamService = new TeamNotificationService();

            $data = $job['data'];
            $options = $job['options'];

            // Forçar modo assíncrono para WhatsApp
            $options['async_whatsapp'] = true;

            $result = $teamService->sendToTeam(
                $job['org_id'],
                $data['notification_type'],
                $data['title'],
                $data['message'],
                $data['related_entity_type'] ?? null,
                $data['related_entity_id'] ?? null,
                $options
            );

            return [
                'success' => true,
                'result' => $result
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Carrega a fila do arquivo
     */
    private function loadQueue() {
        if (!file_exists($this->queueFile)) {
            return [];
        }

        $content = file_get_contents($this->queueFile);
        if (empty($content)) {
            return [];
        }

        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Salva a fila no arquivo
     */
    private function saveQueue($queue) {
        file_put_contents($this->queueFile, json_encode($queue, JSON_PRETTY_PRINT));
    }

    /**
     * Remove jobs antigos da fila
     */
    private function cleanOldJobs() {
        $queue = $this->loadQueue();
        $cutoff = date('Y-m-d H:i:s', strtotime('-24 hours'));

        $cleanQueue = array_filter($queue, function($job) use ($cutoff) {
            return $job['created_at'] > $cutoff;
        });

        if (count($cleanQueue) !== count($queue)) {
            $this->saveQueue(array_values($cleanQueue));
            error_log("Cleaned " . (count($queue) - count($cleanQueue)) . " old jobs from queue");
        }
    }

    /**
     * Obtém a URL base da aplicação
     */
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $port = $_SERVER['SERVER_PORT'] ?? '80';

        // Para localhost/MAMP
        if ($host === 'localhost' && $port !== '80' && $port !== '443') {
            return "{$protocol}://{$host}/financeiro";
        }

        return "{$protocol}://{$host}/financeiro";
    }

    /**
     * Obtém estatísticas da fila
     */
    public function getQueueStats() {
        $queue = $this->loadQueue();
        $stats = [
            'total' => count($queue),
            'pending' => 0,
            'completed' => 0,
            'failed' => 0,
            'retry' => 0
        ];

        foreach ($queue as $job) {
            $stats[$job['status']]++;
        }

        return $stats;
    }
}
?>