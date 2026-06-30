<div class="w-full mx-auto sm:px-6 lg:px-8">
    {{-- SweetAlert for success messages --}}
    @if (session('swal_success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: @json(session('swal_success.title')),
                    text: @json(session('swal_success.text')),
                    confirmButtonColor: '#10b981'
                });
            });
        </script>
    @endif

    @if (session('swal_error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: @json(session('swal_error.title')),
                    text: @json(session('swal_error.text')),
                    confirmButtonColor: '#ef4444'
                });
            });
        </script>
    @endif

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: @json(session('success')),
                    confirmButtonColor: '#10b981'
                });
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: @json(session('error')),
                    confirmButtonColor: '#ef4444'
                });
            });
        </script>
    @endif

    <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-4 sm:p-6 lg:p-8">
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
        @if (session('success'))
        @endif

        @if (session('error'))
        @endif

        @if (auth()->user()->role === 'operator-desa')
            <div class="bg-pink-50 border-l-4 border-pink-500 p-4 rounded-lg mb-6">
                <div class="flex items-start">
                    <i class="bi bi-info-circle-fill text-pink-500 mr-2 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Kelola Kader di Posyandu Anda</p>
                        <p class="text-xs text-gray-600 mt-1">
                            Anda dapat melihat, mengedit, reset password, dan mengaktifkan/menonaktifkan akun kades, bu kades,
                            ketua posyandu, serta kader di desa Anda.
                        </p>
                    </div>
                </div>
            </div>
        @endif
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <!-- Kiri: Judul (50%) -->
            <div class="w-full md:w-1/2">
                <h2 class="text-xl md:text-2xl font-bold text-gray-800">List Pengguna</h2>
            </div>
            
            <!-- Kanan: Filter & Search (50%) -->
            <div class="w-full md:w-1/2 flex items-center justify-end gap-2">
                @if (in_array(auth()->user()->role, ['admin', 'operator-desa', 'admin-kabupaten']))
                    <a href="{{ route('admin.users.create') }}"
                        class="whitespace-nowrap px-4 py-2 bg-pink-500 text-white rounded-md text-sm font-semibold hover:bg-pink-600 text-center transition-colors duration-150">
                        <i class="bi bi-plus-circle-fill mr-2"></i>Tambah User
                    </a>
                @endif
                
                <div class="w-full max-w-[150px]">
                    <select wire:model.live="role" class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-pink-500 focus:border-pink-500">
                        <option value="">Semua Role</option>
                        @foreach ($allowedRoles as $roleOption)
                            <option value="{{ $roleOption }}">
                                {{ ucfirst(str_replace('-', ' ', $roleOption)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex-1 relative">
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama, email, NIK..."
                        class="w-full pl-4 pr-10 py-2 border border-gray-300 rounded-md text-sm focus:ring-pink-500 focus:border-pink-500">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <i class="bi bi-search text-gray-400"></i>
                    </div>
                </div>

                @if (auth()->user()->role === 'operator-desa')
                    <div class="w-full max-w-[150px]">
                        <select name="status"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500"
                            onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
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
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-gray-900">
                                    {{ $user->name }}
                                </div>
                                <div class="text-gray-500 text-sm">
                                    @if (in_array($user->role, ['masyarakat', 'kader', 'ketua-posyandu']))
                                        <i class="bi bi-geo-alt-fill text-xs mr-1"></i>
                                        {{ $user->posyandu?->nama_posyandu ?? 'Belum Terdaftar' }}
                                    @elseif ($user->kecamatan)
                                        <i class="bi bi-pin-map-fill text-xs mr-1"></i>
                                        Kec. <span>{{ $user->kecamatan ? $user->kecamatan : '-' }}</span>
                                    @elseif ($user->kabupaten)
                                        <i class="bi bi-building text-xs mr-1"></i>
                                        <span>{{ $user->kabupaten ? $user->kabupaten : '-' }}</span>
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
                                @php
                                    $roleClasses = [
                                        'admin' => 'bg-purple-100 text-purple-800',
                                        'kabid' => 'bg-blue-100 text-blue-800',
                                        'admin-kecamatan' => 'bg-indigo-100 text-indigo-800',
                                        'ketua-posyandu' => 'bg-green-100 text-green-800',
                                        'kader' => 'bg-yellow-100 text-yellow-800',
                                    ];
                                    $roleClass = $roleClasses[$user->role] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $roleClass }}">
                                    {{ ucfirst(str_replace('-', ' ', $user->role)) }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1">
                                    @if ($user->is_active)
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                                    @else
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Nonaktif</span>
                                    @endif
                                    @if ($user->verified_at)
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-teal-100 text-teal-800">Terverifikasi</span>
                                    @else
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Belum Diverifikasi</span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-2">
                                    <a href="{{ route('admin.users.show', $user) }}"
                                        class="px-3 py-1 bg-blue-500 text-white rounded-md text-xs text-center hover:bg-blue-600 transition-colors duration-150">
                                        <i class="bi bi-eye-fill mr-1"></i> Detail
                                    </a>

                                    @can('update', $user)
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                            class="px-3 py-1 bg-yellow-500 text-white rounded-md text-xs text-center hover:bg-yellow-600 transition-colors duration-150">
                                            <i class="bi bi-pencil-fill mr-1"></i> Ubah
                                        </a>
                                    @endcan

                                    @if ($currentUser->role === 'kader' && $user->role === 'masyarakat' && $user->no_telepon)
                                        <a href="{{ $this->generateWhatsAppLink($user) }}" target="_blank"
                                            class="px-3 py-1 bg-green-500 text-white rounded-md text-xs text-center hover:bg-green-600 transition-colors duration-150">
                                            <i class="bi bi-whatsapp mr-1"></i> Kirim WA
                                        </a>
                                    @endif

                                    @if ($currentUser->role === 'admin')
                                        <form id="delete-form-table-{{ $user->id }}" action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                onclick="confirmDeleteUser('{{ $user->id }}', '{{ $user->name }}')"
                                                class="w-full px-3 py-1 bg-red-500 text-white rounded-md text-xs text-center hover:bg-red-600 transition-colors duration-150">
                                                <i class="bi bi-trash mr-1"></i> Hapus
                                            </button>
                                        </form>
                                    @endif

                                    @if (is_null($user->verified_at) && in_array($user->role, ['masyarakat', 'kader']))
                                        @php
                                            $canVerify = false;
                                            if (
                                                ($currentUser->role === 'kader' && $user->role === 'masyarakat') ||
                                                ($currentUser->role === 'ketua-posyandu' && $user->role === 'kader') ||
                                                ($currentUser->role === 'kabid' && $user->role === 'ketua-posyandu') ||
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
                                    @if (auth()->user()->role === 'operator-desa')
                                        <div class="flex justify-between">
                                            <button type="button"
                                                onclick="openResetPasswordModal('{{ $user->id }}', '{{ $user->name }}')"
                                                class="px-3 py-1 bg-yellow-500 text-white rounded-md text-xs hover:bg-yellow-600">
                                                <i class="bi bi-key"></i> Reset
                                            </button>
                                            @if ($user->is_active)
                                                <button type="button"
                                                    onclick="openDeactivateModal('{{ $user->id }}', '{{ $user->name }}')"
                                                    class="px-3 py-1 bg-red-500 text-white rounded-md text-xs hover:bg-red-600">
                                                    <i class="bi bi-x-circle"></i> Nonaktifkan
                                                </button>
                                            @else
                                                <form id="reactivate-form-{{ $user->id }}" action="{{ route('admin.users.reactivate', $user) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    @php
                                                        $roleLabel = match ($user->role) {
                                                            'kades' => 'Kades',
                                                            'bu-kades' => 'Bu Kades',
                                                            'ketua-posyandu' => 'Ketua Posyandu',
                                                            default => 'Kader',
                                                        };
                                                    @endphp
                                                    <button type="button"
                                                        onclick="confirmReactivate('{{ $user->id }}', '{{ $user->name }}', '{{ $roleLabel }}')"
                                                        class="px-3 py-1 bg-green-500 text-white rounded-md text-xs hover:bg-green-600">
                                                        <i class="bi bi-check-circle"></i> Aktifkan
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @elseif(auth()->user()->role === 'admin-kabupaten')
                                        <div class="flex justify-between gap-3">
                                            <button type="button"
                                                onclick="openResetPasswordModalKabid('{{ $user->id }}', '{{ $user->name }}')"
                                                class="w-full px-3 py-1 bg-yellow-500 text-white rounded-md text-xs hover:bg-yellow-600">
                                                <i class="bi bi-key"></i> Reset
                                            </button>
                                            @if ($user->is_active)
                                                <button type="button"
                                                    onclick="openDeactivateModalKabid('{{ $user->id }}', '{{ $user->name }}')"
                                                    class="w-full px-3 py-1 bg-red-500 text-white rounded-md text-xs hover:bg-red-600">
                                                    <i class="bi bi-x-circle"></i> Nonaktifkan
                                                </button>
                                            @else
                                                <form id="reactivate-form-kabid-{{ $user->id }}" action="{{ route('admin.users.reactivate-kabid', $user) }}"
                                                    class="w-full" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="button"
                                                        onclick="confirmReactivateKabid('{{ $user->id }}', '{{ $user->name }}')"
                                                        class="w-full px-3 py-1 bg-green-500 text-white rounded-md text-xs hover:bg-green-600">
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
        <div class="md:hidden space-y-4">
            @forelse ($users as $user)
                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
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
                                    <p class="text-xs text-gray-500">
                                        @if (in_array($user->role, ['masyarakat', 'kader', 'ketua-posyandu']))
                                            <i class="bi bi-geo-alt-fill mr-1"></i>
                                            {{ $user->posyandu?->nama_posyandu ?? 'Belum Terdaftar' }}
                                        @elseif ($user->role === 'admin-kecamatan')
                                            <i class="bi bi-pin-map-fill mr-1"></i>
                                            Kec. <span>{{ $user->kecamatan ? $user->kecamatan : '-' }}</span>
                                        @elseif ($user->role === 'kabid')
                                            <i class="bi bi-building mr-1"></i>
                                            <span>{{ $user->kabupaten ? $user->kabupaten : '-' }}</span>
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
                    <div class="px-4 py-3 space-y-3">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-20">
                                <span class="text-xs font-medium text-gray-500">Email</span>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-900">{{ $user->email ?? '-' }}</p>
                                <p class="text-xs text-gray-500 mt-1">NIK: {{ $user->nik ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-20">
                                <span class="text-xs font-medium text-gray-500">Role</span>
                            </div>
                            <div class="flex-1">
                                @php
                                    $roleClasses = [
                                        'admin' => 'bg-purple-100 text-purple-800',
                                        'kabid' => 'bg-blue-100 text-blue-800',
                                        'admin-kecamatan' => 'bg-indigo-100 text-indigo-800',
                                        'ketua-posyandu' => 'bg-green-100 text-green-800',
                                        'kader' => 'bg-yellow-100 text-yellow-800',
                                    ];
                                    $roleClass = $roleClasses[$user->role] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $roleClass }}">
                                    {{ ucfirst(str_replace('-', ' ', $user->role)) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-20">
                                <span class="text-xs font-medium text-gray-500">Status</span>
                            </div>
                            <div class="flex-1">
                                @if ($user->is_active)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        <i class="bi bi-check-circle-fill mr-1"></i> Aktif
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        <i class="bi bi-x-circle-fill mr-1"></i> Nonaktif
                                    </span>
                                @endif
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
                    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                        <div class="flex flex-col gap-2">
                            <a href="{{ route('admin.users.show', $user) }}"
                                class="flex items-center justify-center px-3 py-2 bg-blue-500 text-white rounded-md text-sm font-medium hover:bg-blue-600 transition-colors duration-150">
                                <i class="bi bi-eye-fill mr-2"></i>
                                Lihat Detail
                            </a>

                            <div class="flex gap-2">
                                @can('update', $user)
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                        class="flex-1 flex items-center justify-center px-3 py-2 bg-yellow-500 text-white rounded-md text-sm font-medium hover:bg-yellow-600 transition-colors duration-150">
                                        <i class="bi bi-pencil-fill mr-1"></i>
                                        Ubah
                                    </a>
                                @endcan

                                @if ($currentUser->role === 'admin')
                                    <form id="delete-form-card-{{ $user->id }}" action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            onclick="confirmDeleteUser('{{ $user->id }}', '{{ $user->name }}')"
                                            class="flex-1 flex items-center justify-center px-3 py-2 bg-red-500 text-white rounded-md text-sm font-medium hover:bg-red-600 transition-colors duration-150">
                                            <i class="bi bi-trash mr-1"></i>
                                            Hapus
                                        </button>
                                    </form>
                                @endif

                                @if (is_null($user->verified_at) && in_array($user->role, ['masyarakat', 'kader']))
                                    @php
                                        $canVerify = false;
                                        if (
                                            ($currentUser->role === 'kader' && $user->role === 'masyarakat') ||
                                            ($currentUser->role === 'ketua-posyandu' && $user->role === 'kader') ||
                                            ($currentUser->role === 'kabid' && $user->role === 'ketua-posyandu') ||
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
        <div id="resetPasswordModal"
            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Reset Password User</h3>
                    <p class="text-sm text-gray-600 mb-4">Reset password untuk: <strong id="resetUserName"></strong>
                    </p>

                    <form id="resetPasswordForm" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                            <input type="password" name="new_password" id="reset_pw_kader" required minlength="8"
                                oninput="validateResetPw('kader')"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500">
                            <span id="reset_pw_kader_hint" class="mt-1 text-xs text-gray-500"><i class="bi bi-info-circle mr-1"></i>Minimal 8 karakter.</span>
                            <span id="reset_pw_kader_error" class="mt-1 text-xs text-red-600 hidden"><i class="bi bi-exclamation-circle mr-1"></i>Password minimal 8 karakter.</span>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password</label>
                            <input type="password" name="new_password_confirmation" id="reset_cf_kader" required minlength="8"
                                oninput="validateResetPw('kader')"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500">
                            <span id="reset_cf_kader_hint" class="mt-1 text-xs text-gray-500"><i class="bi bi-info-circle mr-1"></i>Masukkan ulang password yang sama.</span>
                            <span id="reset_cf_kader_error" class="mt-1 text-xs text-red-600 hidden"><i class="bi bi-exclamation-circle mr-1"></i>Password tidak cocok.</span>
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
        <div id="deactivateModal"
            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Nonaktifkan User</h3>
                    <p class="text-sm text-gray-600 mb-4">Nonaktifkan: <strong id="deactivateUserName"></strong></p>

                    <form id="deactivateForm" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alasan <span
                                    class="text-red-500">*</span></label>
                            <textarea name="reason" required rows="3" placeholder="Masukkan alasan menonaktifkan user ini..."
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
        <div id="resetPasswordModalKabid"
            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Reset Password</h3>
                    <p class="text-sm text-gray-600 mb-4">Reset password untuk: <strong
                            id="resetUserNameKabid"></strong>
                    </p>

                    <form id="resetPasswordFormKabid" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                            <input type="password" name="new_password" id="reset_pw_kabid" required minlength="8"
                                oninput="validateResetPw('kabid')"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500">
                            <span id="reset_pw_kabid_hint" class="mt-1 text-xs text-gray-500"><i class="bi bi-info-circle mr-1"></i>Minimal 8 karakter.</span>
                            <span id="reset_pw_kabid_error" class="mt-1 text-xs text-red-600 hidden"><i class="bi bi-exclamation-circle mr-1"></i>Password minimal 8 karakter.</span>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password</label>
                            <input type="password" name="new_password_confirmation" id="reset_cf_kabid" required minlength="8"
                                oninput="validateResetPw('kabid')"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500">
                            <span id="reset_cf_kabid_hint" class="mt-1 text-xs text-gray-500"><i class="bi bi-info-circle mr-1"></i>Masukkan ulang password yang sama.</span>
                            <span id="reset_cf_kabid_error" class="mt-1 text-xs text-red-600 hidden"><i class="bi bi-exclamation-circle mr-1"></i>Password tidak cocok.</span>
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
        <div id="deactivateModalKabid"
            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Nonaktifkan User</h3>
                    <p class="text-sm text-gray-600 mb-4">Nonaktifkan: <strong id="deactivateUserNameKabid"></strong>
                    </p>

                    <form id="deactivateFormKabid" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alasan <span
                                    class="text-red-500">*</span></label>
                            <textarea name="reason" required rows="3" placeholder="Masukkan alasan menonaktifkan user ini..."
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
        <div class="mt-6">
            {{ $users->links('vendor.pagination.tailwind') }}
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

            function validateResetPw(type) {
                const pw = document.getElementById('reset_pw_' + type);
                const cf = document.getElementById('reset_cf_' + type);
                const pwHint = document.getElementById('reset_pw_' + type + '_hint');
                const pwErr  = document.getElementById('reset_pw_' + type + '_error');
                const cfHint = document.getElementById('reset_cf_' + type + '_hint');
                const cfErr  = document.getElementById('reset_cf_' + type + '_error');

                if (pw.value.length > 0 && pw.value.length < 8) {
                    pwHint.classList.add('hidden'); pwErr.classList.remove('hidden');
                } else {
                    pwHint.classList.remove('hidden'); pwErr.classList.add('hidden');
                }

                if (cf.value.length > 0 && cf.value !== pw.value) {
                    cfHint.classList.add('hidden'); cfErr.classList.remove('hidden');
                } else {
                    cfHint.classList.remove('hidden'); cfErr.classList.add('hidden');
                }
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

            function confirmReactivate(userId, userName, roleLabel) {
                Swal.fire({
                    title: 'Aktifkan Kembali?',
                    text: `Apakah Anda yakin ingin mengaktifkan kembali ${roleLabel} ${userName}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Aktifkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById(`reactivate-form-${userId}`).submit();
                    }
                });
            }

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

            function confirmReactivateKabid(userId, userName) {
                Swal.fire({
                    title: 'Aktifkan Kembali?',
                    text: `Apakah Anda yakin ingin mengaktifkan kembali ${userName}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Aktifkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById(`reactivate-form-kabid-${userId}`).submit();
                    }
                });
            }

            function confirmDeleteUser(userId, userName) {
                Swal.fire({
                    title: 'Hapus Pengguna?',
                    text: `Apakah Anda yakin ingin menghapus pengguna "${userName}"? Tindakan ini tidak dapat dibatalkan.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formTable = document.getElementById(`delete-form-table-${userId}`);
                        const formCard = document.getElementById(`delete-form-card-${userId}`);
                        
                        if (formTable) {
                            formTable.submit();
                        } else if (formCard) {
                            formCard.submit();
                        }
                    }
                });
            }

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
