<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    No
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Nama Pemohon
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Bidang
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Deskripsi
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tanggal
                </th>
                <th scope="col"
                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Aksi
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($semuaAjuan as $index => $ajuan)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $semuaAjuan->firstItem() + $index }}
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div
                                    class="h-10 w-10 rounded-full bg-gradient-to-br from-pink-400 to-purple-500 flex items-center justify-center text-white font-bold">
                                    {{ strtoupper(substr($ajuan->user?->name ?? 'U', 0, 1)) }}
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $ajuan->user?->name ?? 'Pengguna Dihapus' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    RW {{ $ajuan->user?->rw ?? '-' }} / RT {{ $ajuan->user?->rt ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            @php
                                $bidangIcons = [
                                    'kesehatan' => 'bi-heart-pulse-fill text-pink-500',
                                    'pekerjaan-umum' => 'bi-tools text-green-500',
                                    'pendidikan' => 'bi-book-fill text-orange-500',
                                    'perumahan-rakyat' => 'bi-house-fill text-blue-500',
                                    'sosial' => 'bi-people-fill text-rose-500',
                                    'trantibumlinmas' => 'bi-shield-fill-check text-yellow-500',
                                ];
                                $bidangSlug = $ajuan->bidang
                                    ? \Illuminate\Support\Str::slug(
                                        str_replace('Bidang ', '', $ajuan->bidang->nama_bidang),
                                    )
                                    : 'default';
                                $iconClass = $bidangIcons[$bidangSlug] ?? 'bi-folder-fill text-gray-500';
                            @endphp
                            <i class="bi {{ $iconClass }} text-lg mr-2"></i>
                            <span
                                class="text-sm text-gray-900">{{ $ajuan->bidang?->nama_bidang ?? 'Tidak Ada Bidang' }}</span>
                        </div>
                    </td>

                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs">
                        <div class="line-clamp-2" title="{{ $ajuan->deskripsi_pengajuan }}">
                            {{ \Illuminate\Support\Str::limit($ajuan->deskripsi_pengajuan, 60) }}
                        </div>
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col gap-1">
                            @if ($ajuan->status_pengajuan == 'Disetujui')
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="bi bi-check-circle-fill mr-1"></i>
                                    Disetujui
                                </span>
                            @elseif ($ajuan->status_pengajuan == 'Ditolak')
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="bi bi-x-circle-fill mr-1"></i>
                                    Ditolak
                                </span>
                            @elseif ($ajuan->submitted_to_desa)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    <i class="bi bi-send-fill mr-1"></i>
                                    Diajukan ke Desa
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="bi bi-clock-fill mr-1"></i>
                                    Diproses
                                </span>
                            @endif

                            @if ($ajuan->revision_count > 0)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    <i class="bi bi-arrow-repeat mr-1"></i>
                                    {{ $ajuan->revision_count }}x Revisi
                                </span>
                            @endif

                            @if (isset($ajuan->is_waiting_revision) && $ajuan->is_waiting_revision)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="bi bi-exclamation-triangle-fill mr-1"></i>
                                    Menunggu Revisi
                                </span>
                            @elseif (isset($ajuan->has_been_revised_by_user) && $ajuan->has_been_revised_by_user)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="bi bi-check2-circle mr-1"></i>
                                    Sudah Direvisi
                                </span>
                            @endif
                        </div>
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <div class="flex flex-col">
                            <span
                                class="font-medium">{{ \Carbon\Carbon::parse($ajuan->tanggal_permohonan ?? $ajuan->created_at)->format('d M Y') }}</span>
                            <span
                                class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($ajuan->created_at)->format('H:i') }}</span>
                        </div>
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                        <a href="{{ route('ajuan.show', $ajuan) }}"
                            class="inline-flex items-center px-3 py-1.5 bg-pink-500 text-white rounded-md hover:bg-pink-600 transition-colors">
                            <i class="bi bi-eye-fill mr-1"></i>
                            Detail
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center text-gray-400">
                            <i class="bi bi-inbox text-6xl mb-4"></i>
                            <p class="text-lg font-medium text-gray-500">Tidak ada pengajuan</p>
                            <p class="text-sm text-gray-400 mt-1">Data pengajuan akan muncul di sini</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($semuaAjuan->hasPages())
    <div class="mt-4 px-6 py-3 bg-gray-50 border-t border-gray-200">
        {{ $semuaAjuan->links() }}
    </div>
@endif
