/**
 * Sistema Aduaneiro
 * Scripts JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {

    // Inicializar tooltips do Bootstrap
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // Confirmar exclusão com modal Bootstrap
    const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            const itemName = this.getAttribute('data-item-name') || 'este item';
            const deleteUrl = this.getAttribute('href') || this.getAttribute('data-delete-url');

            // Criar modal de confirmação
            showDeleteConfirmModal(itemName, deleteUrl);
        });
    });

    // Máscaras para campos
    const cnpjFields = document.querySelectorAll('.cnpj-mask');
    cnpjFields.forEach(field => {
        field.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 14) {
                value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
            }
            e.target.value = value;
        });
    });

    const ncmFields = document.querySelectorAll('.ncm-mask');
    ncmFields.forEach(field => {
        field.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 8) {
                value = value.replace(/^(\d{4})(\d)/, '$1.$2');
                value = value.replace(/^(\d{4})\.(\d{2})(\d)/, '$1.$2.$3');
            }
            e.target.value = value;
        });
    });

    // Format currency
    const currencyFields = document.querySelectorAll('.currency-mask');
    currencyFields.forEach(field => {
        field.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = (parseFloat(value) / 100).toFixed(2);
            value = value.replace('.', ',');
            value = 'R$ ' + value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            e.target.value = value;
        });
    });

});

/**
 * Mostrar modal de confirmação de exclusão
 */
function showDeleteConfirmModal(itemName, deleteUrl) {
    // Remover modal existente se houver
    const existingModal = document.getElementById('deleteConfirmModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Criar HTML do modal
    const modalHTML = `
        <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar Exclusão</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Deseja realmente excluir <strong>${itemName}</strong>?</p>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            Esta ação não pode ser desfeita.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                            <i class="bi bi-trash"></i> Excluir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Adicionar modal ao body
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();

    // Configurar botão de confirmação
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        // Criar form para POST
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = deleteUrl;

        // Adicionar CSRF token se existir
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'csrf_token';
            input.value = csrfToken.content;
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
    });
}