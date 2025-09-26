# 🔧 Configuração Cron Job - Hostgator

## 📍 Informações do Sistema
- **Domínio**: app.financeiro.dagsolucaodigital.com.br
- **Hospedagem**: Hostgator
- **Sistema atual**: `wget -q -O- https://sistema.dagsolucaodigital.com.br/index.php/cron`

## 🎯 Configuração Recomendada

### Método 1: Via wget (Recomendado para Hostgator)
```bash
# Executa todo dia às 9:00
0 9 * * * wget -q -O- "https://app.financeiro.dagsolucaodigital.com.br/cron/due-date-reminders" >/dev/null 2>&1
```

### Método 2: Via PHP direto (Alternativo)
```bash
# Executa todo dia às 9:00
0 9 * * * /usr/bin/php /home/[seu_usuario]/public_html/app.financeiro/process_due_date_reminders.php >/dev/null 2>&1
```

## 🚀 Implementação Necessária

### 1. Criar Endpoint Cron no Sistema

**Arquivo**: `index.php` (adicionar rota)
```php
// Adicionar ao array $routes['GET']:
'/cron/due-date-reminders' => ['CronController', 'processDueDateReminders'],
```

### 2. Criar CronController

**Arquivo**: `app/controllers/CronController.php`
```php
<?php
require_once 'BaseController.php';

class CronController extends BaseController {

    public function processDueDateReminders() {
        // Verificar IP ou token de segurança
        if (!$this->isValidCronAccess()) {
            http_response_code(403);
            die('Acesso negado');
        }

        try {
            // Executar o script de lembretes
            require_once __DIR__ . '/../../process_due_date_reminders.php';

            echo "Lembretes processados com sucesso - " . date('Y-m-d H:i:s');
        } catch (Exception $e) {
            error_log("Erro no cron de lembretes: " . $e->getMessage());
            http_response_code(500);
            echo "Erro: " . $e->getMessage();
        }
    }

    private function isValidCronAccess() {
        // Verificar se é acesso local/servidor
        $allowedIPs = ['127.0.0.1', '::1', '192.168.1.1']; // IPs do servidor

        // Ou verificar token na URL
        $cronToken = $_GET['token'] ?? '';
        $validToken = 'dag_financeiro_cron_2025'; // Token secreto

        return in_array($_SERVER['REMOTE_ADDR'], $allowedIPs) || $cronToken === $validToken;
    }
}
?>
```

## 🔐 Configuração no cPanel da Hostgator

### Passo a Passo:

1. **Acessar cPanel** da sua conta Hostgator
2. **Procurar "Cron Jobs"** na seção "Avançado"
3. **Clicar em "Cron Jobs"**
4. **Criar novo cron job:**

   - **Minuto**: `0`
   - **Hora**: `9`
   - **Dia**: `*`
   - **Mês**: `*`
   - **Dia da Semana**: `*`
   - **Comando**:
     ```bash
     wget -q -O- "https://app.financeiro.dagsolucaodigital.com.br/cron/due-date-reminders?token=dag_financeiro_cron_2025" >/dev/null 2>&1
     ```

5. **Salvar** a configuração

## 📧 Configuração de Email (Opcional)

Se você quiser receber emails sobre a execução:

```bash
# Versão com email de notificação
0 9 * * * wget -q -O- "https://app.financeiro.dagsolucaodigital.com.br/cron/due-date-reminders?token=dag_financeiro_cron_2025"
```

## 🧪 Teste da Configuração

### 1. Teste Manual
```bash
# Testar direto no navegador:
https://app.financeiro.dagsolucaodigital.com.br/cron/due-date-reminders?token=dag_financeiro_cron_2025
```

### 2. Verificar Logs
- **cPanel > Logs de Erro**: Verificar se há erros PHP
- **Banco de dados**: Tabela `notification_history` deve mostrar envios
- **WhatsApp**: Verificar se mensagens chegaram

## 🚨 Configurações de Segurança

### Proteção por Token
- URL com token secreto impede acesso não autorizado
- Token deve ser único e seguro
- Não compartilhar o token publicamente

### Proteção por IP (Adicional)
```php
// No CronController, adicionar IPs da Hostgator se conhecidos
$allowedIPs = [
    '127.0.0.1',
    '::1',
    '192.168.1.1',
    // Adicionar IPs específicos da Hostgator se necessário
];
```

## 📋 Horários Alternativos

```bash
# Várias opções de horário:
0 9 * * *     # 9:00 todos os dias
0 8,18 * * *  # 8:00 e 18:00 todos os dias
30 8 * * 1-5  # 8:30 apenas dias úteis
0 9 * * 1-6   # 9:00 segunda a sábado
```

## 🔍 Monitoramento

### Verificar se está funcionando:
1. **Logs do cPanel**: Verificar execuções
2. **Banco de dados**: Conferir tabela `notification_history`
3. **WhatsApp**: Confirmar recebimento de mensagens
4. **Tabela `due_date_reminder_sent`**: Ver controle de envios

### Em caso de problemas:
1. Testar URL manualmente no navegador
2. Verificar logs de erro do PHP
3. Confirmar configurações WhatsApp
4. Validar estrutura do banco de dados

## ✅ Checklist Final

- [ ] Criar `CronController.php`
- [ ] Adicionar rota em `index.php`
- [ ] Configurar cron job no cPanel
- [ ] Testar execução manual
- [ ] Verificar primeiro envio automático
- [ ] Confirmar logs de execução
- [ ] Validar recebimento WhatsApp