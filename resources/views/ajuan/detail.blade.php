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
                            <div class="flex md:flex-row w-full gap-6">
                                <div class="w-full ">
                                    <h3 class="text-lg font-semibold text-gray-700">Nama Pengaju:</h3>
                                    <p class="text-gray-600 text-lg">{{ $ajuan->user->name }}</p>
                                </div>
                                <div class="w-full ">
                                    <h3 class="text-lg font-semibold text-gray-700">Bidang:</h3>
                                    <p class="text-gray-600 text-lg">{{ $ajuan->bidang->nama_bidang }}</p>
                                </div>
                            </div>
                            <div class="flex md:flex-row w-full gap-6">
                                <div class="w-full flex flex-col">
                                    <h3 class=" text-lg font-semibold text-gray-700">Alamat:</h3>
                                    <p class="text-gray-600 text-lg">{{ $ajuan->user->alamat }}</p>
                                </div>
                                <div class="w-full flex flex-col">
                                    <h3 class="text-lg font-semibold text-gray-700">No Hp</h3>
                                    <p class="text-gray-600 text-lg">{{ $ajuan->user->phone ?? '082134532110' }}</p>
                                </div>
                            </div>

                            <div class="flex md:flex-row w-full gap-6">
                                <div class="w-full">
                                    <h3 class="text-lg font-semibold text-gray-700">Tempat, Tanggal Lahir</h3>
                                    <p class="text-gray-600 text-lg">{{ $ajuan->user->tempat_lahir }},
                                        {{ $ajuan->user->tanggal_lahir ? \Carbon\Carbon::parse($ajuan->user->tanggal_lahir)->locale('id')->isoFormat('D MMMM Y') : '-' }} </p>
                                </div>
                                <div class="w-full">
                                    <h3 class="text-lg font-semibold text-gray-700">Jenis Kelamin</h3>
                                    <p class="text-gray-600 text-lg">{{ $ajuan->user->jenis_kelamin }}</p>
                                </div>
                            </div>
                            <div class="flex md:flex-row w-full gap-6">
                                <div class="w-full flex flex-col">
                                    <h3 class="text-lg font-semibold text-gray-700">Nama Posyandu</h3>
                                    <p class="text-gray-600 text-lg">
                                        {{ $ajuan->user?->posyandu?->nama_posyandu ?? 'Nama Posyandu' }}</p>
                                </div>
                                <div class="w-full flex flex-col">
                                    <h3 class="text-lg font-semibold text-gray-700">Desa/Kelurahan</h3>
                                    <p class="text-gray-600 text-lg">{{ $ajuan->user?->posyandu?->desa ?? 'Desa' }}</p>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Kecamatan</h3>
                                <p class="text-gray-600 text-lg">
                                    {{ $ajuan->user?->posyandu?->kecamatan ?? 'Kecamatan' }}</p>
                            </div>

                            {{-- Detail Pengajuan --}}
                            <div class="border-t pt-4 mt-4">
                                <h3 class="text-lg font-semibold text-gray-700 mb-3">List Kebutuhan:</h3>

                                @php
                                    $formulirItems = $ajuan->formulir_items;

                                    if (is_string($formulirItems)) {
                                        $formulirItems = json_decode($formulirItems, true);
                                    }

                                    $hasItems = is_array($formulirItems) && count($formulirItems) > 0;
                                @endphp

                                @if ($hasItems)
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <ul class="space-y-2">
                                            @foreach ($formulirItems as $index => $item)
                                                <li class="flex items-start">
                                                    <span
                                                        class="inline-flex items-center justify-center w-6 h-6 mr-3 text-sm font-semibold text-white bg-blue-600 rounded-full flex-shrink-0">
                                                        {{ $index + 1 }}
                                                    </span>
                                                    <span
                                                        class="text-gray-700 text-base pt-0.5">{{ $item }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                        <p class="text-red-600">Tidak ada kebutuhan yang tercatat</p>
                                        {{-- Debug info --}}
                                        <p class="text-xs text-red-500 mt-2">
                                            Debug: Type = {{ gettype($ajuan->formulir_items) }},
                                            Value =
                                            {{ is_string($ajuan->formulir_items) ? $ajuan->formulir_items : json_encode($ajuan->formulir_items) }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <div> 
                                <h3 class="text-lg font-semibold text-gray-700">Deskripsi Permohonan:</h3>
                                <p class="text-gray-600 text-lg">{{ $ajuan->deskripsi_pengajuan }}</p>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Tindak Lanjut Pengajuan:</h3>
                                <p class="text-gray-600 text-lg">
                                    {{ $ajuan->tindak_lanjut ?? 'Belum ada tindak lanjut' }}</p>
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
