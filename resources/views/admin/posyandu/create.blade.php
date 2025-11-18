@extends('dashboard.layouts.dashboard')
@section('title', 'Tambah Data Posyandu')
@section('content')
    <div class="w-full max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-8 text-gray-900">

                <form method="POST" action="{{ route('admin.posyandu.store') }}" x-data="dependentDropdowns()">
                    @csrf
                    <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">Formulir Posyandu Baru</h2>

                    <div class="space-y-4">
                        <div>
                            <x-input-label for="nama_posyandu" :value="__('Nama Posyandu')" />
                            <x-text-input id="nama_posyandu" class="block mt-1 w-full" type="text" name="nama_posyandu"
                                :value="old('nama_posyandu')" required />
                            <x-input-error :messages="$errors->get('nama_posyandu')" class="mt-2" />
                        </div>

                        {{-- Combobox Ketua Kader --}}
                        <div x-data="ketuaCombobox()" @click.away="open = false" class="relative">
                            <div class="flex justify-between items-center">
                                <x-input-label for="ketua_kader_id" :value="__('Pilih Ketua Kader (Opsional)')" />
                                <a href="{{ route('admin.users.create', ['source' => 'posyandu_create']) }}"
                                    class="text-sm text-pink-600 hover:underline">
                                    + Buat User Baru
                                </a>
                            </div>

                            <input type="hidden" name="ketua_kader_id" :value="selected">

                            <div class="relative">
                                <input type="text" x-model="search" @focus="open = true" @input="open = true"
                                    :placeholder="getSelectedName() || 'Cari Ketua Kader...'"
                                    class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    autocomplete="off">

                                <button type="button" @click="open = !open"
                                    class="absolute inset-y-0 right-0 flex items-center px-3">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                            </div>

                            <div x-show="open" x-transition
                                class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">

                                <template x-for="user in filteredUsers()" :key="user.id">
                                    <div @click="selectUser(user)" class="px-4 py-2 cursor-pointer hover:bg-indigo-50"
                                        :class="{ 'bg-indigo-100': selected == user.id }">
                                        <span x-text="`${user.name} (${user.email})`"></span>
                                    </div>
                                </template>

                                <div x-show="filteredUsers().length === 0" class="px-4 py-2 text-gray-500 text-sm">
                                    Tidak ada hasil
                                </div>
                            </div>

                            <x-input-error :messages="$errors->get('ketua_kader_id')" class="mt-2" />
                        </div>

                        {{-- Combobox Kabupaten --}}
                        <div x-data="{ open: false, search: '' }" @click.away="open = false" class="relative">
                            <x-input-label for="kabupaten" :value="__('Kabupaten')" />
                            <input type="hidden" name="kabupaten" :value="selectedKabupaten">
                            <div class="relative">
                                <input type="text" x-model="search" @focus="open = true" @input="open = true"
                                    :placeholder="getKabupatenName(selectedKabupaten) || 'Cari Kabupaten...'"
                                    class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    autocomplete="off">
                                <button type="button" @click="open = !open"
                                    class="absolute inset-y-0 right-0 flex items-center px-3">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                            </div>
                            <div x-show="open" x-transition
                                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                                <template
                                    x-for="kab in kabupatens.filter(k => k.name.toLowerCase().includes(search.toLowerCase()))"
                                    :key="kab.code">
                                    <div @click="selectedKabupaten = `${kab.code}_${kab.name}`; search = ''; open = false; fetchKecamatan()"
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
                        </div>

                        {{-- Combobox Kecamatan --}}
                        <div x-data="{ open: false, search: '' }" @click.away="open = false" class="relative">
                            <x-input-label for="kecamatan" :value="__('Kecamatan')" />
                            <input type="hidden" name="kecamatan" :value="selectedKecamatan">
                            <div class="relative">
                                <input type="text" x-model="search" @focus="open = true" @input="open = true"
                                    :placeholder="getKecamatanName(selectedKecamatan) || 'Cari Kecamatan...'"
                                    :disabled="kecamatanList.length === 0 && !loadingKecamatan"
                                    class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                                    autocomplete="off">
                                <button type="button" @click="open = !open"
                                    class="absolute inset-y-0 right-0 flex items-center px-3"
                                    :disabled="kecamatanList.length === 0 && !loadingKecamatan">
                                    <svg x-show="!loadingKecamatan" class="w-5 h-5 text-gray-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                    <svg x-show="loadingKecamatan" class="animate-spin h-5 w-5 text-indigo-500"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </button>
                            </div>

                            {{-- Loading State --}}
                            <div x-show="loadingKecamatan"
                                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                                <div class="px-4 py-3 flex items-center space-x-2">
                                    <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <span class="text-gray-600 text-sm">Memuat kecamatan...</span>
                                </div>
                            </div>

                            {{-- Dropdown List --}}
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
                        </div>

                        {{-- Combobox Desa --}}
                        <div x-data="{ open: false, search: '' }" @click.away="open = false" class="relative">
                            <x-input-label for="desa" :value="__('Desa/Kelurahan')" />
                            <input type="hidden" name="desa" :value="selectedDesa">
                            <div class="relative">
                                <input type="text" x-model="search" @focus="open = true" @input="open = true"
                                    :placeholder="getDesaName(selectedDesa) || 'Cari Desa/Kelurahan...'"
                                    :disabled="desaList.length === 0 && !loadingDesa"
                                    class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                                    autocomplete="off">
                                <button type="button" @click="open = !open"
                                    class="absolute inset-y-0 right-0 flex items-center px-3"
                                    :disabled="desaList.length === 0 && !loadingDesa">
                                    <svg x-show="!loadingDesa" class="w-5 h-5 text-gray-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                    <svg x-show="loadingDesa" class="animate-spin h-5 w-5 text-indigo-500"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </button>
                            </div>

                            {{-- Loading State --}}
                            <div x-show="loadingDesa"
                                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                                <div class="px-4 py-3 flex items-center space-x-2">
                                    <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <span class="text-gray-600 text-sm">Memuat desa...</span>
                                </div>
                            </div>

                            {{-- Dropdown List --}}
                            <div x-show="open && !loadingDesa" x-transition
                                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                                <template
                                    x-for="desa in desaList.filter(d => d.name.toLowerCase().includes(search.toLowerCase()))"
                                    :key="desa.code">
                                    <div @click="selectedDesa = `${desa.code}_${desa.name}`; search = ''; open = false"
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
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-8 gap-4">
                        <a href="{{ route('admin.posyandu.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm font-semibold hover:bg-gray-300">Batal</a>
                        <x-primary-button>
                            {{ __('Simpan') }}
                        </x-primary-button>
                    </div>
                </form>
                <button id="importBtn" q class="mt-4 px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700">
                    Import Nama Posyandu
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('dependentDropdowns', () => ({
                    selectedKabupaten: '',
                    selectedKecamatan: '',
                    selectedDesa: '',
                    kecamatanList: [],
                    desaList: [],
                    loadingKecamatan: false,
                    loadingDesa: false,
                    kabupatens: @json($kabupatens['data'] ?? []),

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

                    async fetchKecamatan() {
                        this.kecamatanList = [];
                        this.desaList = [];
                        this.selectedKecamatan = '';
                        this.selectedDesa = '';
                        this.loadingKecamatan = true;

                        if (this.selectedKabupaten) {
                            try {
                                const kabId = this.selectedKabupaten.split('_')[0];
                                const response = await fetch(
                                    `{{ route('api.kecamatan') }}?kab_id=${kabId}`);

                                if (!response.ok) throw new Error('Network error');

                                const data = await response.json();
                                this.kecamatanList = data.data ?? [];
                            } catch (error) {
                                console.error('Error fetching kecamatan:', error);
                                alert('Gagal memuat data kecamatan. Silakan coba lagi.');
                            }
                        }
                        this.loadingKecamatan = false;
                    },

                    async fetchDesa() {
                        this.desaList = [];
                        this.selectedDesa = '';
                        this.loadingDesa = true;

                        if (this.selectedKecamatan) {
                            try {
                                const kecId = this.selectedKecamatan.split('_')[0];
                                const response = await fetch(`{{ route('api.desa') }}?kec_id=${kecId}`);

                                if (!response.ok) throw new Error('Network error');

                                const data = await response.json();
                                this.desaList = data.data ?? [];
                            } catch (error) {
                                console.error('Error fetching desa:', error);
                                alert('Gagal memuat data desa. Silakan coba lagi.');
                            }
                        }
                        this.loadingDesa = false;
                    }
                }));

                Alpine.data('ketuaCombobox', () => ({
                    open: false,
                    search: '',
                    selected: '{{ old('ketua_kader_id') }}',
                    users: @json($availableKetuas),

                    getSelectedName() {
                        if (!this.selected) return '';
                        const user = this.users.find(u => u.id == this.selected);
                        return user ? `${user.name} (${user.email})` : '';
                    },

                    filteredUsers() {
                        const keyword = this.search.toLowerCase();
                        return this.users.filter(u =>
                            u.name.toLowerCase().includes(keyword) ||
                            u.email.toLowerCase().includes(keyword)
                        );
                    },

                    selectUser(user) {
                        this.selected = user.id;
                        this.search = '';
                        this.open = false;
                    }
                }));
            });

            // Ganti semua JavaScript di bagian bawah view create.blade.php dengan ini:

            document.getElementById('importBtn').addEventListener('click', function() {
                // STEP 1: Pilih Import atau Download Template
                showMainMenu();
            });

            /**
             * STEP 1: Menu Utama - Pilih Import atau Download Template
             */
            function showMainMenu() {
                let menuHTML = `
        <div class="space-y-6 text-center">
            <p class="text-gray-600 mb-6">Pilih aksi yang ingin dilakukan:</p>
            
            <!-- Upload Import -->
            <div class="bg-gradient-to-r from-emerald-50 to-emerald-100 border-2 border-emerald-300 rounded-xl p-6 hover:shadow-lg transition-all cursor-pointer"
                 id="uploadOption">
                <div class="flex items-center justify-center gap-4">
                    <div class="bg-emerald-500 p-4 rounded-full">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                    </div>
                    <div class="text-left">
                        <h3 class="text-xl font-bold text-emerald-700">Upload & Import Data</h3>
                        <p class="text-sm text-emerald-600">Unggah file Excel untuk import posyandu</p>
                    </div>
                </div>
            </div>

            <!-- Download Template -->
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 border-2 border-blue-300 rounded-xl p-6 hover:shadow-lg transition-all cursor-pointer"
                 id="downloadOption">
                <div class="flex items-center justify-center gap-4">
                    <div class="bg-blue-500 p-4 rounded-full">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="text-left">
                        <h3 class="text-xl font-bold text-blue-700">Download Template</h3>
                        <p class="text-sm text-blue-600">Unduh template Excel untuk diisi</p>
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
                    title: '<h2 class="text-2xl font-bold text-gray-800 mb-2">Import Posyandu</h2>',
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
                            showLocationStep();
                        });

                        // Cancel
                        document.getElementById('cancelMainMenu').addEventListener('click', () => {
                            Swal.close();
                        });
                    }
                });
            }

            /**
             * STEP 2A: Upload File Excel
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
                    <strong>💡 Tips:</strong> Pastikan file Excel Anda sudah sesuai dengan format template yang disediakan.
                </p>
            </div>

            <!-- Buttons -->
            <div class="flex justify-between gap-3 pt-4 border-t">
                <button id="backToMainMenu"
                    class="px-4 py-2.5 bg-gray-200 text-gray-700 hover:bg-gray-300 font-medium rounded-md">
                    ← Kembali
                </button>
                <button id="importExcelBtn"
                    class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-md shadow flex items-center gap-2">
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
                                html: 'Sedang mengupload file, mohon tunggu...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            let formData = new FormData();
                            formData.append('file', file);
                            formData.append('kecamatan', 'AUTO_FROM_EXCEL');
                            formData.append('desa', 'AUTO_FROM_EXCEL');

                            fetch("{{ route('admin.posyandu.import') }}", {
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
             * STEP 2B: Pilih Lokasi untuk Download Template
             */
            function showLocationStep() {
                let locationHTML = `
        <div class="space-y-4 text-left">
            <!-- Filter Kecamatan -->
            <div class="relative">
                <label class="block text-start font-semibold mb-2 text-gray-700">Pilih Kecamatan:</label>
                
                <input type="hidden" id="importKecamatanValue">
                
                <div class="relative">
                    <input 
                        type="text"
                        id="importKecamatanSearch"
                        placeholder="Cari kecamatan atau pilih 'Semua Kecamatan'..."
                        class="block w-full border border-gray-300 rounded-md p-2.5"
                        autocomplete="off"
                    >
                    <button type="button" id="toggleImportKecamatanDropdown"
                        class="absolute inset-y-0 right-0 flex items-center px-3">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
                
                <div id="importKecamatanDropdownList" 
                    class="hidden absolute z-50 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                    <div id="importKecamatanOptions">
                        <div class="px-4 py-2 text-center text-gray-500">
                            <svg class="animate-spin h-5 w-5 mx-auto text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm mt-2">Memuat kecamatan...</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Desa -->
            <div class="relative">
                <label class="block text-start font-semibold mb-2 text-gray-700">Pilih Desa:</label>
                
                <input type="hidden" id="importDesaValue">
                
                <div class="relative">
                    <input 
                        type="text"
                        id="importDesaSearch"
                        placeholder="Pilih kecamatan terlebih dahulu..."
                        class="block w-full border border-gray-300 rounded-md p-2.5 bg-gray-100"
                        autocomplete="off"
                        disabled
                    >
                    <button type="button" id="toggleImportDesaDropdown"
                        class="absolute inset-y-0 right-0 flex items-center px-3"
                        disabled>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
                
                <div id="importDesaDropdownList" 
                    class="hidden absolute z-50 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                    <div id="importDesaOptions"></div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex justify-between gap-3 mt-6 pt-4 border-t">
                <button id="backToMainMenuFromLocation"
                    class="px-4 py-2.5 bg-gray-200 text-gray-700 hover:bg-gray-300 font-medium rounded-md">
                    ← Kembali
                </button>
                <button id="downloadTemplateBtn"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-md shadow flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download Template
                </button>
            </div>
        </div>
    `;

                Swal.fire({
                    title: '<h2 class="text-xl font-bold text-gray-800 mb-2">Pilih Lokasi Template</h2>',
                    html: locationHTML,
                    showConfirmButton: false,
                    showCancelButton: false,
                    width: 600,
                    background: '#f9fafb',
                    customClass: {
                        popup: 'rounded-2xl shadow-2xl p-6'
                    },
                    didOpen: () => {
                        initializeLocationDropdowns();
                    }
                });
            }

            /**
             * Initialize dropdowns untuk pemilihan lokasi
             */
            async function initializeLocationDropdowns() {
                let kecamatanList = [];
                let desaList = [];
                let kabupatenId = '';

                const kecamatanSearch = document.getElementById('importKecamatanSearch');
                const kecamatanDropdown = document.getElementById('importKecamatanDropdownList');
                const kecamatanOptions = document.getElementById('importKecamatanOptions');
                const kecamatanToggle = document.getElementById('toggleImportKecamatanDropdown');
                const kecamatanValue = document.getElementById('importKecamatanValue');

                const desaSearch = document.getElementById('importDesaSearch');
                const desaDropdown = document.getElementById('importDesaDropdownList');
                const desaOptions = document.getElementById('importDesaOptions');
                const desaToggle = document.getElementById('toggleImportDesaDropdown');
                const desaValue = document.getElementById('importDesaValue');

                // Fetch Kabupaten untuk dapat ID Kebumen
                try {
                    const responseKab = await fetch('/api/wilayah/kabupaten');
                    const dataKab = await responseKab.json();

                    const kebumen = dataKab.data.find(kab => kab.name.toLowerCase().includes('kebumen'));
                    if (kebumen) {
                        kabupatenId = kebumen.code;
                    } else {
                        kabupatenId = '33.05';
                    }

                    // Fetch Kecamatan berdasarkan kabupaten ID
                    const response = await fetch(`/api/wilayah/kecamatan/${kabupatenId}`);
                    const data = await response.json();
                    kecamatanList = data.data || [];

                    renderKecamatanOptions();
                } catch (error) {
                    console.error('Error fetching kecamatan:', error);
                    kecamatanOptions.innerHTML =
                        '<div class="px-4 py-2 text-red-500 text-sm">Gagal memuat data kecamatan</div>';
                }

                function renderKecamatanOptions(filter = '') {
                    const filtered = filter ?
                        kecamatanList.filter(k => k.name.toLowerCase().includes(filter.toLowerCase())) :
                        kecamatanList;

                    let html = `
            <div class="kecamatan-option px-4 py-2.5 cursor-pointer hover:bg-blue-50 bg-blue-100 border-b font-semibold text-blue-700" 
                 data-value="all" data-name="SEMUA KECAMATAN">
                ✓ SEMUA KECAMATAN
            </div>
        `;

                    if (filtered.length === 0) {
                        html += '<div class="px-4 py-2 text-gray-500 text-sm">Tidak ada hasil</div>';
                    } else {
                        html += filtered.map(kec =>
                            `<div class="kecamatan-option px-4 py-2 cursor-pointer hover:bg-blue-50" 
                      data-value="${kec.code}" data-name="${kec.name}">
                    ${kec.name}
                </div>`
                        ).join('');
                    }

                    kecamatanOptions.innerHTML = html;

                    document.querySelectorAll('.kecamatan-option').forEach(option => {
                        option.addEventListener('click', async function() {
                            const value = this.getAttribute('data-value');
                            const name = this.getAttribute('data-name');

                            kecamatanSearch.value = name;
                            kecamatanValue.value = value;
                            kecamatanDropdown.classList.add('hidden');

                            // Reset desa
                            desaValue.value = '';
                            desaSearch.value = '';
                            desaList = [];

                            if (value === 'all') {
                                desaSearch.placeholder =
                                    'Pilih "Semua Desa" atau pilih kecamatan spesifik';
                                desaSearch.disabled = false;
                                desaToggle.disabled = false;
                                desaSearch.classList.remove('bg-gray-100');

                                renderDesaOptions();
                            } else {
                                await fetchDesaByKecamatan(value);
                            }
                        });
                    });
                }

                async function fetchDesaByKecamatan(kecamatanId) {
                    desaSearch.placeholder = 'Memuat desa...';
                    desaSearch.disabled = true;
                    desaToggle.disabled = true;
                    desaOptions.innerHTML =
                        '<div class="px-4 py-2 text-center text-gray-500"><svg class="animate-spin h-5 w-5 mx-auto text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>';

                    try {
                        const response = await fetch(`/api/wilayah/desa/${kecamatanId}`);
                        const data = await response.json();
                        desaList = data.data || [];

                        desaSearch.placeholder = 'Cari desa atau pilih "Semua Desa"...';
                        desaSearch.disabled = false;
                        desaToggle.disabled = false;
                        desaSearch.classList.remove('bg-gray-100');

                        renderDesaOptions();
                    } catch (error) {
                        console.error('Error fetching desa:', error);
                        desaOptions.innerHTML =
                            '<div class="px-4 py-2 text-red-500 text-sm">Gagal memuat data desa</div>';
                        desaSearch.placeholder = 'Gagal memuat desa';
                    }
                }

                function renderDesaOptions(filter = '') {
                    const isAllKecamatan = kecamatanValue.value === 'all';

                    let html = `
            <div class="desa-option px-4 py-2.5 cursor-pointer hover:bg-blue-50 bg-blue-100 border-b font-semibold text-blue-700" 
                 data-value="all" data-name="SEMUA DESA">
                ✓ SEMUA DESA
            </div>
        `;

                    if (isAllKecamatan) {
                        html +=
                            '<div class="px-4 py-2 text-gray-500 text-sm italic">Pilih kecamatan spesifik untuk melihat daftar desa</div>';
                    } else {
                        const filtered = filter ?
                            desaList.filter(d => d.name.toLowerCase().includes(filter.toLowerCase())) :
                            desaList;

                        if (filtered.length === 0) {
                            html += '<div class="px-4 py-2 text-gray-500 text-sm">Tidak ada hasil</div>';
                        } else {
                            html += filtered.map(desa =>
                                `<div class="desa-option px-4 py-2 cursor-pointer hover:bg-blue-50" 
                          data-value="${desa.code}" data-name="${desa.name}">
                        ${desa.name}
                    </div>`
                            ).join('');
                        }
                    }

                    desaOptions.innerHTML = html;

                    document.querySelectorAll('.desa-option').forEach(option => {
                        option.addEventListener('click', function() {
                            const value = this.getAttribute('data-value');
                            const name = this.getAttribute('data-name');

                            desaSearch.value = name;
                            desaValue.value = value;
                            desaDropdown.classList.add('hidden');
                        });
                    });
                }

                // Event listeners
                kecamatanSearch.addEventListener('focus', () => kecamatanDropdown.classList.remove('hidden'));
                kecamatanSearch.addEventListener('input', (e) => {
                    renderKecamatanOptions(e.target.value);
                    kecamatanDropdown.classList.remove('hidden');
                });
                kecamatanToggle.addEventListener('click', () => kecamatanDropdown.classList.toggle('hidden'));

                desaSearch.addEventListener('focus', () => {
                    if (!desaSearch.disabled) {
                        desaDropdown.classList.remove('hidden');
                    }
                });
                desaSearch.addEventListener('input', (e) => {
                    renderDesaOptions(e.target.value);
                    desaDropdown.classList.remove('hidden');
                });
                desaToggle.addEventListener('click', () => {
                    if (!desaToggle.disabled) {
                        desaDropdown.classList.toggle('hidden');
                    }
                });

                // Close dropdowns on outside click
                document.addEventListener('click', (e) => {
                    if (!kecamatanSearch.contains(e.target) && !kecamatanDropdown.contains(e.target) && !
                        kecamatanToggle.contains(e.target)) {
                        kecamatanDropdown.classList.add('hidden');
                    }
                    if (!desaSearch.contains(e.target) && !desaDropdown.contains(e.target) && !desaToggle.contains(e
                            .target)) {
                        desaDropdown.classList.add('hidden');
                    }
                });

                // Button handlers
                document.getElementById('backToMainMenuFromLocation').addEventListener('click', () => {
                    showMainMenu();
                });

                document.getElementById('downloadTemplateBtn').addEventListener('click', () => {
                    const kecamatan = kecamatanValue.value;
                    const kecamatanName = kecamatanSearch.value;
                    const desa = desaValue.value;
                    const desaName = desaSearch.value;

                    if (!kecamatan) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Kecamatan Belum Dipilih!',
                            text: 'Silakan pilih kecamatan terlebih dahulu.',
                            confirmButtonColor: '#f87171',
                        });
                        return;
                    }

                    if (!desa) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Desa Belum Dipilih!',
                            text: 'Silakan pilih desa terlebih dahulu.',
                            confirmButtonColor: '#f87171',
                        });
                        return;
                    }

                    // Execute download
                    executeDownload(kecamatan, kecamatanName, desa, desaName);
                });
            }

            /**
             * Execute Download Template
             */
            function executeDownload(kecamatanCode, kecamatanName, desaCode, desaName) {
                let loadingText = 'Mempersiapkan template...';
                if (kecamatanCode === 'all' && desaCode === 'all') {
                    loadingText =
                        'Sedang fetch data dari API wilayah...<br><small class="text-gray-500">Proses ini membutuhkan ~10-20 detik karena generate untuk semua wilayah</small>';
                }

                Swal.fire({
                    title: 'Generating Template',
                    html: loadingText,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                let url;

                // Tentukan URL berdasarkan pilihan
                if (kecamatanCode === 'all' && desaCode === 'all') {
                    url = `/admin/export-posyandu/all/all`;
                } else if (desaCode === 'all') {
                    url = `/admin/export-posyandu-kecamatan/${encodeURIComponent(kecamatanName)}`;
                } else {
                    url = `/admin/export-posyandu/${encodeURIComponent(desaName)}/${encodeURIComponent(kecamatanName)}`;
                }

                // Trigger download
                window.location.href = url;

                // Generate message
                let messageText = '';
                let rowInfo = '';

                if (desaCode === 'all' && kecamatanCode === 'all') {
                    messageText = 'Template untuk <strong>Semua Wilayah</strong> (Semua Kecamatan & Desa)';
                    rowInfo =
                        '<br><small class="text-gray-600">Kolom DESA dan KECAMATAN sudah terisi otomatis untuk setiap wilayah.</small>';
                } else if (desaCode === 'all') {
                    messageText = 'Template untuk <strong>' + kecamatanName + '</strong> (Semua Desa)';
                    rowInfo =
                        '<br><small class="text-gray-600">Kolom DESA dan KECAMATAN sudah terisi otomatis untuk setiap desa.</small>';
                } else {
                    messageText = 'Template untuk <strong>' + desaName + ', ' + kecamatanName + '</strong>';
                    rowInfo =
                        '<br><small class="text-gray-600">Template berisi 20 baris kosong dengan data wilayah terisi otomatis.</small>';
                }

                messageText += ' akan segera diunduh.' + rowInfo;
                messageText +=
                    '<br><br><strong class="text-blue-600">✏️ Anda hanya perlu isi kolom NAMA POSYANDU saja!</strong>';

                // Show success
                Swal.fire({
                    icon: 'success',
                    title: 'Template Sedang Diunduh',
                    html: messageText,
                    timer: 3500,
                    showConfirmButton: false
                });
            }
        </script>
    @endpush

@endsection
