<?php

namespace Database\Seeders;

use App\Models\Posyandu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PosyanduSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Posyandu::truncate();
        Schema::enableForeignKeyConstraints();

        $posyandus = [
            [
                'nama_posyandu' => 'Posyandu Melati',
                'desa' => 'Desa Sukamaju',
                'kecamatan' => 'Cimahi Utara',
                'kabupaten' => 'Kota Cimahi',
            ],
        ];

        // 3. Loop dan gunakan Model::create()
        foreach ($posyandus as $posyandu) {
            Posyandu::create($posyandu);
        }
    }
}