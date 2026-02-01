@extends('dashboard.layouts.dashboard')
@section('title', 'Ubah Data Posyandu')
@section('content')
    <div class="w-full max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-8 text-gray-900">

                <form method="POST" action="{{ route('admin.posyandu.update', $posyandu) }}" x-data="posyanduEditForm()">
                    @csrf
                    @method('PATCH')

                    <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">Formulir Ubah Posyandu</h2>

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
                                <x-text-input id="nama_posyandu" class="block mt-1 w-full" type="text" name="nama_posyandu"
                                    :value="old('nama_posyandu', $posyandu->nama_posyandu)" required />
                                <x-input-error :messages="$errors->get('nama_posyandu')" class="mt-2" />
                            </div>

                            {{-- Kabupaten --}}
                            <div x-data="{ open: false, search: '' }" @click.away="open = false" class="relative">
                                <x-input-label for="kabupaten" :value="__('Kabupaten/Kota')" />
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
                                            :class="{ 'bg-indigo-100': getKabupatenName(selectedKabupaten) === kab.name }"
                                            x-text="kab.name">
                                        </div>
                                    </template>
                                </div>
                                <x-input-error :messages="$errors->get('kabupaten')" class="mt-2" />
                            </div>

                            {{-- Kecamatan --}}
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
                                        class="absolute inset-y-0 right-0 flex items-center px-3">
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
                                            :class="{ 'bg-indigo-100': getKecamatanName(selectedKecamatan) === kec.name }"
                                            x-text="kec.name">
                                        </div>
                                    </template>
                                </div>
                                <x-input-error :messages="$errors->get('kecamatan')" class="mt-2" />
                            </div>

                            {{-- Desa --}}
                            <div x-data="{ open: false, search: '' }" @click.away="open = false" class="relative">
                                <x-input-label for="desa" :value="__('Desa/Kelurahan')" />
                                <input type="hidden" name="desa" :value="selectedDesa">
                                <div class="relative">
                                    <input type="text" x-model="search" @focus="open = true" @input="open = true"
                                        :placeholder="getDesaName(selectedDesa) || 'Cari Desa...'"
                                        :disabled="desaList.length === 0 && !loadingDesa"
                                        class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                                        autocomplete="off">
                                    <button type="button" @click="open = !open"
                                        class="absolute inset-y-0 right-0 flex items-center px-3">
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
                                            :class="{ 'bg-indigo-100': getDesaName(selectedDesa) === desa.name }"
                                            x-text="desa.name">
                                        </div>
                                    </template>
                                </div>
                                <x-input-error :messages="$errors->get('desa')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 2: KELOLA RW/RT --}}
                    <div class="mb-8 p-6 bg-blue-50 rounded-lg border border-blue-200">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="bi bi-diagram-3 text-blue-500"></i>
                            Kelola RW/RT Posyandu
                        </h3>

                        {{-- Counter Stats --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div class="bg-gradient-to-r from-purple-50 to-purple-100 p-4 rounded-lg border border-purple-200">
                                <div class="text-sm text-purple-600 font-semibold">RW yang Dilayani</div>
                                <div class="text-3xl font-bold text-purple-700">
                                    <span x-text="selectedRwList.length"></span>
                                </div>
                                <div class="text-xs text-purple-500 mt-1">dari 15 RW yang ada</div>
                            </div>

                            <div class="bg-gradient-to-r from-green-50 to-green-100 p-4 rounded-lg border border-green-200">
                                <div class="text-sm text-green-600 font-semibold">RT yang Dilayani</div>
                                <div class="text-3xl font-bold text-green-700">
                                    <span x-text="getTotalSelectedRt()"></span>
                                </div>
                                <div class="text-xs text-green-500 mt-1">dari 53 RT yang ada</div>
                            </div>
                        </div>

                        {{-- Pilih RW --}}
                        <div class="mb-6">
                            <h4 class="text-md font-semibold text-gray-800 mb-3">Pilih RW yang Dilayani</h4>
                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                <div class="grid grid-cols-3 md:grid-cols-5 gap-3">
                                    <template x-for="rw in allRwOptions" :key="rw">
                                        <label class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg border cursor-pointer hover:border-pink-300 transition"
                                            :class="{ 'border-pink-500 bg-pink-50': selectedRwList.includes(rw) }">
                                            <input type="checkbox" :value="rw" x-model="selectedRwList"
                                                class="w-4 h-4 text-pink-600 rounded focus:ring-pink-500">
                                            <span class="text-sm font-medium text-gray-700" x-text="rw"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Mapping RT per RW --}}
                        <div x-show="selectedRwList.length > 0">
                            <h4 class="text-md font-semibold text-gray-800 mb-3">Pilih RT untuk Setiap RW</h4>
                            <div class="space-y-4">
                                <template x-for="rw in selectedRwList" :key="rw">
                                    <div class="border border-gray-200 rounded-lg p-4 bg-white">
                                        <div class="flex justify-between items-center mb-3">
                                            <h5 class="font-semibold text-gray-700 text-lg" x-text="rw"></h5>
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm px-2 py-1 bg-blue-100 text-blue-700 rounded font-medium">
                                                    <span x-text="(rtMapping[rw] || []).length"></span> / 53 RT
                                                </span>
                                                <button type="button" @click="selectAllRt(rw)"
                                                    class="px-3 py-1.5 bg-green-500 text-white rounded-md hover:bg-green-600 text-xs">
                                                    Pilih Semua
                                                </button>
                                                <button type="button" @click="clearAllRt(rw)"
                                                    class="px-3 py-1.5 bg-gray-500 text-white rounded-md hover:bg-gray-600 text-xs">
                                                    Hapus Semua
                                                </button>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-4 md:grid-cols-8 lg:grid-cols-10 gap-2">
                                            <template x-for="rt in allRtOptions" :key="rt">
                                                <label class="flex items-center justify-center p-2 bg-gray-50 rounded border cursor-pointer hover:border-blue-300 transition text-xs"
                                                    :class="{ 'border-blue-500 bg-blue-50': rtMapping[rw] && rtMapping[rw].includes(rt) }">
                                                    <input type="checkbox" :value="rt" @change="toggleRt(rw, rt)"
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

                        {{-- Hidden inputs --}}
                        <template x-for="(rw, index) in selectedRwList" :key="index">
                            <input type="hidden" :name="'rw_list[' + index + ']'" :value="rw">
                        </template>
                        <template x-for="rw in selectedRwList" :key="rw">
                            <template x-for="(rt, rtIndex) in (rtMapping[rw] || [])" :key="rtIndex">
                                <input type="hidden" :name="'rt_mapping[' + rw + '][' + rtIndex + ']'" :value="rt">
                            </template>
                        </template>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center justify-between pt-6 border-t gap-4">
                        <a href="{{ route('admin.posyandu.index') }}"
                            class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-8 py-2.5 bg-pink-600 text-white rounded-md hover:bg-pink-700 shadow-lg">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('posyanduEditForm', () => ({
                    kabupatens: @json($kabupatens['data'] ?? []),
                    kecamatanList: [],
                    desaList: [],
                    selectedKabupaten: '',
                    selectedKecamatan: '',
                    selectedDesa: '',
                    loadingKecamatan: false,
                    loadingDesa: false,

                    allRwOptions: Array.from({length: 15}, (_, i) => `RW${String(i + 1).padStart(2, '0')}`),
                    allRtOptions: Array.from({length: 53}, (_, i) => `RT${String(i + 1).padStart(3, '0')}`),
                    selectedRwList: @json($posyandu->rw_list ?? []),
                    rtMapping: @json($posyandu->rt_mapping ?? []),

                    async init() {
                        const initialKab = '{{ old('kabupaten', $posyandu->kabupaten) }}';
                        const initialKec = '{{ old('kecamatan', $posyandu->kecamatan) }}';
                        const initialDesa = '{{ old('desa', $posyandu->desa) }}';

                        const kab = this.kabupatens.find(k => k.name === initialKab);
                        if (kab) {
                            this.selectedKabupaten = `${kab.code}_${kab.name}`;
                            await this.fetchKecamatan();

                            const kec = this.kecamatanList.find(k => k.name === initialKec);
                            if (kec) {
                                this.selectedKecamatan = `${kec.code}_${kec.name}`;
                                await this.fetchDesa();

                                const desa = this.desaList.find(d => d.name === initialDesa);
                                if (desa) {
                                    this.selectedDesa = `${desa.code}_${desa.name}`;
                                }
                            }
                        }

                        this.$watch('selectedRwList', (newVal, oldVal) => {
                            newVal.forEach(rw => {
                                if (!this.rtMapping[rw]) this.rtMapping[rw] = [];
                            });
                            if (oldVal) {
                                oldVal.forEach(rw => {
                                    if (!newVal.includes(rw)) delete this.rtMapping[rw];
                                });
                            }
                        });
                    },

                    getKabupatenName(value) {
                        return value ? value.split('_').slice(1).join('_') : '';
                    },

                    getKecamatanName(value) {
                        return value ? value.split('_').slice(1).join('_') : '';
                    },

                    getDesaName(value) {
                        return value ? value.split('_').slice(1).join('_') : '';
                    },

                    async fetchKecamatan() {
                        this.kecamatanList = [];
                        this.desaList = [];
                        this.loadingKecamatan = true;

                        if (this.selectedKabupaten) {
                            const kabId = this.selectedKabupaten.split('_')[0];
                            const response = await fetch(`{{ route('api.kecamatan') }}?kab_id=${kabId}`);
                            const data = await response.json();
                            this.kecamatanList = data.data ?? [];
                        }
                        this.loadingKecamatan = false;
                    },

                    async fetchDesa() {
                        this.desaList = [];
                        this.loadingDesa = true;

                        if (this.selectedKecamatan) {
                            const kecId = this.selectedKecamatan.split('_')[0];
                            const response = await fetch(`{{ route('api.desa') }}?kec_id=${kecId}`);
                            const data = await response.json();
                            this.desaList = data.data ?? [];
                        }
                        this.loadingDesa = false;
                    },

                    selectAllRt(rw) {
                        this.rtMapping[rw] = [...this.allRtOptions];
                        this.rtMapping = {...this.rtMapping};
                    },

                    clearAllRt(rw) {
                        this.rtMapping[rw] = [];
                        this.rtMapping = {...this.rtMapping};
                    },

                    toggleRt(rw, rt) {
                        if (!this.rtMapping[rw]) this.rtMapping[rw] = [];
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
                            if (Array.isArray(rtList)) total += rtList.length;
                        });
                        return total;
                    }
                }));
            });
        </script>
    @endpush
@endsection
