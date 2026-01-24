<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Revision Deadline Configuration
    |--------------------------------------------------------------------------
    |
    | Durasi deadline untuk revisi pengajuan.
    | - 'days': Jumlah hari untuk production
    | - 'debug_minutes': Jumlah menit untuk testing (set null untuk disable debug)
    |
    */

    'deadline' => [
        'days' => 5, // Production: 5 hari
        'debug_minutes' => 1, // Debug: set ke 1, 5, 10 untuk testing (null = production mode)
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | Set true untuk menggunakan debug_minutes, false untuk production
    |
    */
    'debug_mode' => env('REVISION_DEBUG_MODE', false),
];
