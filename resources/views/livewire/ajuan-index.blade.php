<div class="w-full mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-4 sm:p-6 lg:p-8">
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
        <div class="w-full flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div class="w-full sm:w-auto">
                <div>
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
                    @if (auth()->user()->role === 'ketua-posyandu')
                        <p class="text-sm text-gray-500 mt-1">
                            Menampilkan pengajuan yang memerlukan persetujuan Anda
                        @elseif(auth()->user()->role === 'kades')
                            Menampilkan pengajuan yang diajukan ke Desa
                        @elseif(auth()->user()->role === 'bu-kades')
                            Menampilkan pengajuan untuk monitoring (view dan export saja)
                        @elseif(auth()->user()->role === 'kader')
                            Menampilkan pengajuan yang perlu diverifikasi
                        @else
                            Total: {{ $semuaAjuan->total() }} pengajuan
                    @endif
                </div>
                @if (auth()->user()->role === 'ketua-posyandu')
                    <div class="w-full bg-blue-50 border-l-4 border-blue-500 p-4 rounded-md">
                        <div class="flex items-start">
                            <i class="bi bi-info-circle-fill text-blue-500 mr-3 mt-0.5"></i>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-blue-900">Filter Otomatis Aktif</p>
                                <p class="text-xs text-blue-700 mt-1">
                                    Anda hanya melihat pengajuan yang:
                                </p>
                                <ul class="text-xs text-blue-700 mt-2 space-y-1 list-disc list-inside">
                                    <li>Sudah menyelesaikan kunjungan lapangan (Tahap 3)</li>
                                    <li>Memerlukan persetujuan dari Ketua Posyandu</li>
                                    <li>Atau pengajuan yang sudah disetujui (status "Sesuai")</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @elseif(auth()->user()->role === 'kades')
                    <div class="w-full bg-purple-50 border-l-4 border-purple-500 p-4 rounded-md">
                        <div class="flex items-start">
                            <i class="bi bi-info-circle-fill text-purple-500 mr-3 mt-0.5"></i>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-purple-900">Filter Otomatis Aktif</p>
                                <p class="text-xs text-purple-700 mt-1">
                                    Anda hanya melihat pengajuan yang sudah diajukan ke Desa dan memerlukan persetujuan
                                    akhir dari Kepala Desa
                                </p>
                            </div>
                        </div>
                    </div>
                @elseif(auth()->user()->role === 'bu-kades')
                    <div class="w-full bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded-md">
                        <div class="flex items-start">
                            <i class="bi bi-info-circle-fill text-indigo-500 mr-3 mt-0.5"></i>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-indigo-900">Mode Monitoring</p>
                                <p class="text-xs text-indigo-700 mt-1">
                                    Anda dapat melihat dan mencetak pengajuan, tetapi tidak dapat memberikan persetujuan atau tindak lanjut.
                                </p>
                            </div>
                        </div>
                    </div>
                @elseif(auth()->user()->role === 'kader')
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-md">
                        <div class="flex items-start">
                            <i class="bi bi-info-circle-fill text-yellow-500 mr-3 mt-0.5"></i>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-yellow-900">Filter Otomatis Aktif</p>
                                <p class="text-xs text-yellow-700 mt-1">
                                    Anda hanya melihat pengajuan dengan status "Diproses" yang memerlukan verifikasi
                                    atau kunjungan lapangan
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
                @if (in_array(auth()->user()->role, ['ketua-posyandu', 'kades', 'admin']))
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-xs font-semibold text-gray-700 mb-2">Legenda Progress:</p>
                        <div class="flex flex-wrap gap-4 text-xs">
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center text-white">
                                    <i class="bi bi-check-lg text-xs"></i>
                                </span>
                                <span class="text-gray-600">Tahap Selesai</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-5 h-5 rounded-full bg-blue-500 flex items-center justify-center text-white">
                                    <i class="bi bi-hourglass-split text-xs"></i>
                                </span>
                                <span class="text-gray-600">Sedang Proses</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-5 h-5 rounded-full bg-gray-300 flex items-center justify-center text-gray-500">
                                    <i class="bi bi-lock-fill text-xs"></i>
                                </span>
                                <span class="text-gray-600">Belum Dimulai</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            @if (auth()->user()->role === 'kader' || auth()->user()->role === 'masyarakat')
                <div class="w-full sm:w-auto">
                    @if (auth()->user()->role === 'masyarakat')
                        <a href="{{ route('dashboard.partials.pilih-layanan') }}"
                            class="w-full sm:w-auto flex items-center justify-center px-4 py-2 bg-pink-500 text-white rounded-md text-sm font-semibold hover:bg-pink-600 transition-colors duration-150">
                            <i class="bi bi-plus-circle-fill mr-2"></i>
                            <span class="hidden sm:inline">Tambah Ajuan</span>
                            <span class="sm:hidden">Buat Ajuan Baru</span>
                        </a>
                    @else
                        <a href="{{ route('dashboard.partials.pilih-user') }}"
                            class="w-full sm:w-auto flex items-center justify-center px-4 py-2 bg-pink-500 text-white rounded-md text-sm font-semibold hover:bg-pink-600 transition-colors duration-150">
                            <i class="bi bi-plus-circle-fill mr-2"></i>
                            <span class="hidden sm:inline">Tambah Ajuan</span>
                            <span class="sm:hidden">Buat Ajuan Baru</span>
                        </a>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center justify-between mb-4 bg-gray-50 p-3 rounded-xl border border-gray-100">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-white rounded-lg shadow-sm">
                    <i
                        class="bi {{ $showArchived ? 'bi-archive-fill text-amber-500' : 'bi-inbox-fill text-blue-500' }}"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">
                        {{ $showArchived ? 'Arsip Pengajuan' : 'Pengajuan Aktif' }}
                    </h3>
                    <p class="text-xs text-gray-500">
                        {{ $showArchived ? 'Menampilkan data yang telah selesai Anda tindak lanjuti' : 'Menampilkan data yang memerlukan tindakan Anda' }}
                    </p>
                </div>
            </div>

            <button wire:click="toggleArchive" wire:loading.attr="disabled"
                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $showArchived ? 'bg-amber-500' : 'bg-gray-200' }} disabled:opacity-50">
                <span
                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $showArchived ? 'translate-x-5' : 'translate-x-0' }}"></span>
            </button>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 mb-6">
            <div class="w-full sm:w-auto sm:flex-1 sm:max-w-xs">
                <select wire:model.live="status"
                    class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-pink-500 focus:border-pink-500">
                    <option value="">Semua Status</option>
                    <option value="Diproses">Diproses</option>
                    <option value="Disetujui">Disetujui</option>
                    <option value="Ditolak">Ditolak</option>
                </select>
            </div>

            <div class="relative w-full sm:flex-1">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari berdasarkan nama..."
                    class="w-full pl-4 pr-10 py-2 border border-gray-300 rounded-md text-sm focus:ring-pink-500 focus:border-pink-500">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <i class="bi bi-search text-gray-400"></i>
                </div>
            </div>

            @if ($search || $status)
                <button wire:click="resetFilters" type="button"
                    class="w-full sm:w-auto px-4 py-2 text-sm text-gray-600 hover:text-gray-900 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors duration-150">
                    <i class="bi bi-arrow-clockwise mr-1"></i>
                    Reset Filter
                </button>
            @endif
        </div>

        @if (auth()->user()->role === 'admin-kecamatan' && auth()->user()->kecamatan)
            <div class="mb-4 p-3 bg-pink-50 border-l-4 border-pink-500 rounded-lg">
                <div class="flex items-start">
                    <i class="bi bi-info-circle-fill text-pink-500 mr-2 mt-0.5 flex-shrink-0"></i>
                    <p class="text-sm text-gray-700">
                        Anda mengelola pengajuan di <strong>Kecamatan {{ auth()->user()->kecamatan }}</strong>
                    </p>
                </div>
            </div>
        @endif

        @if (auth()->user()->role === 'kabid' && auth()->user()->kabupaten)
            <div class="mb-4 p-3 bg-pink-50 border-l-4 border-pink-500 rounded-lg">
                <div class="flex items-start">
                    <i class="bi bi-info-circle-fill text-pink-500 mr-2 mt-0.5 flex-shrink-0"></i>
                    <p class="text-sm text-gray-700">
                        Anda mengelola pengajuan di <strong>{{ auth()->user()->kabupaten }}</strong> untuk
                        <strong>{{ auth()->user()->bidang->nama_bidang }}</strong>
                    </p>
                </div>
            </div>
        @endif

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

        @if (auth()->user()->role === 'ketua-posyandu' && auth()->user()->posyandu)
            <div class="mb-4 p-3 bg-pink-50 border-l-4 border-pink-500 rounded-lg">
                <div class="flex items-start">
                    <i class="bi bi-info-circle-fill text-pink-500 mr-2 mt-0.5 flex-shrink-0"></i>
                    <p class="text-sm text-gray-700">
                        Anda mengelola pengajuan di <strong>{{ auth()->user()->posyandu->nama_posyandu }}</strong>
                    </p>
                </div>
            </div>
        @endif

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

        <div wire:loading class="mb-4">
            <div class="flex items-center justify-center p-4 bg-gray-50 rounded-lg">
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
        <div class="mt-6">
            {{ $semuaAjuan->links() }}
        </div>
    </div>
</div>
