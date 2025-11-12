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

    protected $policies = [
        Pengajuan::class => AjuanPolicy::class,
        BukuSaku::class => BukuSakuPolicy::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::define('view', [AjuanPolicy::class, 'view']);
        Gate::define('verify', [AjuanPolicy::class, 'verify']);

        Gate::define('viewAny', [BukuSakuPolicy::class, 'viewAny']);
        Gate::define('view', [BukuSakuPolicy::class, 'view']);
        Gate::define('create', [BukuSakuPolicy::class, 'create']);
        Gate::define('update', [BukuSakuPolicy::class, 'update']);
        Gate::define('delete', [BukuSakuPolicy::class, 'delete']);
    }
}