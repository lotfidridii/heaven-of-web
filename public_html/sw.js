/* Heaven of Web service worker: offline support for the public site + admin push notifications. */
const VERSION = 'how-v2';
const STATIC_CACHE = `${VERSION}-static`;
const PAGE_CACHE = `${VERSION}-pages`;
const PRECACHE = [
  '/offline.html',
  '/assets/img/icons/icon-192.png',
  '/assets/img/icons/badge-72.png',
];

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(STATIC_CACHE).then((c) => c.addAll(PRECACHE)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const keys = await caches.keys();
    await Promise.all(keys.filter((k) => !k.startsWith(VERSION)).map((k) => caches.delete(k)));
    await self.clients.claim();
  })());
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  if (url.origin !== location.origin) return;
  if (url.pathname.startsWith('/api/')) return;

  // Admin: always live data, never cached. Offline → offline page.
  if (url.pathname.startsWith('/admin')) {
    if (req.mode === 'navigate') {
      event.respondWith(fetch(req).catch(() => caches.match('/offline.html')));
    }
    return;
  }

  // Versioned static files and uploads: cache first.
  if (url.pathname.startsWith('/assets/') || url.pathname.startsWith('/uploads/')) {
    event.respondWith((async () => {
      const cache = await caches.open(STATIC_CACHE);
      const hit = await cache.match(req);
      if (hit) return hit;
      const res = await fetch(req);
      if (res.ok) cache.put(req, res.clone());
      return res;
    })());
    return;
  }

  // Public pages: network first, then the last cached copy, then the offline page.
  if (req.mode === 'navigate') {
    event.respondWith((async () => {
      try {
        const res = await fetch(req);
        if (res.ok) (await caches.open(PAGE_CACHE)).put(req, res.clone());
        return res;
      } catch {
        return (await caches.match(req)) || (await caches.match('/offline.html'));
      }
    })());
  }
});

/* ---------- Push notifications (new contact messages) ---------- */
self.addEventListener('push', (event) => {
  let data = {};
  try { data = event.data ? event.data.json() : {}; } catch { data = { body: event.data && event.data.text() }; }
  const title = data.title || 'Heaven of Web';
  event.waitUntil((async () => {
    await self.registration.showNotification(title, {
      body: data.body || '',
      icon: data.icon || '/assets/img/icons/icon-192.png',
      badge: data.badge || '/assets/img/icons/badge-72.png',
      tag: data.tag || 'how-message',
      renotify: true,
      data: { url: data.url || '/admin/messages' },
    });
    // Tell open admin tabs to refresh their unread counter.
    const clients = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
    clients.forEach((c) => c.postMessage({ type: 'new-message' }));
    if (self.navigator && 'setAppBadge' in self.navigator) self.navigator.setAppBadge().catch(() => {});
  })());
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const target = new URL(event.notification.data?.url || '/admin/messages', location.origin).href;
  event.waitUntil((async () => {
    const clients = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
    const admin = clients.find((c) => new URL(c.url).pathname.startsWith('/admin'));
    if (admin) {
      await admin.focus();
      return admin.navigate ? admin.navigate(target) : undefined;
    }
    return self.clients.openWindow(target);
  })());
});
