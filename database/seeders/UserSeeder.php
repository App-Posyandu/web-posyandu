<?php

namespace Database\Seeders;

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

        User::create([
            'posyandu_id' => 1, // Mengasumsikan sudah ada posyandu dengan id=1
            'name' => 'Kader Eposy',
            'email' => 'kader@eposy.com',
            'password' => Hash::make('password'), // Passwordnya adalah 'password'
            'role' => 'kader',
            'nik' => '3301234567890001',
            'alamat' => 'Jl. Merdeka No. 1, Purwokerto',
            'tempat_lahir' => 'Purwokerto',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Perempuan',
            // Kolom ktp dan kk bisa dikosongkan (nullable) atau diisi placeholder
            'ktp' => null,
            'kk' => null,
            'email_verified_at' => now(),
        ]);
        User::factory(15)->create();
    }
}
