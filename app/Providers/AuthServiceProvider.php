<?php

namespace App\Providers;

use App\Models\BukuSaku;
use App\Models\Pengajuan;
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
    }
}
