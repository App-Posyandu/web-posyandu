<div class="w-full max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-8">
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

        @if (!$isVerified)
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg mb-6"
                role="alert">
                <div class="flex">
                    <div class="py-1"><i class="bi bi-shield-lock-fill mr-3"></i></div>
                    <div>
                        <p class="font-bold">Akun Belum Terverifikasi</p>
                        <p class="text-sm">Akun Anda sedang menunggu verifikasi dari atasan. Anda belum dapat
                            mengelola data apa pun.</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="flex md:flex-row justify-between items-center mb-6 gap-4">
            {{-- TAMPILKAN BIDANG JIKA USER ADALAH KADER --}}
            @if (auth()->user()->role === 'kader' && auth()->user()->bidang)
                <h2 class="text-2xl font-bold text-gray-800">
                    List Pengajuan
                    <span class="text-pink-600">{{ auth()->user()->bidang->nama_bidang }}</span>
                </h2>
            @else
                <h2 class="text-2xl font-bold text-gray-800">List Pengajuan</h2>
            @endif

            <div class="flex items-center gap-2">
                @if (auth()->user()->role === 'kader')
                    <a href="{{ route('dashboard.partials.pilih-user') }}"
                        class="px-4 py-2 bg-pink-500 text-white rounded-md text-sm font-semibold hover:bg-pink-600">
                        <i class="bi bi-plus-circle-fill mr-2"></i>Tambah Ajuan
                    </a>
                @endif

                {{-- Filter dan Search dengan Livewire --}}
                <div class="flex flex-col md:flex-row items-center gap-2 w-full md:w-auto">
                    {{-- Filter berdasarkan Status --}}
                    <select wire:model.live="status"
                        class="border-gray-300 rounded-md shadow-sm text-sm w-full md:w-auto">
                        <option value="">Semua Status</option>
                        <option value="Diproses">Diproses</option>
                        <option value="Disetujui">Disetujui</option>
                        <option value="Ditolak">Ditolak</option>
                    </select>

                    {{-- Search Input --}}
                    <div class="relative w-full md:w-auto">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Cari berdasarkan nama..."
                            class="w-full md:w-64 pl-4 pr-10 py-2 border border-gray-300 rounded-md text-sm focus:ring-pink-500 focus:border-pink-500">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <i class="bi bi-search text-gray-400"></i>
                        </div>
                    </div>

                    {{-- Tombol Reset Filter --}}
                    @if ($search || $status)
                        <button wire:click="resetFilters" type="button"
                            class="text-sm text-gray-600 hover:text-gray-900">
                            Reset
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- INFO BADGE JIKA KADER --}}
        @if (auth()->user()->role === 'kader' && auth()->user()->bidang)
            <div class="mb-4 p-3 bg-pink-50 border-l-4 border-pink-500 rounded-lg">
                <div class="flex items-center">
                    <i class="bi bi-info-circle-fill text-pink-500 mr-2"></i>
                    <p class="text-sm text-gray-700">
                        Anda mengelola pengajuan di <strong>{{ auth()->user()->bidang->nama_bidang }}</strong>
                        untuk <strong>{{ auth()->user()->posyandu->nama_posyandu ?? 'Posyandu Anda' }}</strong>
                    </p>
                </div>
            </div>
        @endif

        @if (auth()->user()->role === 'ketua-kader' && auth()->user()->posyandu)
            <div class="mb-4 p-3 bg-pink-50 border-l-4 border-pink-500 rounded-lg">
                <div class="flex items-center">
                    <i class="bi bi-info-circle-fill text-pink-500 mr-2"></i>
                    <p class="text-sm text-gray-700">
                        Anda mengelola pengajuan di <strong>{{ auth()->user()->posyandu->nama_posyandu }}</strong>
                    </p>
                </div>
            </div>
        @endif

        @if (auth()->user()->role === 'masyarakat' && auth()->user()->posyandu)
            <div class="mb-4 p-3 bg-pink-50 border-l-4 border-pink-500 rounded-lg">
                <div class="flex items-center">
                    <i class="bi bi-info-circle-fill text-pink-500 mr-2"></i>
                    <p class="text-sm text-gray-700">
                        Anda terdaftar di <strong>{{ auth()->user()->posyandu->nama_posyandu }}</strong>
                    </p>
                </div>
            </div>
        @endif

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

        @include('ajuan.table', ['semuaAjuan' => $semuaAjuan])

        <div class="mt-4 flex justify-between items-center text-sm text-gray-600">
            {{ $semuaAjuan->links() }}
        </div>
    </div>
</div>
