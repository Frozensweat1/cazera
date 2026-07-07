const CACHE_VERSION = 'cazera-pwa-v1';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const OFFLINE_URL = '/offline.html';

const STATIC_ASSETS = [
    OFFLINE_URL,
    '/manifest.webmanifest',
    '/pwa/icon-192.png',
    '/pwa/icon-512.png',
    '/pwa/icon.svg',
    '/pwa/maskable-icon.svg',
    '/assets/css/style.css',
    '/assets/css/backoffice-overrides.css',
    '/assets/css/perfect-scrollbar.min.css',
    '/assets/css/animate.css',
    '/assets/js/custom.js',
    '/assets/js/perfect-scrollbar.min.js',
    '/assets/js/popper.min.js',
    '/assets/js/tippy-bundle.umd.min.js',
    '/assets/js/sweetalert.min.js'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => Promise.allSettled(
                STATIC_ASSETS.map((asset) => cache.add(asset))
            ))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys
                .filter((key) => key.startsWith('cazera-pwa-') && key !== STATIC_CACHE)
                .map((key) => caches.delete(key))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    if (isStaticAsset(url)) {
        event.respondWith(cacheFirst(request));
    }
});

self.addEventListener('message', (event) => {
    if (event.data?.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

function isStaticAsset(url) {
    return [
        '/assets/',
        '/build/',
        '/pwa/',
        '/storage/'
    ].some((prefix) => url.pathname.startsWith(prefix))
        || url.pathname === '/manifest.webmanifest'
        || url.pathname === '/favicon.ico';
}

async function cacheFirst(request) {
    const cached = await caches.match(request);

    if (cached) {
        return cached;
    }

    const response = await fetch(request);

    if (response && response.ok) {
        const cache = await caches.open(STATIC_CACHE);
        cache.put(request, response.clone());
    }

    return response;
}
