<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>

    @vite('resources/css/app.css')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body class="bg-gray-100 font-sans">

    <div x-data="{ sidebarOpen: false }" class="flex h-screen bg-gray-200">

        @include('partials.sidebar')

        <div class="flex-1 flex flex-col overflow-hidden">

            @include('partials.header')

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                @yield('content')
            </main>

            @include('partials.footer')
        </div>
    </div>
</body>

</html>
