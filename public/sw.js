const CACHE_NAME = "offline-v2";
const OFFLINE_URL = "/offline.html";
const filesToCache = ["/", OFFLINE_URL];

const preLoad = function () {
    return caches.open(CACHE_NAME).then(function (cache) {
        return cache.addAll(filesToCache);
    });
};

self.addEventListener("install", function (event) {
    event.waitUntil(
        preLoad().then(function () {
            return self.skipWaiting();
        })
    );
});

self.addEventListener("activate", function (event) {
    event.waitUntil(
        caches.keys().then(function (keys) {
            return Promise.all(
                keys
                    .filter(function (key) {
                        return key.startsWith("offline") && key !== CACHE_NAME;
                    })
                    .map(function (key) {
                        return caches.delete(key);
                    })
            );
        }).then(function () {
            return self.clients.claim();
        })
    );
});

self.addEventListener("message", function (event) {
    if (event.data && event.data.type === "SKIP_WAITING") {
        self.skipWaiting();
    }
});

const checkResponse = function (request) {
    return new Promise(function (fulfill, reject) {
        fetch(request).then(function (response) {
            // Keep original HTTP responses (including 404) so server error pages can render.
            fulfill(response);
        }, reject);
    });
};

const returnFromCache = function (request) {
    return caches.open(CACHE_NAME).then(function (cache) {
        return cache.match(request).then(function (matching) {
            if (matching && matching.status !== 404) {
                return matching;
            }

            // Show offline page only for navigation requests when network is unavailable.
            if (request.mode === "navigate") {
                return cache.match(OFFLINE_URL);
            }

            return Response.error();
        });
    });
};

self.addEventListener("fetch", function (event) {
    const request = event.request;
    const url = request.url;

    if (request.method !== "GET") {
        return;
    }

    if (!url.startsWith("http")) {
        return;
    }

    if (
        url.includes("/export-all-bidang-dan-desa") ||
        url.includes("/export-bidang-dan-desa/")
    ) {
        return;
    }

    event.respondWith(
        checkResponse(request).catch(function () {
            return returnFromCache(request);
        })
    );
});
