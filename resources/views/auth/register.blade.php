<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" x-data="dependentDropdowns()">
        @csrf

        @if (session()->has('google_user_email'))
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Lengkapi Pendaftaran Akun Anda</h2>
        @else
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Pembuatan Akun</h2>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">

            {{-- <div>
                <x-input-label class="text-sm md:text-lg" for="nik" :value="__('NIK')" />
                <x-text-input id="nik" class="block mt-1 w-full" type="text" name="nik" :value="old('nik')"
                    autofocus placeholder="Masukkan NIK" maxlength="16" />
                <x-input-error :messages="$errors->get('nik')" class="mt-2" />
            </div> --}}

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

            <div>
                <x-input-label class="text-sm md:text-lg" for="email" :value="__('Email')" />
                @if (session()->has('google_user_email'))
                    <x-text-input id="email" class="block mt-1 w-full bg-gray-100 cursor-not-allowed" type="email"
                        name="email" :value="session('google_user_email')" required readonly />
                @else
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                        :value="old('email')" required autocomplete="username" placeholder="Masukkan Email" />
                @endif
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            {{-- <div class="md:col-span-2">
                <x-input-label class="text-sm md:text-lg" for="alamat" :value="__('Alamat')" />
                <textarea id="alamat" name="alamat"
                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    rows="3" placeholder="Masukkan Alamat Lengkap">{{ old('alamat') }}</textarea>
                <x-input-error :messages="$errors->get('alamat')" class="mt-2" />
            </div> --}}

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
                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    required>
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

            {{-- Combobox Kabupaten --}}
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

            {{-- Combobox Kecamatan --}}
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

            {{-- Combobox Desa --}}
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
                            {{-- <-- PERBAIKI DI SINI --}} class="px-4 py-2 cursor-pointer hover:bg-indigo-50"
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

            {{-- ✅ COMBOBOX POSYANDU DENGAN SELECT2 (SEARCHABLE + CREATE NEW) --}}
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
            {{--
            <div x-data="{ fileName: '' }">
                <x-input-label class="text-sm md:text-lg" for="ktp" :value="__('KTP')" />
                <label for="ktp"
                    class="mt-1 flex justify-between items-center px-4 py-2 bg-white text-gray-500 rounded-md shadow-sm border border-gray-300 cursor-pointer hover:text-gray-700">
                    <span x-text="fileName || 'Unggah KTP'"></span>
                    <i class="bi bi-cloud-upload text-pink-500 text-lg"></i>
                </label>
                <input id="ktp" class="hidden" type="file" name="ktp" accept="image/*,application/pdf"
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
                <input id="kk" class="hidden" type="file" name="kk" accept="image/*,application/pdf"
                    @change="fileName = $event.target.files[0].name" />
                <x-input-error :messages="$errors->get('kk')" class="mt-2" />
            </div> --}}

            <div class="md:col-span-2">
                <x-input-label class="text-sm md:text-lg" for="role" :value="__('Role')" />
                <select id="role" name="role"
                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    required>
                    <option value="" disabled selected>Pilih role</option>
                    <option value="masyarakat" @selected(old('role') == 'masyarakat')>Masyarakat</option>
                    <option value="kader" @selected(old('role') == 'kader')>Kader</option>
                </select>
                <x-input-error :messages="$errors->get('role')" class="mt-2" />
            </div>

            {{-- ✅ DROPDOWN BIDANG (HANYA MUNCUL JIKA ROLE = KADER) --}}
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
        document.addEventListener('alpine:init', () => {
            Alpine.data('dependentDropdowns', () => ({
                kecamatanList: [],
                desaList: [],
                posyanduList: [],
                selectedKabupaten: '',
                selectedKecamatan: '',
                selectedDesa: '',
                selectedPosyandu: '',
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
                    this.kecamatanList = [];
                    this.desaList = [];
                    this.posyanduList = [];
                    this.fetchKecamatan(kab.code);
                },

                selectKecamatan(kec) {
                    this.selectedKecamatan = `${kec.code}_${kec.name}`;
                    this.selectedDesa = '';
                    this.selectedPosyandu = '';
                    this.desaList = [];
                    this.posyanduList = [];
                    this.fetchDesa(kec.code);
                },

                selectDesa(desa) {
                    this.selectedDesa = `${desa.code}_${desa.name}`;
                    this.selectedPosyandu = '';
                    this.posyanduList = [];
                    this.fetchPosyandu();
                },

                selectPosyandu(posyandu) {
                    this.selectedPosyandu = posyandu.id;
                },

                createNewPosyandu(name) {
                    this.selectedPosyandu = name;
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
                        console.log('Desa response:', data);
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
                        console.log(response)
                        this.posyanduList = Array.isArray(data) ? data : [];
                    } catch (error) {
                        console.error('Error loading posyandu:', error);
                        this.posyanduList = [];
                    } finally {
                        this.loadingPosyandu = false;
                    }
                }
            }));
            const roleSelect = document.getElementById('role');
            const bidangField = document.getElementById('bidang-field');
            const bidangSelect = document.getElementById('bidang_id');

            function toggleBidangField() {
                if (roleSelect.value === 'kader') {
                    bidangField.style.display = 'block';
                    bidangSelect.required = true;
                } else {
                    bidangField.style.display = 'none';
                    bidangSelect.required = false;
                    bidangSelect.value = '';
                }
            }

            roleSelect.addEventListener('change', toggleBidangField);
            toggleBidangField();
        });
    </script>
</x-guest-layout>
