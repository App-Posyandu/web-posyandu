@extends('dashboard.layouts.dashboard')
@section('title', 'Tambah Data Posyandu')
@section('content')
    <div class="w-full max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-8 text-gray-900">

                <form method="POST" action="{{ route('admin.posyandu.store') }}" x-data="posyanduForm()">
                    @csrf
                    <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">Formulir Posyandu Baru</h2>

                    {{-- SECTION 1: DATA POSYANDU --}}
                    <div class="mb-8 p-6 bg-gray-50 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="bi bi-building text-pink-500"></i>
                            Informasi Posyandu
                        </h3>

                        <div class="space-y-4">
                            {{-- Nama Posyandu --}}
                            <div>
                                <x-input-label for="nama_posyandu" :value="__('Nama Posyandu')" />
                                <x-text-input id="nama_posyandu" class="block mt-1 w-full" type="text"
                                    name="nama_posyandu" :value="old('nama_posyandu')" required autofocus
                                    placeholder="Contoh: Posyandu Melati 1" />
                                <x-input-error :messages="$errors->get('nama_posyandu')" class="mt-2" />
                            </div>

                            {{-- Kabupaten/Kota --}}
                            @if (auth()->user()->kabupaten)
                                {{-- User sudah punya kabupaten - Auto Fill --}}
                                <div>
                                    <x-input-label for="kabupaten" :value="__('Kabupaten/Kota')" />
                                    <input type="hidden" name="kabupaten" value="{{ auth()->user()->kabupaten }}">
                                    <input type="text" value="{{ auth()->user()->kabupaten }}" disabled
                                        class="block mt-1 w-full border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed">
                                    <p class="mt-1 text-xs text-gray-500">
                                        <i class="bi bi-lock-fill text-gray-400"></i>
                                        Kabupaten sudah ditetapkan sesuai wilayah kerja Anda
                                    </p>
                                </div>
                            @else
                                {{-- Admin atau role lain - Pilih Kabupaten --}}
                                <div x-data="{ open: false, search: '' }" @click.away="open = false" class="relative">
                                    <x-input-label for="kabupaten" :value="__('Kabupaten/Kota')" />
                                    <input type="hidden" name="kabupaten" :value="selectedKabupaten">
                                    <div class="relative">
                                        <input type="text" x-model="search" @focus="open = true" @input="open = true"
                                            :placeholder="getKabupatenName(selectedKabupaten) || 'Cari Kabupaten/Kota...'"
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
                            @endif

                            {{-- Kecamatan --}}
                            @if (auth()->user()->kecamatan)
                                {{-- User sudah punya kecamatan - Auto Fill --}}
                                <div>
                                    <x-input-label for="kecamatan" :value="__('Kecamatan')" />
                                    <input type="hidden" name="kecamatan" value="{{ auth()->user()->kecamatan }}">
                                    <input type="text" value="{{ auth()->user()->kecamatan }}" disabled
                                        class="block mt-1 w-full border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed">
                                    <p class="mt-1 text-xs text-gray-500">
                                        <i class="bi bi-lock-fill text-gray-400"></i>
                                        Kecamatan sudah ditetapkan sesuai wilayah kerja Anda
                                    </p>
                                </div>
                            @else
                                {{-- Pilih Kecamatan Manual --}}
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
                                            @if (auth()->user()->kabupaten)
                                                Tidak ada data kecamatan
                                            @else
                                                Pilih kabupaten terlebih dahulu
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Desa/Kelurahan --}}
                            @if (auth()->user()->desa)
                                {{-- User sudah punya desa - Auto Fill --}}
                                <div>
                                    <x-input-label for="desa" :value="__('Desa/Kelurahan')" />
                                    <input type="hidden" name="desa" value="{{ auth()->user()->desa }}">
                                    <input type="text" value="{{ auth()->user()->desa }}" disabled
                                        class="block mt-1 w-full border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed">
                                    <p class="mt-1 text-xs text-gray-500">
                                        <i class="bi bi-lock-fill text-gray-400"></i>
                                        Desa sudah ditetapkan sesuai wilayah kerja Anda
                                    </p>
                                </div>
                            @else
                                {{-- Pilih Desa Manual --}}
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
                                            Pilih kecamatan terlebih dahulu
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- SECTION 2: KELOLA RW/RT --}}
                    <div class="mb-8 p-6 bg-blue-50 rounded-lg border border-blue-200">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="bi bi-diagram-3 text-blue-500"></i>
                            Kelola RW/RT Posyandu
                        </h3>

                        {{-- Info Card --}}
                        <div class="mb-6 bg-white border-l-4 border-blue-500 p-4 rounded">
                            <div class="flex items-start">
                                <i class="bi bi-info-circle-fill text-blue-500 mr-3 mt-0.5 text-xl"></i>
                                <div class="text-sm text-blue-700">
                                    <p class="font-semibold mb-2">ℹ️ Informasi Sistem RW/RT</p>
                                    <ul class="list-disc list-inside space-y-1 ml-2">
                                        <li><strong>RW tersedia:</strong> RW01 sampai RW15 (total 15 RW)</li>
                                        <li><strong>RT tersedia:</strong> RT001 sampai RT053 (total 53 RT)</li>
                                        <li>Pilih RW/RT mana saja yang <strong>dilayani oleh posyandu ini</strong></li>
                                        <li>Masyarakat yang daftar akan memilih dari RW/RT yang Anda tentukan</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Counter Stats --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div
                                class="bg-gradient-to-r from-purple-50 to-purple-100 p-4 rounded-lg border border-purple-200">
                                <div class="text-sm text-purple-600 font-semibold">RW yang Dilayani</div>
                                <div class="text-3xl font-bold text-purple-700">
                                    <span x-text="selectedRwList.length"></span>
                                </div>
                                <div class="text-xs text-purple-500 mt-1">dari 15 RW yang ada</div>
                            </div>

                            <div
                                class="bg-gradient-to-r from-green-50 to-green-100 p-4 rounded-lg border border-green-200">
                                <div class="text-sm text-green-600 font-semibold">RT yang Dilayani</div>
                                <div class="text-3xl font-bold text-green-700">
                                    <span x-text="getTotalSelectedRt()"></span>
                                </div>
                                <div class="text-xs text-green-500 mt-1">dari 53 RT yang ada</div>
                            </div>
                        </div>

                        {{-- Pilih RW --}}
                        <div class="mb-6">
                            <h4 class="text-md font-semibold text-gray-800 mb-3 flex items-center gap-2">
                                <i class="bi bi-check-square text-pink-500"></i>
                                Pilih RW yang Dilayani Posyandu
                            </h4>

                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                <div class="grid grid-cols-3 md:grid-cols-5 gap-3">
                                    <template x-for="rw in allRwOptions" :key="rw">
                                        <label
                                            class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg border cursor-pointer hover:border-pink-300 transition"
                                            :class="{ 'border-pink-500 bg-pink-50': selectedRwList.includes(rw) }">
                                            <input type="checkbox" :value="rw" x-model="selectedRwList"
                                                class="w-4 h-4 text-pink-600 rounded focus:ring-pink-500">
                                            <span class="text-sm font-medium text-gray-700" x-text="rw"></span>
                                        </label>
                                    </template>
                                </div>
                                <p class="text-xs text-gray-500 mt-3">
                                    <i class="bi bi-info-circle text-blue-500"></i>
                                    Centang RW mana saja yang dilayani oleh posyandu ini
                                </p>
                            </div>
                        </div>

                        {{-- Mapping RT per RW --}}
                        <div x-show="selectedRwList.length > 0">
                            <h4 class="text-md font-semibold text-gray-800 mb-3 flex items-center gap-2">
                                <i class="bi bi-list-check text-blue-500"></i>
                                Pilih RT untuk Setiap RW
                            </h4>

                            <div class="space-y-4">
                                <template x-for="rw in selectedRwList" :key="rw">
                                    <div class="border border-gray-200 rounded-lg p-4 bg-white">
                                        <div class="flex justify-between items-center mb-3">
                                            <h5 class="font-semibold text-gray-700 text-lg" x-text="rw"></h5>
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="text-sm px-2 py-1 bg-blue-100 text-blue-700 rounded font-medium">
                                                    <span x-text="(rtMapping[rw] || []).length"></span> / 53 RT
                                                </span>
                                                <button type="button" @click="selectAllRt(rw)"
                                                    class="px-3 py-1.5 bg-green-500 text-white rounded-md hover:bg-green-600 text-xs">
                                                    <i class="bi bi-check-all mr-1"></i>
                                                    Pilih Semua
                                                </button>
                                                <button type="button" @click="clearAllRt(rw)"
                                                    class="px-3 py-1.5 bg-gray-500 text-white rounded-md hover:bg-gray-600 text-xs">
                                                    <i class="bi bi-x-circle mr-1"></i>
                                                    Hapus Semua
                                                </button>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-4 md:grid-cols-8 lg:grid-cols-10 gap-2">
                                            <template x-for="rt in allRtOptions" :key="rt">
                                                <label
                                                    class="flex items-center justify-center p-2 bg-gray-50 rounded border cursor-pointer hover:border-blue-300 transition text-xs"
                                                    :class="{ 'border-blue-500 bg-blue-50': rtMapping[rw] && rtMapping[rw]
                                                            .includes(rt) }">
                                                    <input type="checkbox" :value="rt"
                                                        @change="toggleRt(rw, rt)"
                                                        :checked="rtMapping[rw] && rtMapping[rw].includes(rt)"
                                                        class="sr-only">
                                                    <span class="font-medium" x-text="rt"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Hidden inputs untuk submit RW/RT --}}
                        <template x-for="(rw, index) in selectedRwList" :key="index">
                            <input type="hidden" :name="'rw_list[' + index + ']'" :value="rw">
                        </template>

                        <template x-for="rw in selectedRwList" :key="rw">
                            <template x-for="(rt, rtIndex) in (rtMapping[rw] || [])" :key="rtIndex">
                                <input type="hidden" :name="'rt_mapping[' + rw + '][' + rtIndex + ']'"
                                    :value="rt">
                            </template>
                        </template>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center justify-between pt-6 border-t gap-4">
                        <a href="{{ route('admin.posyandu.index') }}"
                            class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            <i class="bi bi-arrow-left mr-2"></i>
                            Batal
                        </a>
                        <button type="submit"
                            class="px-8 py-2.5 bg-pink-600 text-white rounded-md hover:bg-pink-700 shadow-lg">
                            <i class="bi bi-save mr-2"></i>
                            💾 Simpan Posyandu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('posyanduForm', () => ({
                    // Data Wilayah
                    kabupatens: @json($kabupatens['data'] ?? []),
                    kecamatanList: [],
                    desaList: [],

                    @if (!auth()->user()->kabupaten)
                        selectedKabupaten: '',
                    @endif

                    @if (!auth()->user()->kecamatan)
                        selectedKecamatan: '',
                    @endif

                    @if (!auth()->user()->desa)
                        selectedDesa: '',
                    @endif

                    loadingKecamatan: false,
                    loadingDesa: false,

                    // Data RW/RT
                    allRwOptions: Array.from({
                        length: 15
                    }, (_, i) => `RW${String(i + 1).padStart(2, '0')}`),
                    allRtOptions: Array.from({
                        length: 53
                    }, (_, i) => `RT${String(i + 1).padStart(3, '0')}`),
                    selectedRwList: [],
                    rtMapping: {},

                    init() {
                        console.log('🟢 Posyandu Form initialized');

                        @if (auth()->user()->kabupaten && !auth()->user()->kecamatan)
                            // Auto-fetch kecamatan jika user punya kabupaten tapi belum punya kecamatan
                            this.fetchKecamatan();
                        @endif

                        @if (auth()->user()->kecamatan && !auth()->user()->desa)
                            // Auto-fetch desa jika user punya kecamatan tapi belum punya desa
                            this.fetchDesa();
                        @endif

                        // Watch selectedRwList untuk auto-init rtMapping
                        this.$watch('selectedRwList', (newVal, oldVal) => {
                            console.log('📝 RW List changed:', newVal);

                            newVal.forEach(rw => {
                                if (!this.rtMapping[rw]) {
                                    this.rtMapping[rw] = [];
                                }
                            });

                            if (oldVal) {
                                oldVal.forEach(rw => {
                                    if (!newVal.includes(rw)) {
                                        delete this.rtMapping[rw];
                                    }
                                });
                            }
                        });

                        this.$watch('rtMapping', (newVal) => {
                            console.log('📊 RT Mapping changed:', newVal);
                        }, {
                            deep: true
                        });
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

                    async fetchKecamatan() {
                        console.log('Fetching kecamatan...');

                        this.kecamatanList = [];
                        this.desaList = [];

                        @if (!auth()->user()->kecamatan)
                            this.selectedKecamatan = '';
                        @endif

                        @if (!auth()->user()->desa)
                            this.selectedDesa = '';
                        @endif

                        this.loadingKecamatan = true;

                        @if (auth()->user()->kabupaten)
                            const kabupatenName = '{{ auth()->user()->kabupaten }}';
                            const kabupaten = this.kabupatens.find(k => k.name === kabupatenName);
                            if (!kabupaten) {
                                console.error('Kabupaten tidak ditemukan:', kabupatenName);
                                this.loadingKecamatan = false;
                                return;
                            }
                            var kabId = kabupaten.code;
                        @else
                            if (!this.selectedKabupaten) {
                                this.loadingKecamatan = false;
                                return;
                            }
                            var kabId = this.selectedKabupaten.split('_')[0];
                        @endif

                        try {
                            const response = await fetch(
                            `{{ route('api.kecamatan') }}?kab_id=${kabId}`);
                            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                            const data = await response.json();
                            this.kecamatanList = data.data ?? [];

                            if (this.kecamatanList.length === 0) {
                                alert('Tidak ada data kecamatan untuk wilayah ini.');
                            }
                        } catch (error) {
                            console.error('Error fetching kecamatan:', error);
                            alert('Gagal memuat data kecamatan.');
                        } finally {
                            this.loadingKecamatan = false;
                        }
                    },

                    async fetchDesa() {
                        console.log('Fetching desa...');

                        this.desaList = [];

                        @if (!auth()->user()->desa)
                            this.selectedDesa = '';
                        @endif

                        this.loadingDesa = true;

                        @if (auth()->user()->kecamatan)
                            const kecamatanName = '{{ auth()->user()->kecamatan }}';
                            const kecamatan = this.kecamatanList.find(k => k.name === kecamatanName);
                            if (!kecamatan) {
                                console.error('Kecamatan tidak ditemukan:', kecamatanName);
                                this.loadingDesa = false;
                                return;
                            }
                            var kecId = kecamatan.code;
                        @else
                            if (!this.selectedKecamatan) {
                                this.loadingDesa = false;
                                return;
                            }
                            var kecId = this.selectedKecamatan.split('_')[0];
                        @endif

                        try {
                            const response = await fetch(`{{ route('api.desa') }}?kec_id=${kecId}`);
                            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                            const data = await response.json();
                            this.desaList = data.data ?? [];

                            if (this.desaList.length === 0) {
                                alert('Tidak ada data desa untuk kecamatan ini.');
                            }
                        } catch (error) {
                            console.error('Error fetching desa:', error);
                            alert('Gagal memuat data desa.');
                        } finally {
                            this.loadingDesa = false;
                        }
                    },

                    // RW/RT Functions
                    selectAllRt(rw) {
                        console.log('✅ Select all RT for', rw);
                        this.rtMapping[rw] = [...this.allRtOptions];
                        this.rtMapping = {
                            ...this.rtMapping
                        };
                    },

                    clearAllRt(rw) {
                        console.log('❌ Clear all RT for', rw);
                        this.rtMapping[rw] = [];
                        this.rtMapping = {
                            ...this.rtMapping
                        };
                    },

                    toggleRt(rw, rt) {
                        if (!this.rtMapping[rw]) {
                            this.rtMapping[rw] = [];
                        }

                        const index = this.rtMapping[rw].indexOf(rt);
                        if (index > -1) {
                            this.rtMapping[rw].splice(index, 1);
                        } else {
                            this.rtMapping[rw].push(rt);
                        }
                    },

                    getTotalSelectedRt() {
                        let total = 0;
                        Object.values(this.rtMapping).forEach(rtList => {
                            if (Array.isArray(rtList)) {
                                total += rtList.length;
                            }
                        });
                        return total;
                    }
                }));
            });
        </script>
    @endpush

@endsection
