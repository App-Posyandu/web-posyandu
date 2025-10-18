<?php

namespace Database\Seeders;

use App\Models\BidangPengajuan;
use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class PengajuanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Pengajuan::truncate();
        Schema::enableForeignKeyConstraints();

        // Ambil semua user dan bidang yang ada di database untuk efisiensi
        $users = User::all();
        $bidangs = BidangPengajuan::all();

        if ($users->isEmpty() || $bidangs->isEmpty()) {
            $this->command->info('Tabel users atau bidang_pengajuans kosong, PengajuanSeeder dilewati.');
            return;
        }

        $formTemplates = [
            'pendidikan' => [
                'formulir_items' => [
                    'Pendidikan anak usia dini (0 s.d 6 Tahun)',
                    'Identifikasi ketersediaan dan pengelolaan perpustakaan desa',
                    'Penguatan pemanfaatan literasi',
                    'Identifikasi penyediaan alat peraga edukasi (APE)',
                    'Pembiayaan sekolah',
                    'Perlengkapan sekolah',
                    'Lainnya...',
                ],
                'administrasi_items' => ['ktp' => 'KTP', 'kk' => 'KK', 'surat_keterangan_sekolah' => 'Surat Keterangan Sekolah'],
            ],
            'kesehatan' => [
                'formulir_items' => [
                    'Pemberian alat/sarpras kesehatan',
                    'Kunjungan Posyandu pada sasaran',
                    'Penyuluhan kesehatan',
                    'Deteksi dini risiko masalah kesehatan pada sasaran',
                    'Rujukan ke unit kesehatan desa/kelurahan atau pusat kesehatan masyarakat',
                    'Pemantauan perilaku kepatuhan keluarga untuk mendapatkan pelayanan kesehatan',
                    'Akses untuk mendapatkan imunisasi, vitamin A, tablet tambah darah',
                    'Pemberian makanan tambahan bagi anak usia sekolah',
                    'Lainnya...',
                ],
                'administrasi_items' => ['ktp' => 'KTP', 'kk' => 'KK', 'kartu_bpjs' => 'Kartu BPJS'],
            ],
            'pekerjaan-umum' => [
                'formulir_items' => [
                    'Pemenuhan kebutuhan pokok air bersih',
                    'Pengelolaan limbah domestik/rumah tangga',
                    'Penyediaan WC',
                    'Pengelolaan sampah',
                    'Pemeliharaan/pemeliharaan embung air baku',
                    'Pemeliharaan jaringan air bersih',
                    'Identifikasi/Rehabilitasi sumur air tanah untuk air baku',
                    'Identifikasi kebutuhan pembangunan jalan desa',
                    'Lainnya...',
                ],
                'administrasi_items' => ['ktp' => 'KTP', 'kk' => 'KK', 'surat_permohonan' => 'Surat Permohonan RT/RW'],
            ],
            'perumahan-rakyat' => [
                'formulir_items' => [
                    'Penyediaan dan rehabilitasi rumah layak huni',
                    'Komunikasi, informasi dan edukasi perilaku hidup bersih dan sehat',
                    'Pengelolaan pekarangan rumah untuk budidaya tanaman',
                    'Pembuatan biopori',
                    'Pembuatan hidroponik di pekarangan rumah',
                    'Lainnya...',
                ],
                'administrasi_items' => ['ktp' => 'KTP', 'kk' => 'KK', 'surat_tanah' => 'Surat Tanah', 'foto_rumah' => 'Foto Kondisi Rumah'],
            ],
            'sosial' => [
                'formulir_items' => [
                    'Komunikasi, informasi dan edukasi dalam kesetaraan dan keadilan gender',
                    'Komunikasi, informasi dan edukasi dalam disabilitas',
                    'Komunikasi, informasi dan edukasi dalam inklusi sosial',
                    'Identifikasi dan pendataan fakir miskin/masyarakat',
                    'Tidak mampu',
                    'Penyaluran bantuan sosial',
                    'Lainnya...',
                ],
                'administrasi_items' => ['ktp' => 'KTP', 'kk' => 'KK', 'surat_tidak_mampu' => 'Surat Keterangan Tidak Mampu'],
            ],
            'trantibumlinmas' => [
                'formulir_items' => [
                    'Penyuluhan dan rehabilitasi trauma pas ca bencana',
                    'Komunikasi, informasi dan edukasi terhadap kesiapsiagaan bencana',
                    'Deteksi dini dan cegah dini gangguan trantibumlinmas',
                    'Pembinaan dan penyuluhan pelaksanaan patrol pengmanan',
                    'Pemberdayaan perlindungan masyarakat',
                    'Perbaikan poskamling',
                    'Penyediaan APAR',
                    'Penyediaan alat deteksi bencana',
                    'Lainnya...',
                ],
                'administrasi_items' => ['ktp' => 'KTP', 'kk' => 'KK'],
            ],
        ];

        // 2. Lakukan perulangan untuk membuat 20 data pengajuan palsu
        foreach (range(1, 20) as $index) {
            $user = $users->random();
            $bidang = $bidangs->random();

            $template = $formTemplates[$bidang->slug] ?? null;
            if (!$template) continue; // Lewati jika slug tidak ada di template

            // Pilih beberapa item checklist secara acak
            $checklistData = collect($template['formulir_items'])->random(rand(1, count($template['formulir_items'])))->values()->all();

            // Cek jika pengguna memilih 'Lainnya...'
            if (in_array('Lainnya...', $checklistData)) {
                $checklistData[] = 'Lainnya: ' . fake()->sentence(3); // Tambah data teks manual
            }

            // Buat data dokumen palsu
            $dokumenData = [];
            foreach (array_keys($template['administrasi_items']) as $key) {
                $dokumenData[$key] = 'dokumen/' . fake()->word() . '.pdf';
            }

            Pengajuan::create([
                'user_id' => $user->id,
                'bidang_id' => $bidang->id,
                'status' => fake()->randomElement(['Diproses', 'Disetujui', 'Ditolak']),
                'deskripsi_pengajuan' => fake()->sentence(),
                'formulir_items' => $checklistData,
                'administrasi_items' => $dokumenData,
            ]);
        }
    }
}
