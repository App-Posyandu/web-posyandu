<?php

namespace Database\Seeders;

use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Posyandu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WilayahSeeder extends Seeder
{
    public function run(): void
    {
        // Dummy data for Kabupaten Kebumen
        $kabupaten = Kabupaten::create([
            'nama_kabupaten' => 'Kebumen',
            'jenis' => 'kabupaten', // harus huruf kecil sesuai enum di migration
        ]);

        // Dummy kecamatan & desa (minimal, bisa ditambah sesuai kebutuhan)
        $kecamatans = [
            'Kebumen' => ['Desa Gemeksekti', 'Desa Kutosari'],
            'Alian' => ['Desa Kalirancang', 'Desa Sidoagung'],
            'Karanganyar' => ['Desa Karanganyar', 'Desa Plarangan'],
        ];

        foreach ($kecamatans as $namaKecamatan => $desaList) {
            $kecamatan = Kecamatan::create([
                'nama_kecamatan' => $namaKecamatan,
                'kabupaten_id' => $kabupaten->id,
            ]);

            foreach ($desaList as $desa) {
                // Buat 1 posyandu per desa
                Posyandu::create([
                    'nama_posyandu' => 'Posyandu ' . Str::random(5),
                    'desa' => $desa,
                    'kecamatan' => $namaKecamatan,
                    'kabupaten' => 'Kebumen',
                    'kecamatan_id' => $kecamatan->id,
                    'kabupaten_id' => $kabupaten->id,
                ]);
            }
        }
    }
}
