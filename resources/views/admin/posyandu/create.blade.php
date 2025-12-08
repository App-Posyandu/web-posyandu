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
                        {{-- <div x-data="ketuaCombobox()" @click.away="open = false" class="relative">
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
                        </div> --}}

                        {{-- Combobox Kabupaten --}}
                        @if (auth()->user()->role === 'kabid' && auth()->user()->kabupaten)
                            <div>
                                <x-input-label for="kabupaten" :value="__('Kabupaten')" />
                                <input type="hidden" name="kabupaten" value="{{ auth()->user()->kabupaten }}">
                                <input type="text" value="{{ auth()->user()->kabupaten }}" disabled
                                    class="block mt-1 w-full border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed">
                                <p class="mt-1 text-xs text-gray-500">Kabupaten sudah ditetapkan oleh Admin.</p>
                            </div>

                            <script>
                                document.addEventListener('alpine:init', () => {
                                    Alpine.store('posyanduForm', {
                                        selectedKabupaten: '{{ auth()->user()->kabupaten }}'
                                    });
                                });
                            </script>
                        @else
                            {{-- Combobox Kabupaten --}}
                            @if (auth()->user()->role === 'kabid' && auth()->user()->kabupaten)
                                {{-- KABID: Kabupaten/Kota sudah fixed --}}
                                <div>
                                    <x-input-label for="kabupaten" :value="__('Wilayah Kerja')" />
                                    <input type="hidden" name="kabupaten" value="{{ auth()->user()->kabupaten }}">
                                    <input type="text" value="{{ auth()->user()->kabupaten }}" disabled
                                        class="block mt-1 w-full border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed">
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ ucfirst(auth()->user()->jenis_wilayah) }} sudah ditetapkan oleh Admin.
                                    </p>
                                </div>
                            @else
                                {{-- ADMIN atau role lain: Pilih kabupaten --}}
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
                            @endif
                        @endif

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
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('dependentDropdowns', () => ({
                    selectedKabupaten: @if (auth()->user()->role === 'kabid')
                        '{{ auth()->user()->kabupaten }}'
                    @else
                        ''
                    @endif ,
                    selectedKecamatan: '',
                    selectedDesa: '',
                    kecamatanList: [],
                    desaList: [],
                    loadingKecamatan: false,
                    loadingDesa: false,
                    kabupatens: @json($kabupatens['data'] ?? []),

                    init() {
                        @if (auth()->user()->role === 'kabid' && auth()->user()->kabupaten)
                            const kabupatenData = @json($kabupatens['data'] ?? []);
                            const foundKab = kabupatenData.find(k => k.name ===
                                '{{ auth()->user()->kabupaten }}');
                            if (foundKab) {
                                this.selectedKabupaten = `${foundKab.code}_${foundKab.name}`;
                                this.fetchKecamatan();
                            }
                        @endif
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
                                const response = await fetch(
                                    `{{ route('api.desa') }}?kec_id=${kecId}`);

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
        </script>
    @endpush

@endsection