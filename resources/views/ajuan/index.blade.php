<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>List Pengajuan - {{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="relative min-h-screen bg-gray-100">
        <x-colorful-background />

        <div class="relative z-10 flex flex-col min-h-screen">
            @include('layouts.partials.header-new')

            <main class="flex-grow flex items-center justify-center py-12">
                <div class="w-full max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-8">
                        @if (session('success'))
                            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6"
                                role="alert">
                                <div class="flex">
                                    <div class="py-1">
                                        <i class="bi bi-check-circle-fill mr-3"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold">Berhasil</p>
                                        <p class="text-sm">{{ session('success') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">List Pengajuan</h2>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <i class="bi bi-search text-gray-400"></i>
                                </span>
                                <input type="text" placeholder="Search"
                                    class="w-full md:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500">
                            </div>
                        </div>

                        @include('ajuan.table', ['semuaAjuan' => $semuaAjuan])

                        <div class="mt-4 flex justify-between items-center text-sm text-gray-600">
                            {{ $semuaAjuan->links() }}
                        </div>

                    </div>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
