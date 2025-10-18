<?php

use App\Http\Controllers\AjuanController;
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
    Route::get('/ajuan', [AjuanController::class, 'index'])->name('ajuan.index');
    Route::get('/ajuan/create/{bidang}', [AjuanController::class, 'create'])->name('ajuan.create');
    Route::post('/ajuan/store-permohonan', [AjuanController::class, 'storePermohonan'])->name('ajuan.store.permohonan');

    Route::get('/ajuan/administrasi', [AjuanController::class, 'createAdministrasi'])->name('ajuan.create.administrasi');
    Route::post('/ajuan/store-administrasi', [AjuanController::class, 'storeAdministrasi'])->name('ajuan.store.administrasi');

    Route::get('/ajuan/verifikasi', [AjuanController::class, 'showVerifikasi'])->name('ajuan.verifikasi');
    Route::post('/ajuan/store-final', [AjuanController::class, 'storeFinal'])->name('ajuan.store.final');
});

// Rute Otentikasi Google Socialite
Route::get('/auth/google/redirect', [GoogleLoginController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleLoginController::class, 'handleGoogleCallback']);

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');


// Grup rute yang membutuhkan login (bawaan Breeze + Rute Kustom Anda)
Route::middleware('auth')->group(function () {

    Route::get('/ajuan', [AjuanController::class, 'index'])->name('ajuan.index');
    // --- Rute Profil (dari Breeze) ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- Rute Aplikasi Posyandu Anda (Tambahkan di sini) ---

    // Rute untuk Kader & Kabid
    Route::middleware(['role:kader,kabid'])->group(function () {
        Route::resource('penimbangan', PenimbanganController::class);
    });

    // Rute HANYA untuk Kabid
    Route::middleware(['role:kabid,kader,admin,ketua-kader'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::resource('users', UserController::class);
        Route::patch('/users/{user}/verify', [UserController::class, 'verify'])->name('users.verify');
    });
});

Route::get('/set-session', function () {
    session(['test' => 'Berhasil']);
    return 'Session di-set!';
});

Route::get('/get-session', function () {
    return session('test', 'Gagal, session kosong!');
});

// Memuat semua rute otentikasi dari Breeze (login, register, dll.)
require __DIR__ . '/auth.php';
