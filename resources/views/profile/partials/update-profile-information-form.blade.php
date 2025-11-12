<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Informasi Profil') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            Lengkapi profil Anda untuk mengajukan layanan atau mendapatkan verifikasi.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    {{-- 1. Tambahkan enctype untuk upload file --}}
    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <x-input-label for="name" :value="__('Nama')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)"
                    required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="nik" :value="__('NIK')" />
                <x-text-input id="nik" name="nik" type="text" class="mt-1 block w-full" :value="old('nik', $user->nik)"
                    placeholder="Masukkan 16 digit NIK" />
                <x-input-error class="mt-2" :messages="$errors->get('nik')" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)"
                    required />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
                {{-- ... (kode verifikasi email Anda) ... --}}
            </div>

            <div class="md:col-span-2">
                <x-input-label for="alamat" :value="__('Alamat')" />
                <textarea id="alamat" name="alamat" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" rows="3">{{ old('alamat', $user->alamat) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('alamat')" />
            </div>

            <div>
                <x-input-label for="tempat_lahir" :value="__('Tempat Lahir')" />
                <x-text-input id="tempat_lahir" name="tempat_lahir" type="text" class="mt-1 block w-full"
                    :value="old('tempat_lahir', $user->tempat_lahir)" />
                <x-input-error class="mt-2" :messages="$errors->get('tempat_lahir')" />
            </div>

            <div>
                <x-input-label for="tanggal_lahir" :value="__('Tanggal Lahir')" />
                <x-text-input id="tanggal_lahir" name="tanggal_lahir" type="date" class="mt-1 block w-full"
                    :value="old('tanggal_lahir', $user->tanggal_lahir)" />
                <x-input-error class="mt-2" :messages="$errors->get('tanggal_lahir')" />
            </div>

            <div>
                <x-input-label for="jenis_kelamin" :value="__('Jenis Kelamin')" />
                <select id="jenis_kelamin" name="jenis_kelamin"
                    class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                    <option value="" disabled>Pilih Jenis Kelamin</option>
                    <option value="Laki-laki" @selected(old('jenis_kelamin', $user->jenis_kelamin) == 'Laki-laki')>Laki-laki</option>
                    <option value="Perempuan" @selected(old('jenis_kelamin', $user->jenis_kelamin) == 'Perempuan')>Perempuan</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('jenis_kelamin')" />
            </div>

            <div>
                <x-input-label for="no_telepon" :value="__('Nomor Telepon')" />
                <x-text-input id="no_telepon" name="no_telepon" type="text" class="mt-1 block w-full"
                    :value="old('no_telepon', $user->no_telepon)" />
                <x-input-error class="mt-2" :messages="$errors->get('no_telepon')" />
            </div>

            <div x-data="{ previewUrl: '{{ $user->ktp ?? '' }}' }">
                <x-input-label for="ktp" :value="__('KTP (Opsional)')" />
                <div
                    class="mt-1 w-full h-32 flex items-center justify-center border-2 border-dashed border-gray-300 rounded-md">
                    <img x-show="previewUrl && previewUrl.startsWith('data:image')" :src="previewUrl"
                        class="max-h-full max-w-full object-contain" alt="Preview KTP">
                    <span x-show="previewUrl && !previewUrl.startsWith('data:image')" class="text-gray-500">File PDF
                        (Tidak ada preview)</span>
                    <span x-show="!previewUrl" class="text-gray-400">Preview KTP/PDF</span>
                </div>
                <input id="ktp" class="block mt-2" type="file" name="ktp" accept="image/*,application/pdf"
                    @change="
                           const file = $event.target.files[0];
                           if (file && file.type.startsWith('image/')) {
                               previewUrl = URL.createObjectURL(file);
                           } else if (file) {
                               previewUrl = 'pdf-selected'; // Placeholder
                           } else {
                               previewUrl = '{{ $user->ktp ?? '' }}';
                           }
                       " />
                <x-input-error class="mt-2" :messages="$errors->get('ktp')" />
            </div>

            <div x-data="{ previewUrl: '{{ $user->kk ?? '' }}' }">
                <x-input-label for="kk" :value="__('KK (Opsional)')" />
                <div
                    class="mt-1 w-full h-32 flex items-center justify-center border-2 border-dashed border-gray-300 rounded-md">
                    <img x-show="previewUrl && previewUrl.startsWith('data:image')" :src="previewUrl"
                        class="max-h-full max-w-full object-contain" alt="Preview KK">
                    <span x-show="previewUrl && !previewUrl.startsWith('data:image')" class="text-gray-500">File PDF
                        (Tidak ada preview)</span>
                    <span x-show="!previewUrl" class="text-gray-400">Preview KK/PDF</span>
                </div>
                <input id="kk" class="block mt-2" type="file" name="kk" accept="image/*,application/pdf"
                    @change="
                           const file = $event.target.files[0];
                           if (file && file.type.startsWith('image/')) {
                               previewUrl = URL.createObjectURL(file);
                           } else if (file) {
                               previewUrl = 'pdf-selected'; // Placeholder
                           } else {
                               previewUrl = '{{ $user->kk ?? '' }}';
                           }
                       " />
                <x-input-error class="mt-2" :messages="$errors->get('kk')" />
            </div>

        </div> {{-- Akhir dari grid --}}

        <div class="flex items-center gap-4 mt-6">
            <x-primary-button>{{ __('Simpan') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600">{{ __('Tersimpan.') }}</p>
            @endif
        </div>
    </form>
</section>
