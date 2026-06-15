<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" x-data="dependentDropdowns()">
        @csrf

        @if (session()->has('google_user_email'))
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Lengkapi Pendaftaran Akun Anda</h2>
        @else
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Pembuatan Akun</h2>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <strong class="block mb-1">Mohon periksa kembali isian Anda:</strong>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">

            <div>
                <x-input-label class="text-sm md:text-lg" for="name" :value="__('Nama')" />
                @if (session()->has('google_user_name'))
                    <x-text-input id="name" class="block mt-1 w-full bg-gray-100 cursor-not-allowed" type="text"
                        name="name" :value="session('google_user_name')" required readonly />
                @else
                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                        :value="old('name')" required autocomplete="name" placeholder="Masukkan Nama" />
                @endif
                <p class="mt-1 text-xs text-gray-500"><i class="bi bi-info-circle mr-1"></i>Nama lengkap sesuai KTP, maks. 255 karakter.</p>
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label class="text-sm md:text-lg" for="email" :value="__('Email')" />
                @if (session()->has('google_user_email'))
                    <x-text-input id="email" class="block mt-1 w-full bg-gray-100 cursor-not-allowed" type="email"
                        name="email" :value="session('google_user_email')" required readonly />
                @else
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                        :value="old('email')" required autocomplete="username" placeholder="Masukkan Email" />
                @endif
                <p class="mt-1 text-xs text-gray-500"><i class="bi bi-info-circle mr-1"></i>Format email valid, contoh: nama@domain.com. Harus unik.</p>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label class="text-sm md:text-lg" for="tempat_lahir" :value="__('Tempat Lahir')" />
                <x-text-input id="tempat_lahir" class="block mt-1 w-full" type="text" name="tempat_lahir"
                    :value="old('tempat_lahir')" required placeholder="Contoh: Kebumen" />
                <p class="mt-1 text-xs text-gray-500"><i class="bi bi-info-circle mr-1"></i>Nama kota/kabupaten sesuai KTP.</p>
                <x-input-error :messages="$errors->get('tempat_lahir')" class="mt-2" />
            </div>

            <div>
                <x-input-label class="text-sm md:text-lg" for="tanggal_lahir" :value="__('Tanggal Lahir')" />
                <x-text-input id="tanggal_lahir" class="block mt-1 w-full" type="date" name="tanggal_lahir"
                    :value="old('tanggal_lahir')" required />
                <p class="mt-1 text-xs text-gray-500"><i class="bi bi-info-circle mr-1"></i>Tanggal lahir sesuai KTP.</p>
                <x-input-error :messages="$errors->get('tanggal_lahir')" class="mt-2" />
            </div>

            <div>
                <x-input-label class="text-sm md:text-lg" for="jenis_kelamin" :value="__('Jenis Kelamin')" />
                <select id="jenis_kelamin" name="jenis_kelamin"
                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    required>
                    <option value="" disabled selected>Pilih Jenis Kelamin</option>
                    <option value="Laki-laki" @selected(old('jenis_kelamin') == 'Laki-laki')>Laki-laki</option>
                    <option value="Perempuan" @selected(old('jenis_kelamin') == 'Perempuan')>Perempuan</option>
                </select>
                <p class="mt-1 text-xs text-gray-500"><i class="bi bi-info-circle mr-1"></i>Pilih salah satu: Laki-laki atau Perempuan.</p>
                <x-input-error :messages="$errors->get('jenis_kelamin')" class="mt-2" />
            </div>

            <div>
                <x-input-label class="text-sm md:text-lg" for="no_telepon" :value="__('Nomor Telepon')" />
                <x-text-input id="no_telepon" class="block mt-1 w-full" type="text" name="no_telepon"
                    :value="old('no_telepon')" required placeholder="Contoh: 081234567890" />
                <p class="mt-1 text-xs text-gray-500"><i class="bi bi-info-circle mr-1"></i>Nomor aktif WhatsApp, maks. 20 digit, dan unik.</p>
                <x-input-error :messages="$errors->get('no_telepon')" class="mt-2" />
            </div>

            <div x-data="{ open: false, search: '' }" @click.away="open = false" class="relative">
                <x-input-label class="text-sm md:text-lg" for="kabupaten" :value="__('Kabupaten')" />
                <input type="hidden" name="kabupaten" :value="selectedKabupaten">
                <div class="relative">
                    <input type="text" x-model="search" @focus="open=true" @input="open=true"
                        :placeholder="getKabupatenName(selectedKabupaten) || 'Cari Kabupaten...'"
                        class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        autocomplete="off">
                    <button type="button" @click="open=!open"
                        class="absolute inset-y-0 right-0 flex items-center px-3">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>
                </div>
                <div x-show="open" x-transition
                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                    <template x-for="kab in kabupatens.filter(k => k.name.toLowerCase().includes(search.toLowerCase()))"
                        :key="kab.code">
                        <div @click="selectedKabupaten=`${kab.code}_${kab.name}`; search = ''; open = false; fetchKecamatan()"
                            class="px-4 py-2 cursor-pointer hover:bg-indigo-50"
                            :class="{ 'bg-indigo-100': selectedKabupaten === `${kab.code}_${kab.name}` }"
                            x-text="kab.name">
                        </div>
                    </template>
                    <div x-show="kabupatens.filter(k => k.name.toLowerCase().includes(search.toLowerCase())).length === 0"
                        class="px-4 py-2 text-gray-500 text-sm">
                        Tidak ada hasil
                    </div>
                </div>
                <x-input-error :messages="$errors->get('kabupaten')" class="mt-2" />
            </div>

            <div x-data="{ open: false, search: '' }" @click.away="open=false" class="relative">
                <x-input-label class="text-sm md:text-lg" for="kecamatan" :value="__('Kecamatan')" />
                <input type="hidden" name="kecamatan" :value="selectedKecamatan">
                <div class="relative">
                    <input type="text" x-model="search" @focus="open = true" @input="open = true"
                        :placeholder="getKecamatanName(selectedKecamatan) || 'Cari Kecamatan...'"
                        :disabled="kecamatanList.length === 0"
                        class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                        autocomplete="off">
                    <button type="button" @click="open = !open"
                        class="absolute inset-y-0 right-0 flex items-center px-3"
                        :disabled="kecamatanList.length === 0">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>
                </div>
                <div x-show="open && !loadingKecamatan" x-transition
                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                    <template
                        x-for="kec in kecamatanList.filter(k => k.name.toLowerCase().includes(search.toLowerCase()))"
                        :key="kec.code">
                        <div @click="selectedKecamatan = `${kec.code}_${kec.name}`; search = ''; open = false; fetchDesa()"
                            class="px-4 py-2 cursor-pointer hover:bg-indigo-50"
                            :class="{ 'bg-indigo-100': selectedKecamatan === `${kec.code}_${kec.name}` }"
                            x-text="kec.name">
                        </div>
                    </template>
                    <div x-show="kecamatanList.filter(k => k.name.toLowerCase().includes(search.toLowerCase())).length === 0"
                        class="px-4 py-2 text-gray-500 text-sm">
                        Tidak ada hasil
                    </div>
                </div>
                <div x-show="loadingKecamatan"
                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                    <div class="px-4 py-2 text-gray-500 text-sm">Memuat data kecamatan...</div>
                </div>
                <x-input-error :messages="$errors->get('kecamatan')" class="mt-2" />
            </div>

            <div x-data="{ open: false, search: '' }" @click.away="open = false" class="relative">
                <x-input-label class="text-sm md:text-lg" for="desa" :value="__('Desa/Kelurahan')" />
                <input type="hidden" name="desa" :value="selectedDesa">
                <div class="relative">
                    <input type="text" x-model="search" @focus="open = true" @input="open = true"
                        :placeholder="getDesaName(selectedDesa) || 'Cari Desa/Kelurahan...'"
                        :disabled="desaList.length === 0"
                        class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                        autocomplete="off">
                    <button type="button" @click="open = !open"
                        class="absolute inset-y-0 right-0 flex items-center px-3" :disabled="desaList.length === 0">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>
                </div>
                <div x-show="open && !loadingDesa" x-transition
                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                    <template x-for="desa in desaList.filter(d => d.name.toLowerCase().includes(search.toLowerCase()))"
                        :key="desa.code">
                        <div @click="selectedDesa = `${desa.code}_${desa.name}`; selectedDesaName = desa.name; search = ''; open = false; fetchPosyandu()"
                            class="px-4 py-2 cursor-pointer hover:bg-indigo-50"
                            :class="{ 'bg-indigo-100': selectedDesa === `${desa.code}_${desa.name}` }"
                            x-text="desa.name">
                        </div>
                    </template>
                    <div x-show="desaList.filter(d => d.name.toLowerCase().includes(search.toLowerCase())).length === 0"
                        class="px-4 py-2 text-gray-500 text-sm">
                        Tidak ada hasil
                    </div>
                </div>
                <div x-show="loadingDesa"
                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                    <div class="px-4 py-2 text-gray-500 text-sm">Memuat data desa...</div>
                </div>
                <x-input-error :messages="$errors->get('desa')" class="mt-2" />
            </div>

            <div x-data="{ open: false, search: '', creating: false }" @click.away="open = false" class="relative">
                <x-input-label class="text-sm md:text-lg" for="posyandu" :value="__('Nama Posyandu')" />
                <input type="hidden" name="posyandu_id" :value="selectedPosyandu">
                <div class="relative">
                    <input type="text" x-model="search" @focus="open = true"
                        @input="open = true; creating = false"
                        :placeholder="getPosyanduName(selectedPosyandu) || 'Cari atau ketik nama posyandu baru...'"
                        :disabled="!selectedDesa"
                        class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                        autocomplete="off">
                    <button type="button" @click="open = !open"
                        class="absolute inset-y-0 right-0 flex items-center px-3" :disabled="!selectedDesa">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>
                </div>
                <div x-show="open && !loadingPosyandu" x-transition
                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                    <template x-for="posyandu in filteredPosyandu" :key="posyandu.id">
                        <div @click="selectPosyandu(posyandu); search = ''; open = false"
                            class="px-4 py-2 cursor-pointer hover:bg-indigo-50"
                            :class="{ 'bg-indigo-100': selectedPosyandu === posyandu.id }"
                            x-text="posyandu.nama_posyandu">
                        </div>
                    </template>
                    <template x-if="search && filteredPosyandu.length === 0 && !creating">
                        <div @click="createNewPosyandu(search); open = false"
                            class="px-4 py-2 cursor-pointer hover:bg-green-50 text-green-600 font-medium border-t">
                            ✨ Buat Posyandu Baru: <span x-text="search"></span>
                        </div>
                    </template>
                    <div x-show="filteredPosyandu.length === 0 && !search" class="px-4 py-2 text-gray-500 text-sm">
                        Tidak ada posyandu di wilayah ini
                    </div>
                </div>
                <div x-show="loadingPosyandu"
                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                    <div class="px-4 py-2 text-gray-500 text-sm">Memuat data posyandu...</div>
                </div>
                <p class="mt-1 text-xs text-gray-500">Ketik untuk mencari atau buat posyandu baru.</p>
                <x-input-error :messages="$errors->get('posyandu_id')" class="mt-2" />
            </div>
            
            <div id="bidang-field" style="display: none;" class="md:col-span-2">
                <x-input-label class="text-sm md:text-lg" for="bidang_id" :value="__('Bidang Tugas')" />
                <select id="bidang_id" name="bidang_id"
                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="" disabled selected>Pilih Bidang</option>
                    @foreach (\App\Models\BidangPengajuan::orderBy('nama_bidang')->get() as $bidang)
                        <option value="{{ $bidang->id }}" @selected(old('bidang_id') == $bidang->id)>
                            {{ $bidang->nama_bidang }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Kader hanya bisa mengelola 1 bidang.</p>
                <x-input-error :messages="$errors->get('bidang_id')" class="mt-2" />
            </div>
            
            <div x-show="selectedPosyandu" x-transition class="md:col-span-2">
                <x-input-label for="rw" :value="__('RW (Rukun Warga)')" />
                <span class="text-red-600">*</span>
                <select id="rw" name="rw" required
                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    x-model="selectedRw" @change="fetchRtList()">
                    <option value="" disabled selected>Pilih RW</option>
                    <template x-for="rw in availableRw" :key="rw">
                        <option :value="rw" x-text="rw"></option>
                    </template>
                </select>
                <p class="text-xs text-gray-500 mt-1">
                    <i class="bi bi-info-circle text-blue-500"></i>
                    Pilih RW sesuai domisili Anda (RW yang dilayani posyandu ini)
                </p>
                <x-input-error :messages="$errors->get('rw')" class="mt-2" />
            </div>

            <div x-show="selectedRw" x-transition class="md:col-span-2">
                <x-input-label for="rt" :value="__('RT (Rukun Tetangga)')" />
                <span class="text-gray-500 text-sm">(Opsional)</span>
                <select id="rt" name="rt"
                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    x-model="selectedRt">
                    <option value="">-- Tidak ada/Tidak tahu --</option>
                    <template x-for="rt in availableRt" :key="rt">
                        <option :value="rt" x-text="rt"></option>
                    </template>
                </select>
                <p class="text-xs text-gray-500 mt-1">
                    <i class="bi bi-info-circle text-blue-500"></i>
                    Pilih RT jika tahu (RT yang dilayani di RW Anda)
                </p>
                <x-input-error :messages="$errors->get('rt')" class="mt-2" />
            </div>

            <div x-show="selectedPosyandu && availableRw.length === 0" x-transition class="md:col-span-2">
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                    <div class="flex">
                        <i class="bi bi-exclamation-triangle text-yellow-400 mr-3"></i>
                        <div class="text-sm text-yellow-700">
                            <p class="font-semibold">Posyandu belum setup RW/RT</p>
                            <p class="mt-1">Posyandu yang Anda pilih belum memiliki mapping RW/RT. Silakan hubungi
                                Operator Desa atau pilih posyandu lain.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2">
                <x-input-label class="text-sm md:text-lg" for="password" :value="__('Password')" />
                <div class="relative mt-1">
                    <x-text-input id="password" class="block w-full pr-10" type="password" name="password" required
                        autocomplete="new-password" placeholder="Masukkan Password" />
                    <button type="button" onclick="togglePassword('password', this)"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none"
                        tabindex="-1">
                        <i class="bi bi-eye text-lg"></i>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-xs text-red-600"><i class="bi bi-exclamation-circle mr-1"></i>{{ $message }}</p>
                @else
                    <p class="mt-1 text-xs text-gray-500"><i class="bi bi-info-circle mr-1"></i>Minimal 8 karakter.</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <x-input-label class="text-sm md:text-lg" for="password_confirmation" :value="__('Konfirmasi Password')" />
                <div class="relative mt-1">
                    <x-text-input id="password_confirmation" class="block w-full pr-10" type="password"
                        name="password_confirmation" required autocomplete="new-password"
                        placeholder="Konfirmasi Password" />
                    <button type="button" onclick="togglePassword('password_confirmation', this)"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none"
                        tabindex="-1">
                        <i class="bi bi-eye text-lg"></i>
                    </button>
                </div>
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
        function togglePassword(id, btn) {
            const input = document.getElementById(id);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash text-lg';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye text-lg';
            }
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('dependentDropdowns', () => ({
                kecamatanList: [],
                desaList: [],
                posyanduList: [],
                availableRw: [],
                availableRt: [],
                selectedKabupaten: '',
                selectedKecamatan: '',
                selectedDesa: '',
                selectedPosyandu: '',
                selectedRw: '',
                selectedRt: '',
                selectedRole: '{{ old('role') }}',
                loadingKecamatan: false,
                loadingDesa: false,
                loadingPosyandu: false,
                kabupatens: @json($kabupatens['data']),

                get filteredPosyandu() {
                    if (!this.posyanduList) return [];
                    return this.posyanduList;
                },

                getKabupatenName(value) {
                    if (!value) return '';
                    return value.split('_').slice(1).join('_');
                },

                getKecamatanName(value) {
                    if (!value) return '';
                    return value.split('_').slice(1).join('_');
                },

                getDesaName(value) {
                    if (!value) return '';
                    return value.split('_').slice(1).join('_');
                },

                getPosyanduName(value) {
                    if (!value) return '';
                    const posyandu = this.posyanduList.find(p => p.id === value);
                    return posyandu ? posyandu.nama_posyandu : value;
                },

                selectKabupaten(kab) {
                    this.selectedKabupaten = `${kab.code}_${kab.name}`;
                    this.selectedKecamatan = '';
                    this.selectedDesa = '';
                    this.selectedPosyandu = '';
                    this.selectedRw = '';
                    this.selectedRt = '';
                    this.kecamatanList = [];
                    this.desaList = [];
                    this.posyanduList = [];
                    this.availableRw = [];
                    this.availableRt = [];
                    this.fetchKecamatan(kab.code);
                },

                selectKecamatan(kec) {
                    this.selectedKecamatan = `${kec.code}_${kec.name}`;
                    this.selectedDesa = '';
                    this.selectedPosyandu = '';
                    this.selectedRw = '';
                    this.selectedRt = '';
                    this.desaList = [];
                    this.posyanduList = [];
                    this.availableRw = [];
                    this.availableRt = [];
                    this.fetchDesa(kec.code);
                },

                selectDesa(desa) {
                    this.selectedDesa = `${desa.code}_${desa.name}`;
                    this.selectedPosyandu = '';
                    this.selectedRw = '';
                    this.selectedRt = '';
                    this.posyanduList = [];
                    this.availableRw = [];
                    this.availableRt = [];
                    this.fetchPosyandu();
                },

                selectPosyandu(posyandu) {
                    this.selectedPosyandu = posyandu.id;
                    this.selectedRw = '';
                    this.selectedRt = '';
                    this.fetchRwList(posyandu.id);
                },

                createNewPosyandu(name) {
                    this.selectedPosyandu = name;
                    this.availableRw = [];
                    this.availableRt = [];
                    alert(
                        'Posyandu baru belum memiliki mapping RW/RT. Admin akan setup setelah Anda mendaftar.'
                    );
                },

                async fetchKecamatan() {
                    this.kecamatanList = [];
                    this.desaList = [];
                    this.selectedKecamatan = '';
                    this.selectedDesa = '';
                    this.loadingKecamatan = true;

                    if (this.selectedKabupaten) {
                        const kabId = this.selectedKabupaten.split('_')[0];
                        const response = await fetch(
                            `{{ url('/api/wilayah/kecamatan') }}/${kabId}`);
                        const data = await response.json();

                        this.kecamatanList = Array.isArray(data) ? data : (data.data ?? []);
                    }
                    this.loadingKecamatan = false;
                },

                async fetchDesa() {
                    this.desaList = [];
                    this.selectedDesa = '';
                    this.loadingDesa = true;

                    if (this.selectedKecamatan) {
                        const kecId = this.selectedKecamatan.split('_')[0];
                        const response = await fetch(`{{ url('/api/wilayah/desa') }}/${kecId}`);
                        const data = await response.json();
                        this.desaList = Array.isArray(data) ? data : (data.data ?? []);
                    }
                    this.loadingDesa = false;
                },

                async fetchPosyandu() {
                    if (!this.selectedDesa) return;

                    this.loadingPosyandu = true;
                    try {
                        const kabupatenName = this.getKabupatenName(this.selectedKabupaten);
                        const kecamatanName = this.getKecamatanName(this.selectedKecamatan);
                        const desaName = this.getDesaName(this.selectedDesa);

                        const url =
                            `{{ route('api.posyandu.by-wilayah') }}?kabupaten=${encodeURIComponent(kabupatenName)}&kecamatan=${encodeURIComponent(kecamatanName)}&desa=${encodeURIComponent(desaName)}`;

                        const response = await fetch(url);
                        const data = await response.json();
                        this.posyanduList = Array.isArray(data) ? data : [];
                    } catch (error) {
                        console.error('Error loading posyandu:', error);
                        this.posyanduList = [];
                    } finally {
                        this.loadingPosyandu = false;
                    }
                },
                async fetchRwList(posyanduId = null) {
                    const id = posyanduId || this.selectedPosyandu;
                    if (!id) return;

                    try {
                        const posyandu = this.posyanduList.find(p => p.id === id);

                        if (posyandu && posyandu.rw_list && Array.isArray(posyandu.rw_list)) {
                            this.availableRw = posyandu.rw_list;
                        } else {
                            const response = await fetch(`/api/posyandu/${id}/rw-rt`);
                            const data = await response.json();

                            if (data.success) {
                                this.availableRw = data.data.rw_list || [];
                            } else {
                                this.availableRw = [];
                            }
                        }

                        this.selectedRw = '';
                        this.availableRt = [];
                    } catch (error) {
                        console.error('Error loading RW:', error);
                        this.availableRw = [];
                    }
                },

                async fetchRtList() {
                    if (!this.selectedRw || !this.selectedPosyandu) return;

                    try {
                        const posyandu = this.posyanduList.find(p => p.id === this
                        .selectedPosyandu);

                        if (posyandu && posyandu.rt_mapping && posyandu.rt_mapping[this
                            .selectedRw]) {
                            this.availableRt = posyandu.rt_mapping[this.selectedRw];
                        } else {
                            const response = await fetch(
                                `/api/posyandu/${this.selectedPosyandu}/rw-rt`);
                            const data = await response.json();

                            if (data.success && data.data.rt_mapping) {
                                this.availableRt = data.data.rt_mapping[this.selectedRw] || [];
                            } else {
                                this.availableRt = [];
                            }
                        }

                        this.selectedRt = '';
                    } catch (error) {
                        console.error('Error loading RT:', error);
                        this.availableRt = [];
                    }
                }
            }));
        });
    </script>
</x-guest-layout>
