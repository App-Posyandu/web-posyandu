@extends('admin.layouts.index')
@section('title', 'Manajemen Pengguna')
@section('content')
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manajemen Pengguna') }}
            </h2>
        </x-slot>
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6" role="alert">
                <div class="flex">
                    <div class="py-1">
                        {{-- Ganti ikon menjadi centang --}}
                        <i class="bi bi-check-circle-fill mr-3"></i>
                    </div>
                    <div>
                        <p class="font-bold">Berhasil</p>
                        <p class="text-sm">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Notifikasi untuk error (jika Anda butuh) --}}
        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6" role="alert">
                <div class="flex">
                    <div class="py-1">
                        <i class="bi bi-exclamation-triangle-fill mr-3"></i>
                    </div>
                    <div>
                        <p class="font-bold">Gagal</p>
                        <p class="text-sm">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                            <h2 class="text-2xl font-bold text-gray-800">List Pengguna</h2>
                            <form action="{{ route('admin.users.index') }}" method="GET" class="flex items-center gap-2">
                                <select name="role" onchange="this.form.submit()"
                                    class="border-gray-300 rounded-md shadow-sm">
                                    <option value="">Semua Role</option>
                                    <option value="masyarakat" @selected(request('role') == 'masyarakat')>Masyarakat</option>
                                    <option value="kader" @selected(request('role') == 'kader')>Kader</option>
                                    <option value="kabid" @selected(request('role') == 'kabid')>Kabid</option>
                                </select>
                                <div class="relative">
                                    <input type="text" name="search" placeholder="Cari nama, email, NIK..."
                                        value="{{ request('search') }}"
                                        class="w-full md:w-64 pl-4 pr-10 py-2 border border-gray-300 rounded-md">
                                    <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <i class="bi bi-search text-gray-400"></i>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email &
                                            NIK</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($users as $user)
                                        <tr>
                                            <td class="px-6 py-4">{{ $loop->iteration + $users->firstItem() - 1 }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $user->posyandu?->nama_posyandu ?? 'Belum Terdaftar' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $user->email }}</div>
                                                <div class="text-sm text-gray-500">NIK: {{ $user->nik }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ ucfirst($user->role) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($user->status == 'verified')
                                                    <span
                                                        class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Terverifikasi</span>
                                                @else
                                                    <span
                                                        class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Belum
                                                        Diverifikasi</span>
                                                @endif
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm font-medium flex flex-col items-start space-y-2">
                                                <a href="{{ route('admin.users.show', $user) }}"
                                                    class="flex items-center justify-center w-24 px-3 py-1 bg-blue-500 text-white rounded-md text-xs hover:bg-blue-600">Detail</a>
                                                @if ($user->status != 'verified')
                                                    <form action="{{ route('admin.users.verify', $user) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit"
                                                            class="flex items-center justify-center w-24 px-3 py-1 bg-green-500 text-white rounded-md text-xs hover:bg-green-600">Verifikasi</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada data
                                                pengguna ditemukan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $users->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-app-layout>
@endsection
