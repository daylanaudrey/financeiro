/**
 * Sistema de Notificações - Desktop e PWA
 * Suporta notificações nativas do browser e toast notifications
 */

class NotificationManager {
    constructor() {
        this.permission = Notification.permission || 'default';
        this.isSupported = 'Notification' in window;
        this.swRegistration = null;
        this.toastContainer = null;
        
        this.init();
    }
    
    async init() {
        // Criar container para toast notifications
        this.createToastContainer();
        
        // Verificar suporte e solicitar permissão
        if (this.isSupported) {
            await this.requestPermission();
        }
        
        // Registrar service worker se disponível
        if ('serviceWorker' in navigator) {
            try {
                this.swRegistration = await navigator.serviceWorker.ready;
                console.log('Service Worker pronto para notificações');
            } catch (error) {
                console.error('Erro ao registrar SW para notificações:', error);
            }
        }
    }
    
    createToastContainer() {
        if (!document.getElementById('toast-container')) {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
            this.toastContainer = container;
        } else {
            this.toastContainer = document.getElementById('toast-container');
        }
    }
    
    async requestPermission() {
        if (this.permission === 'default') {
            try {
                const result = await Notification.requestPermission();
                this.permission = result;
                
                if (result === 'granted') {
                    this.showToast('Notificações ativadas!', 'success');
                    console.log('Permissão para notificações concedida');
                } else if (result === 'denied') {
                    this.showToast('Notificações bloqueadas. Ative nas configurações do navegador.', 'warning');
                    console.log('Permissão para notificações negada');
                }
            } catch (error) {
                console.error('Erro ao solicitar permissão:', error);
            }
        }
        
        return this.permission;
    }
    
    /**
     * Mostrar notificação nativa (desktop/PWA)
     */
    async showNotification(title, options = {}) {
        // Verificar permissão
        if (this.permission !== 'granted') {
            const permission = await this.requestPermission();
            if (permission !== 'granted') {
                console.warn('Notificações não permitidas');
                // Fallback para toast
                this.showToast(title + (options.body ? ': ' + options.body : ''), 'info');
                return null;
            }
        }
        
        // Configurações padrão
        const defaultOptions = {
            icon: '/financeiro/assets/icons/icon-192x192.png',
            badge: '/financeiro/assets/icons/icon-72x72.png',
            vibrate: [200, 100, 200],
            requireInteraction: false,
            silent: false,
            tag: 'financeiro-' + Date.now(),
            timestamp: Date.now(),
            data: {}
        };
        
        const notificationOptions = { ...defaultOptions, ...options };
        
        try {
            let notification;
            
            // Usar service worker se disponível (para PWA)
            if (this.swRegistration && this.swRegistration.showNotification) {
                await this.swRegistration.showNotification(title, notificationOptions);
                console.log('Notificação enviada via Service Worker');
            } else {
                // Fallback para API de Notificação do browser
                notification = new Notification(title, notificationOptions);
                
                // Adicionar event listeners
                notification.onclick = () => {
                    window.focus();
                    notification.close();
                    if (notificationOptions.data.url) {
                        window.location.href = notificationOptions.data.url;
                    }
                };
                
                notification.onerror = (error) => {
                    console.error('Erro na notificação:', error);
                    // Fallback para toast
                    this.showToast(title + (options.body ? ': ' + options.body : ''), 'warning');
                };
            }
            
            return notification;
        } catch (error) {
            console.error('Erro ao mostrar notificação:', error);
            // Fallback para toast
            this.showToast(title + (options.body ? ': ' + options.body : ''), 'warning');
            return null;
        }
    }
    
    /**
     * Mostrar toast notification (visual no app)
     */
    showToast(message, type = 'info', duration = 5000) {
        const toastId = 'toast-' + Date.now();
        
        const toastHTML = `
            <div id="${toastId}" class="toast align-items-center text-white bg-${this.getToastColor(type)} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas ${this.getToastIcon(type)} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        this.toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: duration
        });
        
        toast.show();
        
        // Remover do DOM após esconder
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
        
        return toast;
    }
    
    getToastColor(type) {
        const colors = {
            success: 'success',
            error: 'danger',
            warning: 'warning',
            info: 'info',
            primary: 'primary'
        };
        return colors[type] || 'secondary';
    }
    
    getToastIcon(type) {
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle',
            primary: 'fa-bell'
        };
        return icons[type] || 'fa-bell';
    }
    
    /**
     * Notificações específicas do sistema
     */
    
    // Notificar novo lançamento
    notifyNewTransaction(transaction) {
        const isIncome = transaction.kind === 'entrada';
        const icon = isIncome ? '💰' : '💸';
        const title = isIncome ? 'Nova Receita' : 'Nova Despesa';
        
        this.showNotification(title, {
            body: `${transaction.description} - R$ ${transaction.value}`,
            icon: isIncome ? '/financeiro/assets/icons/income.png' : '/financeiro/assets/icons/expense.png',
            tag: `transaction-${transaction.id}`,
            data: {
                url: `/financeiro/transactions?id=${transaction.id}`,
                transactionId: transaction.id
            }
        });
        
        // Também mostrar toast
        this.showToast(
            `${icon} ${transaction.description} - R$ ${transaction.value}`,
            isIncome ? 'success' : 'warning'
        );
    }
    
    // Notificar vencimento próximo
    notifyUpcomingDue(transaction) {
        const daysUntilDue = transaction.daysUntilDue;
        const urgency = daysUntilDue <= 1 ? 'danger' : daysUntilDue <= 3 ? 'warning' : 'info';
        
        this.showNotification('⏰ Vencimento Próximo', {
            body: `${transaction.description} vence ${daysUntilDue === 0 ? 'hoje' : `em ${daysUntilDue} dias`} - R$ ${transaction.value}`,
            requireInteraction: daysUntilDue <= 1,
            tag: `due-${transaction.id}`,
            data: {
                url: `/financeiro/transactions?id=${transaction.id}`,
                transactionId: transaction.id
            }
        });
        
        this.showToast(
            `⏰ ${transaction.description} vence ${daysUntilDue === 0 ? 'hoje!' : `em ${daysUntilDue} dias`}`,
            urgency,
            daysUntilDue <= 1 ? 10000 : 5000
        );
    }
    
    // Notificar meta atingida
    notifyGoalReached(vault) {
        this.showNotification('🎯 Meta Atingida!', {
            body: `Parabéns! Você atingiu a meta de ${vault.name}`,
            icon: '/financeiro/assets/icons/goal.png',
            requireInteraction: true,
            tag: `goal-${vault.id}`,
            data: {
                url: `/financeiro/vaults?id=${vault.id}`,
                vaultId: vault.id
            }
        });
        
        this.showToast(
            `🎯 Parabéns! Meta de ${vault.name} atingida!`,
            'success',
            10000
        );
    }
    
    // Notificar saldo baixo
    notifyLowBalance(account) {
        this.showNotification('⚠️ Saldo Baixo', {
            body: `A conta ${account.name} está com saldo de R$ ${account.balance}`,
            requireInteraction: true,
            tag: `low-balance-${account.id}`,
            data: {
                url: `/financeiro/accounts?id=${account.id}`,
                accountId: account.id
            }
        });
        
        this.showToast(
            `⚠️ Saldo baixo em ${account.name}: R$ ${account.balance}`,
            'warning',
            8000
        );
    }
    
    /**
     * Agendar notificação
     */
    async scheduleNotification(title, options, delay) {
        setTimeout(() => {
            this.showNotification(title, options);
        }, delay);
    }
    
    /**
     * Verificar e mostrar notificações pendentes
     */
    async checkPendingNotifications() {
        try {
            const response = await fetch('/financeiro/api/notifications/pending');
            const data = await response.json();
            
            if (data.success && data.notifications) {
                for (const notification of data.notifications) {
                    if (notification.type === 'transaction_due') {
                        this.notifyUpcomingDue(notification.data);
                    } else if (notification.type === 'low_balance') {
                        this.notifyLowBalance(notification.data);
                    } else {
                        this.showNotification(notification.title, notification.options);
                    }
                }
            }
        } catch (error) {
            console.error('Erro ao verificar notificações pendentes:', error);
        }
    }
    
    /**
     * Configurar verificação periódica
     */
    startPeriodicCheck(interval = 300000) { // 5 minutos por padrão
        // Verificar imediatamente
        this.checkPendingNotifications();
        
        // Configurar intervalo
        setInterval(() => {
            this.checkPendingNotifications();
        }, interval);
    }
}

// Inicializar automaticamente quando o DOM carregar
let notificationManager;

document.addEventListener('DOMContentLoaded', () => {
    notificationManager = new NotificationManager();
    
    // Exportar para uso global APÓS a criação
    window.notificationManager = notificationManager;
    
    // Iniciar verificação periódica se estiver logado
    if (document.body.dataset.userLoggedIn === 'true') {
        notificationManager.startPeriodicCheck();
    }
    
    console.log('NotificationManager inicializado e disponível globalmente');
});

// Exportar classe para uso global
window.NotificationManager = NotificationManager;