<?php
$title = 'Integrações - Painel Administrativo';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="fas fa-plug me-2"></i>Integrações do Sistema</h2>
        <p class="text-muted mb-0">Configure e gerencie todas as integrações externas do sistema</p>
    </div>
</div>

    <div class="row">
        <!-- N8N Integration -->
        <div class="col-12 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-robot me-2"></i>N8N Automation</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Automatize a criação de lançamentos através do N8N.</p>

                    <div class="mb-3">
                        <label class="form-label"><strong>Webhook URL:</strong></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="n8nWebhookUrl" readonly>
                            <button class="btn btn-outline-secondary" onclick="copyToClipboard('n8nWebhookUrl')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Formato JSON esperado:</strong></label>
                        <pre class="bg-light p-2 rounded"><code>{
  "valor": "150.50",
  "descricao": "Compra supermercado",
  "tipo": "saida",
  "categoria": "Alimentação",
  "conta": "Cartão Crédito",
  "data": "2025-01-15",
  "observacoes": "Opcional"
}</code></pre>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" onclick="testN8N()">
                            <i class="fas fa-play me-2"></i>Testar N8N
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- WhatsApp Integration -->
        <div class="col-12 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fab fa-whatsapp me-2"></i>WhatsApp (w-api.app)</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Envie notificações automáticas via WhatsApp.</p>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><strong>Status da Conexão:</strong></span>
                            <span id="whatsappStatus" class="badge bg-secondary">Verificando...</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Token w-api.app:</strong></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="wapiToken" placeholder="Seu token w-api.app">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('wapiToken')">
                                <i class="fas fa-eye" id="wapiTokenEye"></i>
                            </button>
                        </div>
                        <small class="text-muted">Token de autenticação da sua instância w-api.app</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Instance ID:</strong></label>
                        <input type="text" class="form-control" id="wapiInstanceId" placeholder="ID da sua instância">
                        <small class="text-muted">ID único da sua instância w-api.app</small>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" onclick="saveWhatsAppConfig()">
                                <i class="fas fa-save me-2"></i>Salvar Configurações
                            </button>
                            <button class="btn btn-outline-secondary" onclick="loadWhatsAppConfigForEdit()">
                                <i class="fas fa-edit me-2"></i>Editar Existente
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Telefone de Teste:</label>
                        <input type="text" class="form-control" id="testPhone" placeholder="11999999999">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mensagem de Teste:</label>
                        <textarea class="form-control" id="testMessage" rows="3" placeholder="Teste de integração WhatsApp"></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-success" onclick="testWhatsAppConnection()">
                            <i class="fas fa-check-circle me-2"></i>Testar Conexão
                        </button>
                        <button class="btn btn-outline-success" onclick="sendTestWhatsApp()">
                            <i class="fas fa-paper-plane me-2"></i>Enviar Teste
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Integration (MailerSend) -->
        <div class="col-12 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Email (MailerSend)</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Configure o MailerSend para envio de emails automáticos e lembretes.</p>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><strong>Status da Configuração:</strong></span>
                            <span id="emailStatus" class="badge bg-secondary">Verificando...</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>API Key MailerSend:</strong></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="mailersendApiKey" placeholder="mlsn.xxxxxxxxxxxxxxxx">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('mailersendApiKey')">
                                <i class="fas fa-eye" id="mailersendApiKeyEye"></i>
                            </button>
                        </div>
                        <small class="text-muted">Obtenha sua API Key em <a href="https://app.mailersend.com/api-tokens" target="_blank">MailerSend</a></small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><strong>Email Remetente:</strong></label>
                                <input type="email" class="form-control" id="mailersendFromEmail" placeholder="noreply@seudominio.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><strong>Nome Remetente:</strong></label>
                                <input type="text" class="form-control" id="mailersendFromName" placeholder="Sua Empresa">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" onclick="saveEmailConfig()">
                                <i class="fas fa-save me-2"></i>Salvar Configurações
                            </button>
                            <button class="btn btn-outline-secondary" onclick="loadEmailConfigForEdit()">
                                <i class="fas fa-edit me-2"></i>Carregar Existente
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email de Teste:</label>
                        <input type="email" class="form-control" id="testEmailAddress" placeholder="teste@empresa.com">
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-info" onclick="testEmailConnection()">
                            <i class="fas fa-cog me-2"></i>Testar Configuração
                        </button>
                        <button class="btn btn-outline-info" onclick="sendTestEmail()">
                            <i class="fas fa-paper-plane me-2"></i>Enviar Email Teste
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Configurações</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Configuração WhatsApp</h6>
                        <p class="mb-2">Para usar a integração WhatsApp com w-api.app:</p>
                        <ol class="mb-2">
                            <li>Acesse <a href="https://w-api.app" target="_blank">w-api.app</a> e crie uma conta</li>
                            <li>Crie uma nova instância do WhatsApp</li>
                            <li>Copie o <strong>Token</strong> e <strong>Instance ID</strong> gerados</li>
                            <li>Certifique-se que a instância está <strong>ativa</strong> e conectada</li>
                            <li>Cole as informações nos campos acima e teste a conexão</li>
                        </ol>
                        <div class="alert alert-warning mt-2 mb-0">
                            <small><i class="fas fa-exclamation-triangle me-1"></i>
                            <strong>Importante:</strong> A instância deve estar ativa na w-api.app para funcionar.
                            Verifique o status no painel da w-api.app se o teste de conexão falhar.
                            </small>
                        </div>
                    </div>

                    <div class="alert alert-success">
                        <h6><i class="fas fa-robot me-2"></i>Como usar N8N</h6>
                        <p class="mb-2">Para conectar o N8N ao sistema:</p>
                        <ol class="mb-0">
                            <li>Copie a URL do webhook acima</li>
                            <li>No N8N, use um nó HTTP Request com método POST</li>
                            <li>Configure o JSON conforme o formato mostrado</li>
                            <li>Teste a integração com o botão "Testar N8N"</li>
                        </ol>
                    </div>

                    <div class="alert alert-primary">
                        <h6><i class="fas fa-envelope me-2"></i>Configuração MailerSend</h6>
                        <p class="mb-2">Para usar o MailerSend para emails automáticos:</p>
                        <ol class="mb-2">
                            <li>Acesse <a href="https://app.mailersend.com" target="_blank">MailerSend</a> e crie uma conta</li>
                            <li>Vá em <strong>API Tokens</strong> e gere uma nova API Key</li>
                            <li>Configure seu domínio verificado no MailerSend</li>
                            <li>Copie a API Key e configure os campos acima</li>
                            <li>Teste a configuração e envie um email de teste</li>
                        </ol>
                        <div class="alert alert-info mt-2 mb-0">
                            <small><i class="fas fa-info-circle me-1"></i>
                            <strong>Recursos:</strong> Emails de lembretes, recuperação de senha, convites de equipe e notificações automáticas.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Logs -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Logs de Notificações WhatsApp</h5>
                    <div class="d-flex gap-2">
                        <select id="logStatusFilter" class="form-select form-select-sm" style="width: auto;">
                            <option value="">Todos os status</option>
                            <option value="sent">Enviadas</option>
                            <option value="failed">Falharam</option>
                        </select>
                        <button class="btn btn-sm btn-outline-primary" onclick="loadNotificationLogs()">
                            <i class="fas fa-sync me-1"></i>Atualizar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="notificationLogs">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                            <p>Carregando logs de notificações...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Results -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Resultados dos Testes</h5>
                </div>
                <div class="card-body">
                    <div id="testResults" class="bg-light p-3 rounded" style="min-height: 100px; font-family: monospace; font-size: 0.9rem;">
                        <div class="text-muted">Os resultados dos testes aparecerão aqui...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let config = {};

// Carregar configurações ao carregar a página - removido (duplicado abaixo)

async function loadConfig() {
    try {
        const response = await fetch('<?= url('/api/integrations/config') ?>');
        const data = await response.json();

        if (data.success) {
            config = data.config;
            updateUI();
        }
    } catch (error) {
        addTestResult('Erro ao carregar configurações: ' + error.message, 'error');
    }
}

function updateUI() {
    // Atualizar URL do webhook N8N
    document.getElementById('n8nWebhookUrl').value = config.n8n.webhook_url;

    // Atualizar status WhatsApp
    const statusElement = document.getElementById('whatsappStatus');
    if (config.whatsapp.configured) {
        statusElement.textContent = 'Configurado';
        statusElement.className = 'badge bg-success';
    } else {
        statusElement.textContent = 'Não configurado';
        statusElement.className = 'badge bg-warning';
    }

    // Carregar configurações salvas do WhatsApp
    // Apenas carregar se não há configuração ou se os campos estão vazios
    if (config.whatsapp.token && !document.getElementById('wapiToken').value) {
        // Para tokens mascarados, não preencher (mantém vazio para nova entrada)
        if (!config.whatsapp.token.includes('...')) {
            document.getElementById('wapiToken').value = config.whatsapp.token;
        }
    }
    if (config.whatsapp.instance_id && !document.getElementById('wapiInstanceId').value) {
        document.getElementById('wapiInstanceId').value = config.whatsapp.instance_id;
    }
}

function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    document.execCommand('copy');

    // Feedback visual
    const button = element.nextElementSibling;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i>';
    button.classList.add('btn-success');

    setTimeout(() => {
        button.innerHTML = originalText;
        button.classList.remove('btn-success');
    }, 2000);
}

async function testWhatsAppConnection() {
    addTestResult('Testando conexão WhatsApp...', 'info');

    try {
        const response = await fetch('<?= url('/api/integrations/test-whatsapp') ?>', {
            method: 'POST'
        });
        const data = await response.json();

        addTestResult('Teste WhatsApp: ' + data.message, data.success ? 'success' : 'error');

        if (data.data) {
            addTestResult('Dados da conexão: ' + JSON.stringify(data.data, null, 2), 'info');
        }
    } catch (error) {
        addTestResult('Erro no teste WhatsApp: ' + error.message, 'error');
    }
}

async function sendTestWhatsApp() {
    const phone = document.getElementById('testPhone').value;
    const message = document.getElementById('testMessage').value;

    if (!phone || !message) {
        addTestResult('Preencha telefone e mensagem para o teste', 'error');
        return;
    }

    addTestResult('Enviando mensagem de teste...', 'info');

    try {
        const response = await fetch('<?= url('/api/integrations/send-test-whatsapp') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ phone, message })
        });
        const data = await response.json();

        addTestResult('Teste de envio: ' + data.message, data.success ? 'success' : 'error');
    } catch (error) {
        addTestResult('Erro no envio: ' + error.message, 'error');
    }
}

async function testN8N() {
    addTestResult('Testando webhook N8N...', 'info');

    try {
        const response = await fetch('<?= url('/api/integrations/test-n8n') ?>', {
            method: 'POST'
        });
        const data = await response.json();

        addTestResult('Teste N8N: ' + data.message, data.success ? 'success' : 'error');
        addTestResult('HTTP Code: ' + data.http_code, 'info');

        if (data.test_data) {
            addTestResult('Dados enviados: ' + JSON.stringify(data.test_data, null, 2), 'info');
        }

        if (data.response) {
            addTestResult('Resposta: ' + JSON.stringify(data.response, null, 2), 'info');
        }
    } catch (error) {
        addTestResult('Erro no teste N8N: ' + error.message, 'error');
    }
}

function addTestResult(message, type = 'info') {
    const container = document.getElementById('testResults');
    const timestamp = new Date().toLocaleTimeString();

    const colors = {
        'info': '#6c757d',
        'success': '#28a745',
        'error': '#dc3545',
        'warning': '#ffc107'
    };

    const color = colors[type] || colors.info;

    const resultHtml = `<div style="color: ${color}; margin-bottom: 0.5rem;">
        [${timestamp}] ${message}
    </div>`;

    container.innerHTML += resultHtml;
    container.scrollTop = container.scrollHeight;
}

function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const eye = document.getElementById(inputId + 'Eye');

    if (input.type === 'password') {
        input.type = 'text';
        eye.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        eye.className = 'fas fa-eye';
    }
}

async function saveWhatsAppConfig() {
    const token = document.getElementById('wapiToken').value;
    const instanceId = document.getElementById('wapiInstanceId').value;

    if (!token || !instanceId) {
        addTestResult('Preencha o token e instance ID', 'error');
        return;
    }

    addTestResult('Salvando configurações WhatsApp...', 'info');

    try {
        const response = await fetch('<?= url('/api/integrations/save-whatsapp-config') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                token: token,
                instance_id: instanceId
            })
        });
        const data = await response.json();

        addTestResult('Configurações: ' + data.message, data.success ? 'success' : 'error');

        if (data.success) {
            // Recarregar configurações
            await loadConfig();
        }
    } catch (error) {
        addTestResult('Erro ao salvar: ' + error.message, 'error');
    }
}

async function loadWhatsAppConfigForEdit() {
    addTestResult('Carregando configurações para edição...', 'info');

    try {
        const response = await fetch('<?= url('/api/integrations/whatsapp-config-edit') ?>');
        const data = await response.json();

        if (data.success) {
            document.getElementById('wapiToken').value = data.config.token;
            document.getElementById('wapiInstanceId').value = data.config.instance_id;
            addTestResult('Configurações carregadas para edição', 'success');
        } else {
            addTestResult('Erro ao carregar configurações: ' + data.message, 'error');
        }
    } catch (error) {
        addTestResult('Erro ao carregar configurações: ' + error.message, 'error');
    }
}

// Carregar logs de notificação
async function loadNotificationLogs() {
    const logsContainer = document.getElementById('notificationLogs');
    const statusFilter = document.getElementById('logStatusFilter').value;

    logsContainer.innerHTML = `
        <div class="text-center text-muted py-4">
            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
            <p>Carregando logs de notificações...</p>
        </div>
    `;

    try {
        let url = '<?= url('/api/notifications/audit-log') ?>?delivery_method=whatsapp&limit=20';
        if (statusFilter) {
            url += '&status=' + statusFilter;
        }

        const response = await fetch(url, {
            method: 'GET',
            credentials: 'same-origin'
        });
        const data = await response.json();

        if (data.success && data.audit_logs) {
            displayNotificationLogs(data.audit_logs);
        } else if (data.error === 'authentication_required') {
            logsContainer.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-lock fa-2x mb-3"></i>
                    <p>Você precisa estar logado para ver os logs</p>
                    <a href="/login" class="btn btn-primary btn-sm">Fazer Login</a>
                </div>
            `;
        } else {
            logsContainer.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                    <p>Erro ao carregar logs: ${data.message || 'Erro desconhecido'}</p>
                </div>
            `;
        }
    } catch (error) {
        logsContainer.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                <p>Erro de conexão: ${error.message}</p>
            </div>
        `;
    }
}

function displayNotificationLogs(logs) {
    const logsContainer = document.getElementById('notificationLogs');

    if (logs.length === 0) {
        logsContainer.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="fas fa-inbox fa-2x mb-3"></i>
                <p>Nenhum log de notificação WhatsApp encontrado</p>
                <small>Crie um lançamento para gerar notificações</small>
            </div>
        `;
        return;
    }

    let html = '<div class="table-responsive"><table class="table table-sm table-hover">';
    html += `
        <thead class="table-light">
            <tr>
                <th style="width: 100px">Status</th>
                <th style="width: 150px">Data/Hora</th>
                <th>Usuário</th>
                <th>Título</th>
                <th style="width: 120px">Telefone</th>
                <th style="width: 100px">MessageID</th>
                <th>Erro</th>
            </tr>
        </thead>
        <tbody>
    `;

    logs.forEach(log => {
        const auditData = log.notification_data_decoded?.whatsapp_audit || {};
        const statusClass = log.status === 'sent' ? 'success' : (log.status === 'failed' ? 'danger' : 'secondary');
        const statusIcon = log.status === 'sent' ? 'check-circle' : (log.status === 'failed' ? 'times-circle' : 'clock');

        html += `
            <tr>
                <td>
                    <span class="badge bg-${statusClass}">
                        <i class="fas fa-${statusIcon} me-1"></i>
                        ${log.status}
                    </span>
                </td>
                <td>
                    <small>${log.sent_at_relative || 'N/A'}</small><br>
                    <small class="text-muted">${new Date(log.sent_at).toLocaleString('pt-BR')}</small>
                </td>
                <td>
                    <strong>${log.user_name || 'N/A'}</strong><br>
                    <small class="text-muted">${log.user_email || ''}</small>
                </td>
                <td>
                    <strong>${log.title}</strong><br>
                    <small class="text-muted">${log.notification_type}</small>
                </td>
                <td>
                    <small>${auditData.phone_formatted || auditData.phone_original || 'N/A'}</small>
                </td>
                <td>
                    <small class="text-muted">
                        ${auditData.message_id ? auditData.message_id.substring(0, 10) + '...' : 'N/A'}
                    </small>
                </td>
                <td>
                    ${log.status === 'failed' ?
                        `<small class="text-danger">${log.error_message || auditData.error_message || 'Erro desconhecido'}</small>` :
                        '<small class="text-muted">-</small>'
                    }
                </td>
            </tr>
        `;
    });

    html += '</tbody></table></div>';

    // Adicionar estatísticas
    const successCount = logs.filter(log => log.status === 'sent').length;
    const failedCount = logs.filter(log => log.status === 'failed').length;
    const total = logs.length;

    html += `
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Mostrando últimos ${total} logs</small>
                    <div class="d-flex gap-3">
                        <span class="badge bg-success">${successCount} enviadas</span>
                        <span class="badge bg-danger">${failedCount} falharam</span>
                        <span class="badge bg-secondary">${total} total</span>
                    </div>
                </div>
            </div>
        </div>
    `;

    logsContainer.innerHTML = html;
}

// === FUNÇÕES EMAIL/MAILERSEND ===

// Carregar configurações de email para edição
async function loadEmailConfigForEdit() {
    addTestResult('Carregando configurações de email para edição...', 'info');

    try {
        const response = await fetch('<?= url('/api/integrations/email-config-edit') ?>');
        const data = await response.json();

        if (data.success) {
            document.getElementById('mailersendApiKey').value = data.config.api_key;
            document.getElementById('mailersendFromEmail').value = data.config.from_email;
            document.getElementById('mailersendFromName').value = data.config.from_name;
            addTestResult('Configurações carregadas para edição', 'success');

            // Atualizar status visual
            const statusElement = document.getElementById('emailStatus');
            if (data.config.api_key) {
                statusElement.textContent = 'Configurado';
                statusElement.className = 'badge bg-success';
            } else {
                statusElement.textContent = 'Não configurado';
                statusElement.className = 'badge bg-warning';
            }
        } else {
            addTestResult('Erro ao carregar configurações: ' + data.message, 'error');

            const statusElement = document.getElementById('emailStatus');
            statusElement.textContent = 'Erro';
            statusElement.className = 'badge bg-danger';
        }
    } catch (error) {
        addTestResult('Erro ao carregar configurações de email: ' + error.message, 'error');
    }
}

// Salvar configurações de email
async function saveEmailConfig() {
    const apiKey = document.getElementById('mailersendApiKey').value.trim();
    const fromEmail = document.getElementById('mailersendFromEmail').value.trim();
    const fromName = document.getElementById('mailersendFromName').value.trim();

    if (!apiKey || !fromEmail || !fromName) {
        addTestResult('Por favor, preencha todos os campos de email', 'error');
        return;
    }

    addTestResult('Salvando configurações de email...', 'info');

    try {
        const response = await fetch('<?= url('/api/integrations/save-email-config') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                api_key: apiKey,
                from_email: fromEmail,
                from_name: fromName
            })
        });
        const data = await response.json();

        addTestResult('Configurações de email: ' + data.message, data.success ? 'success' : 'error');

        if (data.success) {
            // Atualizar status visual
            const statusElement = document.getElementById('emailStatus');
            statusElement.textContent = 'Configurado';
            statusElement.className = 'badge bg-success';
        }
    } catch (error) {
        addTestResult('Erro ao salvar configurações de email: ' + error.message, 'error');
    }
}

// Testar conexão de email
async function testEmailConnection() {
    try {
        const response = await fetch('<?= url('/admin/test-email-config') ?>', {
            method: 'POST',
            credentials: 'same-origin'
        });
        const data = await response.json();

        if (data.success) {
            addTestResult('✅ Conexão MailerSend: ' + data.message, 'success');

            // Atualizar status visual
            const statusElement = document.getElementById('emailStatus');
            statusElement.textContent = 'Funcionando';
            statusElement.className = 'badge bg-success';
        } else {
            addTestResult('❌ Erro MailerSend: ' + data.message, 'error');

            const statusElement = document.getElementById('emailStatus');
            statusElement.textContent = 'Erro';
            statusElement.className = 'badge bg-danger';
        }
    } catch (error) {
        addTestResult('Erro ao testar conexão de email: ' + error.message, 'error');
    }
}

// Enviar email de teste
async function sendTestEmail() {
    const testEmail = document.getElementById('testEmailAddress').value.trim();

    if (!testEmail) {
        addTestResult('Por favor, insira um email de teste', 'error');
        return;
    }

    if (!/\S+@\S+\.\S+/.test(testEmail)) {
        addTestResult('Por favor, insira um email válido', 'error');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('email', testEmail);

        const response = await fetch('<?= url('/admin/send-test-email') ?>', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        const data = await response.json();

        if (data.success) {
            addTestResult('✅ Email de teste enviado para: ' + testEmail, 'success');
            addTestResult('Verifique a caixa de entrada (e spam) em alguns instantes', 'info');
        } else {
            addTestResult('❌ Erro ao enviar email: ' + data.message, 'error');
        }
    } catch (error) {
        addTestResult('Erro ao enviar email de teste: ' + error.message, 'error');
    }
}

// Carregar logs automaticamente quando a página carrega
document.addEventListener('DOMContentLoaded', function() {
    loadConfig();
    loadNotificationLogs();
    loadEmailConfigForEdit(); // Carregar status do email também

    // Atualizar logs quando o filtro muda
    document.getElementById('logStatusFilter').addEventListener('change', loadNotificationLogs);
});

// Atualizar logs a cada 30 segundos
setInterval(loadNotificationLogs, 30000);
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>