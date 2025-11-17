<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6" role="alert">
                    <div class="flex">
                        <div class="py-1">
                            <i class="bi bi-check-circle-fill mr-3"></i>
                        </div>
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
                        <div class="py-1">
                            <i class="bi bi-exclamation-triangle-fill mr-3"></i>
                        </div>
                        <div>
                            <p class="font-bold">Gagal</p>
                            <p class="text-sm">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                        <h2 class="text-2xl font-bold text-gray-800">List Pengguna</h2>
                        <div class="flex items-center gap-2">   
                            <a href="{{ route('admin.users.create') }}"
                                class="px-4 py-2 bg-pink-500 text-white rounded-md text-sm font-semibold hover:bg-pink-600">
                                <i class="bi bi-plus-circle-fill mr-2"></i>Tambah User
                            </a>

                            {{-- Filter dengan Livewire --}}
                            <div class="flex items-center gap-2">
                                <select wire:model.live="role" class="border-gray-300 rounded-md shadow-sm text-sm">
                                    @if ($currentUser->role === 'admin')
                                        <option value="">Semua Role</option>
                                        <option value="masyarakat">Masyarakat</option>
                                        <option value="kader">Kader</option>
                                        <option value="ketua-kader">Ketua Kader</option>
                                        <option value="kabid">Kabid</option>
                                    @elseif ($currentUser->role === 'kader')
                                        <option value="">Semua Role</option>
                                        <option value="masyarakat">Masyarakat</option>
                                    @elseif ($currentUser->role === 'ketua-kader')
                                        <option value="">Semua Role</option>
                                        <option value="masyarakat">Masyarakat</option>
                                        <option value="kader">Kader</option>
                                    @elseif ($currentUser->role === 'kabid')
                                        <option value="">Semua Role</option>
                                        <option value="masyarakat">Masyarakat</option>
                                        <option value="kader">Kader</option>
                                        <option value="ketua-kader">Ketua Kader</option>
                                    @endif
                                </select>

                                <div class="relative">
                                    <input type="text" wire:model.live.debounce.300ms="search"
                                        placeholder="Cari nama, email, NIK..."
                                        class="w-full md:w-64 pl-4 pr-10 py-2 border border-gray-300 rounded-md text-sm focus:ring-pink-500 focus:border-pink-500">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <i class="bi bi-search text-gray-400"></i>
                                    </div>
                                </div>

                                {{-- Tombol Reset --}}
                                @if ($search || $role)
                                    <button wire:click="resetFilters" type="button"
                                        class="text-sm text-gray-600 hover:text-gray-900">
                                        Reset
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Loading Indicator --}}
                    <div wire:loading class="mb-4">
                        <div class="flex items-center justify-center p-4">
                            <svg class="animate-spin h-5 w-5 text-pink-500 mr-3" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span class="text-sm text-gray-600">Memuat data...</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">No</th>
                                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Nama
                                    </th>
                                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Email &
                                        NIK</th>
                                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Role
                                    </th>
                                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($users as $user)
                                    <tr>
                                        <td class="px-6 py-4">{{ $loop->iteration + $users->firstItem() - 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500 md:text-base">
                                                {{ $user->posyandu?->nama_posyandu ?? 'Belum Terdaftar' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-gray-900">{{ $user->email }}</div>
                                            <div class="text-sm text-gray-500 md:text-base">NIK: {{ $user->nik }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-base text-gray-500">
                                            {{ ucfirst($user->role) }}
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($user->verified_at)
                                                <span
                                                    class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Terverifikasi</span>
                                            @else
                                                <span
                                                    class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Belum
                                                    Diverifikasi</span>
                                            @endif
                                        </td>

                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium flex flex-col items-start space-y-2">
                                            <a href="{{ route('admin.users.show', $user) }}"
                                                class="flex items-center justify-center w-24 px-3 py-1 bg-blue-500 text-white rounded-md text-xs hover:bg-blue-600">Detail</a>

                                            @if ($currentUser->role === 'admin')
                                                <a href="{{ route('admin.users.edit', $user) }}"
                                                    class="flex items-center justify-center w-24 px-3 py-1 bg-yellow-500 text-white rounded-md text-xs hover:bg-yellow-600">Ubah</a>
                                            @endif

                                            @if (is_null($user->verified_at) && in_array($user->role, ['masyarakat', 'kader']))
                                                @php
                                                    $canVerify = false;
                                                    if (
                                                        ($currentUser->role === 'kader' &&
                                                            $user->role === 'masyarakat') ||
                                                        ($currentUser->role === 'ketua-kader' &&
                                                            $user->role === 'kader') ||
                                                        ($currentUser->role === 'kabid' &&
                                                            $user->role === 'ketua-kader') ||
                                                        $currentUser->role === 'admin'
                                                    ) {
                                                        $canVerify = true;
                                                    }
                                                @endphp

                                                @if ($canVerify)
                                                    <form action="{{ route('admin.users.verify', $user) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit"
                                                            class="flex items-center justify-center w-24 px-3 py-1 bg-green-500 text-white rounded-md text-xs hover:bg-green-600">Verifikasi</button>
                                                    </form>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada data
                                            pengguna ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
