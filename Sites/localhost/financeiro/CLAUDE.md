## Database Configurations

- Banco de Dados Configuration LOCALHOST:
  ```
  'host' => '127.0.0.1',
  'port' => '3307',
  'database' => 'dag_financeiro',
  'username' => 'root',
  'password' => 'root'
  ```
**LOCALHOST ESTÁ RODANDO NA PORTA 80**

## UI/UX Standards

### Notifications & Modals
- **SweetAlert2** para notificações e alertas de sucesso/erro
- **Bootstrap Modal** para confirmações de exclusão e interações complexas
- **NUNCA usar** `alert()` ou `confirm()` nativo do browser

### Modal System - Confirmações de Exclusão
- **Padrão obrigatório**:
    - Título: "Confirmar Exclusão"
    - Mensagem com contexto específico (nome do item)
    - Alert warning: "Esta ação não pode ser desfeita"
    - Botões: "Cancelar" (secondary) e "Excluir [Item]" (danger) com ícone trash
- **Estrutura HTML**:
  ```html
  <div class="modal fade" id="uniqueModalId" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirmar Exclusão</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Mensagem de confirmação...</p>
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Esta ação não pode ser desfeita.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-danger" onclick="confirmAction()">
            <i class="fas fa-trash me-2"></i>Excluir Item
          </button>
        </div>
      </div>
    </div>
  </div>
  ```

### TinyMCE Integration
- **API Key**: `19d99e4d3fbf347c1f5faf64c0eea533a6bbcaf750d780e8d6eac0ded74f91f7`
- **Configuração padrão**:
  ```javascript
  tinymce.init({
    selector: '#campo-id',
    height: 200,
    menubar: false,
    plugins: 'lists link code',
    toolbar: 'bold italic underline | bullist numlist | link | code | undo redo'
  });
  ```

## File Organization

- **Arquivos temporários**: Sempre na pasta `/temp`
- **Uploads**: Organizar por tipo e data quando necessário

## Database & Architecture Rules

- **MVC**: Usar estrutura MVC
- **Consultas ao banco**: APENAS no MODEL, NUNCA no controller ou views
- **Validação de dados**: Sempre no controller antes de chamar o model
- **Estrutura JSON para APIs**:
  ```json
  {
    "success": true/false,
    "message": "Mensagem descritiva",
    "data": {} // quando aplicável
  }
  ```

### Before Implementation
1. **SEMPRE verificar CLAUDE.md** primeiro
2. **Identificar padrões existentes** no código
3. **Seguir convenções estabelecidas** do projeto
4. **Testar funcionalidade** antes de marcar como concluído

### Testing Protocol
- **MCP Integration**: SEMPRE usar MCP (Model Context Protocol) para testes
- **Testes de código**: executar através do MCP no Chrome
- **Testes de banco**: validar consultas e operações via MCP
- **Validação funcional**: usar MCP para verificar comportamento das funcionalidades

### GERAL - REGRAS CRÍTICAS DE PRODUÇÃO

#### Arquitetura MVC Rigorosa
- **NUNCA** criar consultas SQL (SELECT, INSERT, UPDATE, DELETE) nos controllers
- **SEMPRE** manter toda lógica de banco no MODEL
- **Sistema MVC** - controllers apenas coordenam, models acessem dados

#### Controller Standards
- **Organization ID**: Por enquanto, sempre usar `$orgId = 1;` (nunca `getCurrentOrgId()`)
- **BaseController Methods**: Todos controllers herdam de BaseController que possui:
  - `jsonResponse($data)` - para respostas JSON padronizadas
  - `handleError($exception, $message)` - para tratamento de erros
  - `render($view, $data)` - para renderizar views
- **Error Handling**: SEMPRE usar try/catch nos controllers e chamar `$this->handleError($e)`

#### Banco de Dados - Ambiente de Produção
- **NUNCA alterar** o arquivo `database.php`
- **Estrutura de referência**: `database/local.sql` (sempre atualizar)
- **Scripts SQL**: executar manualmente no banco, NUNCA via código
- **Migrations**: criar scripts separados e testar antes de aplicar

#### Preservação de Dados Crítica
- **Sistema em produção** - usuários ativos utilizando
- **Zero data loss** - qualquer alteração deve preservar dados
- **Backward compatibility** - não quebrar funcionalidades existentes
- **Testes obrigatórios** - validar antes de aplicar em produção

#### Fluxo de Alterações no Banco
1. Criar script SQL separado
2. Testar em ambiente local
3. Atualizar `database/local.sql`
4. Documentar mudanças
5. Aplicar manualmente em produção

## Security & Performance Rules

### Content Security Policy (CSP)
- **CSP configurado** em `app/security/SecurityHelper.php`
- **Domains permitidos**: cdn.jsdelivr.net, cdnjs.cloudflare.com, fonts.googleapis.com, cdn.tiny.cloud, sp.tinymce.com
- **Antes de adicionar novos CDNs**: atualizar CSP primeiro

### Error Handling & Logging
- **NUNCA expor** detalhes técnicos para usuários finais
- **Error logs**: usar `error_log()` para debug, não `var_dump()` ou `print_r()`
- **Sempre sanitizar** dados antes de output no HTML

### API Response Standards
- **SEMPRE retornar JSON** no formato padronizado:
  ```json
  {
    "success": true/false,
    "message": "Mensagem para o usuário",
    "data": {} // opcional
  }
  ```

## Code Quality Standards

### File Modifications
- **SEMPRE ler arquivo** antes de editar (usar Read tool)
- **Preservar indentação** existente (tabs/spaces)
- **Não adicionar emojis** nos arquivos, exceto se solicitado
- **Comentários**: apenas quando explicitamente solicitado
- **NÃO atualizar** o arquivo `tarefas.txt` - não é necessário

### Backup & Recovery
- **Antes de alterações grandes**: fazer backup do banco
- **Migrations reversíveis**: sempre criar rollback script
- **Documentar breaking changes**: avisar sobre impactos

### Environment Specific
- **Configurações**: usar variáveis de ambiente quando possível
- **Paths absolutos**: usar função `url()` para URLs
- **Cache**: limpar quando necessário após mudanças
- NUNCA USAR temporizador, timestemp ou algo assim.