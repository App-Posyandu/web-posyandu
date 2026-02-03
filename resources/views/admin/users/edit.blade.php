@extends('dashboard.layouts.dashboard')
@section('title', 'Edit Pengguna')
@section('content')
    <div class="w-full max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">
                <i class="bi bi-person-fill-gear mr-2 text-pink-600"></i>
                Edit Pengguna: {{ $user->name }}
            </h2>
            <a href="{{ route('admin.users.index') }}" class="flex items-center text-gray-600 hover:text-gray-900">
                <i class="bi bi-arrow-left mr-2"></i> Kembali
            </a>
        </div>

        @if (auth()->user()->id === $user->id)
            <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                <div class="flex">
                    <i class="bi bi-exclamation-triangle-fill text-yellow-400 mr-3"></i>
                    <div>
                        <p class="text-sm text-yellow-700">
                            <strong>Perhatian:</strong> Anda sedang mengedit akun Anda sendiri.
                            Untuk keamanan, role dan beberapa field sensitif tidak dapat diubah.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.update', $user) }}" x-data="userEditForm()" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="bi bi-person-circle text-pink-600"></i>
                    Informasi Dasar
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="name" :value="__('Nama Lengkap')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                            :value="old('name', $user->name)" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="nik" :value="__('NIK')" />
                        <x-text-input id="nik" name="nik" type="text" class="mt-1 block w-full"
                            :value="old('nik', $user->nik)" maxlength="16" />
                        <x-input-error :messages="$errors->get('nik')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                            :value="old('email', $user->email)" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="no_telepon" :value="__('No. Telepon')" />
                        <x-text-input id="no_telepon" name="no_telepon" type="text" class="mt-1 block w-full"
                            :value="old('no_telepon', $user->no_telepon)" />
                        <x-input-error :messages="$errors->get('no_telepon')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="tempat_lahir" :value="__('Tempat Lahir')" />
                        <x-text-input id="tempat_lahir" name="tempat_lahir" type="text" class="mt-1 block w-full"
                            :value="old('tempat_lahir', $user->tempat_lahir)" />
                        <x-input-error :messages="$errors->get('tempat_lahir')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="tanggal_lahir" :value="__('Tanggal Lahir')" />
                        <x-text-input id="tanggal_lahir" name="tanggal_lahir" type="date" class="mt-1 block w-full"
                            :value="old(
                                'tanggal_lahir',
                                $user->tanggal_lahir
                                    ? \Carbon\Carbon::parse($user->tanggal_lahir)->format('Y-m-d')
                                    : '',
                            )" />
                        <x-input-error :messages="$errors->get('tanggal_lahir')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="jenis_kelamin" :value="__('Jenis Kelamin')" />
                        <select id="jenis_kelamin" name="jenis_kelamin"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-pink-500 focus:ring-pink-500">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="Laki-laki"
                                {{ old('jenis_kelamin', $user->jenis_kelamin) === 'Laki-laki' ? 'selected' : '' }}>
                                Laki-laki
                            </option>
                            <option value="Perempuan"
                                {{ old('jenis_kelamin', $user->jenis_kelamin) === 'Perempuan' ? 'selected' : '' }}>
                                Perempuan
                            </option>
                        </select>
                        <x-input-error :messages="$errors->get('jenis_kelamin')" class="mt-2" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="alamat" :value="__('Alamat Lengkap')" />
                        <textarea id="alamat" name="alamat" rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-pink-500 focus:ring-pink-500">{{ old('alamat', $user->alamat) }}</textarea>
                        <x-input-error :messages="$errors->get('alamat')" class="mt-2" />
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="bi bi-key text-pink-600"></i>
                    Keamanan & Status
                </h3>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border">
                        <div>
                            <p class="font-medium text-gray-800">Status Akun</p>
                            <p class="text-sm text-gray-600">
                                {{ $user->is_active ? 'User dapat login' : 'User tidak dapat login' }}
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1"
                                {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-pink-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-600">
                            </div>
                        </label>
                    </div>
                    <div class="border-t pt-4">
                        <div class="flex items-center gap-2 mb-3">
                            <input type="checkbox" id="change_password"
                                @change="showPasswordFields = !showPasswordFields"
                                class="w-4 h-4 text-pink-600 bg-gray-100 border-gray-300 rounded focus:ring-pink-500">
                            <label for="change_password" class="text-sm font-medium text-gray-700">
                                Ubah Password
                            </label>
                        </div>

                        <div x-show="showPasswordFields" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="password" :value="__('Password Baru')" />
                                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                                    autocomplete="new-password" />
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                <p class="text-xs text-gray-500 mt-1">Minimal 8 karakter</p>
                            </div>
                            <div>
                                <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
                                <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                                    class="mt-1 block w-full" autocomplete="new-password" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
                <a href="{{ route('admin.users.index') }}"
                    class="w-full sm:w-auto px-6 py-2.5 bg-gray-200 text-gray-800 rounded-md text-sm font-semibold hover:bg-gray-300 transition text-center">
                    <i class="bi bi-x-circle mr-2"></i>
                    Batal
                </a>

                <button type="submit"
                    class="w-full sm:w-auto px-6 py-2.5 bg-pink-600 text-white rounded-md text-sm font-semibold hover:bg-pink-700 transition shadow-sm">
                    <i class="bi bi-save mr-2"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function userEditForm() {
                return {
                    selectedRole: '{{ $user->role }}',
                    showPasswordFields: false,

                    handleRoleChange() {
                        if (this.selectedRole === 'operator-desa') {
                            document.getElementById('posyandu_id').value = '';
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection
