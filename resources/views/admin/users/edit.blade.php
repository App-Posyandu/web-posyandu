@extends('admin.layouts.index')
@section('title', 'Ubah Pengguna')
@section('content')
    <div>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Ubah Data Pengguna: ') }} {{ $user->name }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-8 text-gray-900">
                        <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')

                            <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">Formulir Ubah Pengguna</h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                                <div>
                                    <x-input-label for="nik" :value="__('NIK')" />
                                    <x-text-input id="nik" class="block mt-1 w-full" type="text" name="nik"
                                        :value="old('nik', $user->nik)" required autofocus placeholder="Masukkan NIK" />
                                    <x-input-error :messages="$errors->get('nik')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="name" :value="__('Nama Lengkap')" />
                                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                        :value="old('name', $user->name)" required placeholder="Masukkan nama lengkap" />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>

                                <div class="md:col-span-2">
                                    <x-input-label for="alamat" :value="__('Alamat')" />
                                    <textarea id="alamat" name="alamat"
                                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        rows="3" required placeholder="Masukkan alamat lengkap">{{ old('alamat', $user->alamat) }}</textarea>
                                    <x-input-error :messages="$errors->get('alamat')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="tempat_lahir" :value="__('Tempat Lahir')" />
                                    <x-text-input id="tempat_lahir" class="block mt-1 w-full" type="text"
                                        name="tempat_lahir" :value="old('tempat_lahir', $user->tempat_lahir)" required />
                                    <x-input-error :messages="$errors->get('tempat_lahir')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="tanggal_lahir" :value="__('Tanggal Lahir')" />
                                    <x-text-input id="tanggal_lahir" class="block mt-1 w-full" type="date"
                                        name="tanggal_lahir" :value="old('tanggal_lahir', $user->tanggal_lahir)" required />
                                    <x-input-error :messages="$errors->get('tanggal_lahir')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="jenis_kelamin" :value="__('Jenis Kelamin')" />
                                    <select id="jenis_kelamin" name="jenis_kelamin"
                                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required>
                                        <option value="Laki-laki" @selected(old('jenis_kelamin', $user->jenis_kelamin) == 'Laki-laki')>Laki-laki</option>
                                        <option value="Perempuan" @selected(old('jenis_kelamin', $user->jenis_kelamin) == 'Perempuan')>Perempuan</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('jenis_kelamin')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="posyandu_id" :value="__('Posyandu')" />
                                    <select id="posyandu_id" name="posyandu_id"
                                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required>
                                        @foreach ($posyandus as $posyandu)
                                            <option value="{{ $posyandu->id }}" @selected(old('posyandu_id', $user->posyandu_id) == $posyandu->id)>
                                                {{ $posyandu->nama_posyandu }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('posyandu_id')" class="mt-2" />
                                </div>

                                {{-- <div x-data="{ previewUrl: '{{ $user->ktp ?? '' }}' }">
                                    <x-input-label for="ktp" :value="__('Ubah KTP (Opsional)')" />
                                    <div
                                        class="mt-1 w-full h-32 flex items-center justify-center border-2 border-dashed border-gray-300 rounded-md">
                                        <img x-show="previewUrl" :src="previewUrl"
                                            class="max-h-full max-w-full object-contain" alt="Preview KTP">
                                        <span x-show="!previewUrl" class="text-gray-400">Tidak ada gambar KTP</span>
                                    </div>
                                    <label for="ktp"
                                        class="mt-2 inline-block px-4 py-2 bg-white text-gray-700 rounded-md shadow-sm border border-gray-300 cursor-pointer hover:bg-gray-50 text-sm">
                                        <span>Pilih File Baru...</span>
                                    </label>
                                    <input id="ktp" class="hidden" type="file" name="ktp" accept="image/*"
                                        @change="previewUrl = URL.createObjectURL($event.target.files[0])" />
                                    <x-input-error :messages="$errors->get('ktp')" class="mt-2" />
                                </div>

                                <div x-data="{ previewUrl: '{{ $user->kk ?? '' }}' }">
                                    <x-input-label for="kk" :value="__('Ubah KK (Opsional)')" />
                                    <div
                                        class="mt-1 w-full h-32 flex items-center justify-center border-2 border-dashed border-gray-300 rounded-md">
                                        <img x-show="previewUrl" :src="previewUrl"
                                            class="max-h-full max-w-full object-contain" alt="Preview KK">
                                        <span x-show="!previewUrl" class="text-gray-400">Tidak ada gambar KK</span>
                                    </div>
                                    <label for="kk"
                                        class="mt-2 inline-block px-4 py-2 bg-white text-gray-700 rounded-md shadow-sm border border-gray-300 cursor-pointer hover:bg-gray-50 text-sm">
                                        <span>Pilih File Baru...</span>
                                    </label>
                                    <input id="kk" class="hidden" type="file" name="kk" accept="image/*"
                                        @change="previewUrl = URL.createObjectURL($event.target.files[0])" />
                                    <x-input-error :messages="$errors->get('kk')" class="mt-2" />
                                </div> --}}

                                <div>
                                    <x-input-label for="email" :value="__('Email')" />
                                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                                        :value="old('email', $user->email)" required />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="no_telepon" :value="__('Nomor Telepon')" />
                                    <x-text-input id="no_telepon" class="block mt-1 w-full" type="text"
                                        name="no_telepon" :value="old('no_telepon', $user->no_telepon)" required />
                                    <x-input-error :messages="$errors->get('no_telepon')" class="mt-2" />
                                </div>

                                <div class="md:col-span-2">
                                    <x-input-label for="role" :value="__('Role')" />
                                    <select id="role" name="role"
                                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required>
                                        <option value="masyarakat" @selected(old('role', $user->role) == 'masyarakat')>Masyarakat</option>
                                        <option value="kader" @selected(old('role', $user->role) == 'kader')>Kader</option>
                                        <option value="ketua-kader" @selected(old('role', $user->role) == 'ketua-kader')>Ketua Kader</option>
                                        <option value="kabid" @selected(old('role', $user->role) == 'kabid')>Kabid</option>
                                        <option value="admin" @selected(old('role', $user->role) == 'admin')>Admin</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                                </div>

                                <div class="mt-4 md:col-span-2">
                                    <x-input-label for="password" :value="__('Password Baru (Opsional)')" />
                                    <x-text-input id="password" class="block mt-1 w-full" type="password"
                                        name="password" autocomplete="new-password"
                                        placeholder="Biarkan kosong jika tidak ingin diubah" />
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>

                                <div class="md:col-span-2">
                                    <x-input-label for="password_confirmation" :value="__('Konfirmasi Password Baru')" />
                                    <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                                        name="password_confirmation" />
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-8 gap-4">
                                <a href="{{ route('admin.users.index') }}"
                                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm font-semibold hover:bg-gray-300">Batal</a>
                                <x-primary-button>
                                    {{ __('Simpan Perubahan') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
