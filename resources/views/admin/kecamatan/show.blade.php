@extends('dashboard.layouts.dashboard')
@section('title', 'Detail Kecamatan ' . $displayName)
@section('content')
    <div class="py-12 w-full max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Kecamatan {{ $displayName }}</h2>
                <p class="text-gray-600 text-sm">
                    Admin:
                    <span class="font-semibold text-pink-600">
                        {{ $adminKecamatan ? $adminKecamatan->name : 'Belum Ditentukan' }}
                    </span>
                </p>
            </div>
            <a href="{{ route('admin.kecamatan.index') }}"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Kembali
            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h3 class="text-lg font-semibold mb-4 border-b pb-2">Daftar Posyandu di Kecamatan Ini</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Posyandu
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Desa/Kelurahan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ketua Kader</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($posyandus as $posyandu)
                                @php
                                    $ketua = $posyandu->users->firstWhere('role', 'ketua-kader');
                                    $cleanDesa = explode('_', $posyandu->desa)[1] ?? $posyandu->desa;
                                @endphp
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                        {{ $posyandu->nama_posyandu }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                        {{ $cleanDesa }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                        {{ $ketua->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('admin.posyandu.edit', $posyandu) }}"
                                            class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                        Tidak ada Posyandu di kecamatan ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $posyandus->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
