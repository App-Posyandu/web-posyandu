<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Detail Pengajuan - {{ config('app.name', 'Laravel') }}</title>
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
                <div class="w-full max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-8">

                        <div class="mb-6">
                            <a href="{{ route('ajuan.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50">
                                <i class="bi bi-arrow-left me-2"></i> Kembali ke List Pengajuan
                            </a>
                        </div>

                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Detail Pengajuan</h2>

                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Nama Pengaju:</h3>
                                <p class="text-gray-600">{{ $ajuan->user->name }}</p>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Bidang:</h3>
                                <p class="text-gray-600">{{ $ajuan->bidang->nama_bidang }}</p>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Deskripsi Permohonan:</h3>
                                <p class="text-gray-600">{{ $ajuan->deskripsi_pengajuan }}</p>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Tindak Lanjut Pengajuan:</h3>
                                <p class="text-gray-600">{{ $ajuan->tindak_lanjut ?? 'Belum ada tindak lanjut' }}</p>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Status Pengajuan:</h3>
                                @if ($ajuan->status == 'Disetujui')
                                    <span
                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Disetujui
                                    </span>
                                @elseif ($ajuan->status == 'Ditolak')
                                    <span
                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Ditolak
                                    </span>
                                @else
                                    <span
                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-700">
                                        Diproses
                                    </span>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
</body>
</html>
