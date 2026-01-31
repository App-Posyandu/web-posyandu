@extends('dashboard.layouts.dashboard')
@section('title', 'Add Users')
@section('content')
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <strong>Validation Errors:</strong>
            <ul class="list-disc list-inside">
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
    <div class="w-full mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-8 text-gray-900">
                <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data">
                    @csrf
                    @if (request()->has('source'))
                        <input type="hidden" name="source" value="{{ request('source') }}">
                    @endif
                    <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">Formulir Pengguna Baru</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Nama & No Telepon (Tidak Berubah) --}}
                        <div>
                            <x-input-label for="name" :value="__('Nama Lengkap')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                :value="old('name')" required autofocus placeholder="Masukkan nama lengkap" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="no_telepon" :value="__('Nomor Whatsapp')" />
                            <x-text-input id="no_telepon" class="block mt-1 w-full" type="text" name="no_telepon"
                                :value="old('no_telepon')" required />
                            <x-input-error :messages="$errors->get('no_telepon')" class="mt-2" />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="alamat" :value="__('Alamat')" />
                            <textarea id="alamat" name="alamat"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                rows="3" required placeholder="Masukkan alamat lengkap">{{ old('alamat') }}</textarea>
                            <x-input-error :messages="$errors->get('alamat')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="nik" :value="__('NIK')" />
                            <x-text-input id="nik" class="block mt-1 w-full" type="text" name="nik"
                                :value="old('nik')" required maxlength="16" />
                            <x-input-error :messages="$errors->get('nik')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="tempat_lahir" :value="__('Tempat Lahir')" />
                            <x-text-input id="tempat_lahir" class="block mt-1 w-full" type="text" name="tempat_lahir"
                                :value="old('tempat_lahir')" required />
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
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                <option value="" disabled selected>Pilih Jenis Kelamin</option>
                                <option value="Laki-laki" @selected(old('jenis_kelamin') == 'Laki-laki')>Laki-laki</option>
                                <option value="Perempuan" @selected(old('jenis_kelamin') == 'Perempuan')>Perempuan</option>
                            </select>
                            <x-input-error :messages="$errors->get('jenis_kelamin')" class="mt-2" />
                        </div>

                        @if (in_array(auth()->user()->role, ['ketua-kader', 'admin-kecamatan']))
                            <div>
                                <x-input-label for="posyandu_id" :value="__('Posyandu (Opsional)')" />
                                <select id="posyandu_id" name="posyandu_id"
                                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="" selected>-- Belum Ditugaskan --</option>
                                    @foreach ($posyandus as $posyandu)
                                        <option value="{{ $posyandu->id }}" @selected(old('posyandu_id') == $posyandu->id)>
                                            {{ $posyandu->nama_posyandu }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Biarkan kosong jika user ini belum memiliki Posyandu.
                                </p>
                                <x-input-error :messages="$errors->get('posyandu_id')" class="mt-2" />
                            </div>
                        @endif

                        {{-- Role --}}
                        <div
                            class="{{ in_array(auth()->user()->role, ['ketua-kader', 'admin-kecamatan', 'kader']) ? 'hidden' : '' }}">
                            <x-input-label for="role" :value="__('Role')" />
                            <select id="role" name="role"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                {{ isset($defaultRole) ? 'disabled' : '' }}>
                                <option value="" disabled {{ !isset($defaultRole) ? 'selected' : '' }}>Pilih Role
                                </option>
                                @php $currentUserRole = auth()->user()->role; @endphp

                                @if ($currentUserRole === 'admin')
                                    <option value="admin-kabupaten"
                                        {{ isset($defaultRole) && $defaultRole === 'admin-kabupaten' ? 'selected' : '' }}>
                                        Admin Kabupaten</option>
                                    <option value="ketua-posyandu"
                                        {{ isset($defaultRole) && $defaultRole === 'ketua-posyandu' ? 'selected' : '' }}>
                                        Ketua Posyandu</option>
                                    <option value="kabid"
                                        {{ isset($defaultRole) && $defaultRole === 'kabid' ? 'selected' : '' }}>Kabid
                                    </option>
                                    <option value="admin-kecamatan"
                                        {{ isset($defaultRole) && $defaultRole === 'admin-kecamatan' ? 'selected' : '' }}>
                                        Admin Kecamatan</option>
                                    <option value="kades"
                                        {{ isset($defaultRole) && $defaultRole === 'kades' ? 'selected' : '' }}>Kades
                                    </option>
                                    <option value="ketua-kader"
                                        {{ isset($defaultRole) && $defaultRole === 'ketua-kader' ? 'selected' : '' }}>Ketua
                                        Kader</option>
                                    <option value="operator-desa"
                                        {{ isset($defaultRole) && $defaultRole === 'operator-desa' ? 'selected' : '' }}>
                                        Operator Desa</option>
                                    <option value="kader"
                                        {{ isset($defaultRole) && $defaultRole === 'kader' ? 'selected' : '' }}>Kader
                                    </option>
                                    <option value="masyarakat"
                                        {{ isset($defaultRole) && $defaultRole === 'masyarakat' ? 'selected' : '' }}>
                                        Masyarakat</option>
                                @elseif ($currentUserRole === 'admin-kabupaten')
                                    <option value="ketua-posyandu">Ketua Posyandu</option>
                                    <option value="kabid">Kabid</option>
                                    <option value="admin-kecamatan">Admin Kecamatan</option>
                                    <option value="kades">Kades</option>
                                    <option value="operator-desa">Operator Desa</option>
                                @elseif ($currentUserRole === 'admin-kecamatan')
                                    {{-- Admin Kecamatan tidak boleh membuat user apapun --}}
                                @elseif ($currentUserRole === 'operator-desa')
                                    <option value="ketua-kader">Ketua Kader</option>
                                    <option value="kader">Kader</option>
                                @elseif ($currentUserRole === 'ketua-kader')
                                    <option value="kader" @selected(true)>Kader</option>
                                @elseif ($currentUserRole === 'kader')
                                    <option value="masyarakat" @selected(true)>Masyarakat</option>
                                @endif
                            </select>

                            {{-- ✅ Hidden input jika role di-disable (dari pilih-user) --}}
                            @if (isset($defaultRole))
                                <input type="hidden" name="role" value="{{ $defaultRole }}">
                                <p class="mt-1 text-xs text-gray-500">
                                    <i class="bi bi-info-circle"></i>
                                    Role otomatis diset sebagai <strong>Masyarakat</strong> karena Anda membuat user dari
                                    halaman pilih masyarakat
                                </p>
                            @endif

                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        {{-- JENIS WILAYAH (KHUSUS KABID) --}}
                        <div class="{{ in_array(auth()->user()->role, ['admin', 'ketua-kader']) ? 'md:col-span-2' : '' }}"
                            id="jenis-wilayah-field" style="display: none;">
                            <x-input-label for="jenis_wilayah" :value="__('Jenis Wilayah')" />
                            <select id="jenis_wilayah" name="jenis_wilayah"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="" disabled selected>Pilih Jenis Wilayah</option>
                                <option value="kabupaten" @selected(old('jenis_wilayah') == 'kabupaten')>Kabupaten</option>
                                <option value="kota" @selected(old('jenis_wilayah') == 'kota')>Kota</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Pilih apakah Kabid mengelola Kabupaten atau Kota.</p>
                            <x-input-error :messages="$errors->get('jenis_wilayah')" class="mt-2" />
                        </div>

                        {{-- ✅ HIDDEN INPUT UNTUK KABUPATEN --}}
                        <input type="hidden" id="kabupaten-hidden" name="kabupaten" value="">

                        {{-- KABUPATEN COMBOBOX (HANYA UI) --}}
                        <div id="kabupaten-field" style="display: none;" class="md:col-span-2">
                            <div x-data="kabupatenCombobox()" @click.away="open = false" x-init="$watch('selectedKabupaten', value => {
                                document.getElementById('kabupaten-hidden').value = value;
                            })"
                                class="relative">
                                <x-input-label for="kabupaten" :value="__('Pilih Kabupaten')" />
                                <div class="relative">
                                    <input type="text" x-model="search" @focus="open = true" @input="open = true"
                                        :placeholder="getKabupatenName(selectedKabupaten) || 'Cari Kabupaten...'"
                                        class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        autocomplete="off">
                                </div>
                                <div x-show="open"
                                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
                                    style="display: none;">
                                    <template
                                        x-for="kab in kabupatens.filter(k => (k.name || '').toLowerCase().includes(search.toLowerCase()))"
                                        :key="kab.id || kab.code">
                                        <div @click="selectKabupaten(kab)"
                                            class="px-4 py-2 cursor-pointer hover:bg-indigo-50"
                                            x-text="getDisplayName(kab.name)">
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- KOTA COMBOBOX (HANYA UI) --}}
                        <div id="kota-field" style="display: none;" class="md:col-span-2">
                            {{-- ... (Kode Combobox Kota Anda yang lama) ... --}}
                            <div x-data="kotaCombobox()" @click.away="open = false" x-init="$watch('selectedKota', value => {
                                document.getElementById('kabupaten-hidden').value = value;
                            })"
                                class="relative">
                                <x-input-label for="kota" :value="__('Pilih Kota')" />
                                <div class="relative">
                                    <input type="text" x-model="search" @focus="open = true" @input="open = true"
                                        :placeholder="getKotaName(selectedKota) || 'Cari Kota...'"
                                        class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        autocomplete="off">
                                </div>
                                <div x-show="open"
                                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
                                    style="display: none;">
                                    <template
                                        x-for="kota in kotas.filter(k => (k.name || '').toLowerCase().includes(search.toLowerCase()))"
                                        :key="kota.id || kota.code">
                                        <div @click="selectKota(kota)" class="px-4 py-2 cursor-pointer hover:bg-indigo-50"
                                            x-text="getDisplayName(kota.name)">
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div id="kecamatan-field" style="display: none;" class="md:col-span-2">
                            <input type="hidden" name="kecamatan" id="kecamatan-hidden">

                            <div x-data="kecamatanCombobox()" @region-selected.window="fetchKecamatan($event.detail.code)"
                                @click.away="open = false" class="relative">

                                <x-input-label for="kecamatan" :value="__('Pilih Kecamatan')" />

                                <div class="relative">
                                    <input type="text" x-model="search" @focus="open = true" @input="open = true"
                                        :placeholder="selectedKecamatanName || 'Cari Kecamatan...'"
                                        class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :disabled="loading" autocomplete="off">

                                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                        <svg x-show="loading" class="animate-spin h-5 w-5 text-indigo-500"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>

                                <div x-show="open && !loading"
                                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
                                    style="display: none;">

                                    <template
                                        x-for="kec in kecamatanList.filter(k => (k.name || '').toLowerCase().includes(search.toLowerCase()))"
                                        :key="kec.id || kec.code">
                                        <div @click="selectKecamatan(kec)"
                                            class="px-4 py-2 cursor-pointer hover:bg-indigo-50" x-text="kec.name"></div>
                                    </template>

                                    <div x-show="kecamatanList.length === 0" class="px-4 py-2 text-gray-500 text-sm">
                                        Tidak ada data kecamatan / Silakan pilih Kabupaten dulu
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ✅ FIELD UNTUK KETUA KADER: Pilih Posyandu (dibuat oleh Kabid atau Admin Kecamatan) -->
                        <div id="posyandu-field" style="display: none;" class="md:col-span-2">
                            <x-input-label for="posyandu_id" :value="__('Pilih Posyandu')" />
                            <select id="posyandu_id" name="posyandu_id"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="" disabled selected>Pilih Posyandu</option>
                                @foreach ($posyandus as $posyandu)
                                    <option value="{{ $posyandu->id }}">
                                        {{ $posyandu->nama_posyandu }} - {{ $posyandu->kecamatan }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                Ketua Kader akan memimpin posyandu ini
                            </p>
                        </div>

                        <!-- ✅ FIELD UNTUK KADER: Pilih Bidang (posyandu otomatis dari Ketua Kader) -->
                        <div id="bidang-field" style="display: none;"
                            class="{{ in_array(auth()->user()->role, ['admin', 'operator-desa', 'ketua-kader']) ? 'md:col-span-2' : '' }}">
                            <x-input-label for="bidang_id" :value="__('Bidang Tugas')" />
                            <select id="bidang_id" name="bidang_id"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="" disabled selected>Pilih Bidang</option>
                                @foreach ($bidangs as $bidang)
                                    <option value="{{ $bidang->id }}">
                                        {{ $bidang->nama_bidang }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                Kader akan bekerja di bidang ini pada posyandu Anda
                            </p>
                        </div>

                        {{-- ✅ TAMBAHKAN SETELAH DROPDOWN POSYANDU/BIDANG, SEBELUM PASSWORD --}}
                        {{-- HANYA UNTUK ROLE MASYARAKAT --}}

                        <div id="rw-rt-fields" style="display: none;" class="md:col-span-2 space-y-4">
                            {{-- RW Dropdown --}}
                            <div>
                                <x-input-label for="rw" :value="__('RW (Rukun Warga)')" />
                                <span class="text-red-600">*</span>
                                <select id="rw" name="rw"
                                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="" disabled selected>Pilih RW</option>
                                    {{-- Will be populated by JavaScript --}}
                                </select>
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="bi bi-info-circle text-blue-500"></i>
                                    RW sesuai domisili user (Format: RW01, RW02, dst)
                                </p>
                                <x-input-error :messages="$errors->get('rw')" class="mt-2" />
                            </div>

                            {{-- RT Dropdown --}}
                            <div>
                                <x-input-label for="rt" :value="__('RT (Rukun Tetangga)')" />
                                <span class="text-gray-500 text-sm">(Opsional)</span>
                                <select id="rt" name="rt"
                                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">-- Tidak ada/Tidak tahu --</option>
                                    {{-- Will be populated by JavaScript --}}
                                </select>
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="bi bi-info-circle text-blue-500"></i>
                                    RT jika diketahui (Format: RT001, RT002, dst)
                                </p>
                                <x-input-error :messages="$errors->get('rt')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="password" :value="__('Password')" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password"
                                required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                                name="password_confirmation" required />
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="flex items-center justify-between mt-8 gap-4">
                            @php
                                $cancelUrl = route('admin.users.index');
                            @endphp
                            <div class="flex gap-4">
                                <a href="{{ $cancelUrl }}"
                                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm font-semibold hover:bg-gray-300">
                                    Batal
                                </a>
                                <x-primary-button>
                                    {{ __('Simpan Pengguna') }}
                                </x-primary-button>
                                <button type="button" id="importBtn"
                                    class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm font-semibold hover:bg-emerald-700">
                                    Import Nama Pengguna
                                </button>
                            </div>
                        </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // ✅ PASTIKAN ALPINE INIT DULU
            document.addEventListener('alpine:init', () => {

                // Helper function untuk mencari ID/Code yang valid
                const getRegionCode = (region) => region.id || region.code;

                // 1. KABUPATEN COMBOBOX
                Alpine.data('kabupatenCombobox', () => ({
                    open: false,
                    search: '',
                    selectedKabupaten: '{{ old('kabupaten') }}',
                    kabupatens: @json($kabupatenList ?? []),

                    init() {
                        // Debugging: Cek data pertama untuk memastikan struktur
                        if (this.kabupatens.length > 0) {
                            console.log('Sample Data Kabupaten:', this.kabupatens[0]);
                        }

                        if (this.selectedKabupaten) {
                            let code = this.selectedKabupaten.split('_')[0];
                            this.$dispatch('region-selected', {
                                code: code
                            });
                        }
                    },

                    getKabupatenName(value) {
                        if (!value) return '';
                        let fullName = value.split('_').slice(1).join('_');
                        return fullName.replace('Kabupaten ', '');
                    },

                    getDisplayName(name) {
                        return name ? name.replace('Kabupaten ', '') : '';
                    },

                    selectKabupaten(kab) {
                        const code = getRegionCode(kab); // Ambil ID atau Code

                        this.selectedKabupaten = `${code}_${kab.name}`;
                        this.search = '';
                        this.open = false;

                        document.getElementById('kabupaten-hidden').value = this.selectedKabupaten;

                        // Dispatch code wilayah agar Kecamatan bisa fetch
                        console.log('Dispatching Region Code:', code);
                        this.$dispatch('region-selected', {
                            code: code
                        });
                    }
                }));

                // 2. KOTA COMBOBOX
                Alpine.data('kotaCombobox', () => ({
                    open: false,
                    search: '',
                    selectedKota: '{{ old('kota') }}',
                    kotas: @json($kotaList ?? []),

                    getKotaName(value) {
                        if (!value) return '';
                        let fullName = value.split('_').slice(1).join('_');
                        return fullName.replace('Kota ', '');
                    },

                    getDisplayName(name) {
                        return name ? name.replace('Kota ', '') : '';
                    },

                    selectKota(kota) {
                        const code = getRegionCode(kota);

                        this.selectedKota = `${code}_${kota.name}`;
                        this.search = '';
                        this.open = false;

                        document.getElementById('kabupaten-hidden').value = this.selectedKota;

                        console.log('Dispatching Region Code:', code);
                        this.$dispatch('region-selected', {
                            code: code
                        });
                    }
                }));

                // 3. KECAMATAN COMBOBOX
                Alpine.data('kecamatanCombobox', () => ({
                    open: false,
                    search: '',
                    loading: false,
                    kecamatanList: [],
                    selectedKecamatanRaw: '{{ old('kecamatan') }}',
                    selectedKecamatanName: '',

                    init() {
                        if (this.selectedKecamatanRaw) {
                            this.selectedKecamatanName = this.selectedKecamatanRaw.split('_').slice(1).join(
                                '_');
                            document.getElementById('kecamatan-hidden').value = this.selectedKecamatanRaw;
                        }
                    },

                    async fetchKecamatan(parentId) {
                        if (!parentId) return;

                        console.log('Fetching Kecamatan for Parent:', parentId);
                        this.loading = true;
                        this.kecamatanList = [];
                        this.selectedKecamatanName = '';
                        document.getElementById('kecamatan-hidden').value = '';

                        try {
                            // Fetch API
                            const response = await fetch(`/api/wilayah/kecamatan/${parentId}`);
                            const data = await response.json();

                            // Normalisasi Data: Pastikan jadi Array
                            let list = [];
                            if (Array.isArray(data)) {
                                list = data;
                            } else if (data && Array.isArray(data.data)) {
                                list = data.data;
                            }

                            this.kecamatanList = list;
                            console.log('Kecamatan Loaded:', this.kecamatanList);

                        } catch (error) {
                            console.error('Gagal mengambil data kecamatan:', error);
                            this.kecamatanList = [];
                        } finally {
                            this.loading = false;
                        }
                    },

                    selectKecamatan(kec) {
                        const code = getRegionCode(kec);

                        // Format: Code_Nama
                        const val = `${code}_${kec.name}`;
                        document.getElementById('kecamatan-hidden').value = val;

                        this.selectedKecamatanName = kec.name;
                        this.search = '';
                        this.open = false;
                    }
                }));
            });

            document.addEventListener('DOMContentLoaded', function() {
                const roleSelect = document.getElementById('role');
                const rwRtFields = document.getElementById('rw-rt-fields');
                const posyanduSelect = document.getElementById('posyandu_id');
                const rwSelect = document.getElementById('rw');
                const rtSelect = document.getElementById('rt');

                // ✅ Toggle RW/RT fields ketika role = masyarakat
                function toggleRwRtFields() {
                    if (roleSelect.value === 'masyarakat') {
                        rwRtFields.style.display = 'block';
                        rwSelect.required = true;
                    } else {
                        rwRtFields.style.display = 'none';
                        rwSelect.required = false;
                        rtSelect.value = '';
                        rwSelect.value = '';
                    }
                }

                // ✅ Fetch RW/RT ketika posyandu dipilih (untuk role masyarakat)
                async function fetchRwRtOptions() {
                    if (roleSelect.value !== 'masyarakat' || !posyanduSelect.value) {
                        return;
                    }

                    try {
                        // Fetch data posyandu
                        const response = await fetch(`/api/posyandu/${posyanduSelect.value}/rw-rt`);
                        const data = await response.json();

                        // Populate RW dropdown
                        rwSelect.innerHTML = '<option value="" disabled selected>Pilih RW</option>';
                        if (data.rw_list && data.rw_list.length > 0) {
                            data.rw_list.forEach(rw => {
                                const option = document.createElement('option');
                                option.value = rw;
                                option.textContent = rw;
                                rwSelect.appendChild(option);
                            });
                        } else {
                            // Fallback: Generate RW01-RW15
                            for (let i = 1; i <= 15; i++) {
                                const rw = `RW${String(i).padStart(2, '0')}`;
                                const option = document.createElement('option');
                                option.value = rw;
                                option.textContent = rw;
                                rwSelect.appendChild(option);
                            }
                        }

                        // Store rt_mapping for later use
                        window.rtMapping = data.rt_mapping || {};

                    } catch (error) {
                        console.error('Error fetching RW/RT:', error);
                    }
                }

                // ✅ Populate RT ketika RW dipilih
                rwSelect.addEventListener('change', function() {
                    const selectedRw = this.value;
                    rtSelect.innerHTML = '<option value="">-- Tidak ada/Tidak tahu --</option>';

                    if (window.rtMapping && window.rtMapping[selectedRw]) {
                        window.rtMapping[selectedRw].forEach(rt => {
                            const option = document.createElement('option');
                            option.value = rt;
                            option.textContent = rt;
                            rtSelect.appendChild(option);
                        });
                    } else {
                        // Fallback: Generate RT001-RT053
                        for (let i = 1; i <= 53; i++) {
                            const rt = `RT${String(i).padStart(3, '0')}`;
                            const option = document.createElement('option');
                            option.value = rt;
                            option.textContent = rt;
                            rtSelect.appendChild(option);
                        }
                    }
                });

                // Event listeners
                roleSelect.addEventListener('change', toggleRwRtFields);
                posyanduSelect.addEventListener('change', fetchRwRtOptions);

                // Initial check
                toggleRwRtFields();
            });

            // ✅ TOGGLE FIELDS SETELAH DOM READY
            document.addEventListener('DOMContentLoaded', function() {
                const roleSelect = document.getElementById('role');
                const bidangField = document.getElementById('bidang-field');
                const bidangSelect = document.getElementById('bidang_id');
                const jenisWilayahField = document.getElementById('jenis-wilayah-field');
                const jenisWilayahSelect = document.getElementById('jenis_wilayah');
                const kabupatenField = document.getElementById('kabupaten-field');
                const kotaField = document.getElementById('kota-field');
                const kecamatanField = document.getElementById('kecamatan-field');
                const posyanduField = document.getElementById('posyandu-field');
                const posyanduSelect = document.getElementById('posyandu_id');

                const kabupatenSelect = document.getElementById('kabupaten_id');
                const kecamatanSelect = document.getElementById('kecamatan_id');

                function toggleFields() {
                    // Hide all fields first
                    jenisWilayahField.style.display = 'none';
                    kabupatenField.style.display = 'none';
                    kotaField.style.display = 'none';
                    kecamatanField.style.display = 'none';
                    posyanduField.style.display = 'none';
                    bidangField.style.display = 'none';

                    // Reset all required
                    jenisWilayahSelect.required = false;
                    // kabupaten akan di-handle oleh hidden input
                    posyanduSelect.required = false;
                    bidangSelect.required = false;

                    const role = roleSelect.value;
                    const currentUserRole = '{{ auth()->user()->role }}';

                    // ✅ KETUA POSYANDU: Pilih Jenis Wilayah + Kabupaten/Kota
                    if (role === 'ketua-posyandu' || role === 'admin-kabupaten') {
                        jenisWilayahField.style.display = 'block';
                        jenisWilayahSelect.required = true;
                        // Kabupaten/Kota akan muncul setelah pilih jenis wilayah
                    }

                    // ✅ KABID: Pilih Bidang + Kabupaten
                    if (role === 'kabid') {
                        bidangField.style.display = 'block';
                        bidangSelect.required = true;

                        // Tampilkan pilihan jenis wilayah dulu
                        jenisWilayahField.style.display = 'block';
                        jenisWilayahSelect.required = true;
                    }

                    // ✅ ADMIN KECAMATAN: Pilih Kabupaten + Kecamatan (dari API)
                    if (role === 'admin-kecamatan') {
                        jenisWilayahField.style.display = 'block';
                        jenisWilayahSelect.required = true;
                    }

                    // ✅ KETUA KADER: Pilih Posyandu
                    if (role === 'ketua-kader') {
                        posyanduField.style.display = 'block';
                        posyanduSelect.required = true;
                    }

                    // ✅ OPERATOR DESA: Pilih Posyandu yang sama dengan Ketua Kader
                    if (role === 'operator-desa') {
                        posyanduField.style.display = 'block';
                        posyanduSelect.required = true;

                        // Jika dibuat oleh Ketua Kader, filter hanya posyandu ketua kader
                        if (currentUserRole === 'ketua-kader') {
                            const ketuaKaderPosyanduId = '{{ auth()->user()->posyandu_id }}';
                            posyanduSelect.value = ketuaKaderPosyanduId;
                            posyanduSelect.disabled = true;
                        }
                    }

                    // ✅ KADER: Pilih Bidang + Posyandu (conditional)
                    if (role === 'kader') {
                        bidangField.style.display = 'block';
                        bidangSelect.required = true;

                        // ✅ OPERATOR DESA: Posyandu auto-inherit, hanya tampilkan Bidang
                        if (currentUserRole === 'operator-desa') {
                            posyanduField.style.display = 'none'; // Hide karena auto-inherit
                            posyanduSelect.required = false;
                        }
                        // Jika dibuat oleh Ketua Kader, posyandu auto-inherit
                        else if (currentUserRole === 'ketua-kader') {
                            posyanduField.style.display = 'none';
                            posyanduSelect.required = false;
                        }
                        // Role lain (Admin, dll) harus pilih posyandu
                        else {
                            posyanduField.style.display = 'block';
                            posyanduSelect.required = true;
                        }
                    }
                }

                function toggleWilayahField() {
                    kabupatenField.style.display = 'none';
                    kotaField.style.display = 'none';
                    kecamatanField.style.display = 'none';

                    const jenisWilayah = jenisWilayahSelect.value;

                    if (jenisWilayah === 'kabupaten') {
                        kabupatenField.style.display = 'block';
                    } else if (jenisWilayah === 'kota') {
                        kotaField.style.display = 'block';
                    }

                    if (roleSelect.value === 'admin-kecamatan') {
                        kecamatanField.style.display = 'block';
                    }
                }

                toggleFields();
                toggleWilayahField();

                roleSelect.addEventListener('change', toggleFields);
                jenisWilayahSelect.addEventListener('change', toggleWilayahField);
            });
            // Replace bagian script import di create.blade.php (user) dengan ini:

            const currentUserRole = @json(auth()->user()->role);
            const roleTargets = {
                'kader': ['masyarakat'],
                'ketua-kader': ['kader'],
                'operator-desa': ['ketua-kader', 'kader'],
                'admin-kecamatan': [],
                'admin-kabupaten': ['ketua-posyandu', 'kabid', 'admin-kecamatan', 'kades', 'operator-desa'],
                'admin': ['admin-kabupaten', 'ketua-posyandu', 'kabid', 'admin-kecamatan', 'kades', 'ketua-kader', 'operator-desa', 'kader', 'masyarakat']
            };
            const roleLabels = {
                'masyarakat': 'Masyarakat',
                'kader': 'Kader',
                'ketua-kader': 'Ketua Kader',
                'operator-desa': 'Operator Desa',
                'admin-kecamatan': 'Admin Kecamatan',
                'kabid': 'Kabid',
                'admin-kabupaten': 'Admin Kabupaten',
                'ketua-posyandu': 'Ketua Posyandu',
                'kades': 'Kades'
            };
            let selectedRoleToCreate = null;

            document.getElementById('importBtn').addEventListener('click', function() {
                const allowedRoles = roleTargets[currentUserRole] || [];

                if (allowedRoles.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tidak Ada Akses',
                        text: 'Role Anda tidak memiliki akses untuk import user.',
                        confirmButtonColor: '#f87171'
                    });
                    return;
                }

                if (allowedRoles.length > 1) {
                    showRoleSelection(allowedRoles);
                    return;
                }

                selectedRoleToCreate = allowedRoles[0];
                showMainMenu();
            });

            /**
             * STEP 0: Pilih Role Target (jika lebih dari 1)
             */
            function showRoleSelection(roles) {
                const rolesHtml = roles.map(role => {
                    const label = roleLabels[role] || role;
                    return `
                        <button type="button" class="role-option w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:border-indigo-400 hover:bg-indigo-50 transition" data-role="${role}">
                            <div class="font-semibold text-gray-800">${label}</div>
                            <div class="text-xs text-gray-500">Role target: ${label}</div>
                        </button>
                    `;
                }).join('');

                Swal.fire({
                    title: '<h2 class="text-xl font-bold text-gray-800 mb-2">Pilih Role User</h2>',
                    html: `
                        <div class="space-y-2">${rolesHtml}</div>
                        <div class="pt-4">
                            <button id="cancelRoleSelect" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Batal</button>
                        </div>
                    `,
                    showConfirmButton: false,
                    showCancelButton: false,
                    width: 520,
                    background: '#f9fafb',
                    customClass: {
                        popup: 'rounded-2xl shadow-2xl p-6'
                    },
                    didOpen: () => {
                        document.querySelectorAll('.role-option').forEach(btn => {
                            btn.addEventListener('click', () => {
                                selectedRoleToCreate = btn.getAttribute('data-role');
                                showMainMenu();
                            });
                        });
                        document.getElementById('cancelRoleSelect').addEventListener('click', () => {
                            Swal.close();
                        });
                    }
                });
            }

            /**
             * STEP 1: Menu Utama - Pilih Import atau Download Template
             */
            function showMainMenu() {
                const roleLabel = roleLabels[selectedRoleToCreate] || 'User';
                let menuHTML = `
        <div class="space-y-6 text-center">
            <p class="text-gray-600 mb-6">Pilih aksi yang ingin dilakukan:</p>

            <!-- Upload Import -->
            <div class="bg-gradient-to-r from-emerald-50 to-emerald-100 border-2 border-emerald-300 rounded-xl p-6 hover:shadow-lg transition-all cursor-pointer"
                 id="uploadOption">
                <div class="flex  gap-4">
                    <div class="bg-emerald-500 p-4 rounded-full">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                    </div>
                    <div class="text-left">
                        <h3 class="text-xl font-bold text-emerald-700">Upload & Import Data</h3>
                        <p class="text-sm text-emerald-600">Unggah file Excel untuk import user</p>
                    </div>
                </div>
            </div>

            <!-- Download Template -->
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 border-2 border-blue-300 rounded-xl p-6 hover:shadow-lg transition-all cursor-pointer"
                 id="downloadOption">
                <div class="flex items-center gap-4">
                    <div class="bg-blue-500 p-4 rounded-full">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="text-left">
                        <h3 class="text-xl font-bold text-blue-700">Download Template</h3>
                        <p class="text-sm text-blue-600">Unduh template Excel berdasarkan data Posyandu</p>
                    </div>
                </div>
            </div>

            <!-- Button Batal -->
            <div class="pt-4">
                <button id="cancelMainMenu"
                    class="px-6 py-2.5 bg-gray-500 text-white hover:bg-gray-600 font-medium rounded-md shadow">
                    Batal
                </button>
            </div>
        </div>
    `;

                Swal.fire({
                    title: `<h2 class="text-2xl font-bold text-gray-800 mb-2">Import User ${roleLabel}</h2>`,
                    html: menuHTML,
                    showConfirmButton: false,
                    showCancelButton: false,
                    width: 600,
                    background: '#f9fafb',
                    customClass: {
                        popup: 'rounded-2xl shadow-2xl p-6'
                    },
                    didOpen: () => {
                        // Upload Option
                        document.getElementById('uploadOption').addEventListener('click', () => {
                            showUploadStep();
                        });

                        // Download Template Option
                        document.getElementById('downloadOption').addEventListener('click', () => {
                            executeDownload();
                        });

                        // Cancel
                        document.getElementById('cancelMainMenu').addEventListener('click', () => {
                            Swal.close();
                        });
                    }
                });
            }

            /**
             * STEP 2: Upload File Excel
             */
            function showUploadStep() {
                let uploadHTML = `
        <div class="space-y-5 text-left">
            <!-- Upload File -->
            <div>
                <label class="block text-start font-semibold mb-2 text-gray-700">Upload File Excel:</label>
                <input type="file" id="excelFile" accept=".xlsx,.xls"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer border border-gray-300 rounded-md">
                <p class="mt-2 text-xs text-gray-500">Format: .xlsx atau .xls</p>
            </div>

            <!-- Info -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <p class="text-sm text-blue-700">
                    <strong>Tips:</strong>
                    <br>• Template sudah berisi data Desa/Kecamatan dari Posyandu
                    <br>• Anda hanya perlu isi NAMA dan NOMOR TELEPON
                    <br>• Password default: <code class="bg-white px-2 py-1 rounded">password123</code>
                </p>
            </div>

            <!-- Buttons -->
            <div class="flex justify-between gap-3 pt-4 border-t">
                <button id="backToMainMenu"
                    class="px-4 py-2.5 bg-gray-200 text-gray-700 hover:bg-gray-300 font-medium rounded-md">
                    ← Kembali
                </button>
                <button id="importExcelBtn"
                    class="px-6 py-2.5 bg-pink-500 hover:bg-pink-600 text-white font-bold rounded-md shadow flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    Import Data
                </button>
            </div>
        </div>
    `;

                Swal.fire({
                    title: '<h2 class="text-xl font-bold text-gray-800 mb-2">Upload File Excel</h2>',
                    html: uploadHTML,
                    showConfirmButton: false,
                    showCancelButton: false,
                    width: 700,
                    background: '#f9fafb',
                    customClass: {
                        popup: 'rounded-2xl shadow-2xl p-6'
                    },
                    didOpen: () => {
                        // Kembali ke menu utama
                        document.getElementById('backToMainMenu').addEventListener('click', () => {
                            showMainMenu();
                        });

                        // Import Excel
                        document.getElementById('importExcelBtn').addEventListener('click', () => {
                            const file = document.getElementById('excelFile').files[0];

                            if (!file) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'File Belum Dipilih!',
                                    text: 'Silakan pilih file Excel terlebih dahulu.',
                                    confirmButtonColor: '#f87171',
                                });
                                return;
                            }

                            // Show loading
                            Swal.fire({
                                title: 'Uploading...',
                                html: 'Sedang mengupload dan memproses file...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            let formData = new FormData();
                            formData.append('file', file);
                            if (selectedRoleToCreate) {
                                formData.append('role', selectedRoleToCreate);
                            }

                            fetch("{{ route('admin.users.import') }}", {
                                    method: "POST",
                                    headers: {
                                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                    },
                                    body: formData
                                })
                                .then(res => res.json())
                                .then(res => {
                                    if (res.success) {
                                        Swal.fire({
                                            icon: "success",
                                            title: "Berhasil!",
                                            html: `<p class="text-gray-700">${res.message}</p>`,
                                            confirmButtonColor: '#10b981',
                                        }).then(() => location.reload());
                                    } else {
                                        Swal.fire({
                                            icon: "error",
                                            title: "Gagal Import",
                                            html: `<p class="text-gray-700">${res.message}</p>`,
                                            confirmButtonColor: '#ef4444',
                                        });
                                    }
                                })
                                .catch(err => {
                                    console.error("Error:", err);
                                    Swal.fire({
                                        icon: "error",
                                        title: "Error",
                                        text: "Terjadi kesalahan saat upload file.",
                                        confirmButtonColor: '#ef4444',
                                    });
                                });
                        });
                    }
                });
            }

            /**
             * Execute Download Template (langsung download tanpa pilih lokasi)
             */
            function executeDownload() {
                Swal.fire({
                    title: 'Generating Template',
                    html: 'Mempersiapkan template berdasarkan data Posyandu terdaftar...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Trigger download
                const roleParam = selectedRoleToCreate ? `?role=${encodeURIComponent(selectedRoleToCreate)}` : '';
                const url = "{{ route('admin.users.export.template') }}" + roleParam;
                window.location.href = url;

                // Show success message
                setTimeout(() => {
                    const roleLabel = roleLabels[selectedRoleToCreate] || 'User';
                    const rowInfo = selectedRoleToCreate === 'kader'
                        ? 'Jumlah baris = 6 per Posyandu'
                        : 'Jumlah baris = Jumlah Posyandu terdaftar';
                    Swal.fire({
                        icon: 'success',
                        title: 'Template Sedang Diunduh',
                        html: `
                <p class="text-gray-700">Template User ${roleLabel} sedang diunduh.</p>
                <br>
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded text-left">
                    <p class="text-sm text-blue-700">
                        <strong>📋 Informasi Template:</strong>
                        <br>• Kolom DESA, KECAMATAN, KABUPATEN sudah terisi otomatis
                        <br>• ${rowInfo}
                        <br>• <strong>Anda hanya perlu isi NAMA dan NOMOR TELEPON</strong>
                        <br>• Password default: <code class="bg-white px-2 py-1 rounded">password123</code>
                    </p>
                </div>
            `,
                        timer: 5000,
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#10b981'
                    });
                }, 1000);
            }
        </script>
    @endpush
@endsection
