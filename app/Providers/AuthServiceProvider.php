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
    public function register(): void
    {
    }
    public function boot(): void
    {
        Gate::define('viewAjuan', [AjuanPolicy::class, 'viewAjuan']);
        Gate::define('verify', [AjuanPolicy::class, 'verify']);
        Gate::define('viewAny', [BukuSakuPolicy::class, 'viewAny']);
        Gate::define('viewBukuSaku', [BukuSakuPolicy::class, 'viewBukuSaku']);
        Gate::define('create', [BukuSakuPolicy::class, 'create']);
        Gate::define('update', [BukuSakuPolicy::class, 'update']);
        Gate::define('delete', [BukuSakuPolicy::class, 'delete']);

        Gate::define('admin.ajuan.pilih-user', function (User $user): bool {
            return $this->hasAnyRole($user, ['admin', 'kabid', 'ketua-posyandu', 'kader', 'admin-kecamatan']);
        });

        Gate::define('admin.settings.manage', function (User $user): bool {
            return $this->hasAnyRole($user, ['admin', 'admin-kabupaten']);
        });

        Gate::define('admin.users.manage', function (User $user): bool {
            return $this->hasAnyRole($user, ['admin', 'admin-kabupaten', 'operator-desa', 'kader']);
        });

        Gate::define('admin.posyandu.manage', function (User $user): bool {
            return $this->hasAnyRole($user, ['admin', 'admin-kabupaten', 'admin-kecamatan', 'operator-desa', 'kabid', 'ketua-timpembina-posyandu']);
        });

        Gate::define('admin.laporan.view', function (User $user): bool {
            return $this->hasAnyRole($user, ['admin', 'admin-kabupaten', 'kabid', 'ketua-timpembina-posyandu', 'ketua-posyandu', 'kader', 'admin-kecamatan', 'operator-desa', 'kades', 'bu-kades']);
        });
    }

    private function hasAnyRole(User $user, array $roles): bool
    {
        return in_array($user->role, $roles, true);
    }
}
