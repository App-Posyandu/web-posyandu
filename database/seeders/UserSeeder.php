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
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        User::truncate();
        Schema::enableForeignKeyConstraints();

        $allPosyandu = Posyandu::all();
        $allBidang = BidangPengajuan::all();

        // 1 admin
        User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@dummy.com',
            'bidang_id' => $allBidang->random()->id ?? null,
            'posyandu_id' => $allPosyandu->random()->id ?? null,
        ]);

        // Role yang diminta 5 user
        $roles5 = [
            'kabid',
            'admin-kabupaten',
            'kades',
            'bu-kades',
            'ketua-timpembina-posyandu',
            'operator-desa',
        ];
        foreach ($roles5 as $role) {
            for ($i = 1; $i <= 5; $i++) {
                User::factory()->create([
                    'role' => $role,
                    'email' => $role . $i . '@dummy.com',
                    'bidang_id' => $allBidang->random()->id ?? null,
                    'posyandu_id' => $allPosyandu->random()->id ?? null,
                ]);
            }
        }

        // Role lain (masing-masing 1 user, posyandu diacak)
        $roles1 = [
            'admin-kecamatan',
            'ketua-posyandu',
            'masyarakat',
        ];
        foreach ($roles1 as $role) {
            User::factory()->create([
                'role' => $role,
                'email' => $role . '@dummy.com',
                'bidang_id' => $allBidang->random()->id ?? null,
                'posyandu_id' => $allPosyandu->random()->id ?? null,
            ]);
        }

        // 6 kader, 6 bidang berbeda, semua di 1 posyandu yang sama
        $kaderPosyandu = $allPosyandu->random();
        $bidangList = $allBidang->take(6);
        $kaderNum = 1;
        foreach ($bidangList as $bidang) {
            User::factory()->create([
                'role' => 'kader',
                'email' => 'kader' . $kaderNum . '@dummy.com',
                'bidang_id' => $bidang->id,
                'posyandu_id' => $kaderPosyandu->id,
            ]);
            $kaderNum++;
        }
    }
}