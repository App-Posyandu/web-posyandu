<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Posyandu;
use App\Models\BidangPengajuan;
use App\Models\Pengajuan;
use App\Models\History;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use Illuminate\Support\Str;

class AjuanTestingSeeder extends Seeder
{
    public function run()
    {
        // 1. Data Bidang (Ambil semua bidang yang sudah ada di database)
        $bidangObjects = BidangPengajuan::all();

        // Jika database benar-benar kosong, buat fallback minimal (untuk jaga-jaga)
        if ($bidangObjects->isEmpty()) {
            $bidangObjects = collect([
                BidangPengajuan::firstOrCreate(['slug' => 'kesehatan'], ['nama_bidang' => 'Kesehatan']),
                BidangPengajuan::firstOrCreate(['slug' => 'pendidikan'], ['nama_bidang' => 'Pendidikan']),
            ]);
        }

        // 2. Data Wilayah (Kabupaten & Kecamatan)
        $kabupatens = [
            'Kebumen' => [
                'Ayah' => ['Desa Ayah 1', 'Desa Ayah 2'],
                'Gombong' => ['Desa Gombong 1', 'Desa Gombong 2']
            ],
            'Banyumas' => [
                'Baturraden' => ['Desa Baturraden 1', 'Desa Baturraden 2'],
                'Purwokerto' => ['Desa Purwokerto 1', 'Desa Purwokerto 2']
            ]
        ];

        $posyandus = [];
        $defaultPassword = bcrypt('password123');

        // Superadmin
        User::updateOrCreate(
            ['email' => 'superadmin_test@test.com'],
            ['name' => 'Superadmin Test', 'password' => $defaultPassword, 'role' => 'admin', 'verified_at' => now()]
        );

        $masyarakatUsers = [];

        foreach ($kabupatens as $kabName => $kecamatanList) {
            $kab = Kabupaten::firstOrCreate(
                ['nama_kabupaten' => $kabName],
                ['jenis' => 'kabupaten']
            );

            // Admin Kabupaten
            User::updateOrCreate(
                ['email' => 'adminkab_' . Str::slug($kabName) . '@test.com'],
                [
                    'name' => "Admin Kab $kabName", 'password' => $defaultPassword, 'role' => 'admin-kabupaten',
                    'kabupaten' => $kabName, 'kabupaten_id' => $kab->id, 'verified_at' => now()
                ]
            );

            // Kabid (Satu untuk tiap bidang di setiap kabupaten)
            foreach ($bidangObjects as $bidang) {
                User::updateOrCreate(
                    ['email' => 'kabid_' . $bidang->slug . '_' . Str::slug($kabName) . '@test.com'],
                    [
                        'name' => "Kabid {$bidang->nama_bidang} $kabName", 'password' => $defaultPassword, 'role' => 'kabid',
                        'kabupaten' => $kabName, 'kabupaten_id' => $kab->id,
                        'bidang_id' => $bidang->id, 'verified_at' => now()
                    ]
                );
            }

            foreach ($kecamatanList as $kecName => $desaList) {
                $kec = Kecamatan::firstOrCreate([
                    'nama_kecamatan' => $kecName,
                    'kabupaten_id' => $kab->id
                ]);

                // Admin Kecamatan
                User::updateOrCreate(
                    ['email' => 'adminkec_' . Str::slug($kecName) . '@test.com'],
                    [
                        'name' => "Admin Kec $kecName", 'password' => $defaultPassword, 'role' => 'admin-kecamatan',
                        'kabupaten' => $kabName, 'kecamatan' => $kecName, 
                        'kabupaten_id' => $kab->id, 'kecamatan_id' => $kec->id, 'verified_at' => now()
                    ]
                );

                foreach ($desaList as $desaName) {
                    // Kades & Operator Desa
                    User::updateOrCreate(
                        ['email' => "kades_" . Str::slug($desaName) . "@test.com"],
                        ['name' => "Kades $desaName", 'password' => $defaultPassword, 'role' => 'kades', 'desa' => $desaName, 'kecamatan' => $kecName, 'kabupaten' => $kabName, 'verified_at' => now()]
                    );
                    User::updateOrCreate(
                        ['email' => "operator_" . Str::slug($desaName) . "@test.com"],
                        ['name' => "Operator $desaName", 'password' => $defaultPassword, 'role' => 'operator-desa', 'desa' => $desaName, 'kecamatan' => $kecName, 'kabupaten' => $kabName, 'verified_at' => now()]
                    );

                    for ($i = 1; $i <= 2; $i++) {
                        $pos = Posyandu::create([
                            'nama_posyandu' => "Posyandu $i $desaName",
                            'kabupaten' => $kabName,
                            'kecamatan' => $kecName,
                            'desa' => $desaName,
                            'kabupaten_id' => $kab->id,
                            'kecamatan_id' => $kec->id,
                        ]);
                        $posyandus[] = $pos;

                        // Ketua Kader
                        User::updateOrCreate(
                            ['email' => "ketua_" . Str::slug($pos->nama_posyandu) . "@test.com"],
                            ['name' => "Ketua " . $pos->nama_posyandu, 'password' => $defaultPassword, 'role' => 'ketua-posyandu', 'posyandu_id' => $pos->id, 'desa' => $desaName, 'kecamatan' => $kecName, 'kabupaten' => $kabName, 'verified_at' => now()]
                        );

                        // Kader untuk Tiap Bidang
                        foreach ($bidangObjects as $index => $bidang) {
                            User::updateOrCreate(
                                ['email' => "kader_" . $bidang->slug . "_" . Str::slug($pos->nama_posyandu) . "@test.com"],
                                ['name' => "Kader {$bidang->nama_bidang} " . $pos->nama_posyandu, 'password' => $defaultPassword, 'role' => 'kader', 'bidang_id' => $bidang->id, 'posyandu_id' => $pos->id, 'desa' => $desaName, 'kecamatan' => $kecName, 'kabupaten' => $kabName, 'verified_at' => now()]
                            );
                        }

                        // Masyarakat
                        $masyarakatUsers[] = User::updateOrCreate(
                            ['email' => "warga_" . Str::slug($pos->nama_posyandu) . "@test.com"],
                            ['name' => "Warga " . $pos->nama_posyandu, 'password' => $defaultPassword, 'role' => 'masyarakat', 'posyandu_id' => $pos->id, 'desa' => $desaName, 'kecamatan' => $kecName, 'kabupaten' => $kabName, 'verified_at' => now()]
                        );
                    }
                }
            }
        }

        // 5. Data Pengajuan
        $statuses = [
            ['status' => 'Diproses', 'submitted_to_desa' => false, 'approved_by_ketua' => false, 'approved_by_kades' => false, 'rejected_by' => null],
            ['status' => 'Diproses', 'submitted_to_desa' => true, 'approved_by_ketua' => true, 'approved_by_kades' => false, 'rejected_by' => null],
            ['status' => 'Disetujui', 'submitted_to_desa' => true, 'approved_by_ketua' => true, 'approved_by_kades' => true, 'rejected_by' => null],
            ['status' => 'Ditolak', 'submitted_to_desa' => false, 'approved_by_ketua' => false, 'approved_by_kades' => false, 'rejected_by' => 'ketua'],
            ['status' => 'Ditolak', 'submitted_to_desa' => true, 'approved_by_ketua' => true, 'approved_by_kades' => false, 'rejected_by' => 'kades'],
        ];

        for ($i = 1; $i <= 50; $i++) {
            $warga = $masyarakatUsers[array_rand($masyarakatUsers)];
            $bidang = $bidangObjects->random();
            $statusSet = $statuses[array_rand($statuses)];
            
            $ajuan = Pengajuan::create([
                'user_id' => $warga->id,
                'bidang_id' => $bidang->id,
                'deskripsi_pengajuan' => "Pengajuan Dummy Testing Filter #$i dari " . $warga->name,
                'status_pengajuan' => $statusSet['status'],
                'tanggal_permohonan' => now()->subDays(rand(1, 30)),
                'submitted_to_desa' => $statusSet['submitted_to_desa'],
                'approved_by_ketua' => $statusSet['approved_by_ketua'],
                'approved_by_kades' => $statusSet['approved_by_kades'],
                'tracking_code' => 'PGJ-2026-' . strtoupper(Str::random(5)) . $i,
            ]);

            // History awal
            History::create([
                'pengajuan_id' => $ajuan->id,
                'diubah_oleh' => $warga->id,
                'action_by_role' => null,
                'status' => 'Diajukan',
                'catatan' => 'Pengajuan awal',
                'created_at' => now(),
            ]);

            // History lanjutan
            if ($statusSet['approved_by_ketua'] || $statusSet['rejected_by'] === 'ketua') {
                $ketuaId = User::where('role', 'ketua-posyandu')->where('posyandu_id', $warga->posyandu_id)->value('id');
                $isRejected = $statusSet['rejected_by'] === 'ketua';
                History::create([
                    'pengajuan_id' => $ajuan->id,
                    'diubah_oleh' => $ketuaId,
                    'action_by_role' => 'ketua-posyandu',
                    'status' => $isRejected ? 'Ditolak Ketua Posyandu' : 'Disetujui Ketua',
                    'catatan' => $isRejected ? 'Pengajuan ditolak oleh ketua posyandu' : 'Telah disetujui ketua posyandu',
                    'created_at' => now(),
                ]);
            }

            if ($statusSet['approved_by_kades'] || $statusSet['rejected_by'] === 'kades') {
                $kadesId = User::where('role', 'kades')->where('desa', $warga->desa)->value('id');
                $isRejected = $statusSet['rejected_by'] === 'kades';
                History::create([
                    'pengajuan_id' => $ajuan->id,
                    'diubah_oleh' => $kadesId,
                    'action_by_role' => 'kades',
                    'status' => $isRejected ? 'Ditolak Kades' : 'Disetujui',
                    'catatan' => $isRejected ? 'Pengajuan ditolak oleh kades' : 'Telah disetujui kades',
                    'created_at' => now(),
                ]);
            }
        }
    }
}
