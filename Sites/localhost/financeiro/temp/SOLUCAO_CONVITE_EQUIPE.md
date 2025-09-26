# 🔧 Solução: Problema de Convite de Equipe

## 🎯 Problema Identificado
Usuário `daylan@dagsolucaodigital.com.br` recebe erro **"Apenas administradores podem convidar pessoas"** ao tentar convidar alguém para a equipe.

## 📊 Análise Técnica

### Localização do Problema
- **Arquivo**: `app/controllers/OrganizationController.php`
- **Método**: `inviteUser()`
- **Linha**: 134-136
- **Condição**: `if ($userRole !== 'admin')`

### Como a Verificação Funciona
```php
// No controller:
$userRole = $orgModel->getUserRole($user['id'], $orgId);

if ($userRole !== 'admin') {
    $this->json(['success' => false, 'message' => 'Apenas administradores podem convidar pessoas']);
    return;
}
```

## 🔍 Possíveis Causas em Produção

### 1. **Dados não sincronizados** (Mais provável)
- Banco de produção não tem o usuário como admin
- Migração não aplicada corretamente

### 2. **Sessão `current_org_id` incorreta**
- Sistema pode estar usando uma organização diferente
- Sessão pode estar corrompida

### 3. **Diferença de dados entre Local e Produção**
- Tabela `user_org_roles` não sincronizada

## ✅ Soluções

### **Solução 1: Verificar e Corrigir no Banco de Produção**

1. **Conectar no banco de produção** e executar:

```sql
-- 1. Verificar se o usuário existe
SELECT id, nome, email FROM users
WHERE email = 'daylan@dagsolucaodigital.com.br';

-- 2. Verificar roles atuais (substitua 1 pelo ID encontrado)
SELECT
    uor.user_id,
    uor.org_id,
    uor.role,
    o.nome as org_name
FROM user_org_roles uor
JOIN organizations o ON uor.org_id = o.id
WHERE uor.user_id = 1;

-- 3. Se não retornar nada ou role não for 'admin', corrigir:
-- OPÇÃO A: Atualizar role existente
UPDATE user_org_roles
SET role = 'admin'
WHERE user_id = 1 AND org_id = 1;

-- OPÇÃO B: Inserir se não existir
INSERT INTO user_org_roles (user_id, org_id, role, created_at)
VALUES (1, 1, 'admin', NOW());
```

### **Solução 2: Debug em Produção**

Criar um endpoint temporário para debug:

1. **Adicionar rota temporária** no `index.php`:
```php
'/debug/user-role' => ['OrganizationController', 'debugUserRole'],
```

2. **Adicionar método no OrganizationController**:
```php
public function debugUserRole() {
    if (($_GET['secret'] ?? '') !== 'dag_debug_2025') {
        die('Acesso negado');
    }

    $user = AuthMiddleware::requireAuth();
    $orgId = $_SESSION['current_org_id'] ?? 1;
    $orgModel = new Organization();
    $userRole = $orgModel->getUserRole($user['id'], $orgId);

    header('Content-Type: application/json');
    echo json_encode([
        'user_id' => $user['id'],
        'user_email' => $user['email'],
        'current_org_id' => $orgId,
        'user_role' => $userRole,
        'can_invite' => $userRole === 'admin'
    ]);
}
```

3. **Testar em produção**:
```
https://app.financeiro.dagsolucaodigital.com.br/debug/user-role?secret=dag_debug_2025
```

### **Solução 3: Aplicar Script de Migração**

Se o problema for sincronização de dados, usar o script já criado:
```bash
# Aplicar migration_optimized.sql que está na pasta /temp
```

## 🧪 Teste da Solução

### 1. **Verificação via Browser**
- Acessar: `https://app.financeiro.dagsolucaodigital.com.br/debug/user-role?secret=dag_debug_2025`
- Verificar se `can_invite: true`

### 2. **Teste do Convite**
- Tentar convidar uma pessoa novamente
- Deve funcionar normalmente

### 3. **Limpeza**
- Remover rota de debug após resolver

## 📋 Checklist de Resolução

- [ ] **Executar queries de verificação** no banco de produção
- [ ] **Corrigir role** se necessário (`UPDATE` ou `INSERT`)
- [ ] **Testar convite** na interface
- [ ] **Verificar se funciona** corretamente
- [ ] **Documentar correção** aplicada

## 🔐 Comandos Específicos para Hostgator

Se usando phpMyAdmin:
1. Acessar painel da Hostgator
2. Abrir phpMyAdmin
3. Selecionar banco `dagsol97_financeiro`
4. Executar queries SQL de verificação
5. Aplicar correção se necessário

## ⚡ Solução Rápida

**Se quiser resolver imediatamente**, execute no banco de produção:

```sql
-- Garantir que daylan seja admin da org 1
INSERT INTO user_org_roles (user_id, org_id, role, created_at)
SELECT 1, 1, 'admin', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM user_org_roles
    WHERE user_id = 1 AND org_id = 1
);

UPDATE user_org_roles
SET role = 'admin'
WHERE user_id = 1 AND org_id = 1;
```

Este comando é seguro - insere se não existir, atualiza se existir.

## 📞 Suporte Adicional

Se o problema persistir:
1. Verificar logs do servidor
2. Testar com outro usuário
3. Verificar integridade da tabela `user_org_roles`
4. Considerar recriação da associação usuário-organização