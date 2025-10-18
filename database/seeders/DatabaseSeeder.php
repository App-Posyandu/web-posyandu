<?php

namespace Database\Seeders;

use App\Models\Pengajuan;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PosyanduSeeder::class,
            BidangSeeder::class,
            UserSeeder::class,
            PengajuanSeeder::class,
        ]);
    }
}
