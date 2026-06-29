<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - SAPA Posyandu</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Scripts (Tailwind CSS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Fallback if Vite is not running on 500 error -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-pink-50 to-purple-50 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white shadow-xl rounded-2xl p-8 max-w-md w-full text-center">
        <!-- Icon Section -->
        <div class="mb-6">
            @yield('image/icon')
        </div>

        <!-- Error Code -->
        <h1 class="text-6xl font-bold text-pink-500 mb-2">@yield('code')</h1>
        
        <!-- Error Title -->
        <h2 class="text-2xl font-bold text-gray-800 mb-4">@yield('title')</h2>
        
        <!-- Error Message -->
        <p class="text-gray-600 mb-8 leading-relaxed">
            @yield('message')
        </p>

        <!-- Action Button -->
        <div>
            @yield('action')
        </div>
    </div>
</body>
</html>
