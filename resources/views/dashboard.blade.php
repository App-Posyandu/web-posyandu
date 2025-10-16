<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Dashboard - {{ config('app.name', 'Laravel') }}</title>

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


            <main class="flex-grow flex items-center justify-center">
                <div class="max-w-4xl w-full mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-8">

                        <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">
                            Pilih Ajuan Layanan
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <a href="/ajuan/create/perumahanrakyat"
                                class="block p-6 text-center text-white font-semibold bg-blue-500 rounded-lg shadow-md transform hover:scale-105 transition-transform duration-200">
                                Bidang Perumahan Rakyat
                            </a>

                            <a href="/ajuan/create/sosial"
                                class="block p-6 text-center text-white font-semibold bg-pink-500 rounded-lg shadow-md transform hover:scale-105 transition-transform duration-200">
                                Bidang Sosial
                            </a>

                            <a href="/ajuan/create/pendidikan"
                                class="block p-6 text-center text-white font-semibold bg-orange-500 rounded-lg shadow-md transform hover:scale-105 transition-transform duration-200">
                                Bidang Pendidikan
                            </a>

                            <a href="/ajuan/create/pekerjaanUmum"
                                class="block p-6 text-center text-white font-semibold bg-green-500 rounded-lg shadow-md transform hover:scale-105 transition-transform duration-200">
                                Bidang Pekerjaan Umum
                            </a>

                            <a href="/ajuan/create/kesehatan" class="block p-6 text-center text-white font-semibold"
                                style="background-color: #E655A0;">
                                Bidang Kesehatan
                            </a>

                            <a href="/ajuan/create/trantibumlinmas"
                                class="block p-6 text-center text-white font-semibold bg-yellow-500 rounded-lg shadow-md transform hover:scale-105 transition-transform duration-200">
                                Bidang Trantibum Linmas
                            </a>

                        </div>
                    </div>
                </div>
            </main>

        </div>
    </div>
</body>

</html>
