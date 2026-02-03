<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pengajuan - {{ $pengajuan->tracking_code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white;
            }

            .print-section {
                page-break-inside: avoid;
            }
        }

        @page {
            size: A4;
            margin: 2cm;
        }
    </style>
</head>

<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-pink-500 to-pink-600 text-white p-6 print-section">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">BUKTI PENGAJUAN</h1>
                    <p class="text-pink-100">Sistem Informasi Posyandu Terpadu</p>
                </div>
                <div class="text-right">
                    <div class="bg-white text-pink-600 px-4 py-2 rounded-lg font-mono font-bold text-lg">
                        {{ $pengajuan->tracking_code }}
                    </div>
                    <p class="text-sm text-pink-100 mt-1">Kode Pengajuan</p>
                </div>
            </div>
        </div>
        <div class="p-8 print-section">
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4 border-b-2 border-pink-500 pb-2">
                    <i class="bi bi-file-text text-pink-500 mr-2"></i>
                    Informasi Pengajuan
                </h2>

                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Nama Pemohon:</p>
                            <p class="font-semibold text-gray-800">{{ $pengajuan->user->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">NIK:</p>
                            <p class="font-semibold text-gray-800">{{ $pengajuan->user->nik ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Nomor Telepon:</p>
                            <p class="font-semibold text-gray-800">{{ $pengajuan->user->no_telepon }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Alamat:</p>
                            <p class="font-semibold text-gray-800">{{ $pengajuan->user->alamat ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Bidang Pengajuan:</p>
                            <p class="font-semibold text-gray-800">{{ $pengajuan->bidang->nama_bidang }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Tanggal Pengajuan:</p>
                            <p class="font-semibold text-gray-800">
                                {{ $pengajuan->tanggal_permohonan ? $pengajuan->tanggal_permohonan->format('d F Y, H:i') : date('d F Y, H:i') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Status Saat Ini:</p>
                            @php
                                if ($pengajuan->status_pengajuan === 'Disetujui') {
                                    $statusClass = 'bg-green-100 text-green-800';
                                    $statusText = 'Disetujui';
                                } elseif ($pengajuan->status_pengajuan === 'Ditolak') {
                                    $statusClass = 'bg-red-100 text-red-800';
                                    $statusText = 'Ditolak';
                                } elseif ($pengajuan->submitted_to_desa) {
                                    $statusClass = 'bg-purple-100 text-purple-800';
                                    $statusText = 'Diajukan ke Desa';
                                } elseif ($pengajuan->approved_by_timpembina) {
                                    $statusClass = 'bg-blue-100 text-blue-800';
                                    $statusText = 'Disetujui Tim Pembina';
                                } else {
                                    $statusClass = 'bg-yellow-100 text-yellow-800';
                                    $statusText = 'Diproses';
                                }
                            @endphp
                            <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold {{ $statusClass }}">
                                {{ $statusText }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Deskripsi Pengajuan:</h3>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <p class="text-gray-700">{{ $pengajuan->deskripsi_pengajuan }}</p>
                </div>
            </div>

            <div class="border-t-2 border-dashed border-gray-300 pt-8 mt-8 print-section">
                <h2 class="text-xl font-bold text-gray-800 mb-6 text-center">
                    <i class="bi bi-qr-code text-pink-500 mr-2"></i>
                    Kode Pelacakan Pengajuan
                </h2>

                <div class="max-w-md mx-auto">
                    <div class="bg-gradient-to-r from-pink-50 to-pink-100 border-2 border-pink-300 rounded-lg p-6 mb-6">
                        <p class="text-sm text-gray-600 text-center mb-2">Kode Tracking Anda:</p>
                        <div class="bg-white border-2 border-pink-400 rounded-lg p-4 text-center">
                            <p class="text-3xl font-mono font-bold text-gray-800 tracking-wider">
                                {{ $pengajuan->tracking_code }}
                            </p>
                        </div>
                        <p class="text-xs text-gray-600 text-center mt-3">
                            <i class="bi bi-shield-check text-pink-500 mr-1"></i>
                            Simpan kode ini dengan baik untuk melacak status pengajuan
                        </p>
                    </div>
                    <div class="text-center mb-6">
                        <p class="text-sm text-gray-600 mb-3">Atau scan QR Code:</p>
                        <div class="inline-block bg-white p-4 rounded-lg border-2 border-gray-300 shadow-sm">
                            <canvas id="qrcode" class="mx-auto"></canvas>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            Scan dengan aplikasi camera smartphone
                        </p>
                    </div>
                </div>
                <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <div class="flex items-start">
                        <i class="bi bi-info-circle-fill text-blue-500 mr-3 mt-0.5 text-xl flex-shrink-0"></i>
                        <div class="text-sm text-blue-700">
                            <p class="font-semibold mb-2">Cara Melacak Status Pengajuan:</p>
                            <div class="space-y-3">
                                <div>
                                    <p class="font-medium mb-1">📱 Melalui Website:</p>
                                    <ol class="list-decimal list-inside space-y-1 ml-3">
                                        <li>Buka: <span
                                                class="font-mono bg-white px-2 py-0.5 rounded">{{ url('/login') }}</span>
                                        </li>
                                        <li>Klik tab <strong>"Lacak Pengajuan"</strong></li>
                                        <li>Masukkan kode: <span
                                                class="font-mono bg-white px-2 py-0.5 rounded font-bold">{{ $pengajuan->tracking_code }}</span>
                                        </li>
                                        <li>Klik <strong>"Lacak Pengajuan"</strong></li>
                                    </ol>
                                </div>

                                <div>
                                    <p class="font-medium mb-1">🏢 Datang Langsung ke Kantor:</p>
                                    <ol class="list-decimal list-inside space-y-1 ml-3">
                                        <li>Bawa bukti pengajuan ini</li>
                                        <li>Petugas akan membantu cek status</li>
                                        <li>Gunakan kode tracking di atas</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-8 pt-6 border-t border-gray-300 text-center text-sm text-gray-600 print-section">
                <p class="mb-2">
                    <i class="bi bi-telephone-fill text-pink-500 mr-2"></i>
                    Hubungi kami: <span class="font-semibold">+62 123 456 789</span>
                </p>
                <p>
                    <i class="bi bi-envelope-fill text-pink-500 mr-2"></i>
                    Email: <span class="font-semibold">info@posyandu.go.id</span>
                </p>
                <p class="mt-4 text-xs text-gray-500">
                    Simpan bukti ini dengan baik. Bukti ini diperlukan untuk proses selanjutnya.
                </p>
            </div>
        </div>
        <div class="bg-gray-50 p-6 border-t border-gray-200 no-print">
            <div class="flex gap-4 justify-center">
                <button onclick="window.print()"
                    class="px-6 py-3 bg-pink-600 text-white rounded-lg hover:bg-pink-700 transition-colors font-semibold">
                    <i class="bi bi-printer mr-2"></i>
                    Cetak Bukti
                </button>
                <button onclick="downloadPDF()"
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                    <i class="bi bi-download mr-2"></i>
                    Download PDF
                </button>
                <a href="{{ route('dashboard') }}"
                    class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-semibold">
                    <i class="bi bi-arrow-left mr-2"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <script>
        const trackingUrl = "{{ route('pengajuan.track.show', ['code' => $pengajuan->tracking_code]) }}";
        const qrCanvas = document.getElementById('qrcode');

        QRCode.toCanvas(qrCanvas, trackingUrl, {
            width: 200,
            margin: 2,
            color: {
                dark: '#000000',
                light: '#ffffff'
            }
        }, function(error) {
            if (error) console.error(error);
        });
        function downloadPDF() {
            alert(
                'Fitur download PDF akan segera tersedia!\n\nSaat ini, silakan gunakan fitur "Cetak" dan pilih "Save as PDF" pada dialog print.');
        }
    </script>
</body>

</html>
