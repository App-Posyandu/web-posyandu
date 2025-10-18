<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BidangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('bidang_pengajuans')->truncate();
        Schema::enableForeignKeyConstraints();

        DB::table('bidang_pengajuans')->insert([
            ['nama_bidang' => 'Bidang Perumahan Rakyat', 'slug' => 'perumahan-rakyat'],
            ['nama_bidang' => 'Bidang Sosial', 'slug' => 'sosial'],
            ['nama_bidang' => 'Bidang Pendidikan', 'slug' => 'pendidikan'],
            ['nama_bidang' => 'Bidang Pekerjaan Umum', 'slug' => 'pekerjaan-umum'],
            ['nama_bidang' => 'Bidang Kesehatan', 'slug' => 'kesehatan'],
            ['nama_bidang' => 'Bidang Trantibumlinmas', 'slug' => 'trantibumlinmas'],
        ]);
    }
}
