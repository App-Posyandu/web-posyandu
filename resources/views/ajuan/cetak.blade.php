<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Pengajuan - EPOSY</title>
    <style>
        /* ========== RESET & BASE STYLING ========== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        html, body {
            height: 100%;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f3f4f6;
            color: #1f2937;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        h1, h2, h3 {
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

        header h1 {
            font-size: 1.5rem;
            color: #111827;
        }

        header p {
            font-size: 0.875rem;
            color: #4b5563;
        }

        .logo-group {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo-group img {
            height: 48px;
        }

        /* ========== DROPDOWN ========== */
        .dropdown {
            position: relative;
        }

        .dropdown button {
            display: inline-flex;
            align-items: center;
            padding: 8px 12px;
            font-size: 0.875rem;
            color: #6b7280;
            background-color: white;
            border: 1px solid transparent;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }

        .dropdown button:hover {
            color: #374151;
            background-color: #f9fafb;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 110%;
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            min-width: 180px;
            z-index: 20;
        }

        .dropdown-content a {
            display: block;
            padding: 10px 14px;
            font-size: 0.875rem;
            color: #374151;
            transition: background 0.2s;
        }

        .dropdown-content a:hover {
            background-color: #f3f4f6;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        /* ========== MAIN (FLEX FILLER) ========== */
        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 48px 16px;
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
    <header>
        <div>
            <a href="/">
                <h1>EPOSY</h1>
                <p>Pelayanan Elektronik Posyandu</p>
            </a>
        </div>

        <div class="logo-group">
            <img src="{{ asset('assets/image/logo/logo_kebumen.png') }}" alt="Logo Kebumen">
            <img src="{{ asset('assets/image/logo/logo_posyandu.png') }}" alt="Logo Posyandu">
        </div>

        <div class="dropdown">
            <button>
                <span>{{ Auth::user()->name }}</span>
                <span style="margin-left: 4px;">▼</span>
            </button>
            <div class="dropdown-content">
                <a href="{{ route('ajuan.index') }}">Lihat Pengajuan</a>
                <a href="{{ route('profile.edit') }}">Profile</a>
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">Log Out</a>
                </form>
            </div>
        </div>
    </header>

    <main>
        <div class="card">
            <h2>Cetak Pengajuan</h2>
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
            &copy; {{ date('Y') }} <span>EPOSY</span>. Pelayanan Elektronik Posyandu - Dinas Kesehatan Kabupaten Kebumen.<br>
            Dikembangkan oleh tim untuk mendukung layanan kesehatan masyarakat.
        </p>
    </footer>
</body>

</html>
