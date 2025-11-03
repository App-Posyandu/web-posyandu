<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Laravel') }}</title>

    <!-- PWA  -->
    <meta name="theme-color" content="#6777ef" />
    <link rel="apple-touch-icon" href="{{ asset('assets/image/logo/logo_sapaposyandu.png') }}">
    <link rel="manifest" href="{{ asset('/manifest.json') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- @laravelPwa --}}
</head>

<body class="font-sans antialiased">
    <div class="relative min-h-screen bg-gray-100">

        <x-colorful-background />

        <div class="relative z-10 flex flex-col min-h-screen">

            @include('layouts.partials.header-new')

            <main class="flex-grow flex items-center justify-center min-h-[70vh]">
                @yield('content')
            </main>
            <button id="pwa-install-btn"
                style="display:none; position: fixed; bottom: 20px; right: 20px; padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 8px; z-index: 1000;">
                Install App
            </button>

            @include('layouts.partials.footer')
        </div>
    </div>

    <script src="{{ asset('/sw.js') }}"></script>
    <script src="{{ asset('pwa-install.js') }}"></script>
    <script>
        if ("serviceWorker" in navigator) {
            navigator.serviceWorker.register("/sw.js").then(
                (registration) => {
                    console.log("Service worker registration succeeded:", registration);
                },
                (error) => {
                    console.error(`Service worker registration failed: ${error}`);
                },
            );
        } else {
            console.error("Service workers are not supported.");
        }
    </script>
</body>

</html>
