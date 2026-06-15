const CACHE = 'ksa-v1';
const PRECACHE = ['/dashboard'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE).then(cache => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Only handle GET, skip cross-origin, skip API/form POSTs
    if (request.method !== 'GET') return;
    if (!request.url.startsWith(self.location.origin)) return;

    // Network-first for HTML pages (always fresh data)
    if (request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(
            fetch(request)
                .then(response => {
                    const clone = response.clone();
                    caches.open(CACHE).then(cache => cache.put(request, clone));
                    return response;
                })
                .catch(() => caches.match(request))
        );
        return;
    }

    // Cache-first for assets (JS, CSS, fonts, images)
    event.respondWith(
        caches.match(request).then(cached => {
            if (cached) return cached;
            return fetch(request).then(response => {
                if (response.ok) {
                    const clone = response.clone();
                    caches.open(CACHE).then(cache => cache.put(request, clone));
                }
                return response;
            });
        })
    );
});
