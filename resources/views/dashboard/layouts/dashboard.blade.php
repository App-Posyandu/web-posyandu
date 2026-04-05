<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Laravel') }}</title>

    <meta name="theme-color" content="#171717">

    <link rel="manifest" href="{{ asset('./manifest.json') }}">

    <meta name="mobile-web-app-capable" content="yes">

    <meta name="apple-mobile-web-app-capable" content="yes">

    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <link rel="apple-touch-icon" href="{{ asset('./assets/image/logo/app/logo_192x192.png') }}">

    <link rel="apple-touch-startup-image" href="./assets/image/splash/splash_screen_640x1136.png"
        media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)">

    <link rel="apple-touch-startup-image" href="./assets/image/splash/splash_screen_750x1334.png"
        media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)">

    <link rel="apple-touch-startup-image" href="./assets/image/splash/splash_screen_1170x2532.png"
        media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3)">

    <link rel="apple-touch-startup-image" href="./assets/image/splash/splash_screen_1125x2436.png"
        media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3)">

    <link rel="apple-touch-startup-image" href="./assets/image/splash/splash_screen_1668x2224.png"
        media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2)">

    <link rel="apple-touch-startup-image" href="./assets/image/splash/splash_screen_2048x2732.png"
        media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2)">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="relative min-h-screen bg-gray-100">

        <x-colorful-background />

        <div class="relative z-10 flex flex-col min-h-screen">

            @include('layouts.partials.header-new')

            <main class="flex-grow flex justify-center mt-0 md:mt-6 min-h-[70vh]">
                @yield('content')
            </main>
            <button id="pwa-install-btn"
                style="display:none; position: fixed; bottom: 20px; right: 20px; padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 8px; z-index: 1000;">
                Install App
            </button>

            @include('layouts.partials.footer')
        </div>
    </div>

    @include('sweetalert2::index')
    <script src="{{ asset('pwa-install.js') }}"></script>
    @stack('scripts')
    <script>
        if ("serviceWorker" in navigator) {
            const swUrl = "{{ asset('/sw.js') }}?v={{ filemtime(public_path('sw.js')) }}";

            navigator.serviceWorker.register(swUrl, {
                updateViaCache: 'none'
            }).then(
                (registration) => {
                    console.log("Service worker registration succeeded:", registration);

                    registration.update();

                    if (registration.waiting) {
                        registration.waiting.postMessage({
                            type: 'SKIP_WAITING'
                        });
                    }

                    registration.addEventListener('updatefound', () => {
                        const newWorker = registration.installing;
                        if (!newWorker) return;

                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                newWorker.postMessage({
                                    type: 'SKIP_WAITING'
                                });
                            }
                        });
                    });
                },
                (error) => {
                    console.error(`Service worker registration failed: ${error}`);
                },
            );

            let refreshing = false;
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                if (refreshing) return;
                refreshing = true;
                window.location.reload();
            });
        } else {
            console.error("Service workers are not supported.");
        }
    </script>
</body>

</html>
