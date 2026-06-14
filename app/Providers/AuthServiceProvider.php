<?php

namespace App\Providers;

use App\Models\BukuSaku;
use App\Models\Pengajuan;
use App\Models\User;
use App\Policies\AjuanPolicy;
use App\Policies\BukuSakuPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Pengajuan::class => AjuanPolicy::class,
        BukuSaku::class => BukuSakuPolicy::class,
    ];

    public function register(): void {}

    public function boot(): void
    {
        $this->registerPolicies();

        // Gate untuk AuthorizeAdminRoute middleware
        // admin.posyandu.manage — siapa yang boleh kelola posyandu
        Gate::define('admin.posyandu.manage', function (User $user) {
            return in_array($user->role, [
                'admin',
                'admin-kabupaten',
                'kabid',
                'admin-kecamatan',
                'ketua-timpembina-posyandu',
                'operator-desa',
                'kades',
                'bu-kades',
                'ketua-posyandu',
            ]);
        });

        // admin.users.manage — siapa yang boleh kelola user & import
        Gate::define('admin.users.manage', function (User $user) {
            return in_array($user->role, [
                'admin',
                'admin-kabupaten',
                'kabid',
                'admin-kecamatan',
                'operator-desa',
                'kades',
                'bu-kades',
                'ketua-posyandu',
                'kader',
            ]);
        });

        // admin.settings.manage — siapa yang boleh kelola pengaturan sistem
        Gate::define('admin.settings.manage', function (User $user) {
            return in_array($user->role, ['admin', 'admin-kabupaten']);
        });

        // admin.laporan.view — siapa yang boleh lihat laporan
        Gate::define('admin.laporan.view', function (User $user) {
            return in_array($user->role, [
                'admin',
                'admin-kabupaten',
                'kabid',
                'admin-kecamatan',
                'ketua-timpembina-posyandu',
                'operator-desa',
                'kades',
                'bu-kades',
                'ketua-posyandu',
                'kader',
            ]);
        });

        // admin.ajuan.pilih-user — siapa yang boleh pilih user saat buat ajuan
        Gate::define('admin.ajuan.pilih-user', function (User $user) {
            return in_array($user->role, [
                'admin',
                'kabid',
                'kader',
                'admin-kecamatan',
            ]);
        });
    }
}