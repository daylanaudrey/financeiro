<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3">
                        <i class="bi bi-geo-alt"></i> <?= $action === 'create' ? 'Cadastrar Porto' : 'Editar Porto' ?>
                    </h1>
                    <p class="text-muted">Preencha os dados do porto</p>
                </div>
                <div>
                    <a href="<?= BASE_URL ?>ports" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensagens -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle"></i> <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Formulário -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-form"></i> Dados do Porto
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>api/ports/<?= $action === 'create' ? 'create' : 'update' ?>" method="POST">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="id" value="<?= $port['id'] ?>">
                        <?php endif; ?>

                        <div class="row g-3">
                            <!-- Nome do Porto -->
                            <div class="col-md-6">
                                <label for="name" class="form-label">
                                    <i class="bi bi-geo-alt"></i> Nome do Porto *
                                </label>
                                <input type="text" class="form-control" id="name" name="name"
                                       value="<?= htmlspecialchars($port['name'] ?? '') ?>"
                                       placeholder="Ex: Porto de Santos" required>
                                <div class="form-text">Nome completo do porto</div>
                            </div>

                            <!-- Prefixo -->
                            <div class="col-md-3">
                                <label for="prefix" class="form-label">
                                    <i class="bi bi-tag"></i> Prefixo *
                                </label>
                                <input type="text" class="form-control text-uppercase" id="prefix" name="prefix"
                                       value="<?= htmlspecialchars($port['prefix'] ?? '') ?>"
                                       placeholder="Ex: SSZ, PNG" maxlength="10" required>
                                <div class="form-text">Código único do porto (máx. 10 caracteres)</div>
                            </div>

                            <!-- Código de Recinto Alfandegário -->
                            <div class="col-md-3">
                                <label for="customs_code" class="form-label">
                                    <i class="bi bi-shield-check"></i> Código Recinto
                                </label>
                                <input type="text" class="form-control" id="customs_code" name="customs_code"
                                       value="<?= htmlspecialchars($port['customs_code'] ?? '') ?>"
                                       placeholder="Ex: 7811501" maxlength="20">
                                <div class="form-text">Código do recinto alfandegário</div>
                            </div>

                            <!-- Cidade -->
                            <div class="col-md-6">
                                <label for="city" class="form-label">
                                    <i class="bi bi-building"></i> Cidade *
                                </label>
                                <input type="text" class="form-control" id="city" name="city"
                                       value="<?= htmlspecialchars($port['city'] ?? '') ?>"
                                       placeholder="Ex: Santos" required>
                            </div>

                            <!-- Estado -->
                            <div class="col-md-6">
                                <label for="state" class="form-label">
                                    <i class="bi bi-map"></i> Estado
                                </label>
                                <input type="text" class="form-control" id="state" name="state"
                                       value="<?= htmlspecialchars($port['state'] ?? '') ?>"
                                       placeholder="Ex: São Paulo">
                            </div>

                            <!-- País -->
                            <div class="col-md-6">
                                <label for="country" class="form-label">
                                    <i class="bi bi-globe"></i> País
                                </label>
                                <input type="text" class="form-control" id="country" name="country"
                                       value="<?= htmlspecialchars($port['country'] ?? 'Brasil') ?>"
                                       placeholder="Brasil">
                            </div>

                            <!-- Status -->
                            <div class="col-md-6">
                                <label for="is_active" class="form-label">
                                    <i class="bi bi-power"></i> Status
                                </label>
                                <select class="form-select select2" id="is_active" name="is_active">
                                    <option value="1" <?= ($port['is_active'] ?? true) ? 'selected' : '' ?>>Ativo</option>
                                    <option value="0" <?= isset($port['is_active']) && !$port['is_active'] ? 'selected' : '' ?>>Inativo</option>
                                </select>
                                <div class="form-text">Portos inativos não aparecem nas listas de seleção</div>
                            </div>


                            <!-- Observações -->
                            <div class="col-12">
                                <label for="notes" class="form-label">
                                    <i class="bi bi-chat-text"></i> Observações
                                </label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"
                                          placeholder="Informações adicionais sobre o porto..."><?= htmlspecialchars($port['notes'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="<?= BASE_URL ?>ports" class="btn btn-secondary">
                                        <i class="bi bi-x-circle"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i>
                                        <?= $action === 'create' ? 'Cadastrar Porto' : 'Atualizar Porto' ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.pendingScripts = window.pendingScripts || [];
window.pendingScripts.push(function() {
    // Inicializar Select2 específico desta página
    $('.select2').select2({
        theme: 'bootstrap-5',
        allowClear: false
    });
});

// Converter prefixo para maiúsculo automaticamente
document.getElementById('prefix').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});

</script>