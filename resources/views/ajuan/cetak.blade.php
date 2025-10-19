<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Pengajuan - EPOSY</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-KP4tN0s8M1j4XXW0d7xFt9U3QkH8sy0sYwr3HPt1iQrXrRjPOON8p6HQSGCt8yX6Vft0LO0iJ2OKgKBl4d8KQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/js/all.min.js"></script>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-group {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo-group img {
            height: 48px;
        }

        /* ========== MAIN (FLEX FILLER) ========== */
        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 48px 16px;
        }

        .header-title {
            text-align: center;
            flex-grow: 1;

        }

        .header-title>h1 {
            text-transform: uppercase;
            color: #171717;
            font-size: 16px;
        }

        .header-title>h2 {
            text-transform: uppercase;
            color: #171717;
            font-size: 16px;
        }

        .card {
            background-color: #ffffff;
            max-width: 800px;
            width: 100%;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }

        .card h2 {
            font-size: 1.5rem;
            color: #111827;
            margin-bottom: 24px;
        }

        .info-section {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .info-section h3 {
            font-size: 1rem;
            color: #374151;
        }

        .info-section p {
            font-size: 0.95rem;
            color: #4b5563;
            margin-top: 4px;
        }

        /* ========== FOOTER (STAYS AT BOTTOM USING FLEX) ========== */
        footer {
            background-color: #ffffff;
            border-top: 1px solid #e5e7eb;
            padding: 16px;
            text-align: center;
            font-size: 0.875rem;
            color: #6b7280;
        }

        footer span {
            font-weight: 600;
            color: #111827;
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
        <div class="logo-group">
            @if ($kebumenBase64)
                <img src="{{ $kebumenBase64 }}" alt="Logo Kebumen">
            @endif
            @if ($posyanduBase64)
                <img src="{{ $posyanduBase64 }}" alt="Logo Posyandu">
            @endif
        </div>
        <div class="header-title">
            <h1>Formulir Permohonan</h1>
            <h2>Layanan Standar Minimal Pelayanan Posyandu di Kabupaten Kebumen</h2>
        </div>
    </header>

    <main>
        <div class="card">
            <div class="info-section">
                <div>
                    <h3>Nama Pengaju:</h3>
                    <p>{{ $ajuan->user->name }}</p>
                </div>
                <div>
                    <h3>Bidang:</h3>
                    <p>{{ $ajuan->bidang->nama_bidang }}</p>
                </div>
                <div>
                    <h3>Deskripsi Permohonan:</h3>
                    <p>{{ $ajuan->deskripsi_pengajuan }}</p>
                </div>
                <div>
                    <h3>Tindak Lanjut Pengajuan:</h3>
                    <p>{{ $ajuan->tindak_lanjut ?? 'Belum ada tindak lanjut' }}</p>
                </div>
                <div>
                    <h3>Status Pengajuan:</h3>
                    <p>{{ ucfirst($ajuan->status) }}</p>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <p>
            &copy; {{ date('Y') }} <span>EPOSY</span>. Pelayanan Elektronik Posyandu - Dinas Kesehatan Kabupaten
            Kebumen.<br>
            Dikembangkan oleh tim untuk mendukung layanan kesehatan masyarakat.
        </p>
    </footer>
</body>

</html>
