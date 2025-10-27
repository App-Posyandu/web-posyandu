<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
        @csrf

        @if (session()->has('google_user_email'))
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Lengkapi Pendaftaran Akun Anda</h2>
        @else
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Pembuatan Akun</h2>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">

            <div>
                <x-input-label for="nik" :value="__('NIK')" />
                <x-text-input id="nik" class="block mt-1 w-full" type="text" name="nik" :value="old('nik')"
                    required autofocus placeholder="Masukkan NIK" />
                <x-input-error :messages="$errors->get('nik')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="name" :value="__('Nama')" />
                @if (session()->has('google_user_name'))
                    <x-text-input id="name" class="block mt-1 w-full bg-gray-100 cursor-not-allowed" type="text"
                        name="name" :value="session('google_user_name')" required readonly />
                @else
                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                        :value="old('name')" required autocomplete="name" placeholder="Masukkan Nama" />
                @endif
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="alamat" :value="__('Alamat')" />
                <textarea id="alamat" name="alamat"
                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    rows="3" required placeholder="Masukkan Alamat Lengkap">{{ old('alamat') }}</textarea>
                <x-input-error :messages="$errors->get('alamat')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="tempat_lahir" :value="__('Tempat Lahir')" />
                <x-text-input id="tempat_lahir" class="block mt-1 w-full" type="text" name="tempat_lahir"
                    :value="old('tempat_lahir')" required placeholder="Masukkan Tempat Lahir" />
                <x-input-error :messages="$errors->get('tempat_lahir')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="tanggal_lahir" :value="__('Tanggal Lahir')" />
                <x-text-input id="tanggal_lahir" class="block mt-1 w-full" type="date" name="tanggal_lahir"
                    :value="old('tanggal_lahir')" required />
                <x-input-error :messages="$errors->get('tanggal_lahir')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="jenis_kelamin" :value="__('Jenis Kelamin')" />
                <select id="jenis_kelamin" name="jenis_kelamin"
                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="" disabled selected>Pilih Jenis Kelamin</option>
                    <option value="Laki-laki" @selected(old('jenis_kelamin') == 'Laki-laki')>Laki-laki</option>
                    <option value="Perempuan" @selected(old('jenis_kelamin') == 'Perempuan')>Perempuan</option>
                </select>
                <x-input-error :messages="$errors->get('jenis_kelamin')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="nama_posyandu" :value="__('Nama Posyandu')" />
                <x-text-input id="nama_posyandu" class="block mt-1 w-full" type="text" name="nama_posyandu"
                    :value="old('nama_posyandu')" required placeholder="Masukkan Nama Posyandu" />
                <x-input-error :messages="$errors->get('nama_posyandu')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="desa" :value="__('Desa/Kelurahan')" />
                <x-text-input id="desa" class="block mt-1 w-full" type="text" name="desa" :value="old('desa')"
                    required placeholder="Masukkan Desa/Kelurahan" />
                <x-input-error :messages="$errors->get('desa')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="kecamatan" :value="__('Kecamatan')" />
                <x-text-input id="kecamatan" class="block mt-1 w-full" type="text" name="kecamatan"
                    :value="old('kecamatan')" required placeholder="Masukkan Kecamatan" />
                <x-input-error :messages="$errors->get('kecamatan')" class="mt-2" />
            </div>

            <div x-data="{ fileName: '' }">
                <x-input-label for="ktp" :value="__('KTP')" />
                <label for="ktp"
                    class="mt-1 flex justify-between items-center px-4 py-2 bg-white text-gray-500 rounded-md shadow-sm border border-gray-300 cursor-pointer hover:text-gray-700">
                    <span x-text="fileName || 'Unggah KTP'"></span>
                    <i class="bi bi-cloud-upload text-pink-500 text-lg"></i>
                </label>
                <input id="ktp" class="hidden" type="file" name="ktp" accept="image/*,application/pdf" required
                    @change="fileName = $event.target.files[0].name" />
                <x-input-error :messages="$errors->get('ktp')" class="mt-2" />
            </div>

            <div x-data="{ fileName: '' }">
                <x-input-label for="kk" :value="__('KK')" />
                <label for="kk"
                    class="mt-1 flex justify-between items-center px-4 py-2 bg-white text-gray-500 rounded-md shadow-sm border border-gray-300 cursor-pointer hover:text-gray-700">
                    <span x-text="fileName || 'Unggah KK'"></span>
                    <i class="bi bi-cloud-upload text-pink-500 text-lg"></i>
                </label>
                <input id="kk" class="hidden" type="file" name="kk" accept="image/*,application/pdf" required
                    @change="fileName = $event.target.files[0].name" />
                <x-input-error :messages="$errors->get('kk')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Email')" />
                @if (session()->has('google_user_email'))
                    <x-text-input id="email" class="block mt-1 w-full bg-gray-100 cursor-not-allowed"
                        type="email" name="email" :value="session('google_user_email')" required readonly />
                @else
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                        :value="old('email')" required autocomplete="username" placeholder="Masukkan Email" />
                @endif
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="role" :value="__('Role')" />
                <select id="role" name="role"
                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="" disabled selected>Pilih role</option>
                    <option value="masyarakat" @selected(old('role') == 'masyarakat')>Masyarakat</option>
                    <option value="kader" @selected(old('role') == 'kader')>Kader</option>
                </select>
                <x-input-error :messages="$errors->get('role')" class="mt-2" />
            </div>
            <div class="md:col-span-2">
                <x-input-label for="no_telepon" :value="__('Nomor Telepon')" />
                <x-text-input id="no_telepon" class="block mt-1 w-full" type="text" name="no_telepon"
                    :value="old('no_telepon')" required placeholder="Contoh: 081234567890" />
                <x-input-error :messages="$errors->get('no_telepon')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                    autocomplete="new-password" placeholder="Masukkan Password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                    name="password_confirmation" required autocomplete="new-password"
                    placeholder="Konfirmasi Password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

        </div>
        <div class="flex items-center justify-end mt-6">
            <a class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                href="{{ route('login') }}">
                {{ __('Masuk') }}
            </a>

            <button type="submit"
                class="ms-4 inline-flex items-center px-4 py-2 bg-pink-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-pink-600">
                {{ __('Daftar') }}
            </button>
        </div>
    </form>
</x-guest-layout>
