/*
 * Coding First — service worker.
 *
 * Privacy rules (UU PDP; shared/family devices are common):
 *   - Only the static shell is cached: the offline page, its build assets and brand icons.
 *   - Authenticated HTML and API/JSON responses are NEVER cached.
 *   - A response is stored only when it is same-origin, 200, and its Cache-Control has
 *     neither "no-store" nor "private". HTML additionally needs an explicit "public".
 *   - On logout the page posts 'CF_LOGOUT' and every cache is deleted.
 *
 * Strategies: cache-first for /build/assets/* and /brand/*; network-first for page
 * navigations with the offline page as fallback; everything else goes to the network.
 * Bump CACHE_VERSION whenever icons or this file's behaviour change.
 */
const CACHE_VERSION = 'v1-2026-10-13';
const CACHE_PREFIX = 'cf-';
const STATIC_CACHE = `${CACHE_PREFIX}static-${CACHE_VERSION}`;
const OFFLINE_URL = '/offline';
const PRECACHE = [
    '/favicon.svg',
    '/favicon.ico',
    '/brand/a/favicon.svg', '/brand/a/icon-192.png', '/brand/a/icon-512.png', '/brand/a/maskable-512.png', '/brand/a/apple-touch-icon.png',
    '/brand/b/favicon.svg', '/brand/b/icon-192.png', '/brand/b/icon-512.png', '/brand/b/maskable-512.png', '/brand/b/apple-touch-icon.png',
];
const STATIC_PATHS = [/^\/build\/assets\//, /^\/brand\//, /^\/favicon\.(ico|svg)$/];

/** Whether a response may be written to the cache (see privacy rules above). */
function isCacheable(response, { html = false } = {}) {
    if (!response || response.status !== 200 || response.type !== 'basic') {
        return false;
    }
    const cacheControl = (response.headers.get('Cache-Control') || '').toLowerCase();
    if (/no-store|private/.test(cacheControl)) {
        return false;
    }
    if (response.headers.has('Set-Cookie')) {
        return false;
    }
    return html ? /public/.test(cacheControl) : true;
}

/** Cache the offline page plus the CSS/JS/fonts it references, and the brand icons. */
async function precache() {
    const cache = await caches.open(STATIC_CACHE);
    await Promise.all(PRECACHE.map((url) => cache.add(new Request(url, { cache: 'reload' })).catch(() => null)));

    const response = await fetch(new Request(OFFLINE_URL, { cache: 'reload', credentials: 'omit' })).catch(() => null);
    if (!isCacheable(response, { html: true })) {
        return;
    }
    const html = await response.clone().text();
    await cache.put(OFFLINE_URL, response);

    // Reason: build assets are hashed, so read their URLs from the page instead of hard-coding them.
    const assets = [...new Set(html.match(/\/build\/assets\/[^"'\s)]+/g) || [])];
    await Promise.all(assets.map((url) => cache.add(url).catch(() => null)));
}

self.addEventListener('install', (event) => {
    event.waitUntil(precache().then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const keys = await caches.keys();
        await Promise.all(keys.filter((key) => key.startsWith(CACHE_PREFIX) && key !== STATIC_CACHE).map((key) => caches.delete(key)));
        if (self.registration.navigationPreload) {
            await self.registration.navigationPreload.enable().catch(() => null);
        }
        await self.clients.claim();
    })());
});

self.addEventListener('message', (event) => {
    if (event.data === 'CF_LOGOUT' || (event.data && event.data.type === 'CF_LOGOUT')) {
        // Personal data must not persist on shared devices: drop every cache, then
        // re-create only the anonymous offline shell.
        event.waitUntil(caches.keys()
            .then((keys) => Promise.all(keys.map((key) => caches.delete(key))))
            .then(() => precache())
            .catch(() => null));
    }
    if (event.data === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) {
        return cached;
    }
    const response = await fetch(request);
    if (isCacheable(response)) {
        const cache = await caches.open(STATIC_CACHE);
        cache.put(request, response.clone()).catch(() => null);
    }
    return response;
}

async function networkFirstNavigation(event) {
    try {
        const preloaded = await event.preloadResponse;
        if (preloaded) {
            return preloaded;
        }
        // Pages are never written to the cache: they may contain personal data.
        return await fetch(event.request);
    } catch (error) {
        const offline = await caches.match(OFFLINE_URL);
        return offline || new Response('Anda sedang offline.', { status: 503, headers: { 'Content-Type': 'text/plain; charset=utf-8' } });
    }
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') {
        return;
    }
    const url = new URL(request.url);
    if (url.origin !== self.location.origin) {
        return;
    }
    if (request.mode === 'navigate') {
        event.respondWith(networkFirstNavigation(event));
        return;
    }
    if (STATIC_PATHS.some((pattern) => pattern.test(url.pathname))) {
        event.respondWith(cacheFirst(request));
    }
    // Everything else (JSON, images from storage, API) goes straight to the network.
});
