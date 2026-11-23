// Retire the old Laravel offline cache after the site moved to the Next.js frontend.
self.addEventListener("install", (event) => {
  event.waitUntil(self.skipWaiting());
});

self.addEventListener("activate", (event) => {
  event.waitUntil((async () => {
    await caches.delete("isaiah-v1");
    await self.registration.unregister();
  })());
});
