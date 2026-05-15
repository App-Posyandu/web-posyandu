<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Cetak Ringkasan Pengajuan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        @page {
            size: A4 portrait;
            margin: 8mm 12mm;
        }
        body {
            font-size: 8px;
            line-height: 1.2;
            color: #171717;
        }
        header {
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .logo-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }
        .logos {
            display: flex;
            gap: 8px;
        }
        .logos img {
            height: 30px;
        }
        .bidang-box {
            border: 1px solid #9ca3af;
            padding: 2px 6px;
            font-size: 7px;
            text-transform: uppercase;
            font-weight: 600;
        }
        .header-title {
            text-align: center;
        }
        .header-title h1 {
            text-transform: uppercase;
            font-size: 10px;
            font-weight: 700;
            margin: 0;
        }
        .header-title h2 {
            text-transform: uppercase;
            font-size: 9px;
            font-weight: 500;
            margin: 2px 0 0;
        }
        .tracking-section {
            background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%);
            border: 2px solid #ec4899;
            border-radius: 6px;
            padding: 6px;
            margin: 6px 0;
            text-align: center;
        }
        .tracking-label {
            font-size: 7px;
            color: #9333ea;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 3px;
        }
        .tracking-code-box {
            background: white;
            border: 1px dashed #ec4899;
            border-radius: 4px;
            padding: 4px 8px;
            margin: 3px auto;
            max-width: 250px;
        }
        .tracking-code {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            font-weight: 700;
            color: #be123c;
            letter-spacing: 1px;
        }
        .tracking-info {
            font-size: 6px;
            color: #be123c;
            margin-top: 3px;
            font-weight: 500;
        }
        .info-section {
            margin: 6px 0;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 4px;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            width: 28%;
            padding: 1px 0;
            font-size: 7px;
            font-weight: 600;
            color: #374151;
        }
        .info-value {
            display: table-cell;
            padding: 1px 0;
            font-size: 7px;
            color: #4b5563;
        }
        .section-divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 4px 0;
        }
        .sub-title {
            font-size: 8px;
            color: #111827;
            margin: 4px 0 2px;
            font-weight: 700;
        }
        .table-content {
            width: 100%;
            border-collapse: collapse;
        }
        .table-content tr {
            border-bottom: 1px solid #f3f4f6;
        }
        .table-content td {
            padding: 2px 0;
            font-size: 7px;
        }
        .cell-left {
            width: 88%;
            color: #4b5563;
        }
        .img-check {
            width: 10px;
            height: 10px;
        }
        .description-box {
            background-color: #f9fafb;
            border-left: 2px solid #6366f1;
            border-radius: 3px;
            padding: 4px;
            margin: 4px 0;
        }
        .description-box p {
            font-size: 7px;
            color: #4b5563;
            line-height: 1.3;
        }
        .date-approval {
            margin: 6px 0 4px;
            font-size: 7px;
        }
        .date-approval p {
            margin: 1px 0;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .signature-content td {
            text-align: center;
            vertical-align: top;
            padding: 3px;
            font-size: 7px;
        }
        .signature-content td:first-child {
            border-right: 1px dashed #e5e7eb;
        }
        .check {
            width: 10px;
            height: 10px;
            margin: 6px 0;
        }
        footer {
            border-top: 1px solid #e5e7eb;
            padding-top: 4px;
            margin-top: 6px;
            text-align: center;
            font-size: 6px;
            color: #6b7280;
        }
        footer span {
            font-weight: 600;
            color: #111827;
        }
        .two-column {
            display: table;
            width: 100%;
        }
        .column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 4px;
        }
        .column:last-child {
            padding-right: 0;
            padding-left: 4px;
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
            $kebumenBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($kebumenPath));
        }
        if (file_exists($posyanduPath)) {
            $posyanduBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($posyanduPath));
        }
        if (file_exists($checkPath)) {
            $checkBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($checkPath));
        }
    @endphp
    <header>
        <div class="logo-group">
            <div class="logos">
                @if ($kebumenBase64)
                    <img src="{{ $kebumenBase64 }}" alt="Logo Kebumen">
                @endif
                @if ($posyanduBase64)
                    <img src="{{ $posyanduBase64 }}" alt="Logo Posyandu">
                @endif
            </div>
            <h4 class="bidang-box">{{ $ajuan->bidang->nama_bidang }}</h4>
        </div>

        <div class="header-title">
            <h1>Formulir Permohonan</h1>
            <h2>Layanan Standar Minimal Pelayanan Posyandu di Kabupaten Kebumen</h2>
        </div>
    </header>

    <div class="tracking-section">
        <div class="tracking-label">📋 Kode Tracking Pengajuan</div>
        <div class="tracking-code-box">
            <div class="tracking-code">{{ $ajuan->tracking_code ?? 'PGJ-000000-00000' }}</div>
        </div>
        <div class="tracking-info">✓ Simpan kode ini untuk melacak status pengajuan Anda</div>
    </div>

    <div class="info-section">
        <div class="two-column">
            <div class="column">
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Nama Pemohon</div>
                        <div class="info-value">: {{ $ajuan->user->name }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Alamat</div>
                        <div class="info-value">: {{ $ajuan->user->alamat }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">No Hp</div>
                        <div class="info-value">: {{ $ajuan->user->no_telepon ?? '-' }}</div>
                    </div>
                </div>
            </div>
            <div class="column">
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Nama Posyandu</div>
                        <div class="info-value">: {{ $ajuan->user?->posyandu?->nama_posyandu ?? '-' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">RW / RT</div>
                        <div class="info-value">: RW {{ $ajuan->user?->rw ?? '-' }} / RT {{ $ajuan->user?->rt ?? '-' }}
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Desa/Kelurahan</div>
                        <div class="info-value">: {{ $ajuan->user?->posyandu?->desa ?? '-' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Kecamatan</div>
                        <div class="info-value">: {{ $ajuan->user?->posyandu?->kecamatan ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-divider"></div>
        @if ($ajuan->deskripsi_pengajuan)
            <h4 class="sub-title">Deskripsi Permohonan</h4>
            <div class="description-box">
                <p>{{ $ajuan->deskripsi_pengajuan }}</p>
            </div>
        @endif

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
        <h4 class="sub-title">Dokumen Administrasi</h4>
        <table class="table-content">
            @forelse ($ajuan->administrasi_items ?? [] as $key => $path)
                @php
                    $label = $templateData['administrasi_items'][$key] ?? ucfirst(str_replace('_', ' ', $key));
                @endphp
                <tr>
                    <td class="cell-left">{{ $label }}</td>
                    <td>
                        @if ($checkBase64)
                            <img src="{{ $checkBase64 }}" alt="Checked" class="img-check">
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">Tidak ada dokumen yang diunggah</td>
                </tr>
            @endforelse
        </table>

        @if ($ajuan->tindak_lanjut)
            <div class="section-divider"></div>
            <h4 class="sub-title">Tindak Lanjut Rekomendasi</h4>
            <div class="description-box">
                <p>{{ $ajuan->tindak_lanjut }}</p>
            </div>
        @endif

        <div class="date-approval">
            <p><strong>Kota Kebumen, </strong>
                {{ \Carbon\Carbon::parse($ajuan->tanggal_permohonan ?? $ajuan->created_at)->format('d F Y') }}
            </p>
            @if ($ajuan->approved_by_ketua_at)
                <p><strong>Tanggal Persetujuan Ketua Posyandu: </strong>
                    {{ \Carbon\Carbon::parse($ajuan->approved_by_ketua_at)->format('d F Y') }}
                </p>
            @endif
            @if ($ajuan->approved_by_kades_at)
                <p><strong>Tanggal Persetujuan Kades: </strong>
                    {{ \Carbon\Carbon::parse($ajuan->approved_by_kades_at)->format('d F Y') }}
                </p>
            @endif
        </div>

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

        @if ($ajuan->approved_by_kades)
            <table class="signature-table" style="margin-top: 8px;">
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

    <footer>
        <p>&copy; 2026 <span>SAPA POSYANDU</span> - Layanan Standar Minimal Pelayanan Posyandu Kabupaten Kebumen</p>
    </footer>

</body>

</html>
