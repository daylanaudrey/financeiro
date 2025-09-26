/**
 * Exemplos de uso do sistema de notificações
 * 
 * Para usar, adicione este script após notifications.js
 * e chame as funções quando necessário
 */

// Aguardar o sistema de notificações estar pronto
document.addEventListener('DOMContentLoaded', function() {
    // Aguardar um pouco para garantir que o notificationManager foi inicializado
    setTimeout(function() {
        if (window.notificationManager) {
            console.log('Sistema de notificações pronto!');
            
            // Exemplos automáticos (descomente para testar)
            // testNotifications();
        }
    }, 1000);
});

/**
 * Função para testar todas as notificações
 */
function testNotifications() {
    if (!window.notificationManager) {
        console.error('Sistema de notificações não disponível');
        return;
    }
    
    const nm = window.notificationManager;
    
    // Teste 1: Toast simples
    setTimeout(() => {
        nm.showToast('💰 Esta é uma notificação de teste!', 'info');
    }, 1000);
    
    // Teste 2: Notificação nativa
    setTimeout(() => {
        nm.showNotification('🔔 Teste de Notificação', {
            body: 'Esta é uma notificação nativa do browser/PWA',
            icon: '/financeiro/assets/icons/icon-192x192.png'
        });
    }, 3000);
    
    // Teste 3: Simular nova receita
    setTimeout(() => {
        nm.notifyNewTransaction({
            id: 123,
            kind: 'entrada',
            description: 'Salário',
            value: 'R$ 5.000,00'
        });
    }, 5000);
    
    // Teste 4: Simular nova despesa
    setTimeout(() => {
        nm.notifyNewTransaction({
            id: 124,
            kind: 'saida',
            description: 'Aluguel',
            value: 'R$ 1.200,00'
        });
    }, 7000);
    
    // Teste 5: Vencimento próximo
    setTimeout(() => {
        nm.notifyUpcomingDue({
            id: 125,
            description: 'Cartão de Crédito',
            value: 'R$ 800,00',
            daysUntilDue: 1
        });
    }, 9000);
    
    // Teste 6: Meta atingida
    setTimeout(() => {
        nm.notifyGoalReached({
            id: 126,
            name: 'Viagem de Férias'
        });
    }, 11000);
    
    // Teste 7: Saldo baixo
    setTimeout(() => {
        nm.notifyLowBalance({
            id: 127,
            name: 'Conta Corrente',
            balance: 'R$ 50,00'
        });
    }, 13000);
}

/**
 * Integração com formulários de transação
 */

// Função para notificar após salvar receita
function notifyIncomeCreated(transactionData) {
    if (window.notificationManager && transactionData) {
        window.notificationManager.notifyNewTransaction({
            id: transactionData.id,
            kind: 'entrada',
            description: transactionData.description || transactionData.descricao,
            value: transactionData.formattedValue || transactionData.valor
        });
    }
}

// Função para notificar após salvar despesa
function notifyExpenseCreated(transactionData) {
    if (window.notificationManager && transactionData) {
        window.notificationManager.notifyNewTransaction({
            id: transactionData.id,
            kind: 'saida',
            description: transactionData.description || transactionData.descricao,
            value: transactionData.formattedValue || transactionData.valor
        });
    }
}

// Função para notificar confirmação de lançamento
function notifyTransactionConfirmed(transactionData) {
    if (window.notificationManager && transactionData) {
        const isIncome = transactionData.kind === 'entrada';
        const icon = isIncome ? '✅' : '❌';
        const action = isIncome ? 'Receita confirmada' : 'Despesa confirmada';
        
        window.notificationManager.showToast(
            `${icon} ${action}: ${transactionData.description}`,
            isIncome ? 'success' : 'info'
        );
    }
}

// Função para verificar vencimentos próximos
function checkUpcomingDues() {
    if (window.notificationManager) {
        // Esta função seria chamada periodicamente ou ao carregar a página
        fetch('/financeiro/api/transactions/upcoming-dues')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.transactions) {
                    data.transactions.forEach(transaction => {
                        window.notificationManager.notifyUpcomingDue(transaction);
                    });
                }
            })
            .catch(error => {
                console.error('Erro ao verificar vencimentos:', error);
            });
    }
}

// Função para mostrar notificação de boas-vindas
function showWelcomeNotification() {
    if (window.notificationManager) {
        window.notificationManager.showToast(
            '👋 Bem-vindo ao Sistema Financeiro! Notificações ativadas.',
            'success',
            4000
        );
    }
}

// Função para solicitar permissão de notificações
function requestNotificationPermission() {
    if (window.notificationManager) {
        window.notificationManager.requestPermission()
            .then(permission => {
                if (permission === 'granted') {
                    showWelcomeNotification();
                } else {
                    window.notificationManager.showToast(
                        '🔔 Ative as notificações para receber alertas importantes!',
                        'warning',
                        6000
                    );
                }
            });
    }
}

// Expor funções globalmente
window.notifyIncomeCreated = notifyIncomeCreated;
window.notifyExpenseCreated = notifyExpenseCreated;
window.notifyTransactionConfirmed = notifyTransactionConfirmed;
window.checkUpcomingDues = checkUpcomingDues;
window.showWelcomeNotification = showWelcomeNotification;
window.requestNotificationPermission = requestNotificationPermission;
window.testNotifications = testNotifications;