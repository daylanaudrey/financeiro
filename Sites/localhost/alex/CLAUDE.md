# Sistema Aduaneiro - Instruções de Desenvolvimento

## 🎯 Stack Tecnológica
- **Backend**: PHP 8.x com arquitetura MVC
- **Banco de Dados**: MySQL 8.0
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Bibliotecas**: Bootstrap 5, jQuery (quando necessário)

## 📊 Configuração do Banco de Dados
```php
// config/database.php
'host' => '127.0.0.1',
'port' => '3307',
'database' => 'alex',
'username' => 'root',
'password' => 'root'
```

## 🏗️ Arquitetura MVC

### Estrutura de Diretórios
```
alex/
├── app/
│   ├── Controllers/    # Controllers do sistema
│   ├── Models/         # Models com lógica SQL
│   ├── Views/          # Templates HTML/PHP
│   └── Helpers/        # Funções auxiliares
├── config/
│   ├── database.php    # Configuração DB
│   └── config.php      # Configurações gerais
├── public/
│   ├── index.php       # Entry point
│   ├── css/            # Estilos
│   ├── js/             # Scripts
│   └── images/         # Imagens
├── database/
│   └── migrations/     # Scripts SQL sequenciais
├── temp/               # Arquivos temporários
├── vendor/             # Dependências Composer
└── .htaccess          # URL rewriting
```

### Regras MVC
- **Controllers**: Recebem requisições, validam dados, chamam Models
- **Models**: Executam queries SQL, processam dados
- **Views**: Apenas apresentação, sem lógica de negócio
- **NUNCA** executar SQL fora dos Models

## 📁 Padrões de Desenvolvimento

### Controllers
```php
class UserController extends BaseController {
    // Métodos: index, create, store, show, edit, update, destroy
    public function index() {
        $users = User::all();
        $this->render('users/index', ['users' => $users]);
    }
}
```

### Models
```php
class User extends Model {
    protected static $table = 'users';

    public static function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email";
        return self::query($sql, ['email' => $email]);
    }
}
```

### Views
```php
// views/users/index.php
<?php include 'layouts/header.php'; ?>
<div class="container">
    <!-- Conteúdo -->
</div>
<?php include 'layouts/footer.php'; ?>
```

## 🔒 Segurança

### Autenticação
- Senhas com `password_hash()` e `PASSWORD_DEFAULT`
- Sessões PHP para manter usuário logado
- Token CSRF para formulários
- Session timeout: 120 minutos

### Validação de Dados
- **SEMPRE** validar entradas no Controller
- **SEMPRE** usar prepared statements (PDO)
- **NUNCA** concatenar strings em SQL
- Escapar output: `htmlspecialchars()`

### Headers de Segurança
```php
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
```

## 🛡️ Sistema de Permissões

### Regras Obrigatórias de Permissões
- **TODA nova tela/funcionalidade DEVE ter permissões implementadas**
- **NUNCA** criar controllers sem verificação de permissões
- **SEMPRE** usar `Permission::requireAuth()` e `Permission::requirePermission()`
- **TODA** view deve ter verificações condicionais baseadas em permissões

### Estrutura de Permissões
```php
// Em Controllers - SEMPRE verificar permissões
Permission::requireAuth();
Permission::requirePermission('modulo.acao');

// Em Views - SEMPRE verificar antes de exibir elementos
<?php if (Permission::check('modulo.view')): ?>
    <!-- Conteúdo visível apenas com permissão -->
<?php endif; ?>
```

### Padrão de Módulos
Para cada novo módulo, SEMPRE criar as 4 permissões básicas:
- `view` - Visualizar (obrigatório para acessar qualquer função do módulo)
- `create` - Criar/Adicionar novos registros
- `edit` - Editar registros existentes
- `delete` - Excluir registros

### Implementação Obrigatória
1. **Classe Permission**: Adicionar novo módulo em `$permissions`
2. **Banco de Dados**: Criar migração com permissões para todos os roles
3. **Controller**: Verificar permissões em TODOS os métodos
4. **Views**: Exibir elementos condicionalmente baseado em permissões
5. **Menu/Dashboard**: Só mostrar se usuário tem permissão `view`

### Dependências de Permissões
- **VIEW é obrigatório**: Sem `view`, usuário não acessa nada do módulo
- **Hierarquia**: VIEW → CREATE/EDIT/DELETE
- **Interface**: JavaScript deve validar dependências no frontend

### Roles do Sistema
- **admin**: Acesso total (todas as permissões)
- **operator**: Operações normais (exceto usuários e sistema)
- **viewer**: Apenas visualização

## 📋 Convenções de Código

### PHP
- **Classes**: PascalCase (`UserController`)
- **Métodos**: camelCase (`getUserById()`)
- **Variáveis**: camelCase (`$userName`)
- **Constantes**: UPPER_SNAKE_CASE (`DB_HOST`)

### Banco de Dados
- **Tabelas**: plural, snake_case (`users`, `process_items`)
- **Colunas**: snake_case (`created_at`, `user_id`)
- **Chave primária**: sempre `id`
- **Chave estrangeira**: `tabela_id`
- **Timestamps**: `created_at`, `updated_at`
- **Soft delete**: `deleted`, `deleted_at`

### Migrações SQL
- Local: `/database/migrations/`
- Formato: `XX_descricao.sql` (ex: `01_create_users.sql`)
- Numeração sequencial obrigatória

## 🎨 Frontend

### Bootstrap 5
- **SEMPRE** usar Bootstrap 5 para UI
- Componentes responsivos obrigatórios
- Ícones: Bootstrap Icons (`bi-`)

### Modais
- **NUNCA** usar `alert()`, `confirm()` ou SweetAlert
- **SEMPRE** usar Bootstrap Modal
- Padrão de exclusão:
```html
<div class="modal fade" id="deleteModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
            </div>
            <div class="modal-body">
                <p>Deseja realmente excluir [item]?</p>
                <div class="alert alert-warning">
                    Esta ação não pode ser desfeita.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Excluir
                </button>
            </div>
        </div>
    </div>
</div>
```

## 🚀 Funcionalidades do Sistema

### Módulos Principais
1. **Autenticação**: Login, logout, recuperação de senha
2. **Dashboard**: Visão geral do sistema
3. **Produtos**: CRUD com NCM e alíquotas
4. **Clientes**: Gestão completa
5. **Processos**: Numerário de importação
6. **Relatórios**: Exportação PDF/Excel

### Tabelas Principais
- `users` - Usuários e permissões
- `products` - Produtos com NCM
- `clients` - Clientes
- `processes` - Processos de importação
- `process_items` - Itens dos processos
- `process_taxes` - Impostos calculados
- `process_expenses` - Despesas

## ✅ Checklist de Qualidade

Antes de marcar uma tarefa como completa:
- [ ] MVC respeitado (SQL só nos Models)
- [ ] Validação de dados implementada
- [ ] Prepared statements usados
- [ ] **Permissões implementadas em Controllers e Views**
- [ ] **Novo módulo adicionado à classe Permission (se aplicável)**
- [ ] **Migração de permissões criada e executada (se aplicável)**
- [ ] Bootstrap Modal para confirmações
- [ ] Arquivos na estrutura correta
- [ ] Segurança validada
- [ ] Testes realizados

## ⚠️ Regras Importantes

1. **NUNCA** modificar ou editar o arquivo `tarefas.txt`
2. **TODA nova funcionalidade DEVE ter sistema de permissões**
3. **SEMPRE** verificar permissões antes de exibir elementos na UI
4. **SEMPRE** usar prepared statements
5. **NUNCA** confiar em dados do usuário
6. **SEMPRE** validar e sanitizar entradas
7. **MANTER** separação MVC
8. **DOCUMENTAR** código complexo
9. **TESTAR** antes de marcar como completo

---

**Sistema Aduaneiro v2.0** - PHP MVC + MySQL
**Última Atualização**: 2025-09-19