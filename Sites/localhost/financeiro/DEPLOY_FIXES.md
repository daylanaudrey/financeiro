# Correções para Deploy em Produção

## 1. MobileController.php - Correções Críticas

### Problema: org_id fixo e includes faltando
**Arquivo:** `app/controllers/MobileController.php`

**Correções necessárias:**

1. **Adicionar includes dos models:**
```php
<?php
require_once 'BaseController.php';
require_once 'AuthMiddleware.php';
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../models/Account.php';
require_once __DIR__ . '/../models/Category.php';
```

2. **Corrigir org_id fixo:**
```php
// ANTES (INCORRETO):
$orgId = 1;

// DEPOIS (CORRETO):
$orgId = $this->getCurrentOrgId();
```

## 2. Transaction.php - Novo Método

### Adicionar método getScheduledTransactions
**Arquivo:** `app/models/Transaction.php`

**Adicionar após linha 69:**
```php
public function getScheduledTransactions($orgId) {
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));

    $sql = "
        SELECT t.*,
               a.nome as account_name, a.tipo as account_type,
               c.nome as category_name, c.tipo as category_type, c.cor as category_color,
               ct.nome as contact_name, ct.tipo as contact_type,
               u.nome as created_by_name
        FROM {$this->table} t
        LEFT JOIN accounts a ON t.account_id = a.id
        LEFT JOIN categories c ON t.category_id = c.id
        LEFT JOIN contacts ct ON t.contact_id = ct.id
        LEFT JOIN users u ON t.created_by = u.id
        WHERE t.org_id = ?
        AND t.status = 'agendado'
        AND t.data_competencia BETWEEN ? AND ?
        AND t.deleted_at IS NULL
        ORDER BY t.data_competencia ASC, t.created_at ASC
    ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$orgId, $yesterday, $tomorrow]);
    return $stmt->fetchAll();
}
```

## 3. TransactionController.php - Novo Endpoint

### Adicionar método getScheduled
**Arquivo:** `app/controllers/TransactionController.php`

**Adicionar antes do método partialPayment():**
```php
public function getScheduled() {
    try {
        $user = AuthMiddleware::requireAuth();
        $orgId = $this->getCurrentOrgId();

        $transactions = $this->transactionModel->getScheduledTransactions($orgId);

        $this->json([
            'success' => true,
            'transactions' => $transactions
        ]);

    } catch (Exception $e) {
        error_log("Erro ao buscar agendados: " . $e->getMessage());
        $this->json(['success' => false, 'message' => 'Erro interno do servidor']);
    }
}
```

## 4. index.php - Nova Rota

### Adicionar rota para API de agendados
**Arquivo:** `index.php`

**Adicionar na seção de rotas GET:**
```php
'/api/transactions/scheduled' => ['TransactionController', 'getScheduled'],
```

## 5. mobile.php - Interface Atualizada

### Substituir arquivo completo
**Arquivo:** `app/views/mobile.php`

**ATENÇÃO:** O arquivo precisa ser substituído completamente com:
- Botões de refresh e logout no header
- Seção de agendados (ontem, hoje, amanhã)
- Funções JavaScript para loadScheduledTransactions()
- Correções de cache PWA

## 6. mobile-sw.js - Service Worker Atualizado

### Atualizar versão e estratégia de cache
**Arquivo:** `mobile-sw.js`

**Mudanças críticas:**
```javascript
// Atualizar versão
const CACHE_NAME = 'financeiro-mobile-v1.3.0';

// Remover ./mobile do cache estático
const STATIC_CACHE_URLS = [
  './assets/css/style.css', // (sem ./mobile)
  // ... outros recursos
];

// Usar Network First para página mobile
// (código completo no arquivo local)
```

## 7. Correção de Notificações

### TransactionController.php - Método create()
**Correção aplicada:** Notificações apenas para transações confirmadas

```php
// Enviar notificações WhatsApp APENAS para transações confirmadas
if ($status === 'confirmado') {
    // ... código de notificação
} else {
    error_log("Notification skipped - transaction status: $status (only confirmed transactions receive notifications)");
}
```

---

## Procedimento de Deploy

1. **Backup**: Fazer backup da versão atual de produção
2. **Aplicar correções**: Na ordem listada acima
3. **Testar**: Verificar se /mobile carrega sem ERR_FAILED
4. **Verificar logs**: Monitorar logs de erro após deploy

## Problemas Possíveis na Produção

- **SSL/TLS**: Erro de handshake pode ser problema de certificado
- **Permissões**: Verificar permissões de arquivos PHP
- **PHP Version**: Verificar compatibilidade de versão
- **Database**: Confirmar se org_id correto existe na produção