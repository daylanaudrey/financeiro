<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3">
                        <i class="bi bi-trash"></i> Limpeza de Logs de Auditoria
                    </h1>
                    <p class="text-muted">Remover logs antigos para otimizar o banco de dados</p>
                </div>
                <div>
                    <a href="<?= BASE_URL ?>audit" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerta de Aviso -->
    <div class="alert alert-warning" role="alert">
        <h5 class="alert-heading">
            <i class="bi bi-exclamation-triangle"></i> Atenção!
        </h5>
        <p>Esta operação irá <strong>remover permanentemente</strong> todos os logs de auditoria mais antigos que o período especificado.</p>
        <hr>
        <p class="mb-0">
            <i class="bi bi-info-circle"></i>
            <strong>Esta ação não pode ser desfeita.</strong> Certifique-se de que não precisará desses logs no futuro.
        </p>
    </div>

    <!-- Formulário de Limpeza -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-gear"></i> Configurações de Limpeza
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>audit/cleanup" id="cleanupForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="days_to_keep" class="form-label">
                                <i class="bi bi-calendar"></i> Manter logs dos últimos (dias):
                            </label>
                            <select name="days_to_keep" id="days_to_keep" class="form-select" required>
                                <option value="30">30 dias (1 mês)</option>
                                <option value="90">90 dias (3 meses)</option>
                                <option value="180">180 dias (6 meses)</option>
                                <option value="365" selected>365 dias (1 ano)</option>
                                <option value="730">730 dias (2 anos)</option>
                                <option value="1095">1.095 dias (3 anos)</option>
                            </select>
                            <div class="form-text">
                                Logs mais antigos que este período serão removidos permanentemente.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-info-circle"></i> Informações:
                            </label>
                            <div class="bg-light p-3 rounded">
                                <small class="text-muted">
                                    <strong>Data de corte:</strong> <span id="cutoffDate"></span><br>
                                    <strong>Logs a manter:</strong> Criados após a data de corte<br>
                                    <strong>Logs a remover:</strong> Criados antes da data de corte
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmCleanupModal">
                                    <i class="bi bi-trash"></i> Executar Limpeza
                                </button>
                            </div>
                            <div>
                                <small class="text-muted">
                                    <i class="bi bi-shield-check"></i>
                                    Esta operação será registrada no log de auditoria.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Recomendações -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-lightbulb"></i> Recomendações
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="bi bi-check-circle text-success"></i> Boas Práticas:</h6>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-arrow-right"></i> Mantenha pelo menos 1 ano de logs</li>
                        <li><i class="bi bi-arrow-right"></i> Execute limpeza periodicamente</li>
                        <li><i class="bi bi-arrow-right"></i> Considere exportar logs importantes</li>
                        <li><i class="bi bi-arrow-right"></i> Monitore o crescimento do banco</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6><i class="bi bi-exclamation-circle text-warning"></i> Cuidados:</h6>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-arrow-right"></i> Verifique requisitos legais</li>
                        <li><i class="bi bi-arrow-right"></i> Considere políticas de retenção</li>
                        <li><i class="bi bi-arrow-right"></i> Faça backup se necessário</li>
                        <li><i class="bi bi-arrow-right"></i> Teste em ambiente de desenvolvimento</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação -->
<div class="modal fade" id="confirmCleanupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-warning"></i> Confirmar Limpeza
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <strong>Esta ação não pode ser desfeita!</strong>
                </div>
                <p>Você está prestes a remover permanentemente todos os logs de auditoria mais antigos que <strong><span id="modalDaysToKeep"></span> dias</strong>.</p>
                <p>Logs criados antes de <strong><span id="modalCutoffDate"></span></strong> serão removidos.</p>
                <p>Deseja realmente continuar?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" onclick="submitCleanup()">
                    <i class="bi bi-trash"></i> Confirmar Limpeza
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const daysSelect = document.getElementById('days_to_keep');
    const cutoffDateSpan = document.getElementById('cutoffDate');
    const modalDaysSpan = document.getElementById('modalDaysToKeep');
    const modalCutoffSpan = document.getElementById('modalCutoffDate');

    function updateCutoffDate() {
        const days = parseInt(daysSelect.value);
        const cutoffDate = new Date();
        cutoffDate.setDate(cutoffDate.getDate() - days);

        const formattedDate = cutoffDate.toLocaleDateString('pt-BR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        cutoffDateSpan.textContent = formattedDate;
        modalDaysSpan.textContent = days;
        modalCutoffSpan.textContent = formattedDate;
    }

    daysSelect.addEventListener('change', updateCutoffDate);
    updateCutoffDate(); // Initial call
});

function submitCleanup() {
    document.getElementById('cleanupForm').submit();
}
</script>