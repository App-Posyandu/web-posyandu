@extends('dashboard.layouts.dashboard')
@section('title', 'Kelola Status Pengguna')
@section('content')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Status Pengguna: ') }} {{ $user->name }}
        </h2>
    </x-slot>

    <div class="w-full max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 sm:p-8 text-gray-900">

                {{-- User Info Card - Read Only --}}
                <div class="bg-gradient-to-r from-pink-50 to-purple-50 rounded-lg p-6 mb-6 border border-pink-200">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div
                                class="w-16 h-16 bg-pink-500 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $user->name }}</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                <div class="flex items-center gap-2">
                                    <i class="bi bi-person-badge text-pink-600"></i>
                                    <span class="text-gray-600">NIK:</span>
                                    <span class="font-medium text-gray-800">{{ $user->nik }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="bi bi-envelope text-pink-600"></i>
                                    <span class="text-gray-600">Email:</span>
                                    <span class="font-medium text-gray-800">{{ $user->email }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="bi bi-telephone text-pink-600"></i>
                                    <span class="text-gray-600">No. HP:</span>
                                    <span class="font-medium text-gray-800">{{ $user->no_telepon }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="bi bi-briefcase text-pink-600"></i>
                                    <span class="text-gray-600">Role:</span>
                                    <span class="font-medium text-gray-800">{{ ucfirst($user->role) }}</span>
                                </div>
                                @if ($user->posyandu)
                                    <div class="flex items-center gap-2 sm:col-span-2">
                                        <i class="bi bi-hospital text-pink-600"></i>
                                        <span class="text-gray-600">Posyandu:</span>
                                        <span class="font-medium text-gray-800">{{ $user->posyandu->nama_posyandu }}</span>
                                    </div>
                                @endif
                                @if ($user->role === 'kader' && $user->bidang)
                                    <div class="flex items-center gap-2 sm:col-span-2">
                                        <i class="bi bi-folder text-pink-600"></i>
                                        <span class="text-gray-600">Bidang:</span>
                                        <span class="font-medium text-gray-800">{{ $user->bidang->nama_bidang }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Form Edit Status --}}
                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                    @csrf
                    @method('PATCH')

                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="bi bi-toggle-on text-pink-600"></i>
                            Kelola Status Akun
                        </h2>

                        {{-- Current Status Badge --}}
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Status Saat Ini:</p>
                                    @if ($user->is_active)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                            <i class="bi bi-check-circle-fill mr-2"></i>
                                            Aktif
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                                            <i class="bi bi-x-circle-fill mr-2"></i>
                                            Nonaktif
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Status Toggle --}}
                        <div class="mb-6">
                            <label
                                class="flex items-center justify-between p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors duration-150">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-person-check text-2xl text-pink-600"></i>
                                    </div>
                                    <div>
                                        <span class="block text-base font-semibold text-gray-800">Status Akun Aktif</span>
                                        <span class="block text-sm text-gray-600">
                                            {{ $user->is_active ? 'User dapat login dan mengakses sistem' : 'User tidak dapat login ke sistem' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <input type="checkbox" name="is_active" id="is_active" value="1"
                                        {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                        class="w-6 h-6 text-pink-600 bg-gray-100 border-gray-300 rounded focus:ring-pink-500 focus:ring-2">
                                </div>
                            </label>
                            <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
                        </div>

                        <div class="mt-4" x-data="{ isActive: {{ $user->is_active ? 'true' : 'false' }} }">
                            <script>
                                document.getElementById('is_active').addEventListener('change', function() {
                                    const reasonInput = document.getElementById('reason_container');
                                    if (this.checked) {
                                        reasonInput.style.display = 'none';
                                    } else {
                                        reasonInput.style.display = 'block';
                                    }
                                });
                            </script>

                            <div id="reason_container" style="display: {{ $user->is_active ? 'none' : 'block' }};">
                                <x-input-label for="reason" :value="__('Alasan Penonaktifan')" />
                                <textarea id="reason" name="reason" rows="2" required
                                    class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-pink-500 focus:ring-pink-500"
                                    placeholder="Contoh: User sudah pindah domisili, atau mengundurkan diri.">{{ old('reason', $user->deactivation_reason) }}</textarea>
                                <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Warning Info --}}
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-exclamation-triangle-fill text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        <strong class="font-semibold">Perhatian:</strong>
                                        Menonaktifkan user akan mencegah mereka login ke sistem.
                                        User yang dinonaktifkan tidak akan kehilangan data mereka.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col sm:flex-row items-center justify-between mt-6 gap-3">
                        <a href="{{ route('admin.users.index') }}"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 bg-gray-200 text-gray-800 rounded-md text-sm font-semibold hover:bg-gray-300 transition-colors duration-150">
                            <i class="bi bi-arrow-left mr-2"></i>
                            Kembali
                        </a>

                        <button type="submit"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 bg-pink-600 text-white rounded-md text-sm font-semibold hover:bg-pink-700 transition-colors duration-150 shadow-sm">
                            <i class="bi bi-save mr-2"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>

                {{-- Additional Info --}}
                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start">
                        <i class="bi bi-info-circle-fill text-blue-500 mr-3 mt-0.5"></i>
                        <div class="text-sm text-blue-700">
                            <p class="font-semibold mb-1">Info Tambahan:</p>
                            <ul class="list-disc list-inside space-y-1 ml-2">
                                <li>User yang dinonaktifkan tidak dapat login ke sistem</li>
                                <li>Data user tetap tersimpan dan dapat diaktifkan kembali</li>
                                <li>Untuk mengubah data user lainnya, hubungi administrator</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
