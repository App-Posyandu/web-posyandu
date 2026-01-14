<?php

use App\Http\Controllers\AjuanController;
use App\Http\Controllers\BukuSakuController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoogleLoginController;
use App\Http\Controllers\KecamatanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PenimbanganController;
use App\Http\Controllers\PosyanduController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Posyandu;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/cetak-laporan-teknologi', function () {
    // 1. Definisi Deskripsi & Kategori (Database Kecil)
    // Kita map package ke deskripsi bahasa Indonesia agar laporan terlihat profesional
    $libraryMap = [
        // Backend (Composer)
        'laravel/framework' => 'Core Framework utama aplikasi.',
        'livewire/livewire' => 'Framework full-stack untuk antarmuka dinamis.',
        'maatwebsite/excel' => 'Fitur Export dan Import data Excel.',
        'phpoffice/phpspreadsheet' => 'Engine pengolah spreadsheet (Excel).',
        'barryvdh/laravel-dompdf' => 'Library untuk mencetak laporan PDF.',
        'dompdf/dompdf' => 'Converter HTML ke PDF.',
        'laravel/socialite' => 'Autentikasi login pihak ketiga (Google/Sosmed).',
        'sweetalert2/laravel' => 'Notifikasi popup interaktif (Server side).',
        'blade-ui-kit/blade-icons' => 'Komponen ikon untuk Blade template.',
        'mckenziearts/blade-untitledui-icons' => 'Set ikon tambahan untuk UI.',

        // Frontend (NPM)
        'tailwindcss' => 'Framework CSS Utility-first untuk styling tampilan.',
        'chart.js' => 'Library untuk visualisasi grafik data.',
        'chartjs-plugin-datalabels' => 'Plugin label data untuk grafik.',
        'alpinejs' => 'Interaktivitas ringan JavaScript (Dropdown, Modal).',
        'axios' => 'HTTP Client untuk request API.',
        'sweetalert2' => 'Notifikasi popup cantik (Client side).',
        'bootstrap-icons' => 'Set ikon vektor standar.',
    ];

    // 2. Baca File Composer.json
    $composerPath = base_path('composer.json');
    $composerData = json_decode(File::get($composerPath), true);
    $composerPackages = $composerData['require'] ?? [];

    // 3. Baca File Package.json
    $npmPath = base_path('package.json');
    $npmData = json_decode(File::get($npmPath), true);
    // Gabung dependencies dan devDependencies (karena Tailwind sering di dev)
    $npmPackages = array_merge(
        $npmData['dependencies'] ?? [],
        $npmData['devDependencies'] ?? []
    );

    // 4. Filter: Hanya ambil yang ada di daftar $libraryMap (Yang penting saja)
    $laporan = [
        'backend' => [],
        'frontend' => []
    ];

    foreach ($composerPackages as $name => $version) {
        if (isset($libraryMap[$name])) {
            $laporan['backend'][] = [
                'name' => $name,
                'version' => $version,
                'desc' => $libraryMap[$name]
            ];
        }
    }

    foreach ($npmPackages as $name => $version) {
        if (isset($libraryMap[$name])) {
            $laporan['frontend'][] = [
                'name' => $name,
                'version' => $version,
                'desc' => $libraryMap[$name]
            ];
        }
    }

    // 5. Generate PDF menggunakan Blade View on-the-fly
    $html = '
    <html>
    <head>
        <style>
            body { font-family: sans-serif; color: #333; }
            h1 { text-align: center; color: #2d3748; }
            h3 { border-bottom: 2px solid #4a5568; padding-bottom: 5px; margin-top: 30px; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
            th { background-color: #f7fafc; }
            .badge { background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-size: 0.8em; font-family: monospace; }
        </style>
    </head>
    <body>
        <h1>Laporan Teknologi Aplikasi Posyandu</h1>
        <p>Berikut adalah daftar pustaka (library) dan teknologi utama yang digunakan untuk membangun fitur aplikasi ini.</p>

        <h3>A. Backend & Framework (PHP/Laravel)</h3>
        <table>
            <thead>
                <tr>
                    <th>Nama Package</th>
                    <th>Versi</th>
                    <th>Fungsi Utama</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($laporan['backend'] as $item) {
        $html .= '<tr>
            <td><strong>' . $item['name'] . '</strong></td>
            <td><span class="badge">' . $item['version'] . '</span></td>
            <td>' . $item['desc'] . '</td>
        </tr>';
    }

    $html .= '</tbody>
        </table>

        <h3>B. Frontend & UI (Node.js)</h3>
        <table>
            <thead>
                <tr>
                    <th>Nama Package</th>
                    <th>Versi</th>
                    <th>Fungsi Utama</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($laporan['frontend'] as $item) {
        $html .= '<tr>
            <td><strong>' . $item['name'] . '</strong></td>
            <td><span class="badge">' . $item['version'] . '</span></td>
            <td>' . $item['desc'] . '</td>
        </tr>';
    }

    $html .= '</tbody>
        </table>

        <br><br>
        <p style="text-align: right; font-size: 0.9em; color: #777;">
            <em>Generated automatically by System on ' . date('d F Y') . '</em>
        </p>
    </body>
    </html>';

    // Load HTML ke DomPDF
    $pdf = Pdf::loadHTML($html);
    return $pdf->stream('Laporan-Teknologi-Posyandu.pdf');
});

Route::prefix('api/wilayah')->group(function () {
    // Ambil daftar kabupaten (Jawa Tengah)
    Route::get('kabupaten', function () {
        $response = Http::get(env('API_WILAYAH_URL') . 'regencies/33.json'); // 33 = ID Jawa Tengah
        return $response->json();
    })->name('api.kabupaten.public');

    // Ambil daftar kecamatan
    Route::get('kecamatan/{kabupaten_id}', function ($kabupaten_id) {
        $response = Http::get(env('API_WILAYAH_URL') . "districts/{$kabupaten_id}.json");
        return $response->json();
    })->name('api.kecamatan.public');

    // Ambil daftar desa
    Route::get('desa/{kecamatan_id}', function ($kecamatan_id) {
        $response = Http::get(env('API_WILAYAH_URL') . "villages/{$kecamatan_id}.json");
        return $response->json();
    })->name('api.desa.public');

    // Ambil daftar posyandu berdasarkan nama desa
    Route::get('posyandu', [PosyanduController::class, 'getPosyanduByWilayah'])->name('api.posyandu.by-wilayah');
});

Route::prefix('api/db')->group(function () {
    // Get all kabupaten dari database
    Route::get('kabupaten', function () {
        $kabupatens = Kabupaten::orderBy('jenis')->orderBy('nama_kabupaten')->get();
        return response()->json([
            'success' => true,
            'data' => $kabupatens
        ]);
    })->name('api.db.kabupaten');

    // Get kecamatan berdasarkan kabupaten_id dari database
    Route::get('kecamatan/{kabupaten_id}', function ($kabupaten_id) {
        $kecamatans = Kecamatan::where('kabupaten_id', $kabupaten_id)
            ->orderBy('nama_kecamatan')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $kecamatans
        ]);
    })->name('api.db.kecamatan');

    // Get posyandu berdasarkan kecamatan_id dari database
    Route::get('posyandu/by-kecamatan/{kecamatan_id}', function ($kecamatan_id) {
        $posyandus = Posyandu::where('kecamatan_id', $kecamatan_id)
            ->orderBy('nama_posyandu')
            ->get(['id', 'nama_posyandu', 'desa']);

        return response()->json([
            'success' => true,
            'data' => $posyandus
        ]);
    })->name('api.db.posyandu.by-kecamatan');

    // Get posyandu berdasarkan kabupaten_id dari database
    Route::get('posyandu/by-kabupaten/{kabupaten_id}', function ($kabupaten_id) {
        $posyandus = Posyandu::with('kecamatanRelation')
            ->where('kabupaten_id', $kabupaten_id)
            ->orderBy('nama_posyandu')
            ->get(['id', 'nama_posyandu', 'desa', 'kecamatan_id']);

        return response()->json([
            'success' => true,
            'data' => $posyandus
        ]);
    })->name('api.db.posyandu.by-kabupaten');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    // ✅ PILIH LAYANAN
    Route::get('/pilih-layanan', [AjuanController::class, 'pilihLayanan'])
        ->name('dashboard.partials.pilih-layanan');

    // ✅ PILIH USER (UNTUK KADER MEMBUAT AJUAN ATAS NAMA MASYARAKAT)
    Route::get('/admin/ajuan/pilih-user', [AjuanController::class, 'pilihUser'])
        ->middleware('role:admin,kabid,ketua-kader,kader,admin-kecamatan')
        ->name('dashboard.partials.pilih-user');

    Route::post('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    Route::post('users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');

    // ✅ ROUTE AJUAN - TANPA MIDDLEWARE TAMBAHAN
    Route::get('/ajuan', [AjuanController::class, 'index'])->name('ajuan.index');
    Route::get('/ajuan/create/{bidang}', [AjuanController::class, 'create'])->name('ajuan.create');
    Route::post('/ajuan/store-permohonan', [AjuanController::class, 'storePermohonan'])->name('ajuan.store.permohonan');
    Route::get('/ajuan/administrasi', [AjuanController::class, 'createAdministrasi'])->name('ajuan.create.administrasi');
    Route::post('/ajuan/store-administrasi', [AjuanController::class, 'storeAdministrasi'])->name('ajuan.store.administrasi');
    Route::get('/ajuan/{ajuan}', [AjuanController::class, 'show'])->name('ajuan.show');
    Route::get('/ajuan/{ajuan}/edit', [AjuanController::class, 'edit'])->name('ajuan.edit');
    Route::patch('/ajuan/{ajuan}', [AjuanController::class, 'update'])->name('ajuan.update');
    Route::get('/ajuan/{ajuan}/dokumen/{key}', [AjuanController::class, 'downloadDokumen'])->name('ajuan.dokumen.download');
    Route::get('/ajuan/{ajuan}/dokumen/{key}/stream', [AjuanController::class, 'streamDokumen'])->name('ajuan.dokumen.stream');
    Route::patch('/ajuan/{ajuan}/verify', [AjuanController::class, 'verifyAjuan'])->name('ajuan.verify');
    Route::get('/ajuan/cetak/{id}', [AjuanController::class, 'cetak'])->name('ajuan.cetak');
    Route::get('/ajuan/get-items/{slug}', [AjuanController::class, 'getItemsAjax'])
        ->name('ajuan.get-items')
        ->middleware(['auth', 'verified']);

    // ✅ BUKU SAKU
    Route::resource('buku_saku', BukuSakuController::class);
    Route::get('/buku_saku/{bukuSaku}/stream', [BukuSakuController::class, 'stream'])->name('buku_saku.stream');

    // ✅ PROFIL
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ✅ API INTERNAL
    Route::prefix('api')->group(function () {
        Route::get('kecamatan', [PosyanduController::class, 'getKecamatan'])->name('api.kecamatan');
        Route::get('desa', [PosyanduController::class, 'getDesa'])->name('api.desa');
    });

    Route::middleware(['role:operator-desa'])->group(function () {
        Route::patch('admin/users/{user}/reset-password', [UserController::class, 'resetPasswordKader'])
            ->name('admin.users.reset-password');

        Route::patch('admin/users/{user}/deactivate', [UserController::class, 'deactivateKader'])
            ->name('admin.users.deactivate');

        Route::patch('admin/users/{user}/reactivate', [UserController::class, 'reactivateKader'])
            ->name('admin.users.reactivate');
    });

    Route::middleware(['auth'])->prefix('ketua-kader')->name('ketua-kader.')->group(function () {
        Route::get('/takeover', [UserController::class, 'takeoverIndex'])->name('takeover');
        Route::post('/takeover/{kader}/reset', [UserController::class, 'takeoverResetPassword'])->name('takeover.reset');
    });

    // // Admin Kabupaten routes
    Route::middleware(['auth', 'role:admin-kabupaten'])->group(function () {
        Route::patch('/users/{user}/reset-password-kabid', [UserController::class, 'resetPasswordKabid']);
        Route::patch('/users/{user}/deactivate-kabid', [UserController::class, 'deactivateUserKabid']);
        Route::patch('/users/{user}/reactivate-kabid', [UserController::class, 'reactivateUserKabid'])->name('admin.users.reactivate-kabid');
    });

    // ✅ ADMIN ROUTES
    Route::middleware(['role:kabid,kader,admin,ketua-kader,admin-kecamatan,admin-kabupaten,operator-desa,ketua-posyandu'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('admin/posyandu/clear-cache', [PosyanduController::class, 'clearWilayahCache'])
            ->middleware(['auth', 'admin'])
            ->name('admin.posyandu.clear-cache');
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::resource('users', UserController::class);
        Route::patch('/users/{user}/verify', [UserController::class, 'verify'])->name('users.verify');
    });


    Route::middleware(['role:kabid,admin-kecamatan,ketua-kader,ketua-posyandu,operator-desa'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('kecamatan', KecamatanController::class);
        Route::resource('posyandu', PosyanduController::class);

        Route::get('/users/import', function () {
            return view('admin.users.import');
        })->name('import.importPage');
        Route::post('/users/import', [UserController::class, 'importProcess'])->name('import.importProcess');
        Route::get('export-user-template', [UserController::class, 'exportTemplate'])
            ->name('users.export.template');
        //Route::get('/export-all-bidang-dan-desa', [LaporanController::class, 'exportExcelAllBidangDanDesa'])->name('laporan.exportExcelAllBidangDanDesa');

        Route::post('/admin/users/import', [UserController::class, 'importExcel'])->name('users.import');
        Route::post('/admin/posyandu/import', [UserController::class, 'importPosyandu'])->name('posyandu.import');

        Route::get('export-posyandu-all', [PosyanduController::class, 'exportAllPosyandu'])
            ->name('posyandu.export.all');

        // Export berdasarkan kecamatan (single parameter)
        Route::get('export-posyandu-kecamatan/{kecamatan}', [PosyanduController::class, 'exportByKecamatan'])
            ->name('posyandu.export.kecamatan');

        // Export berdasarkan desa (single parameter)
        Route::get('export-posyandu-desa/{desa}', [PosyanduController::class, 'exportByDesa'])
            ->name('posyandu.export.desa');

        // Export dengan filter custom dari form (POST)
        Route::post('export-posyandu', [PosyanduController::class, 'exportWithFilter'])
            ->name('posyandu.export.filter');

        // Export berdasarkan desa dan kecamatan (untuk template import)
        // HARUS DI PALING BAWAH karena catch-all pattern
        Route::get('export-posyandu/{desa}/{kecamatan}', [PosyanduController::class, 'exportByDesaKecamatan'])
            ->name('posyandu.export.template');

        Route::get('/export-all/{desa}', [LaporanController::class, 'exportExcelAll'])->name('laporan.exportExcelAll');
        Route::get('/export/{bidang}/{desa}', [LaporanController::class, 'exportExcelBidang'])->name('laporan.exportExcelBidang');
        Route::get('/export-all-bidang-desa', [LaporanController::class, 'exportExcelAllBidangDanDesa'])->name('laporan.exportExcelAllBidangDanDesa');
        Route::get('/export/{bidang}', [LaporanController::class, 'exportBidangAllDesa'])->name('laporan.exportBidangAllDesa');
    });
});

// ✅ GOOGLE AUTH
Route::get('/auth/google/redirect', [GoogleLoginController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleLoginController::class, 'handleGoogleCallback']);

// Memuat semua rute otentikasi dari Breeze (login, register, dll.)
require __DIR__ . '/auth.php';
