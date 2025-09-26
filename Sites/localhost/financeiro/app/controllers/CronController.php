<?php
require_once 'BaseController.php';

class CronController extends BaseController {

    public function processDueDateReminders() {
        // Log de início da execução
        error_log("Cron de lembretes iniciado - " . date('Y-m-d H:i:s'));

        // Verificar acesso autorizado
        if (!$this->isValidCronAccess()) {
            error_log("Acesso negado ao cron de lembretes - IP: " . $_SERVER['REMOTE_ADDR']);
            http_response_code(403);
            echo "Acesso negado";
            return;
        }

        try {
            // Headers para execução como script
            header('Content-Type: text/plain; charset=utf-8');

            echo "=== SISTEMA DE LEMBRETES DE VENCIMENTO ===\n";
            echo "Iniciado em: " . date('Y-m-d H:i:s') . "\n\n";

            // Capturar output do script principal
            ob_start();

            // Executar o script de lembretes
            require_once __DIR__ . '/../../process_due_date_reminders.php';

            $output = ob_get_clean();
            echo $output;

            echo "\n=== PROCESSAMENTO CONCLUÍDO ===\n";
            echo "Finalizado em: " . date('Y-m-d H:i:s') . "\n";

            // Log de sucesso
            error_log("Cron de lembretes executado com sucesso - " . date('Y-m-d H:i:s'));

        } catch (Exception $e) {
            $errorMsg = "Erro no cron de lembretes: " . $e->getMessage();
            error_log($errorMsg);

            http_response_code(500);
            echo "ERRO: " . $errorMsg . "\n";
            echo "Arquivo: " . $e->getFile() . "\n";
            echo "Linha: " . $e->getLine() . "\n";
        }
    }

    private function isValidCronAccess() {
        // IPs locais e de servidores permitidos
        $allowedIPs = [
            '127.0.0.1',
            '::1',
            '192.168.1.1',
            '10.0.0.1'
        ];

        // Verificar token de segurança na URL
        $cronToken = $_GET['token'] ?? '';
        $validToken = 'dag_financeiro_cron_2025'; // Token secreto

        // Verificar User-Agent de cron jobs comuns
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $validUserAgents = ['wget', 'curl', 'cron'];

        // Permitir se:
        // 1. IP está na lista de IPs permitidos OU
        // 2. Token correto foi fornecido OU
        // 3. User-Agent indica que é um cron job
        $isValidIP = in_array($_SERVER['REMOTE_ADDR'], $allowedIPs);
        $isValidToken = $cronToken === $validToken;
        $isValidUserAgent = false;

        foreach ($validUserAgents as $agent) {
            if (stripos($userAgent, $agent) !== false) {
                $isValidUserAgent = true;
                break;
            }
        }

        return $isValidIP || $isValidToken || $isValidUserAgent;
    }

    /**
     * Endpoint para testar a conectividade do cron
     */
    public function testCron() {
        if (!$this->isValidCronAccess()) {
            http_response_code(403);
            echo "Acesso negado";
            return;
        }

        header('Content-Type: text/plain; charset=utf-8');

        echo "=== TESTE DE CONECTIVIDADE CRON ===\n";
        echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
        echo "IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
        echo "User-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'N/A') . "\n";
        echo "Token fornecido: " . (isset($_GET['token']) ? 'Sim' : 'Não') . "\n";
        echo "\nConexão OK - Sistema funcionando!\n";

        error_log("Teste de cron executado - " . date('Y-m-d H:i:s'));
    }

    /**
     * Endpoint para status do sistema de lembretes
     */
    public function statusLembretes() {
        if (!$this->isValidCronAccess()) {
            http_response_code(403);
            echo "Acesso negado";
            return;
        }

        header('Content-Type: application/json; charset=utf-8');

        try {
            $database = new Database();
            $pdo = $database->getConnection();

            // Estatísticas dos últimos 30 dias
            $stmt = $pdo->prepare("
                SELECT
                    COUNT(*) as total_lembretes,
                    COUNT(DISTINCT user_id) as usuarios_notificados,
                    COUNT(DISTINCT DATE(sent_at)) as dias_ativos,
                    MAX(sent_at) as ultimo_envio
                FROM notification_history
                WHERE notification_type = 'upcoming_due'
                AND sent_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stmt->execute();
            $stats = $stmt->fetch();

            // Próximos vencimentos
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as proximos_vencimentos
                FROM transactions
                WHERE data_competencia BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                AND status != 'confirmado'
                AND deleted_at IS NULL
            ");
            $stmt->execute();
            $vencimentos = $stmt->fetch();

            $response = [
                'status' => 'ok',
                'timestamp' => date('Y-m-d H:i:s'),
                'lembretes_30_dias' => $stats,
                'proximos_vencimentos' => $vencimentos['proximos_vencimentos'] ?? 0
            ];

            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }
}
?>