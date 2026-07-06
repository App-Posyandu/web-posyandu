<div class="hidden md:block overflow-x-auto w-full">
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
                            {{ $ajuan->user?->name ? $ajuan->user->name : 'Pengguna Dihapus' }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $ajuan->user?->posyandu?->nama_posyandu ? $ajuan->user->posyandu->nama_posyandu : 'Belum Terdaftar' }}
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
                            @elseif ($ajuan->submitted_to_desa)
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

                            @if ($ajuan->status_pengajuan == 'Diproses')
                                <div class="flex items-center gap-1 text-xs">
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

                            @if ($ajuan->status_pengajuan != 'Ditolak')
                                @if (isset($ajuan->has_been_revised_by_user) && $ajuan->has_been_revised_by_user)
                                    <span
                                        class="block px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-300">
                                        <i class="bi bi-arrow-clockwise"></i> Sudah Direvisi
                                    </span>
                                @endif

                                @if (isset($ajuan->is_waiting_revision) && $ajuan->is_waiting_revision)
                                    <span
                                        class="block px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800 animate-pulse">
                                        <i class="bi bi-hourglass-split"></i> Menunggu Revisi
                                    </span>
                                @endif
                            @endif

                            @if ($ajuan->revision_count > 0)
                                <span
                                    class="block px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Revisi {{ $ajuan->revision_count }}x
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-2">

                        <a href="/ajuan/cetak/{{ $ajuan->id }}" target="_blank"
                            class="flex items-center px-3 py-1 bg-green-500 text-white rounded-md text-xs hover:bg-green-600">
                            <i class="bi bi-printer-fill mr-1"></i> Cetak
                        </a>
                        @if (auth()->user()->role === 'ketua-posyandu' && $ajuan->status_pengajuan === 'Diproses')
                            @if (!$ajuan->sudah_verifikasi)
                                <a href="{{ route('ajuan.show', $ajuan) }}"
                                    class="px-3 py-1 bg-orange-500 text-white rounded-md text-xs hover:bg-orange-600 transition">
                                    <i class="bi bi-shield-shaded mr-1"></i> Takeover & Verifikasi
                                </a>
                            @elseif (!$ajuan->kunjungan_lapangan)
                                <a href="{{ route('ajuan.show', $ajuan) }}"
                                    class="px-3 py-1 bg-purple-500 text-white rounded-md text-xs hover:bg-purple-600 transition">
                                    <i class="bi bi-arrow-right-circle-fill mr-1"></i> Tindak Lanjut
                                </a>
                            @else
                                <a href="{{ route('ajuan.show', $ajuan) }}"
                                    class="flex items-center px-3 py-1 bg-blue-500 text-white rounded-md text-xs hover:bg-blue-600">
                                    <i class="bi bi-eye-fill mr-1"></i> Detail
                                </a>
                            @endif
                        @elseif (auth()->user()->role === 'ketua-timpembina-posyandu')
                            <a href="{{ route('ajuan.show', $ajuan) }}"
                                class="flex items-center px-3 py-1 bg-blue-500 text-white rounded-md text-xs hover:bg-blue-600">
                                <i class="bi bi-eye-fill mr-1"></i> Detail
                            </a>
                        @elseif (auth()->user()->role === 'kades' && ($ajuan->submitted_to_desa || $ajuan->approved_by_ketua))
                            @if ($ajuan->status_pengajuan === 'Diproses')
                                <a href="{{ route('ajuan.show', $ajuan) }}"
                                    class="px-3 py-1 bg-purple-500 text-white rounded-md text-xs hover:bg-purple-600 transition">
                                    <i class="bi bi-arrow-right-circle-fill mr-1"></i> Tindak Lanjut
                                </a>
                            @else
                                <a href="{{ route('ajuan.show', $ajuan) }}"
                                    class="flex items-center px-3 py-1 bg-blue-500 text-white rounded-md text-xs hover:bg-blue-600">
                                    <i class="bi bi-eye-fill mr-1"></i> Detail
                                </a>
                            @endif
                        @else
                            <a href="{{ route('ajuan.show', $ajuan) }}"
                                class="flex items-center px-3 py-1 bg-blue-500 text-white rounded-md text-xs hover:bg-blue-600">
                                <i class="bi bi-eye-fill mr-1"></i> Detail
                            </a>
                        @endif
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

<div class="block md:hidden space-y-4 mt-4">
    @forelse($semuaAjuan as $index => $ajuan)
        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden {{ $ajuan->has_been_revised_by_user ? 'bg-blue-50' : '' }}">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-3 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-lg">
                            {{ strtoupper(substr($ajuan->user?->name ?? '?', 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">{{ $ajuan->user?->name ?? 'Pengguna Dihapus' }}</h3>
                            <p class="text-xs text-gray-500">
                                <i class="bi bi-geo-alt-fill text-gray-400 mr-1"></i>
                                {{ $ajuan->user?->posyandu?->nama_posyandu ?? 'Belum Terdaftar' }}
                            </p>
                        </div>
                    </div>
                    <span class="text-xs font-medium text-gray-500">
                        #{{ ($semuaAjuan->currentPage() - 1) * $semuaAjuan->perPage() + $loop->iteration }}
                    </span>
                </div>
            </div>

            <div class="px-4 py-3 space-y-3">
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-24 text-xs font-medium text-gray-500">Bidang</div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ $ajuan->bidang->nama_bidang ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ \Illuminate\Support\Str::limit($ajuan->deskripsi_pengajuan ?? 'Tidak ada deskripsi', 50) }}</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="flex-shrink-0 w-24 text-xs font-medium text-gray-500">Status</div>
                    <div class="flex-1 flex flex-col gap-1">
                        <div>
                            @if ($ajuan->status_pengajuan == 'Disetujui')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>
                            @elseif ($ajuan->status_pengajuan == 'Ditolak')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                            @elseif ($ajuan->submitted_to_desa)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Diajukan ke Desa</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Diproses</span>
                            @endif
                        </div>
                        
                        @if ($ajuan->status_pengajuan == 'Diproses')
                            <div class="flex items-center gap-1 text-xs mt-1">
                                @if ($ajuan->sudah_verifikasi)
                                    <span class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center text-white"><i class="bi bi-check-lg text-[10px]"></i></span>
                                @else
                                    <span class="w-5 h-5 rounded-full bg-blue-500 flex items-center justify-center text-white animate-pulse"><i class="bi bi-hourglass-split text-[10px]"></i></span>
                                @endif
                                <div class="w-2 h-0.5 {{ $ajuan->sudah_verifikasi ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                                @if ($ajuan->kunjungan_lapangan)
                                    <span class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center text-white"><i class="bi bi-check-lg text-[10px]"></i></span>
                                @elseif ($ajuan->sudah_verifikasi)
                                    <span class="w-5 h-5 rounded-full bg-blue-500 flex items-center justify-center text-white animate-pulse"><i class="bi bi-hourglass-split text-[10px]"></i></span>
                                @else
                                    <span class="w-5 h-5 rounded-full bg-gray-300 flex items-center justify-center text-gray-500"><i class="bi bi-lock-fill text-[10px]"></i></span>
                                @endif
                                <div class="w-2 h-0.5 {{ $ajuan->kunjungan_lapangan ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                                @if ($ajuan->approved_by_ketua)
                                    <span class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center text-white"><i class="bi bi-check-lg text-[10px]"></i></span>
                                @elseif ($ajuan->kunjungan_lapangan)
                                    <span class="w-5 h-5 rounded-full bg-blue-500 flex items-center justify-center text-white animate-pulse"><i class="bi bi-hourglass-split text-[10px]"></i></span>
                                @else
                                    <span class="w-5 h-5 rounded-full bg-gray-300 flex items-center justify-center text-gray-500"><i class="bi bi-lock-fill text-[10px]"></i></span>
                                @endif
                            </div>
                        @endif

                        @if ($ajuan->status_pengajuan != 'Ditolak')
                            @if (isset($ajuan->has_been_revised_by_user) && $ajuan->has_been_revised_by_user)
                                <span class="inline-block mt-1 px-2 py-1 text-[10px] font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-300">
                                    <i class="bi bi-arrow-clockwise"></i> Sudah Direvisi
                                </span>
                            @endif
                            @if (isset($ajuan->is_waiting_revision) && $ajuan->is_waiting_revision)
                                <span class="inline-block mt-1 px-2 py-1 text-[10px] font-semibold rounded-full bg-orange-100 text-orange-800 animate-pulse">
                                    <i class="bi bi-hourglass-split"></i> Menunggu Revisi
                                </span>
                            @endif
                        @endif
                        @if ($ajuan->revision_count > 0)
                            <span class="inline-block mt-1 px-2 py-1 text-[10px] font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Revisi {{ $ajuan->revision_count }}x
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="/ajuan/cetak/{{ $ajuan->id }}" target="_blank"
                        class="flex items-center px-3 py-1.5 bg-green-500 text-white rounded-md text-xs font-medium hover:bg-green-600">
                        <i class="bi bi-printer-fill mr-1"></i> Cetak
                    </a>
                    @if (auth()->user()->role === 'ketua-posyandu' && $ajuan->status_pengajuan === 'Diproses')
                        @if (!$ajuan->sudah_verifikasi)
                            <a href="{{ route('ajuan.show', $ajuan) }}"
                                class="px-3 py-1.5 bg-orange-500 text-white rounded-md text-xs font-medium hover:bg-orange-600">
                                <i class="bi bi-shield-shaded mr-1"></i> Takeover & Verifikasi
                            </a>
                        @elseif (!$ajuan->kunjungan_lapangan)
                            <a href="{{ route('ajuan.show', $ajuan) }}"
                                class="px-3 py-1.5 bg-purple-500 text-white rounded-md text-xs font-medium hover:bg-purple-600">
                                <i class="bi bi-arrow-right-circle-fill mr-1"></i> Tindak Lanjut
                            </a>
                        @else
                            <a href="{{ route('ajuan.show', $ajuan) }}"
                                class="flex items-center px-3 py-1.5 bg-blue-500 text-white rounded-md text-xs font-medium hover:bg-blue-600">
                                <i class="bi bi-eye-fill mr-1"></i> Detail
                            </a>
                        @endif
                    @elseif (auth()->user()->role === 'ketua-timpembina-posyandu')
                        <a href="{{ route('ajuan.show', $ajuan) }}"
                            class="flex items-center px-3 py-1.5 bg-blue-500 text-white rounded-md text-xs font-medium hover:bg-blue-600">
                            <i class="bi bi-eye-fill mr-1"></i> Detail
                        </a>
                    @elseif (auth()->user()->role === 'kades' && ($ajuan->submitted_to_desa || $ajuan->approved_by_ketua))
                        @if ($ajuan->status_pengajuan === 'Diproses')
                            <a href="{{ route('ajuan.show', $ajuan) }}"
                                class="px-3 py-1.5 bg-purple-500 text-white rounded-md text-xs font-medium hover:bg-purple-600">
                                <i class="bi bi-arrow-right-circle-fill mr-1"></i> Tindak Lanjut
                            </a>
                        @else
                            <a href="{{ route('ajuan.show', $ajuan) }}"
                                class="flex items-center px-3 py-1.5 bg-blue-500 text-white rounded-md text-xs font-medium hover:bg-blue-600">
                                <i class="bi bi-eye-fill mr-1"></i> Detail
                            </a>
                        @endif
                    @else
                        <a href="{{ route('ajuan.show', $ajuan) }}"
                            class="flex items-center px-3 py-1.5 bg-blue-500 text-white rounded-md text-xs font-medium hover:bg-blue-600">
                            <i class="bi bi-eye-fill mr-1"></i> Detail
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-4 text-gray-500 bg-white rounded-lg shadow-sm border border-gray-100">
            Tidak ada data pengajuan.
        </div>
    @endforelse
</div>
