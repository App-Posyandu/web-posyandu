<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Credentials Kader Auto-Generated</title>
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
            padding: 16px 24px;
            width: 100%;
            padding-bottom: 20px;
        }

        header table {
            width: 80%;
            margin: 0 auto;
        }

        header table tr {
            border: none;
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
            border: none;
            text-align: center;
            flex-grow: 1;
            margin-top: 0px;
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

        /* ========== MAIN (FLEX FILLER) ========== */
        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 12px 56px;
        }

        .container {
            max-width: 800px;
            width: 100%;
            padding: 0px 24px;
            border-radius: 16px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }

        /* ========== INFO BOX ========== */
        .info-box {
            background: #fdf2f8;
            border-left: 4px solid #ec4899;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
        }

        .info-box h3 {
            font-size: 11px;
            font-weight: 700;
            color: #831843;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .info-box h3::before {
            content: '🏥';
            margin-right: 6px;
            font-size: 14px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 6px;
            font-size: 9px;
        }

        .info-label {
            font-weight: 600;
            color: #4b5563;
        }

        .info-value {
            color: #1f2937;
        }

        /* ========== TABLE ========== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        thead {
            background: linear-gradient(135deg, #ec4899 0%, #d946a6 100%);
        }

        thead th {
            color: white;
            font-weight: 700;
            padding: 10px 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }

        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        tbody tr:hover {
            background-color: #fef3c7;
        }

        tbody td {
            padding: 10px 8px;
            font-size: 9px;
            vertical-align: top;
        }

        .td-center {
            text-align: center;
        }

        .td-bold {
            font-weight: 700;
            color: #1f2937;
        }

        .password {
            font-weight: 700;
            color: #dc2626;
            font-family: 'Courier New', monospace;
            font-size: 10px;
            background: #fee2e2;
            padding: 4px 6px;
            border-radius: 4px;
            display: inline-block;
        }

        /* ========== FOOTER WARNING ========== */
        .warning-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 6px;
            padding: 12px;
            margin-top: 20px;
        }

        .warning-box h4 {
            font-size: 10px;
            font-weight: 700;
            color: #92400e;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .warning-box h4::before {
            content: '⚠️';
            margin-right: 6px;
            font-size: 14px;
        }

        .warning-box ul {
            margin-left: 20px;
            font-size: 9px;
            color: #78350f;
        }

        .warning-box li {
            margin-bottom: 4px;
        }

        .warning-box strong {
            color: #451a03;
            font-weight: 700;
        }

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

        /* ========== BADGES ========== */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-success {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-info {
            background-color: #dbeafe;
            color: #1e40af;
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
                    <h4 class="bidang-box">Credentials Kader</h4>
                </td>
            </tr>
        </table>

        <div class="header-title">
            <h1>Credentials Akun Kader Auto-Generated</h1>
            <h2>{{ $posyanduName }}</h2>
        </div>
    </header>

    <main>
        <div class="container">
            <!-- INFO BOX -->
    <div class="info-box">
        <h3>Informasi Posyandu</h3>
        <div class="info-grid">
            <div class="info-label">Nama Posyandu:</div>
            <div class="info-value">{{ $posyanduName }}</div>
            
            <div class="info-label">Jumlah Kader:</div>
            <div class="info-value">{{ count($kaders) }} Akun (Auto-Generated)</div>
            
            <div class="info-label">Status:</div>
            <div class="info-value">
                <span class="badge badge-success">Terverifikasi</span>
                <span class="badge badge-success">Aktif</span>
            </div>
            
            <div class="info-label">Password Default:</div>
            <div class="info-value"><code style="background: #fee2e2; padding: 2px 6px; border-radius: 3px; font-weight: 700;">password123</code></div>
        </div>
    </div>

    <!-- TABLE CREDENTIALS -->
    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 150px;">Bidang Kader</th>
                <th style="width: 200px;">Email Login</th>
                <th style="width: 140px;">Nomor Telepon</th>
                <th>Password</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($kaders as $index => $kader)
                <tr>
                    <td class="td-center td-bold">{{ $index + 1 }}</td>
                    <td class="td-bold">{{ $kader['bidang'] }}</td>
                    <td>{{ $kader['email'] }}</td>
                    <td>{{ $kader['phone'] ?? '-' }}</td>
                    <td>
                        <span class="password">{{ $kader['password'] }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- WARNING BOX -->
    <div class="warning-box">
        <h4>Catatan Penting</h4>
        <ul>
            <li>Semua akun kader sudah <strong>terverifikasi</strong> dan <strong>aktif</strong>, siap digunakan untuk login</li>
            <li>Password default: <strong>password123</strong> (semua kader menggunakan password yang sama)</li>
            <li>Kader dapat login menggunakan <strong>email</strong> atau <strong>nomor telepon</strong> mereka</li>
            <li>Ketua Kader <strong>wajib</strong> meminta setiap kader untuk mengganti password setelah login pertama kali</li>
            <li>Ketua Kader dapat <strong>melengkapi data</strong> kader (NIK, tanggal lahir, dll) melalui sistem</li>
            <li>Ketua Kader dapat <strong>reset password</strong> kader jika diperlukan melalui menu manajemen user</li>
            <li><strong>⚠️ SIMPAN DOKUMEN INI DENGAN AMAN!</strong> Informasi credentials hanya ditampilkan sekali saat pembuatan posyandu</li>
        </ul>
        </div>
    </main>

    <footer>
        <p>&copy; {{now()->year}} <span>SAPA POSYANDU</span> - Layanan Standar Minimal Pelayanan Posyandu Kabupaten Kebumen</p>
    </footer>
</body>

</html>
