<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-user-cog me-3"></i>
        Configurações de Perfil
    </h1>
</div>

<div class="row">
    <!-- Informações do Perfil -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 d-flex align-items-center">
                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                        <i class="fas fa-user text-white"></i>
                    </div>
                    Informações Pessoais
                </h5>
            </div>
            <div class="card-body">
                <form id="profileForm">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="mb-3">
                        <label for="profileNome" class="form-label">Nome Completo *</label>
                        <input type="text" class="form-control" id="profileNome" name="nome" value="<?= htmlspecialchars($user['nome'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="profileEmail" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="profileEmail" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                        <div class="form-text">Este email será usado para login</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="profileTelefone" class="form-label">Telefone</label>
                        <input type="tel" class="form-control" id="profileTelefone" name="telefone" value="<?= htmlspecialchars($user['telefone'] ?? '') ?>" placeholder="(11) 99999-9999">
                        <div class="form-text">Opcional</div>
                    </div>

                    <div class="mb-3">
                        <label for="profileWhatsApp" class="form-label">
                            <i class="fab fa-whatsapp me-2 text-success"></i>
                            WhatsApp
                        </label>
                        <input type="tel" class="form-control" id="profileWhatsApp" name="whatsapp_number" value="<?= htmlspecialchars($user['whatsapp_number'] ?? '') ?>" placeholder="5511999999999">
                        <div class="form-text">Número com código do país (ex: 5511999999999) - usado para notificações</div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Alterar Senha -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 d-flex align-items-center">
                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                        <i class="fas fa-lock text-white"></i>
                    </div>
                    Alterar Senha
                </h5>
            </div>
            <div class="card-body">
                <form id="passwordForm">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Senha Atual *</label>
                        <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">Nova Senha *</label>
                        <input type="password" class="form-control" id="newPassword" name="new_password" required minlength="6">
                        <div class="form-text">Mínimo de 6 caracteres</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirmar Nova Senha *</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required minlength="6">
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-key me-2"></i>
                            Alterar Senha
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Informações da Conta -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0 d-flex align-items-center">
                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                        <i class="fas fa-info-circle text-white"></i>
                    </div>
                    Informações da Conta
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <strong>Status:</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="badge bg-success">Ativo</span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-6">
                        <strong>Membro desde:</strong>
                    </div>
                    <div class="col-sm-6">
                        <?= isset($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : 'Não disponível' ?>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-6">
                        <strong>Último acesso:</strong>
                    </div>
                    <div class="col-sm-6">
                        <?= isset($user['updated_at']) ? date('d/m/Y H:i', strtotime($user['updated_at'])) : 'Não disponível' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Configurações de Notificações -->
<div class="row mt-4" id="notifications">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 d-flex align-items-center">
                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                        <i class="fas fa-bell text-white"></i>
                    </div>
                    Configurações de Notificações
                </h5>
            </div>
            <div class="card-body">
                <form id="notificationForm">
                    <div class="row">
                        <!-- Métodos de Entrega -->
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-paper-plane me-2 text-primary"></i>
                                Métodos de Entrega
                            </h6>


                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="enableEmail" name="enable_email_notifications">
                                <label class="form-check-label" for="enableEmail">
                                    <i class="fas fa-envelope me-2"></i>
                                    Notificações por Email
                                </label>
                                <small class="form-text text-muted d-block">Receber resumos por email</small>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="enableWhatsApp" name="enable_whatsapp_notifications">
                                <label class="form-check-label" for="enableWhatsApp">
                                    <i class="fab fa-whatsapp me-2"></i>
                                    Notificações por WhatsApp
                                </label>
                                <small class="form-text text-muted d-block">Receber alertas via WhatsApp</small>
                            </div>
                        </div>

                        <!-- Tipos de Notificação -->
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-list-check me-2 text-success"></i>
                                Tipos de Notificação
                            </h6>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="notifyTransactions" name="notify_new_transactions" checked>
                                <label class="form-check-label" for="notifyTransactions">
                                    <i class="fas fa-plus-circle me-2 text-success"></i>
                                    Novos Lançamentos
                                </label>
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="notifyDueDates" name="notify_upcoming_due_dates" checked>
                                <label class="form-check-label" for="notifyDueDates">
                                    <i class="fas fa-clock me-2 text-warning"></i>
                                    Vencimentos Próximos
                                </label>
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="notifyLowBalance" name="notify_low_balance" checked>
                                <label class="form-check-label" for="notifyLowBalance">
                                    <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                                    Saldo Baixo
                                </label>
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="notifyGoals" name="notify_goal_reached" checked>
                                <label class="form-check-label" for="notifyGoals">
                                    <i class="fas fa-trophy me-2 text-primary"></i>
                                    Metas Atingidas
                                </label>
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="notifyOverdue" name="notify_overdue_transactions" checked>
                                <label class="form-check-label" for="notifyOverdue">
                                    <i class="fas fa-exclamation-circle me-2 text-danger"></i>
                                    Contas Vencidas
                                </label>
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="notifyWeekly" name="notify_weekly_summary">
                                <label class="form-check-label" for="notifyWeekly">
                                    <i class="fas fa-chart-bar me-2 text-info"></i>
                                    Resumo Semanal
                                </label>
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="notifyMonthly" name="notify_monthly_summary">
                                <label class="form-check-label" for="notifyMonthly">
                                    <i class="fas fa-chart-line me-2 text-info"></i>
                                    Resumo Mensal
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Configurações Avançadas -->
                    <hr class="my-4">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-cogs me-2 text-secondary"></i>
                                Configurações Avançadas
                            </h6>

                            <div class="mb-3">
                                <label for="dueDaysReminderMultiple" class="form-label">
                                    <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                    Lembretes de Vencimento
                                </label>
                                <div class="mb-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="remind1" value="1">
                                        <label class="form-check-label" for="remind1">1 dia</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="remind2" value="2">
                                        <label class="form-check-label" for="remind2">2 dias</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="remind3" value="3" checked>
                                        <label class="form-check-label" for="remind3">3 dias</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="remind5" value="5">
                                        <label class="form-check-label" for="remind5">5 dias</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="remind7" value="7" checked>
                                        <label class="form-check-label" for="remind7">7 dias</label>
                                    </div>
                                </div>
                                <input type="hidden" id="dueDaysReminderMultiple" name="due_date_reminder_days" value="3,7">
                                <small class="form-text text-muted">Selecione quando deseja ser lembrado antes do vencimento</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-filter me-2 text-info"></i>
                                    Tipos de Lembrete
                                </label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remindExpenses" name="remind_expenses" checked>
                                    <label class="form-check-label" for="remindExpenses">
                                        <i class="fas fa-minus-circle me-2 text-danger"></i>
                                        Lembrar despesas a pagar
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remindIncome" name="remind_income" checked>
                                    <label class="form-check-label" for="remindIncome">
                                        <i class="fas fa-plus-circle me-2 text-success"></i>
                                        Lembrar receitas a receber
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="lowBalanceThreshold" class="form-label">Valor mínimo para alerta de saldo baixo</label>
                                <input type="text" class="form-control currency-mask" id="lowBalanceThreshold" name="low_balance_threshold" value="100.00">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-moon me-2 text-secondary"></i>
                                Horário de Silêncio
                            </h6>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="enableQuietHours" name="enable_quiet_hours" checked>
                                <label class="form-check-label" for="enableQuietHours">
                                    Ativar horário de silêncio
                                </label>
                                <small class="form-text text-muted d-block">Reduzir notificações em horários específicos</small>
                            </div>

                            <div class="row" id="quietHoursConfig">
                                <div class="col-6">
                                    <label for="quietStart" class="form-label">Início</label>
                                    <input type="time" class="form-control" id="quietStart" name="quiet_hours_start" value="22:00">
                                </div>
                                <div class="col-6">
                                    <label for="quietEnd" class="form-label">Fim</label>
                                    <input type="time" class="form-control" id="quietEnd" name="quiet_hours_end" value="08:00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <span class="text-muted" id="notificationStatus">Carregando preferências...</span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-secondary me-2" onclick="testNotification()">
                                <i class="fas fa-bell me-2"></i>
                                Testar Notificação
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Salvar Configurações
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Máscara para telefone
function phoneMask(input) {
    let value = input.value.replace(/\D/g, '');
    
    if (value.length <= 10) {
        // Formato: (11) 9999-9999
        value = value.replace(/^(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3');
    } else {
        // Formato: (11) 99999-9999
        value = value.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
    }
    
    input.value = value;
}

// Aplicar máscara no campo telefone
document.getElementById('profileTelefone').addEventListener('input', function() {
    phoneMask(this);
});

// Validação de formulário
document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    if (!formData.get('nome') || !formData.get('email')) {
        Swal.fire('Atenção!', 'Nome e email são obrigatórios', 'warning');
        return;
    }
    
    fetch('<?= url('/api/profile/update') ?>', {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            Swal.fire('Erro!', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Erro!', 'Erro ao atualizar perfil', 'error');
    });
});

document.getElementById('passwordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Validações
    if (!formData.get('current_password') || !formData.get('new_password') || !formData.get('confirm_password')) {
        Swal.fire('Atenção!', 'Todos os campos são obrigatórios', 'warning');
        return;
    }
    
    if (formData.get('new_password') !== formData.get('confirm_password')) {
        Swal.fire('Atenção!', 'Nova senha e confirmação não coincidem', 'warning');
        return;
    }
    
    if (formData.get('new_password').length < 6) {
        Swal.fire('Atenção!', 'A nova senha deve ter pelo menos 6 caracteres', 'warning');
        return;
    }
    
    fetch('<?= url('/api/profile/update') ?>', {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                // Limpar formulário
                document.getElementById('passwordForm').reset();
            });
        } else {
            Swal.fire('Erro!', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Erro!', 'Erro ao alterar senha', 'error');
    });
});

// Sistema de Notificações
let notificationPreferences = {};

// Carregar preferências de notificação
async function loadNotificationPreferences() {
    try {
        const response = await fetch('<?= url('/api/notifications/preferences') ?>', {
            credentials: 'same-origin'
        });
        const data = await response.json();

        if (data.success && data.preferences) {
            notificationPreferences = data.preferences;
            populateNotificationForm(data.preferences);
            updateNotificationStatus(data.summary);
        }
    } catch (error) {
        console.error('Erro ao carregar preferências:', error);
        document.getElementById('notificationStatus').textContent = 'Erro ao carregar preferências';
    }
}

// Preencher formulário com preferências
function populateNotificationForm(prefs) {
    // Métodos de entrega (só os que existem no HTML)
    document.getElementById('enableEmail').checked = prefs.enable_email_notifications == 1;
    document.getElementById('enableWhatsApp').checked = prefs.enable_whatsapp_notifications == 1;

    // Tipos de notificação
    document.getElementById('notifyTransactions').checked = prefs.notify_new_transactions == 1;
    document.getElementById('notifyDueDates').checked = prefs.notify_upcoming_due_dates == 1;
    document.getElementById('notifyLowBalance').checked = prefs.notify_low_balance == 1;
    document.getElementById('notifyGoals').checked = prefs.notify_goal_reached == 1;
    document.getElementById('notifyOverdue').checked = prefs.notify_overdue_transactions == 1;
    document.getElementById('notifyWeekly').checked = prefs.notify_weekly_summary == 1;
    document.getElementById('notifyMonthly').checked = prefs.notify_monthly_summary == 1;

    // Configurações avançadas - lembretes de vencimento (support comma-separated values)
    const reminderDays = prefs.due_date_reminder_days || '3';
    document.getElementById('dueDaysReminderMultiple').value = reminderDays;
    updateReminderCheckboxes(reminderDays.toString());

    // Tipos de lembrete
    document.getElementById('remindExpenses').checked = prefs.remind_expenses !== 0;
    document.getElementById('remindIncome').checked = prefs.remind_income !== 0;

    document.getElementById('lowBalanceThreshold').value = prefs.low_balance_threshold || '100.00';
    document.getElementById('enableQuietHours').checked = prefs.enable_quiet_hours == 1;
    document.getElementById('quietStart').value = prefs.quiet_hours_start || '22:00';
    document.getElementById('quietEnd').value = prefs.quiet_hours_end || '08:00';

    // Aplicar máscara no valor
    const thresholdField = document.getElementById('lowBalanceThreshold');
    if (thresholdField.value && !thresholdField.value.includes('R$')) {
        let numValue = parseFloat(thresholdField.value) || 0;
        thresholdField.value = numValue.toFixed(2);
        currencyMask(thresholdField);
    }
}

// Atualizar status das notificações
function updateNotificationStatus(summary) {
    if (summary) {
        const statusText = `${summary.total_enabled} configurações ativadas`;
        document.getElementById('notificationStatus').textContent = statusText;
    }
}

// Controlar visibilidade do horário de silêncio
document.getElementById('enableQuietHours').addEventListener('change', function() {
    const configDiv = document.getElementById('quietHoursConfig');
    if (this.checked) {
        configDiv.style.opacity = '1';
        configDiv.querySelectorAll('input').forEach(input => input.disabled = false);
    } else {
        configDiv.style.opacity = '0.5';
        configDiv.querySelectorAll('input').forEach(input => input.disabled = true);
    }
});

// Salvar preferências de notificação
document.getElementById('notificationForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const preferences = {};

    // Converter FormData para objeto
    for (let [key, value] of formData.entries()) {
        if (key.startsWith('enable_') || key.startsWith('notify_')) {
            preferences[key] = 1;
        } else if (key === 'low_balance_threshold') {
            preferences[key] = removeCurrencyMask(value);
        } else {
            preferences[key] = value;
        }
    }

    // Adicionar campos não marcados como false
    const checkboxes = ['enable_email_notifications', 'enable_whatsapp_notifications',
                       'notify_new_transactions', 'notify_upcoming_due_dates', 'notify_low_balance',
                       'notify_goal_reached', 'notify_overdue_transactions', 'notify_weekly_summary',
                       'notify_monthly_summary', 'enable_quiet_hours'];

    checkboxes.forEach(checkbox => {
        if (!preferences[checkbox]) {
            preferences[checkbox] = 0;
        }
    });

    try {
        const response = await fetch('<?= url('/api/notifications/preferences') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify(preferences)
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Configurações de notificação salvas',
                timer: 2000,
                showConfirmButton: false
            });

            // Recarregar preferências
            setTimeout(loadNotificationPreferences, 1000);
        } else {
            Swal.fire('Erro!', data.message, 'error');
        }
    } catch (error) {
        console.error('Erro ao salvar preferências:', error);
        Swal.fire('Erro!', 'Erro ao salvar configurações', 'error');
    }
});

// Testar notificação
async function testNotification() {
    if (!window.notificationManager) {
        Swal.fire('Atenção!', 'Sistema de notificações não está carregado', 'warning');
        return;
    }

    // Primeiro solicitar permissão se necessário
    await window.notificationManager.requestPermission();

    // Mostrar notificação de teste
    window.notificationManager.showNotification('🧪 Teste de Notificação', {
        body: 'Esta é uma notificação de teste do sistema financeiro!',
        requireInteraction: false
    });

    // Também mostrar toast
    window.notificationManager.showToast('🧪 Notificação de teste enviada!', 'info');

    Swal.fire({
        icon: 'info',
        title: 'Teste Enviado!',
        text: 'Verifique se a notificação apareceu no seu navegador',
        timer: 3000,
        showConfirmButton: false
    });
}

// Funções para múltiplos lembretes
function updateReminderCheckboxes(reminderDaysString) {
    // Desmarcar todos primeiro
    ['remind1', 'remind2', 'remind3', 'remind5', 'remind7'].forEach(id => {
        document.getElementById(id).checked = false;
    });

    // Se não há dias especificados, retornar
    if (!reminderDaysString) return;

    // Converter string para array (support comma-separated values)
    const days = reminderDaysString.split(',').map(d => d.trim());

    // Marcar os dias selecionados
    days.forEach(day => {
        const checkbox = document.getElementById('remind' + day);
        if (checkbox) {
            checkbox.checked = true;
        }
    });
}

function updateReminderDaysField() {
    const checkedDays = [];
    ['remind1', 'remind2', 'remind3', 'remind5', 'remind7'].forEach(id => {
        const checkbox = document.getElementById(id);
        if (checkbox && checkbox.checked) {
            checkedDays.push(checkbox.value);
        }
    });

    // Store all selected days as comma-separated string (database field is now VARCHAR)
    document.getElementById('dueDaysReminderMultiple').value = checkedDays.length > 0 ? checkedDays.join(',') : '3';
}

// Carregar preferências ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    loadNotificationPreferences();

    // Configurar controle de horário de silêncio
    const enableQuietHours = document.getElementById('enableQuietHours');
    if (enableQuietHours) {
        enableQuietHours.dispatchEvent(new Event('change'));
    }

    // Configurar eventos dos checkboxes de lembrete
    ['remind1', 'remind2', 'remind3', 'remind5', 'remind7'].forEach(id => {
        document.getElementById(id).addEventListener('change', updateReminderDaysField);
    });
});
</script>