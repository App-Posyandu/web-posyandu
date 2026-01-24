<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('revisions:auto-reject')
    ->hourly() // Jalan setiap jam
    ->withoutOverlapping() // Prevent double execution
    ->onSuccess(function () {
        Log::info('Auto-reject revisions completed successfully');
    })
    ->onFailure(function () {
        Log::error('Auto-reject revisions failed');
    });
