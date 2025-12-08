@extends('dashboard.layouts.dashboard')
@section('title', 'Tambah Kecamatan')
@section('content')
    <div class="w-full max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-8 text-gray-900">

                <form method="POST" action="{{ route('admin.kecamatan.store') }}" x-data="wilayahDropdown()">
                    @csrf
                    <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">Tambah Kecamatan Baru</h2>

                    <div class="space-y-6">

                        {{-- 1. Combobox Kabupaten (Searchable) --}}
                        <div class="relative" @click.away="openKabupaten = false">
                            <x-input-label for="kabupaten" :value="__('Kabupaten')" />

                            {{-- Hidden Input untuk kirim ke Backend --}}
                            <input type="hidden" name="kabupaten" x-model="selectedKabupatenValue">

                            <div class="relative mt-1">
                                <input type="text" x-model="searchKabupaten" @focus="openKabupaten = true"
                                    @input="openKabupaten = true"
                                    :placeholder="selectedKabupatenName || 'Cari Kabupaten...'"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:border-pink-500 focus:ring-pink-500"
                                    autocomplete="off">

                                {{-- Icon Panah --}}
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>

                            {{-- Dropdown List Kabupaten --}}
                            <div x-show="openKabupaten && filteredKabupaten.length > 0" x-transition
                                class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
                                style="display: none;">

                                <template x-for="kab in filteredKabupaten" :key="kab.code">
                                    <div @click="selectKabupaten(kab)"
                                        class="px-4 py-2 cursor-pointer hover:bg-pink-50 text-sm text-gray-700"
                                        :class="{ 'bg-pink-100 font-semibold': selectedKabupatenValue ===
                                                `${kab.code}_${kab.name}` }">
                                        <span x-text="kab.name"></span>
                                    </div>
                                </template>

                                <div x-show="filteredKabupaten.length === 0"
                                    class="px-4 py-2 text-gray-500 text-sm text-center">
                                    Tidak ada kabupaten ditemukan.
                                </div>
                            </div>
                            <x-input-error :messages="$errors->get('kabupaten')" class="mt-2" />
                        </div>

                        {{-- 2. Combobox Kecamatan (Searchable - Dependent) --}}
                        <div class="relative" @click.away="openKecamatan = false">
                            <x-input-label for="kecamatan" :value="__('Kecamatan')" />

                            <input type="hidden" name="kecamatan" x-model="selectedKecamatanValue">

                            <div class="relative mt-1">
                                <input type="text" x-model="searchKecamatan" @focus="openKecamatan = true"
                                    @input="openKecamatan = true"
                                    :placeholder="selectedKecamatanName || 'Cari Kecamatan...'"
                                    :disabled="!selectedKabupatenValue || loadingKecamatan"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:border-pink-500 focus:ring-pink-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                                    autocomplete="off">

                                {{-- Icon Loading / Panah --}}
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                    <svg x-show="!loadingKecamatan" class="w-5 h-5 text-gray-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                    <svg x-show="loadingKecamatan" class="animate-spin h-5 w-5 text-pink-500"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </div>
                            </div>

                            {{-- Dropdown List Kecamatan --}}
                            <div x-show="openKecamatan && !loadingKecamatan && kecamatanList.length > 0" x-transition
                                class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
                                style="display: none;">

                                <template x-for="kec in filteredKecamatan" :key="kec.code">
                                    <div @click="selectKecamatan(kec)"
                                        class="px-4 py-2 cursor-pointer hover:bg-pink-50 text-sm text-gray-700"
                                        :class="{ 'bg-pink-100 font-semibold': selectedKecamatanValue ===
                                                `${kec.code}_${kec.name}` }">
                                        <span x-text="kec.name"></span>
                                    </div>
                                </template>

                                <div x-show="filteredKecamatan.length === 0"
                                    class="px-4 py-2 text-gray-500 text-sm text-center">
                                    Tidak ada kecamatan ditemukan.
                                </div>
                            </div>

                            <p x-show="!selectedKabupatenValue" class="mt-1 text-xs text-gray-500">
                                Silakan pilih Kabupaten terlebih dahulu.
                            </p>
                            <x-input-error :messages="$errors->get('kecamatan')" class="mt-2" />
                        </div>

                    </div>

                    <div class="flex items-center justify-end mt-8 gap-4">
                        <a href="{{ route('admin.kecamatan.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm font-semibold hover:bg-gray-300">Batal</a>
                        <x-primary-button>{{ __('Simpan') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('wilayahDropdown', () => ({
                    // --- STATE KABUPATEN ---
                    openKabupaten: false,
                    searchKabupaten: '',
                    selectedKabupatenValue: '', // Disimpan ke DB (Code_Nama)
                    selectedKabupatenName: '', // Ditampilkan di input
                    kabupatenList: @json($kabupatens['data'] ?? []), // Inject Data dari PHP

                    // --- STATE KECAMATAN ---
                    openKecamatan: false,
                    loadingKecamatan: false,
                    searchKecamatan: '',
                    selectedKecamatanValue: '',
                    selectedKecamatanName: '',
                    kecamatanList: [],

                    // --- LOGIC KABUPATEN ---
                    get filteredKabupaten() {
                        if (this.searchKabupaten === '') return this.kabupatenList;
                        return this.kabupatenList.filter(kab =>
                            kab.name.toLowerCase().includes(this.searchKabupaten.toLowerCase())
                        );
                    },

                    selectKabupaten(kab) {
                        this.selectedKabupatenValue = `${kab.code}_${kab.name}`;
                        this.selectedKabupatenName = kab.name;
                        this.searchKabupaten = '';
                        this.openKabupaten = false;

                        // Trigger fetch kecamatan
                        this.fetchKecamatan();
                    },

                    // --- LOGIC KECAMATAN ---
                    get filteredKecamatan() {
                        if (this.searchKecamatan === '') return this.kecamatanList;
                        return this.kecamatanList.filter(kec =>
                            kec.name.toLowerCase().includes(this.searchKecamatan.toLowerCase())
                        );
                    },

                    async fetchKecamatan() {
                        // Reset kecamatan saat kabupaten berubah
                        this.kecamatanList = [];
                        this.selectedKecamatanValue = '';
                        this.selectedKecamatanName = '';
                        this.searchKecamatan = '';

                        if (this.selectedKabupatenValue) {
                            this.loadingKecamatan = true;
                            // Ambil kode kabupaten dari string "CODE_NAMA"
                            const kabId = this.selectedKabupatenValue.split('_')[0];

                            try {
                                const response = await fetch(
                                    `{{ url('/api/wilayah/kecamatan') }}/${kabId}`);
                                const data = await response.json();
                                this.kecamatanList = data.data ?? [];
                            } catch (e) {
                                console.error("Gagal memuat kecamatan:", e);
                                alert('Gagal memuat data kecamatan. Periksa koneksi internet Anda.');
                            }

                            this.loadingKecamatan = false;
                        }
                    },

                    selectKecamatan(kec) {
                        this.selectedKecamatanValue = `${kec.code}_${kec.name}`;
                        this.selectedKecamatanName = kec.name;
                        this.searchKecamatan = '';
                        this.openKecamatan = false;
                    }
                }));
            });
        </script>
    @endpush
@endsection
