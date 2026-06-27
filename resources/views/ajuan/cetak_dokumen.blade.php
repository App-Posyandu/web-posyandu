<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Cetak Dokumen Administrasi - {{ $ajuan->tracking_code }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        @page {
            size: A4 portrait;
            margin: 15mm 20mm;
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
        .info-box {
            background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%);
            border: 2px solid #ec4899;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .info-section table {
            width: 100%;
            margin: 10px 0;
        }
        .info-section table tr {
            width: 100%;
        }
        .info-section table tr td {
            padding: 4px 0;
            font-size: 11px;
        }
        .info-section table tr td:first-child {
            width: 40%;
        }
        .info-section h3 {
            font-size: 11px;
            color: #374151;
            font-weight: 600;
        }
        .section-divider {
            height: 2px;
            background-color: #e5e7eb;
            margin: 15px 0;
        }
        .doc-title {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            text-align: center;
        }
        .doc-title h3 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .doc-title p {
            font-size: 10px;
            opacity: 0.9;
        }
        .image-container {
            text-align: center;
            padding: 20px;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            background: #ffffff;
            min-height: 500px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .image-container img {
            max-width: 100%;
            max-height: 500px;
            object-fit: contain;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }
        .no-image {
            text-align: center;
            color: #6c757d;
            font-style: italic;
        }
        .no-image-icon {
            font-size: 48px;
            margin-bottom: 10px;
            opacity: 0.3;
        }
        footer {
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            margin-top: 15px;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
        }
        footer span {
            font-weight: 600;
            color: #111827;
        }
        .document-page {
            page-break-after: always;
        }
        .document-page:last-child {
            page-break-after: auto;
        }
        .container {
            padding: 0 20px;
        }
    </style>
</head>

<body>
    @php
        $kebumenPath = public_path('assets/image/logo/logo_kebumen.png');
        $posyanduPath = public_path('assets/image/logo/logo_posyandu.png');

        $kebumenBase64 = '';
        $posyanduBase64 = '';

        if (file_exists($kebumenPath)) {
            $kebumenData = file_get_contents($kebumenPath);
            $kebumenBase64 = 'data:image/png;base64,' . base64_encode($kebumenData);
        }

        if (file_exists($posyanduPath)) {
            $posyanduData = file_get_contents($posyanduPath);
            $posyanduBase64 = 'data:image/png;base64,' . base64_encode($posyanduData);
        }
        if (!isset($documentCounter)) {
            $documentCounter = 1;
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
            <h1>Lampiran Dokumen Administrasi</h1>
            <h2>Layanan Standar Minimal Pelayanan Posyandu di Kabupaten Kebumen</h2>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="info-section">
                <table>
                    <tr>
                        <td>
                            <h3>Kode Tracking</h3>
                        </td>
                        <td>: {{ $ajuan->tracking_code ?? 'PGJ-000000-00000' }}</td>
                    </tr>
                    <tr>
                        <td>
                            <h3>Nama Pemohon</h3>
                        </td>
                        <td>: <span>{{ $ajuan->user?->name ? $ajuan->user->name : '-' }}</span></td>
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
                        <td>: <span>{{ $ajuan->user?->posyandu?->nama_posyandu ? $ajuan->user->posyandu->nama_posyandu : '-' }}</span></td>
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
                        <td>: <span>{{ $ajuan->user?->posyandu?->desa ? $ajuan->user?->posyandu?->desa : '-' }}</span></td>
                    </tr>
                    <tr>
                        <td>
                            <h3>Kecamatan</h3>
                        </td>
                        <td>: <span>{{ $ajuan->user?->posyandu?->kecamatan ? $ajuan->user?->posyandu?->kecamatan : '-' }}</span></td>
                    </tr>
                </table>

                <div class="section-divider"></div>
            </div>

            @php
                $administrasiItems = $administrasiItems ?? [];
            @endphp

            @forelse ($administrasiItems as $key => $path)
                @php
                    $label = $templateData['administrasi_items'][$key] ?? ucfirst(str_replace('_', ' ', $key));
                    $fullPath = storage_path('app/public/' . $path);
                    $imageBase64 = '';

                    if (file_exists($fullPath)) {
                        $imageData = file_get_contents($fullPath);
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mimeType = finfo_file($finfo, $fullPath);
                        finfo_close($finfo);

                        $imageBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                    }
                @endphp

                <div class="document-page">
                    <div class="doc-title">
                        <h3>{{ $label }}</h3>
                        <p>Dokumen {{ $documentCounter }} dari {{ count($administrasiItems) }}</p>
                    </div>

                    <div class="image-container">
                        @if ($imageBase64)
                            <img src="{{ $imageBase64 }}" alt="{{ $label }}">
                        @else
                            <div class="no-image">
                                <div class="no-image-icon">📄</div>
                                <p>Dokumen tidak ditemukan atau tidak dapat ditampilkan</p>
                                <p style="font-size: 8px; margin-top: 5px;">Path: {{ $path }}</p>
                            </div>
                        @endif
                    </div>
                    <footer>
                        <p>&copy; {{ now()->year }} <span>SAPA POSYANDU</span> - Dokumen {{ $documentCounter }} dari
                            {{ count($administrasiItems) }} | Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
                    </footer>
                </div>

                @php
                    $documentCounter++;
                @endphp

            @empty
                <div class="document-page">
                    <div class="image-container">
                        <div class="no-image">
                            <div class="no-image-icon">📁</div>
                            <p>Tidak ada dokumen administrasi yang dilampirkan</p>
                        </div>
                    </div>

                    <footer>
                        <p>&copy; {{ now()->year }} <span>SAPA POSYANDU</span> - Layanan Standar Minimal Pelayanan
                            Posyandu
                            Kabupaten Kebumen</p>
                    </footer>
                </div>
            @endforelse
        </div>
    </main>

</body>

</html>
