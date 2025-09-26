# 🚀 Instruções para Deploy - Card "Vencimentos de Hoje"

## 🎯 Objetivo
Fazer o card "Vencimentos de Hoje" aparecer **SEMPRE** na versão online, mesmo quando não há vencimentos.

## 📋 Diagnóstico Criado

### 1. **Endpoint de Debug** ✅
Criado endpoint para testar se o código está funcionando online:

```
https://app.financeiro.dagsolucaodigital.com.br/debug/due-today?secret=dag_debug_2025
```

**Resultados possíveis:**
- `{"error": "Método getDueTodayTransactions não existe"}` → Código não foi enviado para produção
- `{"count": 0}` → Não há vencimentos hoje, mas método existe
- `{"count": 1+}` → Há vencimentos, problema em outro lugar

## 📁 Arquivos que DEVEM ser enviados para produção

### 1. **index.php** ✅ (Atualizado com endpoint de debug)
**Localização:** `/index.php`
**Alteração:** Adicionada linha:
```php
'/debug/due-today' => ['HomeController', 'debugDueToday'],
```

### 2. **HomeController.php** ✅ (Método de debug + proteção)
**Localização:** `/app/controllers/HomeController.php`
**Alterações importantes:**
- Método `debugDueToday()` para diagnóstico
- Proteção nos métodos `index()` e `dashboard()`:
```php
// Buscar vencimentos do dia (sempre inicializar array vazio se método não existir)
$dueTodayTransactions = [];
if (method_exists($transactionModel, 'getDueTodayTransactions')) {
    $dueTodayTransactions = $transactionModel->getDueTodayTransactions($orgId, 8);
}
```

### 3. **Transaction.php** ⚠️ (Verificar se método existe)
**Localização:** `/app/models/Transaction.php`
**Método necessário:** `getDueTodayTransactions()`

### 4. **dashboard.php** ⚠️ (Card sempre visível)
**Localização:** `/app/views/dashboard.php`
**Alterações críticas:**
- Condição alterada de `<?php if (!empty($dueTodayTransactions)): ?>` para sempre exibir
- Estado vazio adicionado com ícone e botões de ação

## 🧪 Passos para Resolver

### **Passo 1: Testar Diagnóstico Online**
```bash
curl "https://app.financeiro.dagsolucaodigital.com.br/debug/due-today?secret=dag_debug_2025"
```

### **Passo 2A: Se retornar erro "método não existe"**
➤ **Enviar arquivos para produção:**
1. `app/models/Transaction.php` (com método getDueTodayTransactions)
2. `app/controllers/HomeController.php` (com proteções)
3. `app/views/dashboard.php` (com card sempre visível)
4. `index.php` (com rota de debug)

### **Passo 2B: Se retornar count: 0**
➤ **Código já existe, criar transação de teste:**
```sql
INSERT INTO transactions (
    org_id, account_id, kind, valor, data_competencia,
    status, descricao, created_by, created_at
) VALUES (
    1, 1, 'saida', 50.00, CURDATE(),
    'agendado', 'Teste Card Vencimentos', 1, NOW()
);
```

### **Passo 3: Verificar se Card Aparece**
1. Acessar dashboard online
2. Verificar se card "Vencimentos de Hoje" aparece
3. Se tiver transação de teste, confirmar se está listada
4. Se não tiver transação, verificar se mostra estado vazio

## 🎨 Resultado Esperado

### **Cenário 1: Sem Vencimentos**
```
┌─────────────────────────────────────┐
│ 📅 Vencimentos de Hoje (0 itens)   │
├─────────────────────────────────────┤
│     ✓ [ícone calendário grande]     │
│   Sem agendamentos para hoje       │
│ Nenhuma transação agendada vence    │
│ hoje. Aproveite para organizar!     │
│                                     │
│ [+ Novo Lançamento] [📅 Ver Agenda] │
└─────────────────────────────────────┘
```

### **Cenário 2: Com Vencimentos**
```
┌─────────────────────────────────────┐
│ 📅 Vencimentos de Hoje (2 itens)   │
├─────────────────────────────────────┤
│ • R$ 150,00 - Conta de Luz         │
│ • R$ 300,00 - Aluguel              │
│                                     │
│ Total: R$ 450,00                    │
└─────────────────────────────────────┘
```

## ⚡ Teste Rápido Local vs Online

### **Local (funcionando):**
```bash
curl "http://localhost/financeiro/debug/due-today?secret=dag_debug_2025"
# Retorna: {"has_method": true, "count": 1, ...}
```

### **Online (testar):**
```bash
curl "https://app.financeiro.dagsolucaodigital.com.br/debug/due-today?secret=dag_debug_2025"
# Deve retornar o mesmo resultado
```

## 🔒 Segurança do Debug

O endpoint de debug:
- ✅ Protegido por `secret=dag_debug_2025`
- ✅ Apenas método GET
- ✅ Não altera dados
- ✅ Temporário (pode ser removido após resolver)

## 🗑️ Limpeza Após Resolver

Quando card estiver funcionando online:
1. Remover linha `/debug/due-today` do index.php
2. Remover método `debugDueToday()` do HomeController.php
3. Remover transação de teste se criada

## ✅ Checklist de Resolução

- [ ] Testar endpoint de debug online
- [ ] Enviar arquivos atualizados (se necessário)
- [ ] Verificar se card aparece no dashboard
- [ ] Criar transação de teste (se necessário)
- [ ] Confirmar funcionamento completo
- [ ] Remover código de debug

## 🎯 Prioridade MÁXIMA

**O card "Vencimentos de Hoje" é uma funcionalidade essencial para o controle financeiro diário dos usuários!**

**Status atual:** Card funciona localmente, não aparece online
**Causa provável:** Código não foi enviado para produção
**Solução:** Deploy dos arquivos + teste de confirmação

---

**Com essas instruções, o card estará sempre visível e funcional na versão online! 📅✨**