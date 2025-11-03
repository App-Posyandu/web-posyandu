<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    No</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Nama Pengaju</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Bidang</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Deskripsi Permohonan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tindak Lanjut Pengajuan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status Pengajuan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Action</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($semuaAjuan as $ajuan)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ ($semuaAjuan->currentPage() - 1) * $semuaAjuan->perPage() + $loop->iteration }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <div class="font-medium text-gray-900">
                            {{ $ajuan->user?->name ?? 'Pengguna Dihapus' }}</div>
                        <div class="text-sm text-gray-500">
                            {{ $ajuan->user?->posyandu?->nama_posyandu ?? 'Belum Terdaftar' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $ajuan->bidang->nama_bidang ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ \Illuminate\Support\Str::limit($ajuan->deskripsi_pengajuan ?? 'Tidak ada deskripsi', 50) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <div class="flex flex-col space-y-1">
                            @if ($ajuan->sudah_verifikasi)
                                <span
                                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Sudah Verifikasi
                                </span>
                            @else
                                <span
                                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Belum Verifikasi
                                </span>
                            @endif

                            @if ($ajuan->kunjungan_lapangan)
                                <span
                                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Kunjungan Lapangan
                                </span>
                            @else
                                <span
                                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Belum Kunjungan
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if ($ajuan->status_pengajuan == 'Disetujui')
                            <span
                                class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>
                        @elseif ($ajuan->status_pengajuan == 'Ditolak')
                            <span
                                class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                        @else
                            <span
                                class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Diproses</span>
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
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada
                        data pengajuan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
