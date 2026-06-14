<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ec4899">

    <title>Sapa Posyandu</title>

    <link rel="manifest" href="/site.webmanifest">
    <link rel="apple-touch-icon" href="/android-chrome-192x192.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/js/all.min.js"
        integrity="sha512-6BTOlkauINO65nLhXhthZMtepgJSghyimIalb+crKRPhvhmsCdnIuGcVbR5/aQY2A+260iC1OPy1oCdB6pSSwQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 relative">

        <x-colorful-background />

        <div class="w-full sm:max-w-3xl mt-6 px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-lg z-10">
            {{ $slot }}
        </div>
    </div>

    {{-- PWA Install Banner --}}
    <div id="pwa-install-banner"
        class="fixed bottom-0 left-0 right-0 z-50 hidden"
        role="complementary" aria-label="Install aplikasi">
        <div class="bg-white border-t border-gray-200 shadow-lg px-4 py-3 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <img src="/assets/image/logo/app/logo_192x192.png"
                    alt="Sapa Posyandu" class="w-12 h-12 rounded-xl flex-shrink-0">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">Sapa Posyandu</p>
                    <p class="text-xs text-gray-500">Install aplikasi untuk akses lebih mudah</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button id="pwa-install-dismiss"
                    class="text-xs text-gray-400 hover:text-gray-600 px-2 py-1">
                    Nanti
                </button>
                <button id="pwa-install-btn"
                    class="bg-pink-500 hover:bg-pink-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    Install
                </button>
            </div>
        </div>
    </div>

    <script>
        // Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/sw.js');
            });
        }

        // PWA Install Banner
        let deferredPrompt = null;
        const banner  = document.getElementById('pwa-install-banner');
        const btnInstall  = document.getElementById('pwa-install-btn');
        const btnDismiss  = document.getElementById('pwa-install-dismiss');

        window.addEventListener('beforeinstallprompt', function (e) {
            e.preventDefault();
            deferredPrompt = e;

            // Jangan tampilkan jika user sudah dismiss sebelumnya
            if (!sessionStorage.getItem('pwa-banner-dismissed')) {
                banner.classList.remove('hidden');
            }
        });

        btnInstall.addEventListener('click', async function () {
            if (!deferredPrompt) return;
            banner.classList.add('hidden');
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            deferredPrompt = null;
        });

        btnDismiss.addEventListener('click', function () {
            banner.classList.add('hidden');
            sessionStorage.setItem('pwa-banner-dismissed', '1');
        });

        // Sembunyikan banner jika sudah terinstall
        window.addEventListener('appinstalled', function () {
            banner.classList.add('hidden');
            deferredPrompt = null;
        });
    </script>
</body>

</html>
