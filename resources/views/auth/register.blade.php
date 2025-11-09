<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" x-data="registerForm()">
        @csrf

        @if (session()->has('google_user_email'))
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Lengkapi Pendaftaran Akun Anda</h2>
        @else
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Pembuatan Akun</h2>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">

            <div>
                <x-input-label class="text-sm md:text-lg" for="nik" :value="__('NIK')" />
                <x-text-input id="nik" class="block mt-1 w-full" type="text" name="nik" :value="old('nik')"
                    required autofocus placeholder="Masukkan NIK" />
                <x-input-error :messages="$errors->get('nik')" class="mt-2" />
            </div>

            <div>
                <x-input-label class="text-sm md:text-lg" for="name" :value="__('Nama')" />
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
                <x-input-label class="text-sm md:text-lg" for="alamat" :value="__('Alamat')" />
                <textarea id="alamat" name="alamat"
                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    rows="3" required placeholder="Masukkan Alamat Lengkap">{{ old('alamat') }}</textarea>
                <x-input-error :messages="$errors->get('alamat')" class="mt-2" />
            </div>

            <div>
                <x-input-label class="text-sm md:text-lg" for="tempat_lahir" :value="__('Tempat Lahir')" />
                <x-text-input id="tempat_lahir" class="block mt-1 w-full" type="text" name="tempat_lahir"
                    :value="old('tempat_lahir')" required placeholder="Masukkan Tempat Lahir" />
                <x-input-error :messages="$errors->get('tempat_lahir')" class="mt-2" />
            </div>

            <div>
                <x-input-label class="text-sm md:text-lg" for="tanggal_lahir" :value="__('Tanggal Lahir')" />
                <x-text-input id="tanggal_lahir" class="block mt-1 w-full" type="date" name="tanggal_lahir"
                    :value="old('tanggal_lahir')" required />
                <x-input-error :messages="$errors->get('tanggal_lahir')" class="mt-2" />
            </div>

            <div>
                <x-input-label class="text-sm md:text-lg" for="jenis_kelamin" :value="__('Jenis Kelamin')" />
                <select id="jenis_kelamin" name="jenis_kelamin"
                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="" disabled selected>Pilih Jenis Kelamin</option>
                    <option value="Laki-laki" @selected(old('jenis_kelamin') == 'Laki-laki')>Laki-laki</option>
                    <option value="Perempuan" @selected(old('jenis_kelamin') == 'Perempuan')>Perempuan</option>
                </select>
                <x-input-error :messages="$errors->get('jenis_kelamin')" class="mt-2" />
            </div>

            <div>
                <x-input-label class="text-sm md:text-lg" for="no_telepon" :value="__('Nomor Telepon')" />
                <x-text-input id="no_telepon" class="block mt-1 w-full" type="text" name="no_telepon"
                    :value="old('no_telepon')" required placeholder="Contoh: 081234567890" />
                <x-input-error :messages="$errors->get('no_telepon')" class="mt-2" />
            </div>

            {{-- ✅ COMBOBOX KABUPATEN --}}
            <div>
                <x-input-label class="text-sm md:text-lg" for="kabupaten" :value="__('Kabupaten')" />
                <select id="kabupaten" x-model="kabupatenId" @change="loadKecamatan()"
                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                    <option value="" disabled selected>Pilih Kabupaten</option>
                    <template x-for="kab in kabupatens" :key="kab.id">
                        <option :value="kab.id" x-text="kab.name"></option>
                    </template>
                </select>
                <input type="hidden" name="kabupaten" :value="kabupatenName">
                <x-input-error :messages="$errors->get('kabupaten')" class="mt-2" />
            </div>

            {{-- ✅ COMBOBOX KECAMATAN --}}
            <div>
                <x-input-label class="text-sm md:text-lg" for="kecamatan" :value="__('Kecamatan')" />
                <select id="kecamatan" x-model="kecamatanId" @change="loadDesa()"
                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    :disabled="!kabupatenId" required>
                    <option value="" disabled selected>Pilih Kecamatan</option>
                    <template x-for="kec in kecamatans" :key="kec.id">
                        <option :value="kec.id" x-text="kec.name"></option>
                    </template>
                </select>
                <input type="hidden" name="kecamatan" :value="kecamatanName">
                <x-input-error :messages="$errors->get('kecamatan')" class="mt-2" />
            </div>

            {{-- ✅ COMBOBOX DESA --}}
            <div>
                <x-input-label class="text-sm md:text-lg" for="desa" :value="__('Desa/Kelurahan')" />
                <select id="desa" x-model="desaId" @change="desaName = desas.find(d => d.id === desaId)?.name"
                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    :disabled="!kecamatanId" required>
                    <option value="" disabled selected>Pilih Desa/Kelurahan</option>
                    <template x-for="des in desas" :key="des.id">
                        <option :value="des.id" x-text="des.name"></option>
                    </template>
                </select>
                <input type="hidden" name="desa" :value="desaName">
                <x-input-error :messages="$errors->get('desa')" class="mt-2" />
            </div>

            <div>
                <x-input-label class="text-sm md:text-lg" for="nama_posyandu" :value="__('Nama Posyandu')" />
                <x-text-input id="nama_posyandu" class="block mt-1 w-full" type="text" name="nama_posyandu"
                    :value="old('nama_posyandu')" required placeholder="Masukkan Nama Posyandu" />
                <x-input-error :messages="$errors->get('nama_posyandu')" class="mt-2" />
            </div>

            <div x-data="{ fileName: '' }">
                <x-input-label class="text-sm md:text-lg" for="ktp" :value="__('KTP')" />
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
                <x-input-label class="text-sm md:text-lg" for="kk" :value="__('KK')" />
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
                <x-input-label class="text-sm md:text-lg" for="email" :value="__('Email')" />
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
                <x-input-label class="text-sm md:text-lg" for="role" :value="__('Role')" />
                <select id="role" name="role" x-model="role"
                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="" disabled selected>Pilih role</option>
                    <option value="masyarakat" @selected(old('role') == 'masyarakat')>Masyarakat</option>
                    <option value="kader" @selected(old('role') == 'kader')>Kader</option>
                </select>
                <x-input-error :messages="$errors->get('role')" class="mt-2" />
            </div>

            {{-- ✅ DROPDOWN BIDANG (HANYA MUNCUL JIKA ROLE = KADER) --}}
            <div x-show="role === 'kader'" x-transition class="md:col-span-2">
                <x-input-label class="text-sm md:text-lg" for="bidang_id" :value="__('Bidang Tugas')" />
                <select id="bidang_id" name="bidang_id"
                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    :required="role === 'kader'">
                    <option value="" disabled selected>Pilih Bidang</option>
                    @foreach(\App\Models\BidangPengajuan::orderBy('nama_bidang')->get() as $bidang)
                        <option value="{{ $bidang->id }}" @selected(old('bidang_id') == $bidang->id)>
                            {{ $bidang->nama_bidang }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Kader hanya bisa mengelola 1 bidang.</p>
                <x-input-error :messages="$errors->get('bidang_id')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <x-input-label class="text-sm md:text-lg" for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                    autocomplete="new-password" placeholder="Masukkan Password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <x-input-label class="text-sm md:text-lg" for="password_confirmation" :value="__('Konfirmasi Password')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                    name="password_confirmation" required autocomplete="new-password"
                    placeholder="Konfirmasi Password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

        </div>
        <div class="flex items-center justify-end mt-6">
            <a class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm md:text-base font-medium text-gray-700 bg-white hover:bg-gray-50"
                href="{{ route('login') }}">
                {{ __('Masuk') }}
            </a>

            <button type="submit"
                class="ms-4 inline-flex items-center px-4 py-2 bg-pink-500 border border-transparent rounded-md font-semibold text-sm md:text-base text-white uppercase tracking-widest hover:bg-pink-600">
                {{ __('Daftar') }}
            </button>
        </div>
    </form>

    <script>
        function registerForm() {
            return {
                kabupatenId: '{{ old("kabupaten_id") }}',
                kecamatanId: '{{ old("kecamatan_id") }}',
                desaId: '{{ old("desa_id") }}',
                kabupatenName: '{{ old("kabupaten") }}',
                kecamatanName: '{{ old("kecamatan") }}',
                desaName: '{{ old("desa") }}',
                role: '{{ old("role") }}',
                kabupatens: [],
                kecamatans: [],
                desas: [],

                async init() {
                    await this.loadKabupaten();
                },

                async loadKabupaten() {
                    try {
                        const response = await fetch('{{ env("API_WILAYAH_URL") }}regencies/33.json');
                        this.kabupatens = await response.json();
                    } catch (error) {
                        console.error('Error loading kabupaten:', error);
                    }
                },

                async loadKecamatan() {
                    if (!this.kabupatenId) return;

                    this.kecamatanId = '';
                    this.desaId = '';
                    this.kecamatans = [];
                    this.desas = [];

                    this.kabupatenName = this.kabupatens.find(k => k.id === this.kabupatenId)?.name;

                    try {
                        const response = await fetch(`{{ env("API_WILAYAH_URL") }}districts/${this.kabupatenId}.json`);
                        this.kecamatans = await response.json();
                    } catch (error) {
                        console.error('Error loading kecamatan:', error);
                    }
                },

                async loadDesa() {
                    if (!this.kecamatanId) return;

                    this.desaId = '';
                    this.desas = [];

                    this.kecamatanName = this.kecamatans.find(k => k.id === this.kecamatanId)?.name;

                    try {
                        const response = await fetch(`{{ env("API_WILAYAH_URL") }}villages/${this.kecamatanId}.json`);
                        this.desas = await response.json();
                    } catch (error) {
                        console.error('Error loading desa:', error);
                    }
                }
            }
        }
    </script>
</x-guest-layout>
