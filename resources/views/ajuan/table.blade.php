{{-- DESKTOP VIEW: TABLE --}}
<div class="hidden md:block overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                    No</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                    Nama Pengaju</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                    Bidang</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                    Deskripsi Permohonan</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                    Tindak Lanjut Pengajuan</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                    Status Pengajuan</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                    Action</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($semuaAjuan as $ajuan)
                <tr>
                    <td class="px-2 md:px-4 py-1 md:py-2 whitespace-nowrap text-sm text-gray-500">
                        {{ ($semuaAjuan->currentPage() - 1) * $semuaAjuan->perPage() + $loop->iteration }}</td>
                    <td class="px-2 md:px-4 py-1 md:py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                        <div class="font-medium text-gray-900">
                            {{ $ajuan->user?->name ?? 'Pengguna Dihapus' }}</div>
                        <div class="text-base text-gray-500">
                            {{ $ajuan->user?->posyandu?->nama_posyandu ?? 'Belum Terdaftar' }}
                        </div>
                    </td>
                    <td class="px-2 md:px-4 py-1 md:py-2 whitespace-nowrap text-base font-medium text-gray-900">
                        {{ $ajuan->bidang->nama_bidang ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ \Illuminate\Support\Str::limit($ajuan->deskripsi_pengajuan ?? 'Tidak ada deskripsi', 30) }}
                    </td>
                    <td class="px-2 md:px-4 py-1 md:py-2 whitespace-nowrap text-base text-gray-500">
                        <div class="flex flex-col space-y-1">
                            @if ($ajuan->sudah_verifikasi)
                                <span
                                    class="px-1 md:px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Sudah Verifikasi
                                </span>
                            @else
                                <span
                                    class="px-1 md:px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Belum Verifikasi
                                </span>
                            @endif

                            @if ($ajuan->kunjungan_lapangan)
                                <span
                                    class="px-1 md:px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Kunjungan Lapangan
                                </span>
                            @else
                                <span
                                    class="px-1 md:px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Belum Kunjungan
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if ($ajuan->status_pengajuan == 'Disetujui')
                            <span
                                class="px-1 md:px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>
                        @elseif ($ajuan->status_pengajuan == 'Ditolak')
                            <span
                                class="px-1 md:px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                        @else
                            <span
                                class="px-1 md:px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Diproses</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-2">
                        <a href="{{ route('ajuan.show', $ajuan) }}"
                            class="flex items-center px-3 py-1 bg-blue-500 text-white rounded-md text-xs hover:bg-blue-600"><i
                                class="bi bi-eye-fill mr-1"></i> Detail</a>
                        <a href="/ajuan/cetak/{{ $ajuan->id }}" target="_blank"
                            class="flex items-center px-3 py-1 bg-green-500 text-white rounded-md text-xs hover:bg-green-600"><i
                                class="bi bi-printer-fill mr-1"></i> Cetak</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">Tidak ada
                        data pengajuan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- MOBILE VIEW: CARDS --}}
<div class="md:hidden space-y-4">
    @forelse ($semuaAjuan as $ajuan)
        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
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

                {{-- Tindak Lanjut Pengajuan --}}
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-32">
                        <span class="text-xs font-medium text-gray-500">Tindak Lanjut</span>
                    </div>
                    <div class="flex-1 space-y-1">
                        @if ($ajuan->sudah_verifikasi)
                            <span
                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Sudah Verifikasi
                            </span>
                        @else
                            <span
                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Belum Verifikasi
                            </span>
                        @endif

                        @if ($ajuan->kunjungan_lapangan)
                            <span
                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Kunjungan Lapangan
                            </span>
                        @else
                            <span
                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Belum Kunjungan
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Status Pengajuan --}}
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-32">
                        <span class="text-xs font-medium text-gray-500">Status</span>
                    </div>
                    <div class="flex-1">
                        @if ($ajuan->status_pengajuan == 'Disetujui')
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>
                        @elseif ($ajuan->status_pengajuan == 'Ditolak')
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                        @else
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Diproses</span>
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
