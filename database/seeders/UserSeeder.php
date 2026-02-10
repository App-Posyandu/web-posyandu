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

        $allBidang = BidangPengajuan::all();
        $allPosyandu = Posyandu::all();

        // Ambil satu kabupaten (dari posyandu pertama)
        $mainPosyandu = $allPosyandu->first();
        $mainKabupaten = $mainPosyandu->kabupaten;
        $mainKabupatenId = $mainPosyandu->kabupaten_id;

        // 1 admin
        User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@dummy.com',
            'bidang_id' => null,
            'posyandu_id' => null,
            'kabupaten' => $mainKabupaten,
            'kabupaten_id' => $mainKabupatenId,
            'kecamatan' => null,
            'desa' => null,
        ]);

        // 5 admin-kabupaten
        for ($i = 1; $i <= 5; $i++) {
            User::factory()->create([
                'role' => 'admin-kabupaten',
                'email' => 'admin-kabupaten' . $i . '@dummy.com',
                'posyandu_id' => null,
                'bidang_id' => null,
                'kabupaten' => $mainKabupaten,
                'kabupaten_id' => $mainKabupatenId,
                'kecamatan' => null,
                'desa' => null,
            ]);
        }

        // kabid sejumlah bidang SPM (semua di kabupaten yang sama)
        $bidangNum = 1;
        foreach ($allBidang as $bidang) {
            User::factory()->create([
                'role' => 'kabid',
                'email' => 'kabid' . $bidangNum . '@dummy.com',
                'bidang_id' => $bidang->id,
                'posyandu_id' => null,
                'kabupaten' => $mainKabupaten,
                'kabupaten_id' => $mainKabupatenId,
                'kecamatan' => null,
                'desa' => null,
            ]);
            $bidangNum++;
        }

        // 5 ketua-timpembina-posyandu (semua di kabupaten yang sama)
        for ($i = 1; $i <= 5; $i++) {
            User::factory()->create([
                'role' => 'ketua-timpembina-posyandu',
                'email' => 'ketua-timpembina-posyandu' . $i . '@dummy.com',
                'bidang_id' => null,
                'posyandu_id' => null,
                'kabupaten' => $mainKabupaten,
                'kabupaten_id' => $mainKabupatenId,
                'kecamatan' => null,
                'desa' => null,
            ]);
        }

        $posyanduNum = 1;
        foreach ($allPosyandu as $posyandu) {
            $kabupaten = $posyandu->kabupaten;
            $kabupatenId = $posyandu->kabupaten_id;
            $kecamatan = $posyandu->kecamatan;
            $kecamatanId = $posyandu->kecamatan_id;
            $desa = $posyandu->desa;

            // 1 admin-kecamatan
            User::factory()->create([
                'role' => 'admin-kecamatan',
                'email' => 'admin-kecamatan' . $posyanduNum . '@dummy.com',
                'bidang_id' => null,
                'posyandu_id' => null,
                'kabupaten' => $kabupaten,
                'kabupaten_id' => $kabupatenId,
                'kecamatan' => $kecamatan,
                'kecamatan_id' => $kecamatanId,
                'desa' => $desa,
            ]);

            // 1 kades
            User::factory()->create([
                'role' => 'kades',
                'email' => 'kades' . $posyanduNum . '@dummy.com',
                'bidang_id' => null,
                'posyandu_id' => null,
                'kabupaten' => $kabupaten,
                'kabupaten_id' => $kabupatenId,
                'kecamatan' => $kecamatan,
                'kecamatan_id' => $kecamatanId,
                'desa' => $desa,
            ]);

            // 1 bu-kades
            User::factory()->create([
                'role' => 'bu-kades',
                'email' => 'bu-kades' . $posyanduNum . '@dummy.com',
                'bidang_id' => null,
                'posyandu_id' => null,
                'kabupaten' => $kabupaten,
                'kabupaten_id' => $kabupatenId,
                'kecamatan' => $kecamatan,
                'kecamatan_id' => $kecamatanId,
                'desa' => $desa,
            ]);

            // 1 ketua-posyandu
            User::factory()->create([
                'role' => 'ketua-posyandu',
                'email' => 'ketua-posyandu' . $posyanduNum . '@dummy.com',
                'bidang_id' => null,
                'posyandu_id' => $posyandu->id,
                'kabupaten' => $kabupaten,
                'kabupaten_id' => $kabupatenId,
                'kecamatan' => $kecamatan,
                'kecamatan_id' => $kecamatanId,
                'desa' => $desa,
            ]);

            // 1 operator-desa
            User::factory()->create([
                'role' => 'operator-desa',
                'email' => 'operator-desa' . $posyanduNum . '@dummy.com',
                'bidang_id' => null,
                'posyandu_id' => null,
                'kabupaten' => $kabupaten,
                'kabupaten_id' => $kabupatenId,
                'kecamatan' => $kecamatan,
                'kecamatan_id' => $kecamatanId,
                'desa' => $desa,
            ]);

            // 6 kader (satu untuk setiap bidang SPM) di posyandu yang sama
            $kaderNum = 1;
            foreach ($allBidang as $bidang) {
                User::factory()->create([
                    'role' => 'kader',
                    'email' => 'kader-posyandu' . $posyanduNum . '-bidang' . $kaderNum . '@dummy.com',
                    'bidang_id' => $bidang->id,
                    'posyandu_id' => $posyandu->id,
                    'kabupaten' => $kabupaten,
                    'kabupaten_id' => $kabupatenId,
                    'kecamatan' => $kecamatan,
                    'kecamatan_id' => $kecamatanId,
                    'desa' => $desa,
                ]);
                $kaderNum++;
            }

            $posyanduNum++;
        }

        // 1 masyarakat (acak)
        $jumlahMasyarakat = 10;

        for ($i = 1; $i <= $jumlahMasyarakat; $i++) {
            $posyandu = $allPosyandu->random();

            User::factory()->create([
                'role' => 'masyarakat',
                'email' => 'masyarakat' . $i . '@dummy.com',
                'bidang_id' => null,
                'posyandu_id' => $posyandu->id,
                'kabupaten' => $posyandu->kabupaten,
                'kabupaten_id' => $posyandu->kabupaten_id,
                'kecamatan' => $posyandu->kecamatan,
                'kecamatan_id' => $posyandu->kecamatan_id,
                'desa' => $posyandu->desa,
            ]);
        }
    }
}