<?php
/**
 * Script de teste para verificar lembretes por email
 *
 * Execute: php temp/teste_email_lembretes.php
 */

// Definir diretório base
chdir(__DIR__ . '/..');

// Simular variáveis do servidor para o ambiente CLI
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/financeiro/index.php';

// Incluir funções auxiliares
require_once 'app/helpers/functions.php';

// Incluir arquivos necessários
require_once 'app/services/TeamNotificationService.php';

echo "=== TESTE: Lembretes por Email ===\n\n";

try {
    // Criar instância do serviço
    $teamService = new TeamNotificationService();

    // Dados de teste (simular transações que vencem)
    $testTransactions = [
        [
            'id' => 999,
            'descricao' => 'Conta de Luz - TESTE',
            'valor' => 150.00,
            'kind' => 'saida',
            'data_competencia' => date('Y-m-d', strtotime('+3 days'))
        ],
        [
            'id' => 998,
            'descricao' => 'Recebimento Cliente - TESTE',
            'valor' => 2500.00,
            'kind' => 'entrada',
            'data_competencia' => date('Y-m-d', strtotime('+3 days'))
        ]
    ];

    echo "📧 Testando template de email específico...\n";

    // Testar EmailService diretamente
    require_once 'app/services/EmailService.php';
    $emailService = new EmailService();

    // Email de teste (substitua pelo seu email)
    $testEmail = 'test@example.com'; // ALTERE AQUI
    $testUserName = 'Usuário Teste';

    echo "Enviando email de teste para: {$testEmail}\n";

    $result = $emailService->sendDueDateReminderEmail(
        $testEmail,
        $testUserName,
        $testTransactions,
        3
    );

    if ($result) {
        echo "✅ Email enviado com sucesso!\n";
        echo "📋 Template usado: Lembrete de vencimento profissional\n";
        echo "📊 Transações incluídas: " . count($testTransactions) . "\n";
    } else {
        echo "❌ Falha ao enviar email\n";
        echo "🔧 Verifique as configurações do MailerSend em /admin/system-config\n";
    }

} catch (Exception $e) {
    echo "❌ Erro no teste: " . $e->getMessage() . "\n";
    echo "📍 Arquivo: " . $e->getFile() . "\n";
    echo "📍 Linha: " . $e->getLine() . "\n";
}

echo "\n=== FIM DO TESTE ===\n";
echo "\n📋 PRÓXIMOS PASSOS:\n";
echo "1. Configure o MailerSend em /admin/system-config\n";
echo "2. Altere o email de teste neste arquivo\n";
echo "3. Execute o cron job: /cron/due-date-reminders\n";
echo "4. Verifique os logs de email em /admin/audit-logs\n";
?>