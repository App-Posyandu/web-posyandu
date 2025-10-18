<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PosyanduSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('posyandus')->insert([
            [
                'nama_posyandu' => 'Posyandu Melati',
                'desa' => 'Desa Sukamaju',
                'kecamatan' => 'Cimahi Utara',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_posyandu' => 'Posyandu Mawar',
                'desa' => 'Desa Mekarsari',
                'kecamatan' => 'Cimahi Selatan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_posyandu' => 'Posyandu Anggrek',
                'desa' => 'Desa Cibereum',
                'kecamatan' => 'Cimahi Tengah',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
