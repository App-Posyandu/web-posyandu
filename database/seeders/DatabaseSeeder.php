<?php

namespace Database\Seeders;

use App\Models\BukuSaku;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            WilayahSeeder::class,
            PosyanduSeeder::class,
            BidangSeeder::class,
            UserSeeder::class,
            PengajuanSeeder::class,
            SystemSettingSeeder::class,
        ]);
        BukuSaku::factory(5)->create();
    }
}
