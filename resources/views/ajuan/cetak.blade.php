<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Pengajuan - SAPA POSYANDU</title>

    {{-- ✅ Tambahkan QR Code Library --}}
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>

    <style>
        /* ========== RESET & BASE STYLING ========== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        html,
        body {
            height: 100%;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #ffffff;
            color: #171717;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        h1,
        h2,
        h3 {
            font-weight: bold;
        }

        /* ========== HEADER ========== */
        header {
            background-color: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            padding: 16px 24px;
            width: 100%;
        }

        header table {
            width: 90%;
        }

        header table tr {
            width: 100%;
            /* margin: 0 0 8px 0 */
        }

        .left-cell {
            width: 70%;
        }

        .logo-group {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 1rem;
        }

        .logo-group img {
            height: 48px;
        }

        .bidang-box {
            border: 1px solid #9ca3af;
            width: fit-content;
            padding: 4px 6px;
            text-align: center;
            font-size: 14px;
            text-transform: uppercase;
        }

        .header-title {
            text-align: center;
            flex-grow: 1;
            margin-top: 1px;
        }

        .header-title h1 {
            text-transform: uppercase;
            color: #171717;
            font-size: 1rem;
            margin: 0;
            font-weight: 700;
        }

        .header-title h2 {
            text-transform: uppercase;
            color: #171717;
            font-size: 1rem;
            margin: 1px 0 0;
            font-weight: 500;
        }

        /* ✅ TRACKING CODE STYLES */
        .tracking-section {
            background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%);
            border: 2px solid #ec4899;
            border-radius: 12px;
            padding: 16px;
            margin: 16px 0 24px 0;
            text-align: center;
        }

        .tracking-label {
            font-size: 12px;
            color: #9333ea;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .tracking-code-box {
            background: white;
            border: 2px dashed #ec4899;
            border-radius: 8px;
            padding: 12px 16px;
            margin: 8px auto;
            max-width: 400px;
        }

        .tracking-code {
            font-family: 'Courier New', monospace;
            font-size: 24px;
            font-weight: 700;
            color: #be123c;
            letter-spacing: 2px;
        }

        .tracking-info {
            font-size: 11px;
            color: #be123c;
            margin-top: 8px;
            font-weight: 500;
        }

        .qr-section {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px dashed #ec4899;
        }

        .qr-code-container {
            background: white;
            padding: 8px;
            border-radius: 8px;
            display: inline-block;
            margin: 8px auto;
        }

        .qr-instructions {
            font-size: 10px;
            color: #be123c;
            margin-top: 6px;
        }

        /* ========== MAIN (FLEX FILLER) ========== */
        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 12px 56px;
        }

        .container {
            background-color: #ffffff;
            max-width: 800px;
            width: 100%;
            padding: 0px 24px;
            border-radius: 16px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }

        .info-section {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        .info-section table {
            width: 100%;
            /* margin: 24px 0; */
        }

        .info-section table tr {
            width: 100%;
        }

        .info-section table tr td {
            width: 50%;
            padding: 2px 0;
        }

        .info-section h3 {
            font-size: 14px;
            color: #374151;
        }

        .info-section p {
            font-size: 14px;
            color: #4b5563;
            /* margin-top: 4px; */
        }

        .content {
            margin-bottom: 12px;
        }

        .content span {
            font-weight: normal;
            color: #111827;
        }

        .sub-title {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            /* margin-bottom: 8px; */
            /* margin-top: 10px; */
        }

        .table-content {
            width: 100%;
            margin: 0;
            padding: 0;
            /* margin: 4px 0; */
            border-collapse: collapse;
        }

        .table-content td {
            vertical-align: middle;
            padding: 2px 0;
            font-size: 12px
        }

        .table-content td.cell-left {
            width: 90%;
        }

        .table-content td:last-child {
            width: 10%;
            text-align: right;
        }

        .content-label {
            text-transform: uppercase;
        }

        .img-check {
            height: 20px;
            width: 20px;
        }

        .signature-table {
            width: 100%;
            margin-top: 12px;
            border-collapse: collapse;
            text-align: center;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: middle;
            padding: 4px 0;
        }

        .signature-content td img.check {
            display: inline-block;
            height: 80px;
            width: 80px;
        }

        .persyaratan-administrasi {
            width: 100%;
            /* margin: 16px 0; */
            border-collapse: collapse;
        }

        .persyaratan-administrasi td {
            vertical-align: middle;
            padding: 4px 0;
        }

        .date-approval {
            text-align: right;
            /* margin-top: 2px; */
            /* margin-bottom: 8px; */
            font-size: 14px;
            color: #374151;
        }

        .date-approval p {
            /* margin: 4px 0; */
        }

        .section-divider {
            border-top: 2px solid #e5e7eb;
            margin: 4px 0;
        }

        /* .description-box {
            margin: 16px 0;
            padding: 12px;
            background-color: #f9fafb;
            border-left: 4px solid #6366f1;
            border-radius: 4px;
        } */

        /* .description-box p {
            font-size: 14px;
            color: #4b5563;
            line-height: 1.6;
        } */

        /* ========== FOOTER ========== */
        footer {
            background-color: #ffffff;
            border-top: 1px solid #e5e7eb;
            padding: 8px;
            text-align: center;
            font-size: 0.875rem;
            color: #6b7280;
        }

        footer span {
            font-weight: 600;
            color: #111827;
        }

        /* ========== PRINT STYLES ========== */
        @media print {
            body {
                background: white;
            }

            .tracking-section {
                page-break-inside: avoid;
            }
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 640px) {
            header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .logo-group {
                justify-content: flex-start;
            }

            main {
                padding: 32px 12px;
            }

            .card {
                padding: 24px;
            }

            .tracking-code {
                font-size: 18px;
            }
        }
    </style>
</head>

<body>
    @php
        $kebumenPath = public_path('assets/image/logo/logo_kebumen.png');
        $posyanduPath = public_path('assets/image/logo/logo_posyandu.png');
        $checkPath = public_path('assets/image/icon/check.png');

        $kebumenBase64 = '';
        $posyanduBase64 = '';
        $checkBase64 = '';

        if (file_exists($kebumenPath)) {
            $kebumenData = file_get_contents($kebumenPath);
            $kebumenBase64 = 'data:image/png;base64,' . base64_encode($kebumenData);
        }

        if (file_exists($posyanduPath)) {
            $posyanduData = file_get_contents($posyanduPath);
            $posyanduBase64 = 'data:image/png;base64,' . base64_encode($posyanduData);
        }

        if (file_exists($checkPath)) {
            $checkData = file_get_contents($checkPath);
            $checkBase64 = 'data:image/png;base64,' . base64_encode($checkData);
        }
    @endphp

    <header>
        <table>
            <tr>
                <td class="left-cell">
                    <div class="logo-group">
                        @if ($kebumenBase64)
                            <img src="{{ $kebumenBase64 }}" alt="Logo Kebumen">
                        @endif
                        @if ($posyanduBase64)
                            <img src="{{ $posyanduBase64 }}" alt="Logo Posyandu">
                        @endif
                    </div>
                </td>
                <td>
                    <h4 class="bidang-box">{{ $ajuan->bidang->nama_bidang }}</h4>
                </td>
            </tr>
        </table>

        <div class="header-title">
            <h1>Formulir Permohonan</h1>
            <h2>Layanan Standar Minimal Pelayanan Posyandu di Kabupaten Kebumen</h2>
        </div>
    </header>

    <main>
        <div class="container">
            {{-- ✅ TRACKING CODE SECTION (PROMINENT - SEBELUM NAMA) --}}
            {{-- <div class="tracking-section">
                <div class="tracking-label">📋 Kode Tracking Pengajuan</div>
                <div class="tracking-code-box">
                    <div class="tracking-code">{{ $ajuan->tracking_code ?? 'PGJ-000000-00000' }}</div>
                </div>
                <div class="tracking-info">
                    ✓ Simpan kode ini untuk melacak status pengajuan Anda
                </div>

                <div class="qr-section">
                    <div class="qr-code-container">
                        <canvas id="qrcode" width="120" height="120"></canvas>
                    </div>
                    <div class="qr-instructions">
                        Scan QR Code atau gunakan kode di atas untuk cek status
                    </div>
                </div>
            </div> --}}

            <div class="info-section">
                {{-- Informasi Pemohon --}}
                <table>
                    <tr>
                        <td>
                            <h3>Kode Tracking Pengajuan</h3>
                        </td>
                        <td>: {{ $ajuan->tracking_code ?? 'PGJ-000000-00000' }}</td>
                    </tr>
                    <tr>
                        <td>
                            <h3>Nama Pemohon</h3>
                        </td>
                        <td>: {{ $ajuan->user->name }}</td>
                    </tr>
                    <tr>
                        <td>
                            <h3>Alamat</h3>
                        </td>
                        <td>: {{ $ajuan->user->alamat }}</td>
                    </tr>
                    <tr>
                        <td>
                            <h3>No Hp</h3>
                        </td>
                        <td>: {{ $ajuan->user->no_telepon ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>
                            <h3>Nama Posyandu</h3>
                        </td>
                        <td>: {{ $ajuan->user?->posyandu?->nama_posyandu ?? 'Nama Posyandu' }}</td>
                    </tr>
                    <tr>
                        <td>
                            <h3>RW / RT</h3>
                        </td>
                        <td>: RW {{ $ajuan->user?->rw ?? '-' }} / RT {{ $ajuan->user?->rt ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>
                            <h3>Desa/Kelurahan</h3>
                        </td>
                        <td>: {{ $ajuan->user?->posyandu?->desa ?? 'Desa' }}</td>
                    </tr>
                    <tr>
                        <td>
                            <h3>Kecamatan</h3>
                        </td>
                        <td>: {{ $ajuan->user?->posyandu?->kecamatan ?? 'Kecamatan' }}</td>
                    </tr>
                </table>

                <div class="section-divider"></div>

                {{-- Deskripsi Permohonan --}}
                @if ($ajuan->deskripsi_pengajuan)
                    <h4 class="sub-title">Deskripsi Permohonan</h4>
                    <div class="description-box">
                        <p>{{ $ajuan->deskripsi_pengajuan }}</p>
                    </div>
                @endif

                {{-- Detail Permohonan yang Dipilih --}}
                <h4 class="sub-title">Detail Permohonan Dipilih</h4>
                <table class="table-content">
                    @forelse ($ajuan->formulir_items as $item)
                        <tr>
                            <td class="cell-left">{{ $item }}</td>
                            <td>
                                @if ($checkBase64)
                                    <img src="{{ $checkBase64 }}" alt="Checked" class="img-check">
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">Tidak ada permohonan yang dipilih</td>
                        </tr>
                    @endforelse
                </table>

                <div class="section-divider"></div>

                {{-- Verifikasi Permohonan --}}
                <h4 class="sub-title">Verifikasi Permohonan</h4>
                <table class="table-content">
                    @forelse ($ajuan->formulir_items ?? [] as $item)
                        <tr>
                            <td class="cell-left">{{ $item }}</td>
                            <td>
                                @php
                                    $rawItems = $ajuan->verified_formulir_items;

                                    if (is_string($rawItems)) {
                                        $verifiedItems = json_decode($rawItems, true);
                                    } else {
                                        $verifiedItems = $rawItems;
                                    }

                                    if (!is_array($verifiedItems)) {
                                        $verifiedItems = [];
                                    }
                                @endphp

                                @if ($ajuan->sudah_verifikasi && in_array($item, $verifiedItems))
                                    @if ($checkBase64)
                                        <img src="{{ $checkBase64 }}" alt="Verified" class="img-check">
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">Tidak ada permohonan yang dipilih</td>
                        </tr>
                    @endforelse
                </table>

                {{-- Dokumen Administrasi --}}
                <h4 class="sub-title">Dokumen Administrasi</h4>
                <table class="table-content">
                    @forelse ($ajuan->administrasi_items ?? [] as $key => $path)
                        @php
                            $label = $templateData['administrasi_items'][$key] ?? ucfirst(str_replace('_', ' ', $key));

                            $rawDocs = $ajuan->verified_administrasi_items;

                            if (is_string($rawDocs)) {
                                $verifiedDocs = json_decode($rawDocs, true);
                            } else {
                                $verifiedDocs = $rawDocs;
                            }

                            if (!is_array($verifiedDocs)) {
                                $verifiedDocs = [];
                            }
                        @endphp
                        <tr>
                            <td class="cell-left">{{ $label }}</td>
                            <td>
                                @if ($ajuan->sudah_verifikasi && array_key_exists($key, $verifiedDocs))
                                    @if ($checkBase64)
                                        <img src="{{ $checkBase64 }}" alt="Verified" class="img-check">
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">Tidak ada dokumen yang diunggah</td>
                        </tr>
                    @endforelse
                </table>

                {{-- Tindak Lanjut --}}
                @if ($ajuan->tindak_lanjut)
                    <div class="section-divider"></div>
                    <h4 class="sub-title">Tindak Lanjut Rekomendasi</h4>
                    <div class="description-box">
                        <p>{{ $ajuan->tindak_lanjut }}</p>
                    </div>
                @endif

                {{-- Tanggal Permohonan & Persetujuan --}}
                <div class="date-approval">
                    <p><strong>Kota Kebumen, </strong>
                        {{ \Carbon\Carbon::parse($ajuan->tanggal_permohonan ?? $ajuan->created_at)->format('d F Y') }}
                    </p>
                    @if ($ajuan->approved_by_ketua_at)
                        <p><strong>Tanggal Persetujuan Ketua: </strong>
                            {{ \Carbon\Carbon::parse($ajuan->approved_by_ketua_at)->format('d F Y') }}
                        </p>
                    @endif
                    @if ($ajuan->approved_by_kades_at)
                        <p><strong>Tanggal Persetujuan Kades: </strong>
                            {{ \Carbon\Carbon::parse($ajuan->approved_by_kades_at)->format('d F Y') }}
                        </p>
                    @endif
                </div>

                {{-- Tanda Tangan --}}
                <table class="signature-table">
                    <tr class="signature-content">
                        <td>Ketua Posyandu</td>
                        <td>Pemohon Layanan</td>
                    </tr>
                    <tr class="signature-content">
                        <td>
                            @if ($ajuan->approved_by_ketua)
                                @if ($checkBase64)
                                    <img src="{{ $checkBase64 }}" alt="Approved" class="check">
                                @endif
                            @endif
                        </td>
                        <td>
                            @if ($checkBase64)
                                <img src="{{ $checkBase64 }}" alt="Signed" class="check">
                            @endif
                        </td>
                    </tr>
                    <tr class="signature-content">
                        <td>
                            @if ($ajuan->approved_by_ketua)
                                {{ $ajuan->ketuaPosyandu?->name ?? 'Ketua Posyandu' }}
                            @else
                                (...........................)
                            @endif
                        </td>
                        <td>{{ $ajuan->user->name }}</td>
                    </tr>
                </table>

                {{-- Tanda Tangan Kades (jika sudah disetujui) --}}
                @if ($ajuan->approved_by_kades)
                    <table class="signature-table" style="margin-top: 16px;">
                        <tr class="signature-content">
                            <td colspan="2">Kepala Desa</td>
                        </tr>
                        <tr class="signature-content">
                            <td colspan="2">
                                @if ($checkBase64)
                                    <img src="{{ $checkBase64 }}" alt="Approved by Kades" class="check">
                                @endif
                            </td>
                        </tr>
                        <tr class="signature-content">
                            <td colspan="2">
                                {{ $ajuan->kades?->name ?? 'Kepala Desa' }}
                            </td>
                        </tr>
                    </table>
                @endif
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; {{now()->year}} <span>SAPA POSYANDU</span> - Layanan Standar Minimal Pelayanan Posyandu Kabupaten Kebumen</p>
    </footer>

    {{-- ✅ QR CODE GENERATION SCRIPT --}}
    <script>
        // Generate QR Code for tracking
        document.addEventListener('DOMContentLoaded', function() {
            const trackingCode = "{{ $ajuan->tracking_code ?? '' }}";
            const trackingUrl = "{{ route('ajuan.track.show', ['code' => $ajuan->tracking_code ?? 'INVALID']) }}";
            const qrCanvas = document.getElementById('qrcode');

            if (qrCanvas && trackingCode) {
                QRCode.toCanvas(qrCanvas, trackingUrl, {
                    width: 120,
                    margin: 1,
                    color: {
                        dark: '#be123c',
                        light: '#ffffff'
                    }
                }, function(error) {
                    if (error) console.error('QR Code generation error:', error);
                });
            }
        });
    </script>
</body>

</html>
