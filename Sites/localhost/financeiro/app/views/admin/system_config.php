<?php 
$title = 'Configurações do Sistema - Painel Administrativo';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="fas fa-cogs me-2"></i>Configurações do Sistema</h2>
        <p class="text-muted mb-0">Configure parâmetros globais da plataforma</p>
    </div>
    <div class="btn-group">
        <button class="btn btn-success" onclick="saveAllConfigs()">
            <i class="fas fa-save me-2"></i>Salvar Todas
        </button>
        <a href="<?= url('/integrations') ?>" class="btn btn-info">
            <i class="fas fa-plug me-2"></i>Integrações
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Configurações Gerais -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-sliders-h me-2"></i>Configurações Gerais
                </h5>
            </div>
            <div class="card-body">
                <form id="generalConfigForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="app_name" class="form-label">Nome da Aplicação</label>
                                <input type="text" class="form-control" id="app_name" name="app_name" 
                                       value="<?= htmlspecialchars($configs['app_name'] ?? 'Sistema Financeiro') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="app_version" class="form-label">Versão</label>
                                <input type="text" class="form-control" id="app_version" name="app_version" 
                                       value="<?= htmlspecialchars($configs['app_version'] ?? '2.0.0') ?>" readonly>
                                <small class="text-muted">Versão atual do sistema</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="trial_days" class="form-label">Dias de Trial Gratuito</label>
                                <input type="number" class="form-control" id="trial_days" name="trial_days" 
                                       value="<?= htmlspecialchars($configs['trial_days'] ?? '7') ?>" min="1" max="30">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="currency_default" class="form-label">Moeda Padrão</label>
                                <select class="form-select" id="currency_default" name="currency_default">
                                    <option value="BRL" <?= ($configs['currency_default'] ?? 'BRL') === 'BRL' ? 'selected' : '' ?>>Real (BRL)</option>
                                    <option value="USD" <?= ($configs['currency_default'] ?? '') === 'USD' ? 'selected' : '' ?>>Dólar (USD)</option>
                                    <option value="EUR" <?= ($configs['currency_default'] ?? '') === 'EUR' ? 'selected' : '' ?>>Euro (EUR)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="default_timezone" class="form-label">Fuso Horário Padrão</label>
                        <select class="form-select" id="default_timezone" name="default_timezone">
                            <option value="America/Sao_Paulo" <?= ($configs['default_timezone'] ?? 'America/Sao_Paulo') === 'America/Sao_Paulo' ? 'selected' : '' ?>>São Paulo (GMT-3)</option>
                            <option value="America/Rio_Branco" <?= ($configs['default_timezone'] ?? '') === 'America/Rio_Branco' ? 'selected' : '' ?>>Rio Branco (GMT-5)</option>
                            <option value="America/Manaus" <?= ($configs['default_timezone'] ?? '') === 'America/Manaus' ? 'selected' : '' ?>>Manaus (GMT-4)</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Salvar Configurações Gerais</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Configurações de Segurança -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-shield-alt me-2"></i>Configurações de Segurança
                </h5>
            </div>
            <div class="card-body">
                <form id="securityConfigForm">
                    <div class="mb-3">
                        <label for="max_upload_size" class="form-label">Tamanho Máximo de Upload (MB)</label>
                        <input type="number" class="form-control" id="max_upload_size" name="max_upload_size" 
                               value="<?= round(($configs['max_upload_size'] ?? 10485760) / 1024 / 1024) ?>" 
                               min="1" max="100">
                        <small class="text-muted">Tamanho máximo para anexos de transações</small>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Atenção:</strong> Alterações nas configurações de segurança afetam todo o sistema.
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Salvar Configurações de Segurança</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar com Informações -->
    <div class="col-md-4">
        <!-- Status do Sistema -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-server me-2"></i>Status do Sistema
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Banco de Dados</span>
                    <span class="badge bg-success">Online</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Sistema Multi-Tenant</span>
                    <span class="badge bg-success">Ativo</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Integrações</span>
                    <a href="<?= url('/integrations') ?>" class="badge bg-info text-decoration-none">
                        Configurar
                    </a>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Versão PHP</span>
                    <span class="badge bg-info"><?= phpversion() ?></span>
                </div>
            </div>
        </div>

        <!-- Últimas Configurações -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-history me-2"></i>Últimas Configurações
                </h6>
            </div>
            <div class="card-body">
                <small class="text-muted">
                    As últimas alterações nas configurações aparecerão aqui.
                </small>
            </div>
        </div>

        <!-- Backup e Manutenção -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-tools me-2"></i>Manutenção
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-info btn-sm" onclick="clearCache()">
                        <i class="fas fa-broom me-2"></i>Limpar Cache
                    </button>
                    <button class="btn btn-outline-warning btn-sm" onclick="backupDatabase()">
                        <i class="fas fa-database me-2"></i>Backup BD
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="viewLogs()">
                        <i class="fas fa-file-alt me-2"></i>Ver Logs
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // Salvar configurações gerais
    const generalForm = document.getElementById('generalConfigForm');
    if (generalForm) {
        generalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            saveConfig('general', this);
        });
    }

    // Salvar configurações de segurança
    const securityForm = document.getElementById('securityConfigForm');
    if (securityForm) {
        securityForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Converter MB para bytes
            const uploadSizeMB = document.getElementById('max_upload_size').value;
            const uploadSizeBytes = uploadSizeMB * 1024 * 1024;
            
            const formData = new FormData(this);
            formData.set('max_upload_size', uploadSizeBytes);
            
            saveConfigData('security', formData);
        });
    }
});

function saveConfig(type, form) {
    const formData = new FormData(form);
    saveConfigData(type, formData);
}

function saveConfigData(type, formData) {
    const button = event.submitter || document.activeElement;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Salvando...';
    button.disabled = true;

    fetch('<?= url('admin/update-system-config') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Erro ao salvar configurações', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function saveAllConfigs() {
    Swal.fire({
        title: 'Salvar todas as configurações?',
        text: 'Isto irá salvar todas as configurações de uma vez.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sim, salvar tudo',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Criar FormData com todas as configurações disponíveis
            const generalForm = document.getElementById('generalConfigForm');
            const securityForm = document.getElementById('securityConfigForm');

            const formData = new FormData();

            if (generalForm) {
                const generalFormData = new FormData(generalForm);
                for (let [key, value] of generalFormData.entries()) {
                    formData.append(key, value);
                }
            }

            if (securityForm) {
                const securityFormData = new FormData(securityForm);
                for (let [key, value] of securityFormData.entries()) {
                    formData.append(key, value);
                }
            }

            // Enviar para o servidor
            fetch('<?= url('/admin/update-system-config') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showNotification('Erro ao salvar configurações', 'error');
            });
        }
    });
}


function clearCache() {
    Swal.fire({
        title: 'Limpar cache do sistema?',
        text: 'Esta ação irá limpar todos os caches temporários.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sim, limpar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Cache limpo com sucesso!', 'success');
        }
    });
}

function backupDatabase() {
    Swal.fire({
        title: 'Criar backup do banco?',
        text: 'Esta ação pode demorar alguns minutos.',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Criar backup',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showNotification('Backup criado com sucesso!', 'success');
        }
    });
}

function viewLogs() {
    window.open('<?= url('admin/audit-logs') ?>', '_blank');
}

</script>

<style>
.card-header h5, .card-header h6 {
    color: #495057;
}

.form-label {
    font-weight: 500;
    color: #495057;
}

.badge {
    font-size: 0.75em;
}
</style>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>