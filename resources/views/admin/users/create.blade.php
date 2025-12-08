@extends('dashboard.layouts.dashboard')
@section('title', 'Add Users')
@section('content')
    <div class="w-full mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-8 text-gray-900">
                <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data">
                    @csrf
                    @if (request()->has('source'))
                        <input type="hidden" name="source" value="{{ request('source') }}">
                    @endif
                    <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">Formulir Pengguna Baru</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                            <p class="mt-1 text-xs text-gray-500">Biarkan kosong jika user ini belum memiliki Posyandu.</p>
                            <x-input-error :messages="$errors->get('posyandu_id')" class="mt-2" />
                        </div>

                        {{-- Role --}}
                        <div
                            class="{{ in_array(auth()->user()->role, ['kabid', 'ketua-kader']) ? 'hidden' : '' }} md:col-span-2">
                            <x-input-label for="role" :value="__('Role')" />
                            <select id="role" name="role"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="" disabled selected>Pilih Role</option>
                                @php $currentUserRole = auth()->user()->role; @endphp

                                @if ($currentUserRole === 'admin')
                                    <option value="kabid" @selected(old('role') == 'kabid')>Kabid</option>
                                @elseif ($currentUserRole === 'kabid')
                                    <option value="ketua-kader" @selected(true)>Ketua Kader</option>
                                @elseif ($currentUserRole === 'ketua-kader')
                                    <option value="kader" @selected(true)>Kader</option>
                                @endif
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        {{-- JENIS WILAYAH --}}
                        <div class="md:col-span-2" id="jenis-wilayah-field" style="display: none;">
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

                        {{-- ✅ HIDDEN INPUT DI LUAR DIV YANG DISPLAY:NONE --}}
                        <input type="hidden" id="kabupaten-hidden" name="kabupaten" value="">

                        {{-- KABUPATEN COMBOBOX (HANYA UI) --}}
                        <div id="kabupaten-field" style="display: none;" class="md:col-span-2">
                            <div x-data="kabupatenCombobox()" @click.away="open = false" x-init="$watch('selectedKabupaten', value => {
                                document.getElementById('kabupaten-hidden').value = value;
                                console.log('✅ Kabupaten changed to:', value);
                            })"
                                class="relative">

                                <x-input-label for="kabupaten" :value="__('Pilih Kabupaten')" />

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
                                        <div @click="selectedKabupaten = `${kab.code}_${kab.name}`; search = ''; open = false"
                                            class="px-4 py-2 cursor-pointer hover:bg-indigo-50"
                                            :class="{ 'bg-indigo-100': selectedKabupaten === `${kab.code}_${kab.name}` }"
                                            x-text="getDisplayName(kab.name)">
                                        </div>
                                    </template>
                                    <div x-show="kabupatens.filter(k => k.name.toLowerCase().includes(search.toLowerCase())).length === 0"
                                        class="px-4 py-2 text-gray-500 text-sm">
                                        Tidak ada hasil
                                    </div>
                                </div>

                                <p class="mt-1 text-xs text-gray-500">Pilih kabupaten yang akan menjadi wilayah kerja
                                    Kabid.</p>
                                <x-input-error :messages="$errors->get('kabupaten')" class="mt-2" />
                            </div>
                        </div>

                        {{-- KOTA COMBOBOX (HANYA UI) --}}
                        <div id="kota-field" style="display: none;" class="md:col-span-2">
                            <div x-data="kotaCombobox()" @click.away="open = false" x-init="$watch('selectedKota', value => {
                                document.getElementById('kabupaten-hidden').value = value;
                                console.log('✅ Kota changed to:', value);
                            })"
                                class="relative">

                                <x-input-label for="kota" :value="__('Pilih Kota')" />

                                <div class="relative">
                                    <input type="text" x-model="search" @focus="open = true" @input="open = true"
                                        :placeholder="getKotaName(selectedKota) || 'Cari Kota...'"
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
                                        x-for="kota in kotas.filter(k => k.name.toLowerCase().includes(search.toLowerCase()))"
                                        :key="kota.code">
                                        <div @click="selectedKota = `${kota.code}_${kota.name}`; search = ''; open = false"
                                            class="px-4 py-2 cursor-pointer hover:bg-indigo-50"
                                            :class="{ 'bg-indigo-100': selectedKota === `${kota.code}_${kota.name}` }"
                                            x-text="getDisplayName(kota.name)">
                                        </div>
                                    </template>
                                    <div x-show="kotas.filter(k => k.name.toLowerCase().includes(search.toLowerCase())).length === 0"
                                        class="px-4 py-2 text-gray-500 text-sm">
                                        Tidak ada hasil
                                    </div>
                                </div>

                                <p class="mt-1 text-xs text-gray-500">Pilih kota yang akan menjadi wilayah kerja Kabid.</p>
                                <x-input-error :messages="$errors->get('kabupaten')" class="mt-2" />
                            </div>
                        </div>

                        {{-- ✅ TAMBAHKAN DROPDOWN BIDANG (HANYA MUNCUL JIKA ROLE = KADER) --}}
                        <div id="bidang-field" style="display: none;">
                            <x-input-label for="bidang_id" :value="__('Bidang Tugas')" />
                            <select id="bidang_id" name="bidang_id"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="" disabled selected>Pilih Bidang</option>
                                @foreach ($bidangs as $bidang)
                                    <option value="{{ $bidang->id }}" @selected(old('bidang_id') == $bidang->id)>
                                        {{ $bidang->nama_bidang }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Kader hanya bisa mengelola 1 bidang.</p>
                            <x-input-error :messages="$errors->get('bidang_id')" class="mt-2" />
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
                            <!-- Tombol Import di kiri -->
                            <button type="button" id="importBtn"
                                class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm font-semibold hover:bg-emerald-700">
                                Import Nama Pengguna
                            </button>

                            <!-- Tombol Batal & Simpan di kanan -->
                            <div class="flex gap-4">
                                <a href="{{ $cancelUrl }}"
                                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm font-semibold hover:bg-gray-300">
                                    Batal
                                </a>
                                <x-primary-button>
                                    {{ __('Simpan Pengguna') }}
                                </x-primary-button>
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
                console.log('Alpine initialized'); // ← Debug log

                // Alpine.data untuk Kabupaten
                Alpine.data('kabupatenCombobox', () => ({
                    open: false,
                    search: '',
                    selectedKabupaten: '{{ old('kabupaten') }}',
                    kabupatens: @json($kabupatenList ?? []),

                    init() {
                        console.log('Kabupaten combobox init', this.selectedKabupaten); // ← Debug log
                    },

                    getKabupatenName(value) {
                        if (!value) return '';
                        let fullName = value.split('_').slice(1).join('_');
                        return fullName.replace('Kabupaten ', '');
                    },

                    getDisplayName(name) {
                        return name.replace('Kabupaten ', '');
                    }
                }));

                // Alpine.data untuk Kota
                Alpine.data('kotaCombobox', () => ({
                    open: false,
                    search: '',
                    selectedKota: '{{ old('kabupaten') }}',
                    kotas: @json($kotaList ?? []),

                    init() {
                        console.log('Kota combobox init', this.selectedKota); // ← Debug log
                    },

                    getKotaName(value) {
                        if (!value) return '';
                        let fullName = value.split('_').slice(1).join('_');
                        return fullName.replace('Kota ', '');
                    },

                    getDisplayName(name) {
                        return name.replace('Kota ', '');
                    }
                }));
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

                function toggleFields() {
                    bidangField.style.display = 'none';
                    bidangSelect.required = false;
                    bidangSelect.value = '';

                    jenisWilayahField.style.display = 'none';
                    jenisWilayahSelect.required = false;

                    kabupatenField.style.display = 'none';
                    kotaField.style.display = 'none';

                    if (roleSelect.value === 'kader') {
                        bidangField.style.display = 'block';
                        bidangSelect.required = true;
                    }

                    if (roleSelect.value === 'kabid') {
                        jenisWilayahField.style.display = 'block';
                        jenisWilayahSelect.required = true;
                    }
                }

                function toggleWilayahField() {
                    kabupatenField.style.display = 'none';
                    kotaField.style.display = 'none';

                    if (jenisWilayahSelect.value === 'kabupaten') {
                        kabupatenField.style.display = 'block';
                    } else if (jenisWilayahSelect.value === 'kota') {
                        kotaField.style.display = 'block';
                    }
                }

                toggleFields();
                toggleWilayahField();

                roleSelect.addEventListener('change', toggleFields);
                jenisWilayahSelect.addEventListener('change', toggleWilayahField);
            });
            // Replace bagian script import di create.blade.php (user) dengan ini:

            document.getElementById('importBtn').addEventListener('click', function() {
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
                    title: '<h2 class="text-2xl font-bold text-gray-800 mb-2">Import User Ketua Kader</h2>',
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
                const url = "{{ route('admin.users.export.template') }}";
                window.location.href = url;

                // Show success message
                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Template Sedang Diunduh',
                        html: `
                <p class="text-gray-700">Template User Ketua Kader sedang diunduh.</p>
                <br>
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded text-left">
                    <p class="text-sm text-blue-700">
                        <strong>📋 Informasi Template:</strong>
                        <br>• Kolom DESA, KECAMATAN, KABUPATEN sudah terisi otomatis
                        <br>• Jumlah baris = Jumlah Posyandu terdaftar
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
