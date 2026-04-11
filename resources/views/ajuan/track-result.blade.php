<x-guest-layout>
    <div class="space-y-6">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Status Pengajuan</h2>
            <p class="text-gray-600 text-sm">Kode: <span
                    class="font-mono font-semibold">{{ $pengajuan->tracking_code }}</span></p>
        </div>

        <div class="flex justify-center">
            @php
                $statusConfig = [
                    'Diproses' => ['color' => 'yellow', 'icon' => 'hourglass-split', 'text' => 'Sedang Diproses'],
                    'Disetujui' => ['color' => 'green', 'icon' => 'check-circle-fill', 'text' => 'Disetujui'],
                    'Ditolak' => ['color' => 'red', 'icon' => 'x-circle-fill', 'text' => 'Ditolak'],
                ];
                
                // Determine display status based on workflow
                if ($pengajuan->status_pengajuan === 'Disetujui') {
                    $status = $statusConfig['Disetujui'];
                } elseif ($pengajuan->status_pengajuan === 'Ditolak') {
                    $status = $statusConfig['Ditolak'];
                } elseif ($pengajuan->submitted_to_desa) {
                    $status = ['color' => 'indigo', 'icon' => 'send', 'text' => 'Diajukan ke Desa'];
                } elseif ($pengajuan->approved_by_ketua) {
                    $status = ['color' => 'blue', 'icon' => 'check-circle', 'text' => 'Disetujui Ketua Posyandu'];
                } else {
                    $status = $statusConfig['Diproses'];
                }
            @endphp

            <div
                class="inline-flex items-center px-6 py-3 bg-{{ $status['color'] }}-100 text-{{ $status['color'] }}-800 rounded-full border-2 border-{{ $status['color'] }}-300">
                <i class="bi bi-{{ $status['icon'] }} text-2xl mr-3"></i>
                <span class="font-bold text-lg">{{ $status['text'] }}</span>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="bi bi-file-text text-pink-500 mr-2"></i>
                Informasi Pengajuan
            </h3>

            <div class="space-y-3">
                <div class="grid grid-cols-3 gap-2">
                    <span class="text-sm text-gray-600">Nama Pemohon:</span>
                    <span class="col-span-2 text-sm font-medium text-gray-800">{{ $pengajuan->user->name }}</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <span class="text-sm text-gray-600">Bidang:</span>
                    <span
                        class="col-span-2 text-sm font-medium text-gray-800">{{ $pengajuan->bidang->nama_bidang }}</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <span class="text-sm text-gray-600">Tanggal Pengajuan:</span>
                    <span class="col-span-2 text-sm font-medium text-gray-800">
                        {{ $pengajuan->tanggal_permohonan ? $pengajuan->tanggal_permohonan->format('d M Y H:i') : '-' }}
                    </span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <span class="text-sm text-gray-600">Deskripsi:</span>
                    <span
                        class="col-span-2 text-sm font-medium text-gray-800">{{ $pengajuan->deskripsi_pengajuan }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="bi bi-clock-history text-pink-500 mr-2"></i>
                Timeline Proses
            </h3>

            <div class="relative">
                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                <div class="space-y-6 relative">
                    <div class="flex items-start">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-green-500 text-white z-10">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-semibold text-gray-800">Pengajuan Dibuat</p>
                            <p class="text-xs text-gray-500">
                                {{ $pengajuan->created_at->format('d M Y H:i') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div
                            class="flex items-center justify-center w-8 h-8 rounded-full z-10 {{ $pengajuan->approved_by_ketua || $pengajuan->submitted_to_desa || $pengajuan->status_pengajuan === 'Disetujui' ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-500' }}">
                            @if ($pengajuan->approved_by_ketua || $pengajuan->submitted_to_desa || $pengajuan->status_pengajuan === 'Disetujui')
                                <i class="bi bi-check-lg"></i>
                            @else
                                <i class="bi bi-hourglass-split"></i>
                            @endif
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-semibold text-gray-800">Diproses Kader</p>
                            <p class="text-xs text-gray-500">
                                @if ($pengajuan->approved_by_ketua || $pengajuan->submitted_to_desa || $pengajuan->status_pengajuan === 'Disetujui')
                                    Verifikasi selesai
                                @else
                                    Menunggu verifikasi kader
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div
                            class="flex items-center justify-center w-8 h-8 rounded-full z-10 {{ $pengajuan->approved_by_ketua ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-500' }}">
                            @if ($pengajuan->approved_by_ketua)
                                <i class="bi bi-check-lg"></i>
                            @else
                                <i class="bi bi-hourglass-split"></i>
                            @endif
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-semibold text-gray-800">Persetujuan Ketua Posyandu</p>
                            <p class="text-xs text-gray-500">
                                @if ($pengajuan->approved_by_ketua)
                                    Disetujui pada
                                    {{ $pengajuan->approved_by_ketua_at ? $pengajuan->approved_by_ketua_at->format('d M Y H:i') : '-' }}
                                @else
                                    Menunggu persetujuan Ketua Posyandu
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div
                            class="flex items-center justify-center w-8 h-8 rounded-full z-10 {{ $pengajuan->submitted_to_desa || $pengajuan->status_pengajuan === 'Disetujui' ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-500' }}">
                            @if ($pengajuan->submitted_to_desa || $pengajuan->status_pengajuan === 'Disetujui')
                                <i class="bi bi-check-lg"></i>
                            @else
                                <i class="bi bi-hourglass-split"></i>
                            @endif
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-semibold text-gray-800">Diajukan ke Pemerintah Desa</p>
                            <p class="text-xs text-gray-500">
                                @if ($pengajuan->submitted_to_desa || $pengajuan->status_pengajuan === 'Disetujui')
                                    Pengajuan telah diteruskan ke pemdes
                                @else
                                    Belum diajukan ke pemdes
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div
                            class="flex items-center justify-center w-8 h-8 rounded-full z-10 {{ $pengajuan->status_pengajuan === 'Disetujui' ? 'bg-green-500 text-white' : ($pengajuan->status_pengajuan === 'Ditolak' ? 'bg-red-500 text-white' : 'bg-gray-200 text-gray-500') }}">
                            @if ($pengajuan->status_pengajuan === 'Disetujui')
                                <i class="bi bi-check-lg"></i>
                            @elseif($pengajuan->status_pengajuan === 'Ditolak')
                                <i class="bi bi-x-lg"></i>
                            @else
                                <i class="bi bi-hourglass-split"></i>
                            @endif
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-semibold text-gray-800">Keputusan Akhir</p>
                            <p class="text-xs text-gray-500">
                                @if ($pengajuan->status_pengajuan === 'Disetujui')
                                    Pengajuan disetujui oleh Kepala Desa
                                @elseif($pengajuan->status_pengajuan === 'Ditolak')
                                    Pengajuan ditolak
                                @else
                                    Menunggu keputusan kepala desa
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($pengajuan->status_pengajuan === 'Ditolak')
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                <div class="flex items-start">
                    <i class="bi bi-exclamation-triangle-fill text-red-500 mr-3 mt-0.5"></i>
                    <div class="text-sm text-red-700">
                        <p class="font-semibold mb-1">Pengajuan Ditolak</p>
                        <p>Silakan hubungi kader atau posyandu terdekat untuk informasi lebih lanjut.</p>
                    </div>
                </div>
            </div>
        @endif

        @if ($pengajuan->status_pengajuan === 'Disetujui')
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                <div class="flex items-start">
                    <i class="bi bi-check-circle-fill text-green-500 mr-3 mt-0.5"></i>
                    <div class="text-sm text-green-700">
                        <p class="font-semibold mb-1">Pengajuan Disetujui</p>
                        <p>Pengajuan Anda telah disetujui. Silakan hubungi pemerintah desa untuk proses selanjutnya.</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('login') }}"
                class="flex-1 px-4 py-3 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-center font-medium transition-colors">
                <i class="bi bi-arrow-left mr-2"></i>
                Kembali ke Login
            </a>
            <button onclick="window.print()"
                class="flex-1 px-4 py-3 bg-pink-600 text-white rounded-md hover:bg-pink-700 text-center font-medium transition-colors">
                <i class="bi bi-printer mr-2"></i>
                Cetak Status
            </button>
        </div>

        <div class="text-center text-sm text-gray-600">
            <p>Butuh bantuan? Hubungi posyandu terdekat atau</p>
            <a href="tel:+62123456789" class="text-pink-600 hover:text-pink-700 font-medium">
                <i class="bi bi-telephone-fill mr-1"></i>
                +62 123 456 789
            </a>
        </div>
    </div>

    @push('styles')
        <style>
            @media print {
                .no-print {
                    display: none !important;
                }

                body {
                    background: white;
                }
            }
        </style>
    @endpush
</x-guest-layout>
