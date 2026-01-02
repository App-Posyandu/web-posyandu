<?php

namespace Database\Seeders;

use App\Models\BidangPengajuan;
use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        User::truncate();
        Schema::enableForeignKeyConstraints();

        $firstPosyandu = Posyandu::first();
        $firstBidang = BidangPengajuan::first();

        User::create([
            'bidang_id' => $firstBidang ? $firstBidang->id : null,
            'posyandu_id' => $firstPosyandu ? $firstPosyandu->id : null,
            'name' => 'Kader Eposy',
            'email' => 'kader@eposy.com',
            'password' => Hash::make('password'),
            'role' => 'kader',
            'verified_at' => now(),
            'nik' => '3301234567890001',
            'alamat' => 'Jl. Merdeka No. 1, Purwokerto',
            'tempat_lahir' => 'Purwokerto',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Perempuan',
            'no_telepon' => '081200000001',
            'ktp' => null,
            'kk' => null,
            'email_verified_at' => now(),
        ]);

        // 5. Factory Anda juga harus sudah diperbarui
        User::factory(15)->create();
    }
}
