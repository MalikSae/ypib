const CACHE_NAME = 'pwa-cache-v1';
const PRECACHE_ASSETS = [
    '/offline.html',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/icon-192-maskable.png',
    '/icons/icon-512-maskable.png',
    '/manifest-public.json',
    '/manifest-admin.json',
    '/manifest-panitia.json'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(PRECACHE_ASSETS);
        })
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.filter(name => name !== CACHE_NAME).map(name => caches.delete(name))
            );
        })
    );
});

self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);
    const path = url.pathname;
    
    // Skip dynamic and auth routes
    if (path.includes('/livewire') || path.includes('/admin') || path.includes('/panitia') || path.includes('/pendaftaran') || path.includes('/afiliasi')) {
        return;
    }

    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => {
                return caches.match('/offline.html');
            })
        );
        return;
    }

    if (path.startsWith('/build/') || path.startsWith('/images/') || path.startsWith('/fonts/') || path.startsWith('/icons/')) {
        event.respondWith(
            caches.match(event.request).then(cachedResponse => {
                const fetchPromise = fetch(event.request).then(networkResponse => {
                    if (networkResponse && networkResponse.status === 200 && (networkResponse.type === 'basic' || networkResponse.type === 'cors')) {
                        const responseToCache = networkResponse.clone();
                        caches.open(CACHE_NAME).then(cache => {
                            try {
                                cache.put(event.request, responseToCache);
                            } catch (error) {
                                console.warn('[SW] Cache put failed', error);
                            }
                        });
                    }
                    return networkResponse;
                }).catch(() => {});
                
                return cachedResponse || fetchPromise;
            })
        );
    }
});
