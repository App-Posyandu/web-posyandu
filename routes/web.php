<?php

use App\Http\Controllers\AjuanController;
use App\Http\Controllers\BukuSakuController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoogleLoginController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PenimbanganController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['verified'])
        ->name('dashboard');

    // 2. RUTE BARU: Halaman "Pilih Layanan" untuk masyarakat
    Route::get('/pilih-layanan', [AjuanController::class, 'pilihLayanan'])
        ->name('dashboard.partials.pilih-layanan');

    // 3. RUTE BARU: Halaman untuk Kader memilih user
    Route::get('/admin/ajuan/pilih-user', [AjuanController::class, 'pilihUser'])
        ->middleware('role:admin,kabid,ketua-kader,kader') // Hanya role admin
        ->name('dashboard.partials.pilih-user');

    //Rute untuk Detail Ajuan dan Cetak Ajuan
    Route::get('/ajuan/create/{bidang}', [AjuanController::class, 'create'])->name('ajuan.create');
    Route::post('/ajuan/store-permohonan', [AjuanController::class, 'storePermohonan'])->name('ajuan.store.permohonan');
    Route::get('/ajuan/{ajuan}/edit', [AjuanController::class, 'edit'])->name('ajuan.edit');
    Route::patch('/ajuan/{ajuan}', [AjuanController::class, 'update'])->name('ajuan.update');

    Route::get('/ajuan/administrasi', [AjuanController::class, 'createAdministrasi'])->name('ajuan.create.administrasi');
    Route::post('/ajuan/store-administrasi', [AjuanController::class, 'storeAdministrasi'])->name('ajuan.store.administrasi');
    Route::get('/ajuan/{ajuan}', [AjuanController::class, 'show'])->name('ajuan.show');
    // Route::get('/ajuan/dokumen/download', [AjuanController::class, 'downloadDokumen'])->name('ajuan.dokumen.download');
    Route::get('/ajuan/{ajuan}/dokumen/{key}', [AjuanController::class, 'downloadDokumen'])->name('ajuan.dokumen.download');
    Route::patch('/ajuan/{ajuan}/verify', [AjuanController::class, 'verifyAjuan'])->name('ajuan.verify');

    Route::middleware(['verified', 'role:kader,kabid,masyarakat,ketua-kader,admin'])->group(function () {
        Route::get('/ajuan', [AjuanController::class, 'index'])->name('ajuan.index');
    });
    Route::get('/ajuan/cetak/{id}', [AjuanController::class, 'cetak'])->name('ajuan.cetak');
    // Route::resource('buku-saku', BukuSakuController::class);
    Route::get('buku-saku', [BukuSakuController::class, 'index'])->name('buku-saku.index');
    Route::post('buku-saku', [BukuSakuController::class, 'store'])
        ->name('buku-saku.store')
        ->middleware('role:kabid,admin');
    Route::get('buku-saku/stream', [BukuSakuController::class, 'stream'])
        ->name('buku-saku.stream');
    // --- Rute Profil (dari Breeze) ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    // Rute untuk Kader & Kabid
    Route::middleware(['role:ketua-kader,kader,kabid'])->group(function () {
        Route::resource('penimbangan', PenimbanganController::class);
        //export to excel
        Route::get('/export-excel', [LaporanController::class, 'exportExcel'])->name('laporan.exportExcel');
    });

    Route::middleware(['role:kabid,kader,admin,ketua-kader'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::resource('users', UserController::class);
        Route::patch('/users/{user}/verify', [UserController::class, 'verify'])->name('users.verify');
    });
});

// Rute Otentikasi Google Socialite
Route::get('/auth/google/redirect', [GoogleLoginController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleLoginController::class, 'handleGoogleCallback']);

// Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/set-session', function () {
    session(['test' => 'Berhasil']);
    return 'Session di-set!';
});

Route::get('/get-session', function () {
    return session('test', 'Gagal, session kosong!');
});

// Memuat semua rute otentikasi dari Breeze (login, register, dll.)
require __DIR__ . '/auth.php';
