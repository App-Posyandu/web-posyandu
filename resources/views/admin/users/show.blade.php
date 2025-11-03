@extends('dashboard.layouts.dashboard')

@section('content')
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Pengguna: {{ $user->name }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-8 text-gray-900">

                        <div class="flex justify-end gap-2 mb-6">
                            <a href="{{ route('admin.users.index') }}"
                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm font-semibold hover:bg-gray-300">Kembali</a>
                            @if (auth()->user()->role === 'admin')
                                <a href="#"
                                    class="px-4 py-2 bg-yellow-500 text-white rounded-md text-sm font-semibold hover:bg-yellow-600">Ubah</a>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                            <div class="space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Nama Lengkap</dt>
                                    <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $user->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">NIK</dt>
                                    <dd class="mt-1 text-gray-900">{{ $user->nik }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1 text-gray-900">{{ $user->email }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Alamat</dt>
                                    <dd class="mt-1 text-gray-900">{{ $user->alamat }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Nomor Telepon</dt>
                                    <dd class="mt-1 text-gray-900">{{ $user->no_telepon ?? 'N/A' }}</dd>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Role & Status</dt>
                                    <dd class="mt-1 flex items-center gap-2">
                                        <span
                                            class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">{{ ucfirst($user->role) }}</span>
                                        @if ($user->verified_at)
                                            <span
                                                class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Terverifikasi</span>
                                        @else
                                            <span
                                                class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Belum
                                                Diverifikasi</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Tempat, Tanggal Lahir</dt>
                                    <dd class="mt-1 text-gray-900">{{ $user->tempat_lahir }},
                                        {{ \Carbon\Carbon::parse($user->tanggal_lahir)->format('d F Y') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Jenis Kelamin</dt>
                                    <dd class="mt-1 text-gray-900">{{ $user->jenis_kelamin }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Posyandu</dt>
                                    <dd class="mt-1 text-gray-900">
                                        {{ $user->posyandu?->nama_posyandu ?? 'Belum Terdaftar' }}</dd>
                                </div>
                            </div>
                        </div>

                        <hr class="my-8">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <h3 class="font-semibold mb-2">Kartu Tanda Penduduk (KTP)</h3>
                                @if ($user->ktp)
                                    <img src="{{ $user->ktp }}" alt="Foto KTP" class="w-full h-auto rounded-lg border">
                                @else
                                    <div
                                        class="w-full h-48 flex items-center justify-center bg-gray-100 rounded-lg border text-gray-400">
                                        Tidak ada gambar KTP</div>
                                @endif
                            </div>
                            <div>
                                <h3 class="font-semibold mb-2">Kartu Keluarga (KK)</h3>
                                @if ($user->kk)
                                    <img src="{{ $user->kk }}" alt="Foto KK" class="w-full h-auto rounded-lg border">
                                @else
                                    <div
                                        class="w-full h-48 flex items-center justify-center bg-gray-100 rounded-lg border text-gray-400">
                                        Tidak ada gambar KK</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-app-layout>
@endsection
