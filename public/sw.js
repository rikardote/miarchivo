const CACHE_NAME = 'miarchivo-scanner-v1';
const STATIC_ASSETS = [
    '/manifest.json',
    '/60issste.png',
    '/vendor/html5-qrcode/html5-qrcode.min.js'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        }).catch((err) => {
            console.warn('SW cache assets warning:', err);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    // Only handle GET requests and skip livewire internal polling / actions
    if (event.request.method !== 'GET') return;
    if (event.request.url.includes('/livewire/')) return;

    event.respondWith(
        fetch(event.request).catch(() => {
            return caches.match(event.request);
        })
    );
});
