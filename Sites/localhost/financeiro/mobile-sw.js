const CACHE_NAME = 'financeiro-mobile-v1.0.2';
const OFFLINE_URL = './mobile';

// Recursos essenciais para cache
const STATIC_CACHE_URLS = [
  './mobile',
  './assets/css/style.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
  'https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
  'https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.js'
];

// Install event - cache recursos estáticos
self.addEventListener('install', (event) => {
  console.log('[SW] Installing Service Worker');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[SW] Caching static resources');
        return cache.addAll(STATIC_CACHE_URLS);
      })
      .then(() => {
        console.log('[SW] Static resources cached successfully');
        return self.skipWaiting();
      })
      .catch((error) => {
        console.error('[SW] Failed to cache static resources:', error);
      })
  );
});

// Activate event - limpar caches antigos
self.addEventListener('activate', (event) => {
  console.log('[SW] Activating Service Worker');
  
  event.waitUntil(
    caches.keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => {
            if (cacheName !== CACHE_NAME) {
              console.log('[SW] Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        console.log('[SW] Service Worker activated');
        return self.clients.claim();
      })
  );
});

// Fetch event - estratégia de cache
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Ignorar requisições que não são relevantes
  if (url.protocol !== 'https:' && url.protocol !== 'http:') {
    return;
  }
  
  // Para a página mobile, usar cache first
  if (url.pathname.endsWith('/mobile') || url.pathname === './mobile') {
    event.respondWith(
      caches.match(request)
        .then((response) => {
          if (response) {
            console.log('[SW] Serving from cache:', url.pathname);
            return response;
          }
          
          console.log('[SW] Fetching from network:', url.pathname);
          return fetch(request)
            .then((response) => {
              if (response && response.status === 200) {
                const responseClone = response.clone();
                caches.open(CACHE_NAME)
                  .then((cache) => {
                    cache.put(request, responseClone);
                  });
              }
              return response;
            })
            .catch(() => {
              console.log('[SW] Network failed, serving offline page');
              return caches.match(OFFLINE_URL);
            });
        })
    );
    return;
  }
  
  // Para APIs, tentar network first
  if (url.pathname.includes('/api/')) {
    event.respondWith(
      fetch(request)
        .then((response) => {
          console.log('[SW] API request successful:', url.pathname);
          return response;
        })
        .catch((error) => {
          console.log('[SW] API request failed:', url.pathname, error);
          
          // Para GET requests, tentar cache
          if (request.method === 'GET') {
            return caches.match(request)
              .then((response) => {
                if (response) {
                  console.log('[SW] Serving API from cache:', url.pathname);
                  return response;
                }
                
                // Retornar resposta offline genérica
                return new Response(
                  JSON.stringify({
                    success: false,
                    message: 'Sem conexão. Tente novamente quando estiver online.',
                    offline: true
                  }),
                  {
                    status: 503,
                    statusText: 'Service Unavailable',
                    headers: {
                      'Content-Type': 'application/json'
                    }
                  }
                );
              });
          }
          
          // Para outros métodos, retornar erro
          return new Response(
            JSON.stringify({
              success: false,
              message: 'Operação requer conexão com internet.',
              offline: true
            }),
            {
              status: 503,
              statusText: 'Service Unavailable',
              headers: {
                'Content-Type': 'application/json'
              }
            }
          );
        })
    );
    return;
  }
  
  // Para recursos estáticos, cache first
  if (url.pathname.includes('/assets/') || 
      url.hostname === 'cdn.jsdelivr.net' || 
      url.hostname === 'cdnjs.cloudflare.com') {
    event.respondWith(
      caches.match(request)
        .then((response) => {
          if (response) {
            console.log('[SW] Serving static resource from cache:', url.pathname);
            return response;
          }
          
          return fetch(request)
            .then((response) => {
              if (response && response.status === 200) {
                const responseClone = response.clone();
                caches.open(CACHE_NAME)
                  .then((cache) => {
                    cache.put(request, responseClone);
                  });
              }
              return response;
            });
        })
    );
    return;
  }
  
  // Para outras requisições, network first
  event.respondWith(
    fetch(request)
      .catch(() => {
        return caches.match(request);
      })
  );
});

// Background sync para quando voltar online
self.addEventListener('sync', (event) => {
  console.log('[SW] Background sync triggered:', event.tag);
  
  if (event.tag === 'background-sync') {
    event.waitUntil(
      // Aqui podemos implementar sincronização de dados offline
      console.log('[SW] Performing background sync')
    );
  }
});

// Push notifications (para implementação futura)
self.addEventListener('push', (event) => {
  console.log('[SW] Push notification received');
  
  const options = {
    body: event.data ? event.data.text() : 'Nova notificação do Sistema Financeiro',
    icon: '/financeiro/assets/icons/icon-192x192.png',
    badge: '/financeiro/assets/icons/icon-96x96.png',
    vibrate: [200, 100, 200],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'explore',
        title: 'Abrir App',
        icon: '/financeiro/assets/icons/icon-192x192.png'
      },
      {
        action: 'close',
        title: 'Fechar',
        icon: '/financeiro/assets/icons/icon-192x192.png'
      }
    ]
  };
  
  event.waitUntil(
    self.registration.showNotification('Sistema Financeiro', options)
  );
});

// Notification click
self.addEventListener('notificationclick', (event) => {
  console.log('[SW] Notification click received');
  
  event.notification.close();
  
  if (event.action === 'explore') {
    event.waitUntil(
      clients.openWindow('./mobile')
    );
  }
});