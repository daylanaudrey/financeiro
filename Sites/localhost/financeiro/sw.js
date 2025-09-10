// Service Worker para Sistema Financeiro PWA
const CACHE_NAME = 'financeiro-v1.0.1';
const CACHE_STATIC = 'financeiro-static-v1.0.1';
const CACHE_DYNAMIC = 'financeiro-dynamic-v1.0.1';

// Arquivos para cache inicial - apenas recursos externos estáticos
const STATIC_FILES = [
  '/',
  '/offline.html',
  // Bootstrap CSS
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
  // Bootstrap JS
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
  // Font Awesome
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
  // SweetAlert2
  'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js',
  // Chart.js
  'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js'
];

// URLs que sempre devem buscar da rede
const NETWORK_ONLY = [
  '/api/',
  '/auth/'
];

// URLs que podem usar cache primeiro
const CACHE_FIRST = [
  '/assets/',
  '.css',
  '.js',
  '.png',
  '.jpg',
  '.jpeg',
  '.gif',
  '.svg',
  '.ico',
  '.woff',
  '.woff2'
];

// Instalar Service Worker
self.addEventListener('install', event => {
  console.log('[SW] Instalando Service Worker...');
  
  event.waitUntil(
    caches.open(CACHE_STATIC)
      .then(cache => {
        console.log('[SW] Fazendo cache dos arquivos estáticos...');
        // Tentar cachear arquivos individualmente para evitar que um erro impeça todos
        return Promise.allSettled(
          STATIC_FILES.map(file => 
            cache.add(file).catch(error => {
              console.warn(`[SW] Falha ao cachear ${file}:`, error);
              return null;
            })
          )
        );
      })
      .then(() => {
        console.log('[SW] Cache inicial processado');
        return self.skipWaiting();
      })
      .catch(error => {
        console.error('[SW] Erro no processo de instalação:', error);
        // Continuar mesmo com erro para não impedir o PWA
        return self.skipWaiting();
      })
  );
});

// Ativar Service Worker
self.addEventListener('activate', event => {
  console.log('[SW] Ativando Service Worker...');
  
  event.waitUntil(
    caches.keys()
      .then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => {
            if (cacheName !== CACHE_STATIC && cacheName !== CACHE_DYNAMIC) {
              console.log('[SW] Removendo cache antigo:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        console.log('[SW] Service Worker ativado');
        return self.clients.claim();
      })
  );
});

// Interceptar requisições
self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);
  
  // Ignorar requisições não HTTP/HTTPS
  if (!event.request.url.startsWith('http')) {
    return;
  }
  
  // Network Only - sempre buscar da rede (APIs, autenticação)
  if (NETWORK_ONLY.some(path => url.pathname.includes(path))) {
    event.respondWith(
      networkOnly(event.request)
    );
    return;
  }
  
  // Cache First - recursos estáticos
  if (CACHE_FIRST.some(pattern => {
    return url.pathname.includes(pattern) || event.request.url.includes(pattern);
  })) {
    event.respondWith(
      cacheFirst(event.request)
    );
    return;
  }
  
  // Network First - páginas e conteúdo dinâmico
  event.respondWith(
    networkFirst(event.request)
  );
});

// Estratégia: Network Only
function networkOnly(request) {
  return fetch(request)
    .catch(error => {
      console.log('[SW] Erro na rede (Network Only):', error);
      throw error;
    });
}

// Estratégia: Cache First
function cacheFirst(request) {
  return caches.match(request)
    .then(response => {
      if (response) {
        console.log('[SW] Servindo do cache:', request.url);
        return response;
      }
      
      return fetch(request)
        .then(fetchResponse => {
          // Só cachear respostas válidas
          if (!fetchResponse || fetchResponse.status !== 200 || fetchResponse.type !== 'basic') {
            return fetchResponse;
          }
          
          const responseClone = fetchResponse.clone();
          caches.open(CACHE_DYNAMIC)
            .then(cache => {
              cache.put(request, responseClone);
            });
          
          return fetchResponse;
        })
        .catch(error => {
          console.log('[SW] Erro ao buscar recurso:', request.url, error);
          // Retornar uma resposta padrão para recursos críticos
          if (request.url.includes('.css')) {
            return new Response('/* Offline CSS */', {
              headers: {'Content-Type': 'text/css'}
            });
          }
          if (request.url.includes('.js')) {
            return new Response('/* Offline JS */', {
              headers: {'Content-Type': 'application/javascript'}
            });
          }
          throw error;
        });
    });
}

// Estratégia: Network First
function networkFirst(request) {
  return fetch(request)
    .then(response => {
      // Só cachear respostas válidas
      if (!response || response.status !== 200) {
        return response;
      }
      
      const responseClone = response.clone();
      caches.open(CACHE_DYNAMIC)
        .then(cache => {
          cache.put(request, responseClone);
        });
      
      return response;
    })
    .catch(error => {
      console.log('[SW] Tentando servir do cache:', request.url);
      
      return caches.match(request)
        .then(response => {
          if (response) {
            console.log('[SW] Servindo do cache (offline):', request.url);
            return response;
          }
          
          // Se for uma navegação e não temos cache, mostrar página offline
          if (request.mode === 'navigate') {
            return caches.match('/offline.html');
          }
          
          throw error;
        });
    });
}

// Sincronização em background
self.addEventListener('sync', event => {
  console.log('[SW] Evento de sincronização:', event.tag);
  
  if (event.tag === 'sync-transactions') {
    event.waitUntil(
      syncOfflineTransactions()
    );
  }
});

// Função para sincronizar transações offline
async function syncOfflineTransactions() {
  try {
    // Buscar transações pendentes no IndexedDB ou localStorage
    const offlineTransactions = await getOfflineTransactions();
    
    for (const transaction of offlineTransactions) {
      try {
        const response = await fetch('/api/transactions/create', {
          method: 'POST',
          body: transaction.data
        });
        
        if (response.ok) {
          await removeOfflineTransaction(transaction.id);
          console.log('[SW] Transação sincronizada:', transaction.id);
        }
      } catch (error) {
        console.log('[SW] Erro ao sincronizar transação:', transaction.id, error);
      }
    }
  } catch (error) {
    console.error('[SW] Erro na sincronização:', error);
  }
}

// Funções auxiliares para transações offline (implementar com IndexedDB)
async function getOfflineTransactions() {
  // TODO: Implementar busca no IndexedDB
  return [];
}

async function removeOfflineTransaction(id) {
  // TODO: Implementar remoção no IndexedDB
  console.log('Removendo transação offline:', id);
}

// Notificações Push (para futuras funcionalidades)
self.addEventListener('push', event => {
  if (!event.data) return;
  
  const data = event.data.json();
  const options = {
    body: data.body,
    icon: '/assets/icons/icon-192x192.png',
    badge: '/assets/icons/badge-72x72.png',
    tag: data.tag || 'general',
    actions: data.actions || [],
    requireInteraction: data.requireInteraction || false
  };
  
  event.waitUntil(
    self.registration.showNotification(data.title, options)
  );
});

// Cliques em notificações
self.addEventListener('notificationclick', event => {
  event.notification.close();
  
  const action = event.action;
  const data = event.notification.data || {};
  
  event.waitUntil(
    clients.openWindow(data.url || '/')
  );
});

console.log('[SW] Service Worker carregado');