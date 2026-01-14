<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-6 lg:p-8">

        {{-- WHATSAPP ALERT --}}
        @if (session('whatsapp_link'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-green-800">
                            User Berhasil Ditambahkan!
                        </h3>
                        <div class="mt-2 text-sm text-green-700">
                            <p>Klik tombol di bawah untuk mengirim detail login via WhatsApp kepada user yang baru
                                dibuat.</p>
                        </div>
                        <div class="mt-4">
                            <a href="{{ session('whatsapp_link') }}" target="_blank"
                                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors duration-150">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                                </svg>
                                Kirim Detail Login via WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ALERT SUCCESS --}}
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6" role="alert">
                <div class="flex">
                    <i class="bi bi-check-circle-fill mr-3 mt-1"></i>
                    <div>
                        <p class="font-bold">Berhasil</p>
                        <p class="text-sm">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6" role="alert">
                <div class="flex">
                    <i class="bi bi-exclamation-triangle-fill mr-3 mt-1"></i>
                    <div>
                        <p class="font-bold">Gagal</p>
                        <p class="text-sm">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (auth()->user()->role === 'operator-desa')
            <div class="bg-pink-50 border-l-4 border-pink-500 p-4 rounded-lg mb-6">
                <div class="flex items-start">
                    <i class="bi bi-info-circle-fill text-pink-500 mr-2 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Kelola Kader di Posyandu Anda</p>
                        <p class="text-xs text-gray-600 mt-1">
                            Anda dapat melihat, reset password, dan mengaktifkan/menonaktifkan kader di
                            <strong>{{ auth()->user()->posyandu->nama_posyandu ?? 'posyandu Anda' }}</strong>
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- HEADER / FILTER --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <h2 class="text-xl md:text-2xl font-bold text-gray-800">List Pengguna</h2>

            <div class="flex flex-col md:flex-row items-stretch md:items-center gap-2 w-full md:w-auto">
                <a href="{{ route('admin.users.create') }}"
                    class="px-4 py-2 bg-pink-500 text-white rounded-md text-sm font-semibold hover:bg-pink-600 text-center transition-colors duration-150">
                    <i class="bi bi-plus-circle-fill mr-2"></i>Tambah User
                </a>

                {{-- Filter --}}
                <div class="flex flex-col md:flex-row gap-2 w-full md:w-auto">
                    <select wire:model.live="role" class="border-gray-300 rounded-md shadow-sm text-sm">
                        @if ($currentUser->role === 'admin')
                            <option value="">Semua Role</option>
                            <option value="masyarakat">Masyarakat</option>
                            <option value="kader">Kader</option>
                            <option value="ketua-kader">Ketua Kader</option>
                            <option value="admin-kecamatan">Admin Kecamatan</option>
                            <option value="operator-desa">Operator Desa</option>
                            <option value="kabid">Kabid</option>
                        @elseif ($currentUser->role === 'admin-kabupaten')
                            <option value="">Semua Role</option>
                            <option value="kabid">Kabid</option>
                            <option value="admin-kecamatan">Admin Kecamatan</option>
                            <option value="ketua-kader">Ketua Kader</option>
                        @elseif ($currentUser->role === 'kabid')
                            <option value="">Semua Role</option>
                            <option value="admin-kecamatan">Admin Kecamatan</option>
                            <option value="ketua-kader">Ketua Kader</option>
                            <option value="kader">Kader</option>
                            <option value="masyarakat">Masyarakat</option>
                        @elseif ($currentUser->role === 'admin-kecamatan')
                            <option value="">Semua Role</option>
                            <option value="ketua-kader">Ketua Kader</option>
                            <option value="kader">Kader</option>
                            <option value="masyarakat">Masyarakat</option>
                        @elseif ($currentUser->role === 'operator-desa')
                            <option value="">Semua Role</option>
                            <option value="kader">Kader</option>
                            <option value="masyarakat">Masyarakat</option>
                        @elseif ($currentUser->role === 'ketua-kader')
                            <option value="">Semua Role</option>
                            <option value="masyarakat">Masyarakat</option>
                            <option value="kader">Kader</option>
                        @elseif ($currentUser->role === 'kader')
                            <option value="">Semua Role</option>
                            <option value="masyarakat">Masyarakat</option>
                        @endif
                    </select>

                    <div class="relative w-full md:w-64">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Cari nama, email, NIK..."
                            class="w-full pl-4 pr-10 py-2 border border-gray-300 rounded-md text-sm focus:ring-pink-500 focus:border-pink-500">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="bi bi-search text-gray-400"></i>
                        </div>
                    </div>

                    {{-- Filter Status (khusus Operator Desa) --}}
                    @if (auth()->user()->role === 'operator-desa')
                        <div class="w-full sm:w-48">
                            <select name="status"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500"
                                onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif
                                </option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                                    Nonaktif</option>
                            </select>
                        </div>
                    @endif

                    @if ($search || $role)
                        <button wire:click="resetFilters" type="button"
                            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors duration-150">
                            <i class="bi bi-arrow-clockwise mr-1"></i>
                            Reset
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- LOADING --}}
        <div wire:loading class="mb-4">
            <div class="flex items-center justify-center p-4 bg-gray-50 rounded-lg">
                <svg class="animate-spin h-5 w-5 text-pink-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span class="text-sm text-gray-600">Memuat data...</span>
            </div>
        </div>

        {{-- DESKTOP VIEW: TABLE --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wide">No
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wide">Nama
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wide">Email
                            & NIK</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wide">Role
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wide">
                            Status
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wide">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($users as $user)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $loop->iteration + $users->firstItem() - 1 }}
                            </td>

                            {{-- ✅ NAMA & INFO WILAYAH/POSYANDU --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-gray-900">
                                    {{ $user->name }}
                                </div>
                                <div class="text-gray-500 text-sm">
                                    @if (in_array($user->role, ['masyarakat', 'kader', 'ketua-kader']))
                                        <i class="bi bi-geo-alt-fill text-xs mr-1"></i>
                                        {{ $user->posyandu?->nama_posyandu ?? 'Belum Terdaftar' }}
                                    @elseif ($user->role === 'admin-kecamatan')
                                        <i class="bi bi-pin-map-fill text-xs mr-1"></i>
                                        Kec. {{ $user->kecamatan ?? '-' }}
                                    @elseif ($user->role === 'kabid')
                                        <i class="bi bi-building text-xs mr-1"></i>
                                        {{ $user->kabupaten ?? '-' }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </td>

                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-gray-900 text-sm">
                                    {{ $user->email ?? '-' }}
                                </div>
                                <div class="text-gray-500 text-xs">
                                    NIK: {{ $user->nik ?? '-' }}
                                </div>
                            </td>

                            <td class="px-4 py-3 text-gray-700 text-sm">
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-semibold
                            @if ($user->role === 'admin') bg-purple-100 text-purple-800
                            @elseif($user->role === 'kabid') bg-blue-100 text-blue-800
                            @elseif($user->role === 'admin-kecamatan') bg-indigo-100 text-indigo-800
                            @elseif($user->role === 'ketua-kader') bg-green-100 text-green-800
                            @elseif($user->role === 'kader') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst(str_replace('-', ' ', $user->role)) }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                @if ($user->verified_at)
                                    <span
                                        class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Terverifikasi
                                    </span>
                                @else
                                    <span
                                        class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Belum Diverifikasi
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-2">
                                    <a href="{{ route('admin.users.show', $user) }}"
                                        class="px-3 py-1 bg-blue-500 text-white rounded-md text-xs text-center hover:bg-blue-600 transition-colors duration-150">
                                        <i class="bi bi-eye-fill mr-1"></i> Detail
                                    </a>

                                    @if ($currentUser->role === 'admin')
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                            class="px-3 py-1 bg-yellow-500 text-white rounded-md text-xs text-center hover:bg-yellow-600 transition-colors duration-150">
                                            <i class="bi bi-pencil-fill mr-1"></i> Ubah
                                        </a>
                                    @endif

                                    @if (is_null($user->verified_at) && in_array($user->role, ['masyarakat', 'kader']))
                                        @php
                                            $canVerify = false;
                                            if (
                                                ($currentUser->role === 'kader' && $user->role === 'masyarakat') ||
                                                ($currentUser->role === 'ketua-kader' && $user->role === 'kader') ||
                                                ($currentUser->role === 'kabid' && $user->role === 'ketua-kader') ||
                                                $currentUser->role === 'admin'
                                            ) {
                                                $canVerify = true;
                                            }
                                        @endphp

                                        @if ($canVerify)
                                            <form action="{{ route('admin.users.verify', $user) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="w-full px-3 py-1 bg-green-500 text-white rounded-md text-xs hover:bg-green-600 transition-colors duration-150">
                                                    <i class="bi bi-check-circle-fill mr-1"></i> Verifikasi
                                                </button>
                                            </form>
                                        @endif
                                    @endif

                                    {{-- UNTUK OPERATOR DESA: Kelola Kader --}}
                                    @if (auth()->user()->role === 'operator-desa')
                                        <div class="flex justify-between">
                                            {{-- Detail --}}
                                            {{-- <a href="{{ route('admin.users.show', $user) }}"
                                                class="px-3 py-1 bg-blue-500 text-white rounded-md text-xs hover:bg-blue-600">
                                                <i class="bi bi-eye"></i> Detail
                                            </a> --}}

                                            {{-- Reset Password --}}
                                            <button type="button"
                                                onclick="openResetPasswordModal('{{ $user->id }}', '{{ $user->name }}')"
                                                class="px-3 py-1 bg-yellow-500 text-white rounded-md text-xs hover:bg-yellow-600">
                                                <i class="bi bi-key"></i> Reset
                                            </button>

                                            {{-- Activate/Deactivate --}}
                                            @if ($user->is_active)
                                                <button type="button"
                                                    onclick="openDeactivateModal('{{ $user->id }}', '{{ $user->name }}')"
                                                    class="px-3 py-1 bg-red-500 text-white rounded-md text-xs hover:bg-red-600">
                                                    <i class="bi bi-x-circle"></i> Nonaktifkan
                                                </button>
                                            @else
                                                <form action="{{ route('admin.users.reactivate', $user) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                        onclick="return confirm('Aktifkan kembali {{ $user->name }}?')"
                                                        class="px-3 py-1 bg-green-500 text-white rounded-md text-xs hover:bg-green-600">
                                                        <i class="bi bi-check-circle"></i> Aktifkan
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @elseif(auth()->user()->role === 'admin-kabupaten')
                                        <div class="flex justify-between">
                                            {{-- Detail --}}
                                            {{-- <a href="{{ route('admin.users.show', $user) }}"
                                                class="px-3 py-1 bg-blue-500 text-white rounded-md text-xs hover:bg-blue-600">
                                                <i class="bi bi-eye"></i> Detail
                                            </a> --}}

                                            {{-- Reset Password --}}
                                            <button type="button"
                                                onclick="openResetPasswordModalKabid('{{ $user->id }}', '{{ $user->name }}')"
                                                class="px-3 py-1 bg-yellow-500 text-white rounded-md text-xs hover:bg-yellow-600">
                                                <i class="bi bi-key"></i> Reset
                                            </button>

                                            {{-- Activate/Deactivate --}}
                                            @if ($user->is_active)
                                                <button type="button"
                                                    onclick="openDeactivateModalKabid('{{ $user->id }}', '{{ $user->name }}')"
                                                    class="px-3 py-1 bg-red-500 text-white rounded-md text-xs hover:bg-red-600">
                                                    <i class="bi bi-x-circle"></i> Nonaktifkan
                                                </button>
                                            @else
                                                <form action="{{ route('admin.users.reactivate-kabid', $user) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                        onclick="return confirm('Aktifkan kembali {{ $user->name }}?')"
                                                        class="px-3 py-1 bg-green-500 text-white rounded-md text-xs hover:bg-green-600">
                                                        <i class="bi bi-check-circle"></i> Aktifkan
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="bi bi-people text-5xl text-gray-300 mb-4"></i>
                                    <p class="text-gray-500 text-lg font-medium">Tidak ada data pengguna</p>
                                    <p class="text-gray-400 text-sm mt-1">Belum ada pengguna yang terdaftar</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- MOBILE VIEW: CARDS --}}
        <div class="md:hidden space-y-4">
            @forelse ($users as $user)
                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                    {{-- Card Header --}}
                    <div class="bg-gradient-to-r from-pink-50 to-purple-50 px-4 py-3 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="h-12 w-12 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 font-bold text-lg">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900">
                                        {{ $user->name }}
                                    </h3>
                                    {{-- ✅ INFO WILAYAH/POSYANDU --}}
                                    <p class="text-xs text-gray-500">
                                        @if (in_array($user->role, ['masyarakat', 'kader', 'ketua-kader']))
                                            <i class="bi bi-geo-alt-fill mr-1"></i>
                                            {{ $user->posyandu?->nama_posyandu ?? 'Belum Terdaftar' }}
                                        @elseif ($user->role === 'admin-kecamatan')
                                            <i class="bi bi-pin-map-fill mr-1"></i>
                                            Kec. {{ $user->kecamatan ?? '-' }}
                                        @elseif ($user->role === 'kabid')
                                            <i class="bi bi-building mr-1"></i>
                                            {{ $user->kabupaten ?? '-' }}
                                        @else
                                            -
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <span class="text-xs font-medium text-gray-500">
                                #{{ $loop->iteration + $users->firstItem() - 1 }}
                            </span>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="px-4 py-3 space-y-3">
                        {{-- Email & NIK --}}
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-20">
                                <span class="text-xs font-medium text-gray-500">Email</span>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-900">{{ $user->email ?? '-' }}</p>
                                <p class="text-xs text-gray-500 mt-1">NIK: {{ $user->nik ?? '-' }}</p>
                            </div>
                        </div>

                        {{-- Role --}}
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-20">
                                <span class="text-xs font-medium text-gray-500">Role</span>
                            </div>
                            <div class="flex-1">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full
                            @if ($user->role === 'admin') bg-purple-100 text-purple-800
                            @elseif($user->role === 'kabid') bg-blue-100 text-blue-800
                            @elseif($user->role === 'admin-kecamatan') bg-indigo-100 text-indigo-800
                            @elseif($user->role === 'ketua-kader') bg-green-100 text-green-800
                            @elseif($user->role === 'kader') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst(str_replace('-', ' ', $user->role)) }}
                                </span>
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-20">
                                <span class="text-xs font-medium text-gray-500">Status</span>
                            </div>
                            <div class="flex-1">
                                @if ($user->verified_at)
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        <i class="bi bi-check-circle-fill mr-1"></i> Terverifikasi
                                    </span>
                                @else
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        <i class="bi bi-clock-fill mr-1"></i> Belum Diverifikasi
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Card Footer - Action Buttons --}}
                    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                        <div class="flex flex-col gap-2">
                            <a href="{{ route('admin.users.show', $user) }}"
                                class="flex items-center justify-center px-3 py-2 bg-blue-500 text-white rounded-md text-sm font-medium hover:bg-blue-600 transition-colors duration-150">
                                <i class="bi bi-eye-fill mr-2"></i>
                                Lihat Detail
                            </a>

                            <div class="flex gap-2">
                                @if ($currentUser->role === 'admin')
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                        class="flex-1 flex items-center justify-center px-3 py-2 bg-yellow-500 text-white rounded-md text-sm font-medium hover:bg-yellow-600 transition-colors duration-150">
                                        <i class="bi bi-pencil-fill mr-1"></i>
                                        Ubah
                                    </a>
                                @endif

                                @if (is_null($user->verified_at) && in_array($user->role, ['masyarakat', 'kader']))
                                    @php
                                        $canVerify = false;
                                        if (
                                            ($currentUser->role === 'kader' && $user->role === 'masyarakat') ||
                                            ($currentUser->role === 'ketua-kader' && $user->role === 'kader') ||
                                            ($currentUser->role === 'kabid' && $user->role === 'ketua-kader') ||
                                            $currentUser->role === 'admin'
                                        ) {
                                            $canVerify = true;
                                        }
                                    @endphp

                                    @if ($canVerify)
                                        <form action="{{ route('admin.users.verify', $user) }}" method="POST"
                                            class="flex-1">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="w-full flex items-center justify-center px-3 py-2 bg-green-500 text-white rounded-md text-sm font-medium hover:bg-green-600 transition-colors duration-150">
                                                <i class="bi bi-check-circle-fill mr-1"></i>
                                                Verifikasi
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow-md p-8 text-center">
                    <div class="flex flex-col items-center justify-center">
                        <i class="bi bi-people text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-base font-medium">Tidak ada data pengguna</p>
                        <p class="text-gray-400 text-sm mt-2">Belum ada pengguna yang terdaftar</p>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Modal Reset Password --}}
        <div id="resetPasswordModal"
            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Reset Password Kader</h3>
                    <p class="text-sm text-gray-600 mb-4">Reset password untuk: <strong id="resetUserName"></strong>
                    </p>

                    <form id="resetPasswordForm" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                            <input type="password" name="new_password" required minlength="8"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password</label>
                            <input type="password" name="new_password_confirmation" required minlength="8"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500">
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                class="flex-1 px-4 py-2 bg-pink-500 text-white rounded-md hover:bg-pink-600">
                                Reset Password
                            </button>
                            <button type="button" onclick="closeResetPasswordModal()"
                                class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Deactivate --}}
        <div id="deactivateModal"
            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Nonaktifkan Kader</h3>
                    <p class="text-sm text-gray-600 mb-4">Nonaktifkan: <strong id="deactivateUserName"></strong></p>

                    <form id="deactivateForm" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alasan <span
                                    class="text-red-500">*</span></label>
                            <textarea name="reason" required rows="3" placeholder="Masukkan alasan menonaktifkan kader ini..."
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500"></textarea>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                class="flex-1 px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                                Nonaktifkan
                            </button>
                            <button type="button" onclick="closeDeactivateModal()"
                                class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Reset Password --}}
        <div id="resetPasswordModalKabid"
            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Reset Password</h3>
                    <p class="text-sm text-gray-600 mb-4">Reset password untuk: <strong id="resetUserNameKabid"></strong>
                    </p>

                    <form id="resetPasswordFormKabid" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                            <input type="password" name="new_password" required minlength="8"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password</label>
                            <input type="password" name="new_password_confirmation" required minlength="8"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500">
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                class="flex-1 px-4 py-2 bg-pink-500 text-white rounded-md hover:bg-pink-600">
                                Reset Password
                            </button>
                            <button type="button" onclick="closeResetPasswordModalKabid()"
                                class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Deactivate --}}
        <div id="deactivateModalKabid"
            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Nonaktifkan Kader</h3>
                    <p class="text-sm text-gray-600 mb-4">Nonaktifkan: <strong id="deactivateUserNameKabid"></strong></p>

                    <form id="deactivateFormKabid" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alasan <span
                                    class="text-red-500">*</span></label>
                            <textarea name="reason" required rows="3" placeholder="Masukkan alasan menonaktifkan kader ini..."
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500"></textarea>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                class="flex-1 px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                                Nonaktifkan
                            </button>
                            <button type="button" onclick="closeDeactivateModalKabid()"
                                class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-6">
            {{ $users->links() }}
        </div>

        <script>
            function openResetPasswordModal(userId, userName) {
                document.getElementById('resetUserName').textContent = userName;
                document.getElementById('resetPasswordForm').action = `/admin/users/${userId}/reset-password`;
                document.getElementById('resetPasswordModal').classList.remove('hidden');
            }

            function closeResetPasswordModal() {
                document.getElementById('resetPasswordModal').classList.add('hidden');
                document.getElementById('resetPasswordForm').reset();
            }

            function openDeactivateModal(userId, userName) {
                document.getElementById('deactivateUserName').textContent = userName;
                document.getElementById('deactivateForm').action = `/admin/users/${userId}/deactivate`;
                document.getElementById('deactivateModal').classList.remove('hidden');
            }

            function closeDeactivateModal() {
                document.getElementById('deactivateModal').classList.add('hidden');
                document.getElementById('deactivateForm').reset();
            }

            // Close modal when clicking outside
            window.onclick = function(event) {
                const resetModal = document.getElementById('resetPasswordModal');
                const deactivateModal = document.getElementById('deactivateModal');

                if (event.target == resetModal) {
                    closeResetPasswordModal();
                }
                if (event.target == deactivateModal) {
                    closeDeactivateModal();
                }
            }
        </script>
        <script>
            function openResetPasswordModalKabid(userId, userName) {
                document.getElementById('resetUserNameKabid').textContent = userName;
                document.getElementById('resetPasswordFormKabid').action = `/users/${userId}/reset-password-kabid`;
                document.getElementById('resetPasswordModalKabid').classList.remove('hidden');
            }

            function closeResetPasswordModalKabid() {
                document.getElementById('resetPasswordModalKabid').classList.add('hidden');
                document.getElementById('resetPasswordFormKabid').reset();
            }

            function openDeactivateModalKabid(userId, userName) {
                document.getElementById('deactivateUserNameKabid').textContent = userName;
                document.getElementById('deactivateFormKabid').action = `/users/${userId}/deactivate-kabid`;
                document.getElementById('deactivateModalKabid').classList.remove('hidden');
            }

            function closeDeactivateModalKabid() {
                document.getElementById('deactivateModalKabid').classList.add('hidden');
                document.getElementById('deactivateFormKabid').reset();
            }

            // Close modal when clicking outside
            window.onclick = function(event) {
                const resetModal = document.getElementById('resetPasswordModalKabid');
                const deactivateModal = document.getElementById('deactivateModalKabid');

                if (event.target == resetModal) {
                    closeResetPasswordModalKabid();
                }
                if (event.target == deactivateModal) {
                    closeDeactivateModalKabid();
                }
            }
        </script>
    </div>
</div>
