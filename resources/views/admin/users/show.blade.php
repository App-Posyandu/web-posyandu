@extends('dashboard.layouts.dashboard')
@section('title', 'Detail Pengguna - ' . $user->name)
@section('content')
    <div class="py-12">
        <div class="w-full mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <a href="{{ route('admin.users.index') }}" class="flex items-center text-gray-600 hover:text-gray-900">
                    <i class="bi bi-arrow-left mr-2"></i> Kembali ke Daftar
                </a>
                @can('update', $user)
                    <a href="{{ route('admin.users.edit', $user) }}"
                        class="px-4 py-2 bg-yellow-500 text-white rounded-md text-sm font-semibold hover:bg-yellow-600 shadow-sm">
                        <i class="bi bi-pencil-square mr-2"></i> Ubah Data / Status
                    </a>
                @endcan
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900">
                    <div class="flex flex-col md:flex-row items-start gap-6 mb-8 border-b pb-8">
                        <div class="flex-shrink-0">
                            <div
                                class="w-24 h-24 bg-pink-500 rounded-full flex items-center justify-center text-white text-4xl font-bold shadow-md">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        </div>
                        <div class="flex-1 w-full">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h2 class="text-3xl font-bold text-gray-900">{{ $user->name }}</h2>
                                    <p class="text-gray-500">{{ $user->email }}</p>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide bg-blue-100 text-blue-800 border border-blue-200">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                    @if ($user->is_active)
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide bg-green-100 text-green-800 border border-green-200 flex items-center gap-1">
                                            <i class="bi bi-check-circle-fill"></i> Aktif
                                        </span>
                                    @else
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide bg-red-100 text-red-800 border border-red-200 flex items-center gap-1">
                                            <i class="bi bi-x-circle-fill"></i> Nonaktif
                                        </span>
                                    @endif
                                    @if ($user->verified_at)
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide bg-teal-100 text-teal-800 border border-teal-200 flex items-center gap-1">
                                            <i class="bi bi-shield-check"></i> Terverifikasi
                                        </span>
                                    @else
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide bg-yellow-100 text-yellow-800 border border-yellow-200 flex items-center gap-1">
                                            <i class="bi bi-hourglass-split"></i> Belum Verifikasi
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @if (!$user->is_active)
                                <div class="mt-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-md">
                                    <h4 class="text-sm font-bold text-red-800 mb-1 flex items-center">
                                        <i class="bi bi-exclamation-triangle-fill mr-2"></i> Akun Dinonaktifkan
                                    </h4>
                                    <p class="text-sm text-red-700">
                                        <span class="font-semibold">Alasan:</span>
                                        {{ $user->deactivation_reason ?? 'Tidak ada alasan spesifik.' }}
                                    </p>
                                    <p class="text-xs text-red-500 mt-1">
                                        Dinonaktifkan pada:
                                        {{ \Carbon\Carbon::parse($user->deactivated_at)->format('d F Y, H:i') }}
                                        @php
                                            $admin = \App\Models\User::find($user->deactivated_by);
                                        @endphp
                                        @if ($admin)
                                            oleh {{ $admin->name }}
                                        @endif
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                        <div class="space-y-6">
                            <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Biodata Diri</h3>

                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <dt class="text-xs uppercase tracking-wider text-gray-500 font-semibold">NIK</dt>
                                    <dd class="mt-1 text-base text-gray-900 font-medium">{{ $user->nik ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-wider text-gray-500 font-semibold">Tempat, Tanggal
                                        Lahir</dt>
                                    <dd class="mt-1 text-base text-gray-900">
                                        {{ $user->tempat_lahir ?? '-' }},
                                        {{ $user->tanggal_lahir ? \Carbon\Carbon::parse($user->tanggal_lahir)->format('d F Y') : '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-wider text-gray-500 font-semibold">Jenis Kelamin
                                    </dt>
                                    <dd class="mt-1 text-base text-gray-900">{{ $user->jenis_kelamin ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-wider text-gray-500 font-semibold">Nomor Telepon
                                    </dt>
                                    <dd class="mt-1 text-base text-gray-900">{{ $user->no_telepon ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-wider text-gray-500 font-semibold">Alamat</dt>
                                    <dd class="mt-1 text-base text-gray-900">{{ $user->alamat ?? '-' }}</dd>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-8">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Penugasan</h3>
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <dt class="text-xs uppercase tracking-wider text-gray-500 font-semibold">Posyandu
                                        </dt>
                                        <dd class="mt-1 text-base text-gray-900 flex items-center gap-2">
                                            <i class="bi bi-hospital text-pink-500"></i>
                                            {{ $user->posyandu?->nama_posyandu ?? 'Belum Terdaftar' }}
                                        </dd>
                                    </div>
                                    @if ($user->role === 'kader')
                                        <div>
                                            <dt class="text-xs uppercase tracking-wider text-gray-500 font-semibold">Bidang
                                                Tugas</dt>
                                            <dd class="mt-1 text-base text-gray-900 flex items-center gap-2">
                                                <i class="bi bi-folder text-blue-500"></i>
                                                {{ $user->bidang->nama_bidang ?? '-' }}
                                            </dd>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Dokumen</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div x-data="{ open: false }">
                                        <dt class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-2">KTP
                                        </dt>
                                        @if ($user->ktp)
                                            <div class="relative group cursor-pointer" @click="open = true">
                                                <img src="{{ $user->ktp }}" alt="KTP"
                                                    class="w-full h-24 object-cover rounded-lg border shadow-sm transition transform group-hover:scale-105">
                                                <div
                                                    class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition rounded-lg flex items-center justify-center">
                                                    <i
                                                        class="bi bi-eye text-white opacity-0 group-hover:opacity-100 text-2xl"></i>
                                                </div>
                                            </div>
                                            <div x-show="open"
                                                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-80 p-4"
                                                x-transition style="display: none;">
                                                <div class="relative max-w-3xl w-full" @click.away="open = false">
                                                    <button @click="open = false"
                                                        class="absolute -top-10 right-0 text-white hover:text-gray-300 text-2xl">&times;</button>
                                                    <img src="{{ $user->ktp }}" class="w-full rounded-lg shadow-2xl">
                                                </div>
                                            </div>
                                        @else
                                            <div
                                                class="w-full h-24 bg-gray-100 rounded-lg border border-dashed flex items-center justify-center text-gray-400 text-xs text-center p-2">
                                                Tidak ada KTP
                                            </div>
                                        @endif
                                    </div>
                                    <div x-data="{ open: false }">
                                        <dt class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-2">KK
                                        </dt>
                                        @if ($user->kk)
                                            <div class="relative group cursor-pointer" @click="open = true">
                                                <img src="{{ $user->kk }}" alt="KK"
                                                    class="w-full h-24 object-cover rounded-lg border shadow-sm transition transform group-hover:scale-105">
                                                <div
                                                    class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition rounded-lg flex items-center justify-center">
                                                    <i
                                                        class="bi bi-eye text-white opacity-0 group-hover:opacity-100 text-2xl"></i>
                                                </div>
                                            </div>
                                            <div x-show="open"
                                                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-80 p-4"
                                                x-transition style="display: none;">
                                                <div class="relative max-w-3xl w-full" @click.away="open = false">
                                                    <button @click="open = false"
                                                        class="absolute -top-10 right-0 text-white hover:text-gray-300 text-2xl">&times;</button>
                                                    <img src="{{ $user->kk }}" class="w-full rounded-lg shadow-2xl">
                                                </div>
                                            </div>
                                        @else
                                            <div
                                                class="w-full h-24 bg-gray-100 rounded-lg border border-dashed flex items-center justify-center text-gray-400 text-xs text-center p-2">
                                                Tidak ada KK
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
