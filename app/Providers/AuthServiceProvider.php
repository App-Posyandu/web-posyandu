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

    public function register(): void {}
    public function boot(): void
    {
        $this->registerPolicies();
    }
}