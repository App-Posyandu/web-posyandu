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

                                @elseif ($currentUserRole === 'operator-desa')
                                    <option value="ketua-kader">Ketua Kader</option>
                                    <option value="kader">Kader</option>
                                @elseif ($currentUserRole === 'ketua-kader')
                                    <option value="kader" @selected(true)>Kader</option>
                                @elseif ($currentUserRole === 'kader')
                                    <option value="masyarakat" @selected(true)>Masyarakat</option>
                                @endif
                            </select>

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


                        <div id="kabupaten-field" style="display: none;" class="md:col-span-2">
                            <div x-data="kabupatenCombobox()" @click.away="open = false" x-init="$watch('selectedKabupaten', value => {
                                document.getElementById('kabupaten-hidden').value = value;
                            })"
                                class="relative">
                                <x-input-label for="kabupaten" :value="__('Pilih Kabupaten')" />
                                <input type="hidden" id="kabupaten-hidden" name="kabupaten" :value="selectedKabupaten">
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

                        <div id="kota-field" style="display: none;" class="md:col-span-2">
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

                            <div x-data="kecamatanCombobox()" @region-selected.window="fetchKecamatan($event.detail.code)"
                                @click.away="open = false" class="relative">

                                <x-input-label for="kecamatan" :value="__('Pilih Kecamatan')" />
                                <input type="hidden" name="kecamatan" id="kecamatan-hidden" :value="selectedKecamatan">

                                <div class="relative">
                                    <input type="text" x-model="search" @focus="open = true" @input="open = true"
                                        :placeholder="getKecamatanName(selectedKecamatan) || 'Cari Kecamatan...'"
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
                                            class="px-4 py-2 cursor-pointer hover:bg-indigo-50" x-text="kec.name">
                                        </div>
                                    </template>

                                    <div x-show="kecamatanList.length === 0" class="px-4 py-2 text-gray-500 text-sm">
                                        Tidak ada data kecamatan / Silakan pilih Kabupaten dulu
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="desa-field" style="display: none;" class="md:col-span-2">

                            <div x-data="desaCombobox()" @kecamatan-selected.window="fetchDesa($event.detail.code)"
                                @click.away="open = false" class="relative">

                                <x-input-label for="desa" :value="__('Pilih Desa')" />
                                <input type="hidden" name="desa" id="desa-hidden" :value="selectedDesa">

                                <div class="relative">
                                    <input type="text" x-model="search" @focus="open = true" @input="open = true"
                                        :placeholder="getDesaName(selectedDesa) || 'Cari Desa...'"
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
                                        x-for="desa in desaList.filter(d => (d.name || '').toLowerCase().includes(search.toLowerCase()))"
                                        :key="desa.id || desa.code">
                                        <div @click="selectDesa(desa)" class="px-4 py-2 cursor-pointer hover:bg-indigo-50"
                                            x-text="desa.name"></div>
                                    </template>

                                    <div x-show="desaList.length === 0" class="px-4 py-2 text-gray-500 text-sm">
                                        Tidak ada data desa / Silakan pilih Kabupaten dulu
                                    </div>
                                </div>
                            </div>
                        </div>

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

                        <div id="rw-rt-fields" style="display: none;" class="md:col-span-2">
                            <div
                                class="p-6 bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-300 rounded-xl">
                                {{-- Info Banner --}}
                                <div
                                    class="flex items-start gap-3 mb-6 p-4 bg-white rounded-lg border-l-4 border-green-500 shadow-sm">
                                    <div class="flex-shrink-0">
                                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="text-sm">
                                        <p class="font-bold text-green-900 mb-1">Data Wilayah Otomatis</p>
                                        <p class="text-green-800">
                                            Data <strong>Kabupaten, Kecamatan, Desa, dan Posyandu</strong> sudah otomatis
                                            diambil dari akun Kader.
                                            Silakan pilih <strong>RW dan RT</strong> tempat tinggal masyarakat.
                                        </p>
                                    </div>
                                </div>

                                {{-- Auto-filled Data Display --}}
                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 p-4 bg-white rounded-lg border border-green-200">
                                    <div>
                                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                            Kabupaten</p>
                                        <p class="text-sm font-bold text-gray-800">
                                            {{ auth()->user()->kabupaten ?? 'Tidak Tersedia' }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                            Kecamatan</p>
                                        <p class="text-sm font-bold text-gray-800">
                                            {{ auth()->user()->kecamatan ?? 'Tidak Tersedia' }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Desa
                                        </p>
                                        <p class="text-sm font-bold text-gray-800">
                                            {{ auth()->user()->desa ?? 'Tidak Tersedia' }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                            Posyandu</p>
                                        <p class="text-sm font-bold text-gray-800">
                                            {{ auth()->user()->posyandu->nama_posyandu ?? 'Tidak Tersedia' }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Hidden Inputs for Auto-fill Data --}}
                                <input type="hidden" name="kabupaten" value="{{ auth()->user()->kabupaten }}">
                                <input type="hidden" name="kabupaten_id" value="{{ auth()->user()->kabupaten_id }}">
                                <input type="hidden" name="kecamatan" value="{{ auth()->user()->kecamatan }}">
                                <input type="hidden" name="kecamatan_id" value="{{ auth()->user()->kecamatan_id }}">
                                <input type="hidden" name="desa" value="{{ auth()->user()->desa }}">
                                <input type="hidden" name="posyandu_id" value="{{ auth()->user()->posyandu_id }}">

                                {{-- ✅ RW & RT Selection --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {{-- RW Dropdown --}}
                                    <div>
                                        <x-input-label for="rw" class="font-semibold">
                                            <span class="text-gray-700">RW (Rukun Warga)</span>
                                            <span class="text-red-500">*</span>
                                        </x-input-label>

                                        <select id="rw" name="rw" required
                                            onchange="handleRwChange(this.value)"
                                            class="mt-1 block w-full border-2 border-gray-300 rounded-lg px-4 py-2.5 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition">
                                            <option value="">Pilih RW</option>
                                            @if (auth()->user()->posyandu && auth()->user()->posyandu->rw_list)
                                                @foreach (auth()->user()->posyandu->rw_list as $rw)
                                                    <option value="{{ $rw }}"
                                                        {{ old('rw') == $rw ? 'selected' : '' }}>
                                                        {{ $rw }}
                                                    </option>
                                                @endforeach
                                            @else
                                                <option value="" disabled>Tidak ada RW tersedia</option>
                                            @endif
                                        </select>

                                        <p class="mt-2 text-xs text-gray-600">
                                            RW yang tersedia di Posyandu
                                            {{ auth()->user()->posyandu->nama_posyandu ?? '' }}
                                        </p>

                                        <x-input-error :messages="$errors->get('rw')" class="mt-2" />
                                    </div>

                                    {{-- RT Dropdown --}}
                                    <div>
                                        <x-input-label for="rt" class="font-semibold">
                                            <span class="text-gray-700">RT (Rukun Tetangga)</span>
                                            <span class="text-red-500">*</span>
                                        </x-input-label>

                                        <select id="rt" name="rt" required disabled
                                            class="mt-1 block w-full border-2 rounded-lg px-4 py-2.5 transition border-gray-200 bg-gray-50 cursor-not-allowed text-gray-400">
                                            <option value="">Pilih RW terlebih dahulu</option>
                                        </select>

                                        <p id="rt-helper-text" class="mt-2 text-xs text-amber-600">
                                            ⚠️ Silakan pilih RW terlebih dahulu
                                        </p>

                                        <x-input-error :messages="$errors->get('rt')" class="mt-2" />
                                    </div>
                                </div>

                                {{-- Debug Info (Remove in production) --}}
                                {{-- <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded text-xs" id="debug-info"
                                    style="display: none;">
                                    <p class="font-bold text-blue-900 mb-1">Debug Info:</p>
                                    <div id="debug-content" class="text-blue-800 font-mono"></div>
                                </div> --}}
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
            document.addEventListener('alpine:init', () => {

                const getRegionCode = (region) => region.id || region.code;

                Alpine.data('kabupatenCombobox', () => ({
                    open: false,
                    search: '',
                    selectedKabupaten: '{{ old('kabupaten') }}',
                    kabupatens: @json($kabupatenList ?? []),

                    init() {
                        @if (auth()->user()->kabupaten)
                            // Cari data kabupaten berdasarkan nama untuk mendapatkan kodenya
                            const initialKab = this.kabupatens.find(k => k.name ===
                                '{{ auth()->user()->kabupaten }}');
                            if (initialKab) {
                                const code = initialKab.id || initialKab.code;
                                this.selectedKabupaten = `${code}_${initialKab.name}`;

                                // Beritahu komponen lain bahwa kabupaten sudah terpilih secara otomatis
                                this.$nextTick(() => {
                                    this.$dispatch('region-selected', {
                                        code: code
                                    });
                                });
                            }
                        @endif
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
                        const code = getRegionCode(kab);

                        this.selectedKabupaten = `${code}_${kab.name}`;
                        this.search = '';
                        this.open = false;

                        document.getElementById('kabupaten-hidden').value = this.selectedKabupaten;

                        console.log('Dispatching Region Code:', code);
                        this.$dispatch('region-selected', {
                            code: code
                        });
                    }
                }));

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

                Alpine.data('kecamatanCombobox', () => ({
                    open: false,
                    search: '',
                    loading: false,
                    kecamatanList: [],
                    selectedKecamatan: '{{ old('kecamatan') }}',

                    init() {
                        this.$el.addEventListener('region-selected', (e) => {
                            this.fetchKecamatan(e.detail.code);
                        }, {
                            window: true
                        });
                        if (this.kecamatanList.length > 0) {
                            console.log('Sample Data Kecamatan:', this.kecamatanList[0]);
                        }

                        if (this.selectedKecamatan) {
                            let code = this.selectedKecamatan.split('_')[0];
                            this.$dispatch('region-selected', {
                                code: code
                            });
                        }
                    },

                    getKecamatanName(value) {
                        if (!value) return '';
                        let fullName = value.split('_').slice(1).join('_');
                        return fullName.replace('Kecamatan ', '');
                    },

                    getDisplayName(name) {
                        return name ? name.replace('Kecamatan ', '') : '';
                    },

                    async fetchKecamatan(parentId) {
                        if (!parentId) return;
                        this.loading = true;

                        console.log('Fetching Kecamatan for Parent:', parentId);
                        this.kecamatanList = [];
                        this.selectedKecamatan = '';
                        document.getElementById('kecamatan-hidden').value = '';

                        try {
                            const response = await fetch(
                                `{{ route('api.kecamatan') }}?kab_id=${parentId}`);
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
                            this.loading = false;
                        }
                    },

                    selectKecamatan(kec) {
                        const code = getRegionCode(kec);
                        const val = `${code}_${kec.name}`;
                        this.selectedKecamatan = val;
                        document.getElementById('kecamatan-hidden').value = val;

                        // this.selectedKecamatan = kec.name;
                        this.search = '';
                        this.open = false;
                        this.$dispatch('kecamatan-selected', {
                            code: code
                        });
                    }
                }));

                Alpine.data('desaCombobox', () => ({
                    open: false,
                    search: '',
                    loading: false,
                    desaList: [],
                    selectedDesa: '{{ old('desa') }}',

                    init() {
                        this.$el.addEventListener('kecamatan-selected', (e) => {
                            this.fetchDesa(e.detail.code);
                        }, {
                            window: true
                        });
                        if (this.desaList.length > 0) {
                            console.log('Sample Data Desa:', this.desaList[0]);
                        }

                        // if (this.selectedDesa) {
                        //     let code = this.selectedDesa.split('_')[0];
                        //     this.$dispatch('kecamatan-selected', {
                        //         code: code
                        //     });
                        // }
                    },

                    getDesaName(value) {
                        if (!value) return '';
                        let fullName = value.split('_').slice(1).join('_');
                        return fullName.replace('Desa ', '');
                    },

                    getDisplayName(name) {
                        return name ? name.replace('Desa ', '') : '';
                    },


                    async fetchDesa(parentId) {
                        if (!parentId) return;

                        console.log('Fetching Desa for Parent:', parentId);
                        this.loading = true;
                        this.desaList = [];
                        this.selectedDesa = '';
                        document.getElementById('desa-hidden').value = '';

                        try {
                            const response = await fetch(`{{ route('api.desa') }}?kec_id=${parentId}`);
                            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                            const data = await response.json();
                            this.desaList = data.data ?? [];

                            if (this.desaList.length === 0) {
                                alert('Tidak ada data desa untuk kecamatan ini.');
                            }

                        } catch (error) {
                            console.error('Gagal mengambil data desa:', error);
                            this.desaList = [];
                        } finally {
                            this.loading = false;
                        }
                    },

                    selectDesa(desa) {
                        const code = getRegionCode(desa);
                        const val = `${code}_${desa.name}`;
                        this.selectedDesa = val;
                        document.getElementById('desa-hidden').value = val;

                        this.search = '';
                        this.open = false;
                    }
                }));
            });

            // document.addEventListener('DOMContentLoaded', function() {
            //     const roleSelect = document.getElementById('role');
            //     const rwRtFields = document.getElementById('rw-rt-fields');
            //     const posyanduSelect = document.getElementById('posyandu_id');
            //     const rwSelect = document.getElementById('rw');
            //     const rtSelect = document.getElementById('rt');

            //     function toggleRwRtFields() {
            //         if (roleSelect.value === 'masyarakat') {
            //             rwRtFields.style.display = 'block';
            //             rwSelect.required = true;
            //         } else {
            //             rwRtFields.style.display = 'none';
            //             rwSelect.required = false;
            //             rtSelect.value = '';
            //             rwSelect.value = '';
            //         }
            //     }
            //     async function fetchRwRtOptions() {
            //         if (roleSelect.value !== 'masyarakat' || !posyanduSelect.value) {
            //             return;
            //         }

            //         try {
            //             const response = await fetch(`/api/posyandu/${posyanduSelect.value}/rw-rt`);
            //             const data = await response.json();

            //             rwSelect.innerHTML = '<option value="" disabled selected>Pilih RW</option>';
            //             if (data.rw_list && data.rw_list.length > 0) {
            //                 data.rw_list.forEach(rw => {
            //                     const option = document.createElement('option');
            //                     option.value = rw;
            //                     option.textContent = rw;
            //                     rwSelect.appendChild(option);
            //                 });
            //             } else {
            //                 for (let i = 1; i <= 15; i++) {
            //                     const rw = `RW${String(i).padStart(2, '0')}`;
            //                     const option = document.createElement('option');
            //                     option.value = rw;
            //                     option.textContent = rw;
            //                     rwSelect.appendChild(option);
            //                 }
            //             }
            //             window.rtMapping = data.rt_mapping || {};

            //         } catch (error) {
            //             console.error('Error fetching RW/RT:', error);
            //         }
            //     }

            //     rwSelect.addEventListener('change', function() {
            //         const selectedRw = this.value;
            //         rtSelect.innerHTML = '<option value="">-- Tidak ada/Tidak tahu --</option>';

            //         if (window.rtMapping && window.rtMapping[selectedRw]) {
            //             window.rtMapping[selectedRw].forEach(rt => {
            //                 const option = document.createElement('option');
            //                 option.value = rt;
            //                 option.textContent = rt;
            //                 rtSelect.appendChild(option);
            //             });
            //         } else {
            //             for (let i = 1; i <= 53; i++) {
            //                 const rt = `RT${String(i).padStart(3, '0')}`;
            //                 const option = document.createElement('option');
            //                 option.value = rt;
            //                 option.textContent = rt;
            //                 rtSelect.appendChild(option);
            //             }
            //         }
            //     });

            //     roleSelect.addEventListener('change', toggleRwRtFields);
            //     posyanduSelect.addEventListener('change', fetchRwRtOptions);

            //     toggleRwRtFields();
            // });

            const POSYANDU_RT_MAPPING = @json(auth()->user()->posyandu->rt_mapping ?? []);
            const POSYANDU_RW_LIST = @json(auth()->user()->posyandu->rw_list ?? []);

            console.log('=== KADER CREATE MASYARAKAT DEBUG ===');
            console.log('Posyandu RW List:', POSYANDU_RW_LIST);
            console.log('Posyandu RT Mapping:', POSYANDU_RT_MAPPING);
            console.log('=====================================');

            // ✅ HANDLE RW CHANGE
            function handleRwChange(selectedRw) {
                console.log('[RW Change] Selected RW:', selectedRw);

                const rtSelect = document.getElementById('rt');
                const rtHelperText = document.getElementById('rt-helper-text');
                const debugInfo = document.getElementById('debug-info');
                const debugContent = document.getElementById('debug-content');

                if (!selectedRw || selectedRw === '') {
                    console.log('[RW Change] No RW selected, disabling RT');

                    // Reset RT dropdown
                    rtSelect.disabled = true;
                    rtSelect.className =
                        'mt-1 block w-full border-2 rounded-lg px-4 py-2.5 transition border-gray-200 bg-gray-50 cursor-not-allowed text-gray-400';
                    rtSelect.innerHTML = '<option value="">Pilih RW terlebih dahulu</option>';

                    // Update helper text
                    rtHelperText.className = 'mt-2 text-xs text-amber-600';
                    rtHelperText.innerHTML = '⚠️ Silakan pilih RW terlebih dahulu';

                    return;
                }

                // Get RT list for selected RW
                const rtList = POSYANDU_RT_MAPPING[selectedRw];

                console.log('[RW Change] RT List for ' + selectedRw + ':', rtList);

                // Show debug info
                debugInfo.style.display = 'block';
                debugContent.innerHTML = `
        Selected RW: ${selectedRw}<br>
        RT Mapping exists: ${POSYANDU_RT_MAPPING.hasOwnProperty(selectedRw) ? 'Yes' : 'No'}<br>
        RT List: ${rtList ? JSON.stringify(rtList) : 'null'}<br>
        RT Count: ${rtList ? rtList.length : 0}
    `;

                if (!rtList || rtList.length === 0) {
                    console.warn('[RW Change] No RT found for RW:', selectedRw);

                    // Enable but show no data
                    rtSelect.disabled = false;
                    rtSelect.className =
                        'mt-1 block w-full border-2 rounded-lg px-4 py-2.5 transition border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200';
                    rtSelect.innerHTML = '<option value="">Tidak ada RT untuk RW ini</option>';

                    rtHelperText.className = 'mt-2 text-xs text-red-600';
                    rtHelperText.innerHTML = '❌ Tidak ada RT tersedia untuk RW ' + selectedRw;

                    alert('Peringatan: Tidak ada RT yang tersedia untuk RW ' + selectedRw +
                        '. Silakan hubungi administrator untuk mengatur RT di posyandu ini.');

                    return;
                }

                // ✅ Populate RT dropdown
                console.log('[RW Change] Populating RT dropdown with', rtList.length, 'items');

                rtSelect.disabled = false;
                rtSelect.className =
                    'mt-1 block w-full border-2 rounded-lg px-4 py-2.5 transition border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200';

                // Build options
                let options = '<option value="">Pilih RT</option>';
                rtList.forEach(rt => {
                    const selected = '{{ old('rt') }}' === rt ? 'selected' : '';
                    options += `<option value="${rt}" ${selected}>${rt}</option>`;
                });

                rtSelect.innerHTML = options;

                // Update helper text
                rtHelperText.className = 'mt-2 text-xs text-green-600';
                rtHelperText.innerHTML = `✓ ${rtList.length} RT tersedia untuk RW ${selectedRw}`;

                console.log('[RW Change] RT dropdown populated successfully');
            }

            // ✅ INITIALIZE ON PAGE LOAD
            document.addEventListener('DOMContentLoaded', function() {
                console.log('[DOMContentLoaded] Initializing RW/RT handler');

                const rwSelect = document.getElementById('rw');
                const rtSelect = document.getElementById('rt');

                if (!rwSelect || !rtSelect) {
                    console.error('[DOMContentLoaded] RW or RT select not found!');
                    return;
                }

                // ✅ Check if old RW value exists (from validation error)
                const oldRw = '{{ old('rw') }}';
                if (oldRw) {
                    console.log('[DOMContentLoaded] Old RW value found:', oldRw);
                    rwSelect.value = oldRw;
                    handleRwChange(oldRw);
                }

                console.log('[DOMContentLoaded] RW/RT handler initialized successfully');
            });

            // ✅ SHOW RW/RT FIELDS FOR MASYARAKAT
            document.addEventListener('DOMContentLoaded', function() {
                const roleSelect = document.getElementById('role');
                const rwRtFields = document.getElementById('rw-rt-fields');

                if (!roleSelect || !rwRtFields) return;

                function handleRoleChange() {
                    const selectedRole = roleSelect.value;
                    const currentUserRole = '{{ auth()->user()->role }}';

                    console.log('[Role Change] Selected:', selectedRole, 'Current User:', currentUserRole);

                    // Show RW/RT fields for Masyarakat when created by Kader
                    if (selectedRole === 'masyarakat' && currentUserRole === 'kader') {
                        rwRtFields.style.display = 'block';
                        console.log('[Role Change] RW/RT fields shown');
                    } else {
                        rwRtFields.style.display = 'none';
                        console.log('[Role Change] RW/RT fields hidden');
                    }
                }

                roleSelect.addEventListener('change', handleRoleChange);

                // Trigger on page load
                handleRoleChange();
            });

            // ✅ HELPER: Log RT Mapping to Console (for debugging)
            function debugRtMapping() {
                console.log('=== RT MAPPING DEBUG ===');
                console.log('Full RT Mapping:', POSYANDU_RT_MAPPING);

                Object.keys(POSYANDU_RT_MAPPING).forEach(rw => {
                    console.log(`${rw}:`, POSYANDU_RT_MAPPING[rw]);
                });

                console.log('=======================');
            }

            // Call debug function
            debugRtMapping();

            document.addEventListener('DOMContentLoaded', function() {
                const roleSelect = document.getElementById('role');
                const bidangField = document.getElementById('bidang-field');
                const bidangSelect = document.getElementById('bidang_id');
                const jenisWilayahField = document.getElementById('jenis-wilayah-field');
                const jenisWilayahSelect = document.getElementById('jenis_wilayah');
                const kabupatenField = document.getElementById('kabupaten-field');
                const kotaField = document.getElementById('kota-field');
                const kecamatanField = document.getElementById('kecamatan-field');
                const desaField = document.getElementById('desa-field');
                const posyanduField = document.getElementById('posyandu-field');
                const posyanduSelect = document.getElementById('posyandu_id');

                // const kabupatenSelect = document.getElementById('kabupaten_id');
                // const kecamatanSelect = document.getElementById('kecamatan_id');
                // const desaSelect = document.getElementById('desa_id');

                function toggleFields() {
                    jenisWilayahField.style.display = 'none';
                    kabupatenField.style.display = 'none';
                    kotaField.style.display = 'none';
                    kecamatanField.style.display = 'none';
                    desaField.style.display = 'none';
                    posyanduField.style.display = 'none';
                    bidangField.style.display = 'none';

                    jenisWilayahSelect.required = false;
                    posyanduSelect.required = false;
                    bidangSelect.required = false;
                    // desaSelect.required = false;
                    // kecamatanSelect.required = false;

                    const role = roleSelect.value;
                    const currentUserRole = '{{ auth()->user()->role }}';

                    if (role === 'ketua-posyandu' || role === 'admin-kabupaten') {
                        jenisWilayahField.style.display = 'block';
                        jenisWilayahSelect.required = true;
                    }
                    if (role === 'kabid') {
                        bidangField.style.display = 'block';
                        bidangSelect.required = true;

                        jenisWilayahField.style.display = 'block';
                        jenisWilayahSelect.required = true;
                    }

                    if (role === 'admin-kecamatan') {
                        kecamatanField.style.display = 'block';
                        // kecamatanSelect.required = true;
                    }

                    if (role === 'ketua-kader') {
                        posyanduField.style.display = 'block';
                        posyanduSelect.required = true;
                    }

                    if (role === 'operator-desa') {
                        kecamatanField.style.display = 'block';
                        // kecamatanSelect.required = true;

                        desaField.style.display = 'block';
                        // desaSelect.required = true;
                    }

                    if (role === 'kader') {
                        bidangField.style.display = 'block';
                        bidangSelect.required = true;

                        if (currentUserRole === 'operator-desa') {
                            posyanduField.style.display = 'none';
                            posyanduSelect.required = false;
                        } else if (currentUserRole === 'ketua-kader') {
                            posyanduField.style.display = 'none';
                            posyanduSelect.required = false;
                        } else {
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
            const currentUserRole = @json(auth()->user()->role);
            const roleTargets = {
                'kader': ['masyarakat'],
                'ketua-kader': ['kader'],
                'operator-desa': ['ketua-kader', 'kader'],
                'admin-kecamatan': [],
                'admin-kabupaten': ['ketua-posyandu', 'kabid', 'admin-kecamatan', 'kades', 'operator-desa'],
                'admin': ['admin-kabupaten', 'ketua-posyandu', 'kabid', 'admin-kecamatan', 'kades', 'ketua-kader',
                    'operator-desa', 'kader', 'masyarakat'
                ]
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

            function showMainMenu() {
                const roleLabel = roleLabels[selectedRoleToCreate] || 'User';
                let menuHTML = `
        <div class="space-y-6 text-center">
            <p class="text-gray-600 mb-6">Pilih aksi yang ingin dilakukan:</p>

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
                        document.getElementById('uploadOption').addEventListener('click', () => {
                            showUploadStep();
                        });
                        document.getElementById('downloadOption').addEventListener('click', () => {
                            executeDownload();
                        });
                        document.getElementById('cancelMainMenu').addEventListener('click', () => {
                            Swal.close();
                        });
                    }
                });
            }

            function showUploadStep() {
                let uploadHTML = `
        <div class="space-y-5 text-left">
            <div>
                <label class="block text-start font-semibold mb-2 text-gray-700">Upload File Excel:</label>
                <input type="file" id="excelFile" accept=".xlsx,.xls"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer border border-gray-300 rounded-md">
                <p class="mt-2 text-xs text-gray-500">Format: .xlsx atau .xls</p>
            </div>
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <p class="text-sm text-blue-700">
                    <strong>Tips:</strong>
                    <br>• Template sudah berisi data Desa/Kecamatan dari Posyandu
                    <br>• Anda hanya perlu isi NAMA dan NOMOR TELEPON
                    <br>• Password default: <code class="bg-white px-2 py-1 rounded">password123</code>
                </p>
            </div>
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
                        document.getElementById('backToMainMenu').addEventListener('click', () => {
                            showMainMenu();
                        });
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

            function executeDownload() {
                Swal.fire({
                    title: 'Generating Template',
                    html: 'Mempersiapkan template berdasarkan data Posyandu terdaftar...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                const roleParam = selectedRoleToCreate ? `?role=${encodeURIComponent(selectedRoleToCreate)}` : '';
                const url = "{{ route('admin.users.export.template') }}" + roleParam;
                window.location.href = url;
                setTimeout(() => {
                    const roleLabel = roleLabels[selectedRoleToCreate] || 'User';
                    const rowInfo = selectedRoleToCreate === 'kader' ?
                        'Jumlah baris = 6 per Posyandu' :
                        'Jumlah baris = Jumlah Posyandu terdaftar';
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
