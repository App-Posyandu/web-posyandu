<?php

namespace Database\Seeders;

use App\Models\BidangPengajuan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BidangSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        BidangPengajuan::truncate();
        Schema::enableForeignKeyConstraints();

        $bidangs = [
            ['nama_bidang' => 'Bidang Perumahan Rakyat', 'slug' => 'perumahan-rakyat'],
            ['nama_bidang' => 'Bidang Sosial', 'slug' => 'sosial'],
            ['nama_bidang' => 'Bidang Pendidikan', 'slug' => 'pendidikan'],
            ['nama_bidang' => 'Bidang Pekerjaan Umum', 'slug' => 'pekerjaan-umum'],
            ['nama_bidang' => 'Bidang Kesehatan', 'slug' => 'kesehatan'],
            ['nama_bidang' => 'Bidang Trantibumlinmas', 'slug' => 'trantibumlinmas'],
        ];

        foreach ($bidangs as $bidang) {
            BidangPengajuan::create($bidang);
        }
    }
}