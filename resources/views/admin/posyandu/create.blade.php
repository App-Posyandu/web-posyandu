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

                        <div>
                            <div class="flex justify-between items-center">
                                <x-input-label for="ketua_kader_id" :value="__('Pilih Ketua Kader (Opsional)')" />
                                <a href="{{ route('admin.users.create', ['source' => 'posyandu_create']) }}"
                                    class="text-sm text-pink-600 hover:underline">
                                    + Buat User Baru
                                </a>
                            </div>
                            <select id="ketua_kader_id" name="ketua_kader_id"
                                class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">-- Tidak ada/Pilih Nanti --</option>
                                @foreach ($availableKetuas as $ketua)
                                    <option value="{{ $ketua->id }}" @selected(old('ketua_kader_id') == $ketua->id)>
                                        {{ $ketua->name }} ({{ $ketua->email }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Hanya menampilkan user dengan role "Ketua Kader" yang
                                belum terhubung ke Posyandu lain. Jika user tidak ada, klik "Buat User Baru".</p>
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
                                    :disabled="kecamatanList.length === 0"
                                    class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                                    autocomplete="off">
                                <button type="button" @click="open = !open"
                                    class="absolute inset-y-0 right-0 flex items-center px-3"
                                    :disabled="kecamatanList.length === 0">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
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
                        </div>

                        {{-- Combobox Desa --}}
                        <div x-data="{ open: false, search: '' }" @click.away="open = false" class="relative">
                            <x-input-label for="desa" :value="__('Desa/Kelurahan')" />
                            <input type="hidden" name="desa" :value="selectedDesa">
                            <div class="relative">
                                <input type="text" x-model="search" @focus="open = true" @input="open = true"
                                    :placeholder="getDesaName(selectedDesa) || 'Cari Desa/Kelurahan...'"
                                    :disabled="desaList.length === 0"
                                    class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                                    autocomplete="off">
                                <button type="button" @click="open = !open"
                                    class="absolute inset-y-0 right-0 flex items-center px-3"
                                    :disabled="desaList.length === 0">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
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
                                    Tidak ada hasil
                                </div>
                            </div>
                            <div x-show="loadingDesa"
                                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                                <div class="px-4 py-2 text-gray-500 text-sm">Memuat data desa...</div>
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
                    kabupatens: @json($kabupatens['data']),

                    // Helper function untuk mendapatkan nama dari value (format: code_name)
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
                            const kabId = this.selectedKabupaten.split('_')[0];
                            const response = await fetch(
                                `{{ route('api.kecamatan') }}?kab_id=${kabId}`);
                            const data = await response.json();
                            console.log(data.data);
                            this.kecamatanList = data.data ?? [];
                        }
                        this.loadingKecamatan = false;
                    },

                    async fetchDesa() {
                        this.desaList = [];
                        this.selectedDesa = '';
                        this.loadingDesa = true;

                        if (this.selectedKecamatan) {
                            const kecId = this.selectedKecamatan.split('_')[0];
                            const response = await fetch(`{{ route('api.desa') }}?kec_id=${kecId}`);
                            const data = await response.json();
                            this.desaList = data.data ?? [];
                        }
                        this.loadingDesa = false;
                    }
                }));
            });
        </script>
    @endpush

@endsection
