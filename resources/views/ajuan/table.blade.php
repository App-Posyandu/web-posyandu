{{-- DESKTOP VIEW: TABLE --}}
<div class="hidden md:block overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">No</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Nama Pemohon
                </th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Bidang</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Deskripsi
                    Permohonan</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Tindak Lanjut
                </th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Status
                    Pengajuan</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Action</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($semuaAjuan as $ajuan)
                <tr class="{{ $ajuan->has_been_revised_by_user ? 'bg-blue-50' : '' }}">
                    <td class="px-2 md:px-4 py-1 md:py-2 whitespace-nowrap text-sm text-gray-500">
                        {{ ($semuaAjuan->currentPage() - 1) * $semuaAjuan->perPage() + $loop->iteration }}
                    </td>
                    <td class="px-2 md:px-4 py-1 md:py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                        <div class="font-medium text-gray-900">
                            {{ $ajuan->user?->name ?? 'Pengguna Dihapus' }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $ajuan->user?->posyandu?->nama_posyandu ?? 'Belum Terdaftar' }}
                        </div>
                        <div class="text-xs text-gray-400">
                            RW {{ $ajuan->user?->rw ?? '-' }} / RT {{ $ajuan->user?->rt ?? '-' }}
                        </div>
                    </td>
                    <td class="px-2 md:px-4 py-1 md:py-2 whitespace-nowrap text-base font-medium text-gray-900">
                        {{ $ajuan->bidang->nama_bidang ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ \Illuminate\Support\Str::limit($ajuan->deskripsi_pengajuan ?? 'Tidak ada deskripsi', 30) }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ \Illuminate\Support\Str::limit($ajuan->tindak_lanjut ?? '-', 30) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="space-y-2">
                            {{-- Status Utama --}}
                            @if ($ajuan->status_pengajuan == 'Disetujui')
                                <span
                                    class="px-1 md:px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Disetujui
                                </span>
                            @elseif ($ajuan->status_pengajuan == 'Ditolak')
                                <span
                                    class="px-1 md:px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    Ditolak
                                </span>
                            @elseif ($ajuan->status_pengajuan == 'Sesuai')
                                <span
                                    class="px-1 md:px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Sesuai
                                </span>
                            @elseif ($ajuan->status_pengajuan == 'Diajukan ke Desa')
                                <span
                                    class="px-1 md:px-3 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                    Diajukan ke Desa
                                </span>
                            @else
                                <span
                                    class="px-1 md:px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Diproses
                                </span>
                            @endif

                            {{-- ✅ PROGRESS INDICATOR - Hanya untuk status "Diproses" --}}
                            @if ($ajuan->status_pengajuan == 'Diproses')
                                <div class="flex items-center gap-1 text-xs">
                                    {{-- Tahap 1: Verifikasi --}}
                                    @if ($ajuan->sudah_verifikasi)
                                        <span
                                            class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center text-white"
                                            title="Verifikasi Selesai">
                                            <i class="bi bi-check-lg text-xs"></i>
                                        </span>
                                    @else
                                        <span
                                            class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center text-white animate-pulse"
                                            title="Sedang Verifikasi">
                                            <i class="bi bi-hourglass-split text-xs"></i>
                                        </span>
                                    @endif

                                    <div
                                        class="w-2 h-0.5 {{ $ajuan->sudah_verifikasi ? 'bg-green-500' : 'bg-gray-300' }}">
                                    </div>

                                    {{-- Tahap 2: Kunjungan --}}
                                    @if ($ajuan->kunjungan_lapangan)
                                        <span
                                            class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center text-white"
                                            title="Kunjungan Selesai">
                                            <i class="bi bi-check-lg text-xs"></i>
                                        </span>
                                    @elseif ($ajuan->sudah_verifikasi)
                                        <span
                                            class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center text-white animate-pulse"
                                            title="Menunggu Kunjungan">
                                            <i class="bi bi-hourglass-split text-xs"></i>
                                        </span>
                                    @else
                                        <span
                                            class="w-6 h-6 rounded-full bg-gray-300 flex items-center justify-center text-gray-500"
                                            title="Belum Sampai Tahap Ini">
                                            <i class="bi bi-lock-fill text-xs"></i>
                                        </span>
                                    @endif

                                    <div
                                        class="w-2 h-0.5 {{ $ajuan->kunjungan_lapangan ? 'bg-green-500' : 'bg-gray-300' }}">
                                    </div>

                                    {{-- Tahap 3: Ketua Posyandu --}}
                                    @if ($ajuan->approved_by_ketua)
                                        <span
                                            class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center text-white"
                                            title="Disetujui Ketua">
                                            <i class="bi bi-check-lg text-xs"></i>
                                        </span>
                                    @elseif ($ajuan->kunjungan_lapangan)
                                        <span
                                            class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center text-white animate-pulse"
                                            title="Menunggu Persetujuan Ketua">
                                            <i class="bi bi-hourglass-split text-xs"></i>
                                        </span>
                                    @else
                                        <span
                                            class="w-6 h-6 rounded-full bg-gray-300 flex items-center justify-center text-gray-500"
                                            title="Belum Sampai Tahap Ini">
                                            <i class="bi bi-lock-fill text-xs"></i>
                                        </span>
                                    @endif
                                </div>
                            @endif

                            {{-- Badge Revisi --}}
                            @if ($ajuan->status_pengajuan != 'Ditolak')
                                {{-- Badge: Sudah Direvisi oleh User --}}
                                @if (isset($ajuan->has_been_revised_by_user) && $ajuan->has_been_revised_by_user)
                                    <span
                                        class="block px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-300">
                                        <i class="bi bi-arrow-clockwise"></i> Sudah Direvisi
                                    </span>
                                @endif

                                {{-- Badge: Menunggu Revisi --}}
                                @if (isset($ajuan->is_waiting_revision) && $ajuan->is_waiting_revision)
                                    <span
                                        class="block px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800 animate-pulse">
                                        <i class="bi bi-hourglass-split"></i> Menunggu Revisi
                                    </span>
                                @endif
                            @endif

                            {{-- Badge: Jumlah Revisi --}}
                            @if ($ajuan->revision_count > 0)
                                <span
                                    class="block px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Revisi {{ $ajuan->revision_count }}x
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-2">
                        <a href="{{ route('ajuan.show', $ajuan) }}"
                            class="flex items-center px-3 py-1 bg-blue-500 text-white rounded-md text-xs hover:bg-blue-600">
                            <i class="bi bi-eye-fill mr-1"></i> Detail
                        </a>
                        <a href="/ajuan/cetak/{{ $ajuan->id }}" target="_blank"
                            class="flex items-center px-3 py-1 bg-green-500 text-white rounded-md text-xs hover:bg-green-600">
                            <i class="bi bi-printer-fill mr-1"></i> Cetak
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">Tidak ada data pengajuan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- MOBILE VIEW: CARDS --}}
<div class="md:hidden space-y-4">
    @forelse ($semuaAjuan as $ajuan)
        <div
            class="bg-white rounded-lg shadow-md border {{ $ajuan->has_been_revised_by_user ? 'border-blue-300 ring-2 ring-blue-100' : 'border-gray-200' }} overflow-hidden">
            {{-- Card Header --}}
            <div class="bg-gradient-to-r from-pink-50 to-purple-50 px-4 py-3 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div
                            class="h-10 w-10 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 font-bold text-sm">
                            {{ strtoupper(substr($ajuan->user?->name ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">
                                {{ $ajuan->user?->name ?? 'Pengguna Dihapus' }}
                            </h3>
                            <p class="text-xs text-gray-500">
                                {{ $ajuan->user?->posyandu?->nama_posyandu ?? 'Belum Terdaftar' }}
                            </p>
                            <p class="text-xs text-gray-400">
                                RW {{ $ajuan->user?->rw ?? '-' }} / RT {{ $ajuan->user?->rt ?? '-' }}
                            </p>
                        </div>
                    </div>
                    <span class="text-xs font-medium text-gray-500">
                        #{{ ($semuaAjuan->currentPage() - 1) * $semuaAjuan->perPage() + $loop->iteration }}
                    </span>
                </div>
            </div>

            {{-- Card Body --}}
            <div class="px-4 py-3 space-y-3">
                {{-- Bidang --}}
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-32">
                        <span class="text-xs font-medium text-gray-500">Bidang</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">
                            {{ $ajuan->bidang->nama_bidang ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                {{-- Deskripsi Permohonan --}}
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-32">
                        <span class="text-xs font-medium text-gray-500">Deskripsi</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-700">
                            {{ \Illuminate\Support\Str::limit($ajuan->deskripsi_pengajuan ?? 'Tidak ada deskripsi', 80) }}
                        </p>
                    </div>
                </div>

                {{-- Tindak Lanjut --}}
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-32">
                        <span class="text-xs font-medium text-gray-500">Tindak Lanjut</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-700">
                            {{ \Illuminate\Support\Str::limit($ajuan->tindak_lanjut ?? '-', 80) }}
                        </p>
                    </div>
                </div>

                {{-- Status Pengajuan --}}
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-32">
                        <span class="text-xs font-medium text-gray-500">Status</span>
                    </div>
                    <div class="flex-1 space-y-1">
                        {{-- Status Utama --}}
                        @if ($ajuan->status_pengajuan == 'Disetujui')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                Disetujui
                            </span>
                        @elseif ($ajuan->status_pengajuan == 'Ditolak')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                Ditolak
                            </span>
                        @elseif ($ajuan->status_pengajuan == 'Sesuai')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                Sesuai
                            </span>
                        @elseif ($ajuan->status_pengajuan == 'Diajukan ke Desa')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                Diajukan ke Desa
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Diproses
                            </span>
                        @endif

                        {{-- Badge: Sudah Direvisi oleh User --}}
                        @if ($ajuan->has_been_revised_by_user)
                            <span
                                class="block px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-300">
                                <i class="bi bi-arrow-clockwise"></i> Sudah Direvisi
                            </span>
                        @endif

                        {{-- Badge: Menunggu Revisi --}}
                        @if ($ajuan->is_waiting_revision)
                            <span
                                class="block px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800 animate-pulse">
                                <i class="bi bi-hourglass-split"></i> Menunggu Revisi
                            </span>
                        @endif

                        {{-- Badge: Jumlah Revisi --}}
                        @if ($ajuan->revision_count > 0)
                            <span
                                class="block px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Revisi {{ $ajuan->revision_count }}x
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Card Footer - Action Buttons --}}
            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 flex gap-2">
                <a href="{{ route('ajuan.show', $ajuan) }}"
                    class="flex-1 flex items-center justify-center px-3 py-2 bg-blue-500 text-white rounded-md text-xs font-medium hover:bg-blue-600 transition-colors duration-150">
                    <i class="bi bi-eye-fill mr-1"></i>
                    Detail
                </a>
                <a href="/ajuan/cetak/{{ $ajuan->id }}" target="_blank"
                    class="flex-1 flex items-center justify-center px-3 py-2 bg-green-500 text-white rounded-md text-xs font-medium hover:bg-green-600 transition-colors duration-150">
                    <i class="bi bi-printer-fill mr-1"></i>
                    Cetak
                </a>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <div class="flex flex-col items-center justify-center">
                <i class="bi bi-inbox text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-base font-medium">Tidak ada data pengajuan</p>
            </div>
        </div>
    @endforelse
</div>
