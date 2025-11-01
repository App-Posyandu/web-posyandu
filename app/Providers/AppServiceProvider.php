<?php

namespace App\Providers;

use App\Models\BukuSaku;
use App\Models\Pengajuan;
use App\Policies\AjuanPolicy;
use App\Policies\BukuSakuPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Pengajuan::class => AjuanPolicy::class,
        BukuSaku::class => BukuSakuPolicy::class,
    ];
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();
    }
}
