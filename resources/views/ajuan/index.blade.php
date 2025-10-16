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

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            No</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Bidang</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Deskripsi Permohonan</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tindak Lanjut Pengajuan</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status Pengajuan</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($semuaAjuan as $ajuan)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $loop->iteration }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $ajuan->bidang }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $ajuan->deskripsi }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if ($ajuan->tindak_lanjut == 'Sudah Verifikasi')
                                                    <span
                                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Sudah
                                                        Verifikasi</span>
                                                @else
                                                    <span
                                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if ($ajuan->status == 'Disetujui')
                                                    <span
                                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>
                                                @else
                                                    <span
                                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-2">
                                                <a href="#"
                                                    class="flex items-center px-3 py-1 bg-blue-500 text-white rounded-md text-xs hover:bg-blue-600"><i
                                                        class="bi bi-eye-fill mr-1"></i> Detail</a>
                                                <a href="#"
                                                    class="flex items-center px-3 py-1 bg-yellow-500 text-white rounded-md text-xs hover:bg-yellow-600"><i
                                                        class="bi bi-pencil-fill mr-1"></i> Ubah</a>
                                                <a href="#"
                                                    class="flex items-center px-3 py-1 bg-green-500 text-white rounded-md text-xs hover:bg-green-600"><i
                                                        class="bi bi-printer-fill mr-1"></i> Cetak</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada
                                                data pengajuan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 flex justify-between items-center text-sm text-gray-600">
                            <div>Showing 5 data out of 100</div>
                            <div class="flex items-center space-x-4">
                                <span>Show</span>
                                <select
                                    class="border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option>5</option>
                                    <option>10</option>
                                    <option>25</option>
                                </select>
                                <span>data per page</span>
                                <div class="flex space-x-1">
                                    <button class="p-2 border rounded-md hover:bg-gray-100"><i
                                            class="bi bi-chevron-left"></i></button>
                                    <button class="p-2 border rounded-md hover:bg-gray-100"><i
                                            class="bi bi-chevron-right"></i></button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
