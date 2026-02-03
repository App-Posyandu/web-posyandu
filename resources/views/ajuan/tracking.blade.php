<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Ajuan - {{ $ajuan->tracking_code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>

<body class="bg-gradient-to-br from-pink-100 via-purple-100 to-blue-100 min-h-screen">

    <div class="container mx-auto px-4 py-8 max-w-4xl">

        <div class="mb-6">
            <a href="{{ route('landing') }}"
                class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 transition-colors">
                <i class="bi bi-arrow-left"></i>
                Kembali ke Halaman Utama
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-8 mb-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Status Pengajuan</h1>
                    <p class="text-gray-600">Kode Ajuan: <span
                            class="font-mono font-semibold text-blue-600">{{ $ajuan->tracking_code }}</span></p>
                </div>
                <div class="text-right">
                    @php
                        $statusConfig = [
                            'draft' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => 'bi-file-earmark'],
                            'menunggu' => [
                                'bg' => 'bg-yellow-100',
                                'text' => 'text-yellow-800',
                                'icon' => 'bi-hourglass-split',
                            ],
                            'diajukan' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'icon' => 'bi-send'],
                            'ditindaklanjuti' => [
                                'bg' => 'bg-green-100',
                                'text' => 'text-green-800',
                                'icon' => 'bi-check-circle-fill',
                            ],
                            'tidak_ditindaklanjuti' => [
                                'bg' => 'bg-red-100',
                                'text' => 'text-red-800',
                                'icon' => 'bi-x-circle-fill',
                            ],
                        ];
                        $config = $statusConfig[$ajuan->status] ?? [
                            'bg' => 'bg-gray-100',
                            'text' => 'text-gray-800',
                            'icon' => 'bi-question',
                        ];
                    @endphp
                    <span
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold {{ $config['bg'] }} {{ $config['text'] }}">
                        <i class="bi {{ $config['icon'] }}"></i>
                        {{ ucfirst(str_replace('_', ' ', $ajuan->status)) }}
                    </span>
                </div>
            </div>

            <div class="relative">
                <div class="flex items-center justify-between mb-2">
                    @php
                        $steps = [
                            'draft' => 'Dibuat',
                            'menunggu' => 'Menunggu',
                            'diajukan' => 'Diajukan',
                            'ditindaklanjuti' => 'Selesai',
                        ];
                        $currentStep = array_search($ajuan->status, array_keys($steps));
                        if ($ajuan->status === 'tidak_ditindaklanjuti') {
                            $currentStep = 3;
                        }
                    @endphp

                    @foreach ($steps as $key => $label)
                        @php
                            $stepIndex = array_search($key, array_keys($steps));
                            $isActive = $stepIndex <= $currentStep;
                            $isCurrent = $stepIndex === $currentStep;
                        @endphp
                        <div class="flex flex-col items-center flex-1">
                            <div
                                class="w-10 h-10 rounded-full flex items-center justify-center mb-2 {{ $isActive ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600' }}">
                                @if ($isActive)
                                    <i class="bi bi-check-lg"></i>
                                @else
                                    {{ $stepIndex + 1 }}
                                @endif
                            </div>
                            <span class="text-xs {{ $isCurrent ? 'font-bold text-blue-600' : 'text-gray-600' }}">
                                {{ $label }}
                            </span>
                        </div>
                        @if (!$loop->last)
                            <div class="flex-1 h-1 {{ $isActive ? 'bg-blue-600' : 'bg-gray-300' }} -mt-12"></div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-8 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                <i class="bi bi-info-circle text-blue-600"></i>
                Detail Pengajuan
            </h2>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="text-sm font-medium text-gray-600">Nama Pemohon</label>
                    <p class="text-gray-800 font-semibold">{{ $ajuan->nama_pemohon }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">NIK</label>
                    <p class="text-gray-800 font-semibold">{{ $ajuan->nik }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Jenis Layanan</label>
                    <p class="text-gray-800 font-semibold">{{ $ajuan->jenis_layanan }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Tanggal Pengajuan</label>
                    <p class="text-gray-800 font-semibold">{{ $ajuan->created_at->format('d M Y H:i') }}</p>
                </div>
                @if ($ajuan->posyandu)
                    <div class="md:col-span-2">
                        <label class="text-sm font-medium text-gray-600">Posyandu</label>
                        <p class="text-gray-800 font-semibold">{{ $ajuan->posyandu->nama_posyandu }}</p>
                        <p class="text-sm text-gray-600">{{ $ajuan->posyandu->desa }},
                            {{ $ajuan->posyandu->kecamatan }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if ($ajuan->history && count($ajuan->history) > 0)
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                    <i class="bi bi-clock-history text-purple-600"></i>
                    Riwayat Proses
                </h2>

                <div class="space-y-4">
                    @foreach ($ajuan->history as $history)
                        <div class="flex gap-4 items-start">
                            <div class="flex-shrink-0">
                                <div class="w-3 h-3 bg-blue-600 rounded-full mt-2"></div>
                            </div>
                            <div class="flex-1 pb-4 border-l-2 border-gray-200 pl-6 -ml-1.5">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-semibold text-gray-800">{{ $history['action'] }}</span>
                                    <span
                                        class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($history['timestamp'])->format('d M Y H:i') }}</span>
                                </div>
                                @if (isset($history['by']))
                                    <p class="text-sm text-gray-600">Oleh: {{ $history['by'] }}</p>
                                @endif
                                @if (isset($history['notes']))
                                    <p class="text-sm text-gray-600 mt-1">{{ $history['notes'] }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($ajuan->feedback_kades)
            <div class="bg-white rounded-2xl shadow-xl p-8 mt-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="bi bi-chat-square-text text-green-600"></i>
                    Tindak Lanjut dari Kepala Desa
                </h2>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-gray-800">{{ $ajuan->feedback_kades }}</p>
                    @if ($ajuan->tanggal_feedback_kades)
                        <p class="text-xs text-gray-500 mt-2">
                            {{ \Carbon\Carbon::parse($ajuan->tanggal_feedback_kades)->format('d M Y H:i') }}</p>
                    @endif
                </div>
            </div>
        @endif

        <div class="flex gap-4 mt-6">
            <a href="{{ route('landing') }}"
                class="flex-1 px-6 py-3 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition-colors text-center">
                Kembali
            </a>
            <button onclick="window.print()"
                class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                <i class="bi bi-printer"></i>
                Cetak Status
            </button>
        </div>

    </div>

</body>

</html>
