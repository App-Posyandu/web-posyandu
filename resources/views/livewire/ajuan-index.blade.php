<div class="w-full mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-4 sm:p-6 lg:p-8">

        {{-- Success Alert --}}
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

        {{-- Verification Alert --}}
        @if (!$isVerified)
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg mb-6" role="alert">
                <div class="flex">
                    <div class="py-1"><i class="bi bi-shield-lock-fill mr-3"></i></div>
                    <div>
                        <p class="font-bold">Akun Belum Terverifikasi</p>
                        <p class="text-sm">Akun Anda sedang menunggu verifikasi dari atasan. Anda belum dapat mengelola
                            data apa pun.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            {{-- Title --}}
            <div class="w-full sm:w-auto">
                @if (auth()->user()->role === 'kader' && auth()->user()->bidang)
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-800">
                        List Pengajuan
                        <span class="block sm:inline text-pink-600 mt-1 sm:mt-0">
                            {{ auth()->user()->bidang->nama_bidang }}
                        </span>
                    </h2>
                @else
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-800">List Pengajuan</h2>
                @endif
            </div>

            {{-- Action Button (Mobile Full Width) --}}
            @if (auth()->user()->role === 'kader')
                <div class="w-full sm:w-auto">
                    <a href="{{ route('dashboard.partials.pilih-user') }}"
                        class="w-full sm:w-auto flex items-center justify-center px-4 py-2 bg-pink-500 text-white rounded-md text-sm font-semibold hover:bg-pink-600 transition-colors duration-150">
                        <i class="bi bi-plus-circle-fill mr-2"></i>
                        <span class="hidden sm:inline">Tambah Ajuan</span>
                        <span class="sm:hidden">Buat Ajuan Baru</span>
                    </a>
                </div>
            @endif
        </div>

        {{-- Filter and Search Section --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 mb-6">
            {{-- Filter Status --}}
            <div class="w-full sm:w-auto sm:flex-1 sm:max-w-xs">
                <select wire:model.live="status"
                    class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-pink-500 focus:border-pink-500">
                    <option value="">Semua Status</option>
                    <option value="Diproses">Diproses</option>
                    <option value="Disetujui">Disetujui</option>
                    <option value="Ditolak">Ditolak</option>
                </select>
            </div>

            {{-- Search Input --}}
            <div class="relative w-full sm:flex-1">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari berdasarkan nama..."
                    class="w-full pl-4 pr-10 py-2 border border-gray-300 rounded-md text-sm focus:ring-pink-500 focus:border-pink-500">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <i class="bi bi-search text-gray-400"></i>
                </div>
            </div>

            {{-- Reset Button --}}
            @if ($search || $status)
                <button wire:click="resetFilters" type="button"
                    class="w-full sm:w-auto px-4 py-2 text-sm text-gray-600 hover:text-gray-900 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors duration-150">
                    <i class="bi bi-arrow-clockwise mr-1"></i>
                    Reset Filter
                </button>
            @endif
        </div>

        {{-- Info Badge for Kader --}}
        @if (auth()->user()->role === 'kader' && auth()->user()->bidang)
            <div class="mb-4 p-3 bg-pink-50 border-l-4 border-pink-500 rounded-lg">
                <div class="flex items-start">
                    <i class="bi bi-info-circle-fill text-pink-500 mr-2 mt-0.5 flex-shrink-0"></i>
                    <p class="text-sm text-gray-700">
                        Anda mengelola pengajuan di <strong>{{ auth()->user()->bidang->nama_bidang }}</strong>
                        untuk <strong>{{ auth()->user()->posyandu->nama_posyandu ?? 'Posyandu Anda' }}</strong>
                    </p>
                </div>
            </div>
        @endif

        {{-- Info Badge for Ketua Kader --}}
        @if (auth()->user()->role === 'ketua-kader' && auth()->user()->posyandu)
            <div class="mb-4 p-3 bg-pink-50 border-l-4 border-pink-500 rounded-lg">
                <div class="flex items-start">
                    <i class="bi bi-info-circle-fill text-pink-500 mr-2 mt-0.5 flex-shrink-0"></i>
                    <p class="text-sm text-gray-700">
                        Anda mengelola pengajuan di <strong>{{ auth()->user()->posyandu->nama_posyandu }}</strong>
                    </p>
                </div>
            </div>
        @endif

        {{-- Info Badge for Masyarakat --}}
        @if (auth()->user()->role === 'masyarakat' && auth()->user()->posyandu)
            <div class="mb-4 p-3 bg-pink-50 border-l-4 border-pink-500 rounded-lg">
                <div class="flex items-start">
                    <i class="bi bi-info-circle-fill text-pink-500 mr-2 mt-0.5 flex-shrink-0"></i>
                    <p class="text-sm text-gray-700">
                        Anda terdaftar di <strong>{{ auth()->user()->posyandu->nama_posyandu }}</strong>
                    </p>
                </div>
            </div>
        @endif

        {{-- Loading Indicator --}}
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

        {{-- Table/Card Content --}}
        @include('ajuan.table', ['semuaAjuan' => $semuaAjuan])

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $semuaAjuan->links() }}
        </div>
    </div>
</div>
