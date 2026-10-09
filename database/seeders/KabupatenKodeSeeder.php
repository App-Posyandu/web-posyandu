<?php

namespace Database\Seeders;

use App\Models\Kabupaten;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Mengisi kolom `kode` (BPS) untuk kabupaten/kota di Provinsi Jawa Tengah (33).
 * Aman dijalankan berulang kali — hanya meng-update baris yang namanya cocok.
 */
class KabupatenKodeSeeder extends Seeder
{
    /**
     * Pemetaan nama kabupaten/kota (lowercase, tanpa prefix) => kode BPS.
     */
    private array $kodeMap = [
        'cilacap'      => '3301',
        'banyumas'     => '3302',
        'purbalingga'  => '3303',
        'banjarnegara' => '3304',
        'kebumen'      => '3305',
        'purworejo'    => '3306',
        'wonosobo'     => '3307',
        'magelang'     => '3308', // kabupaten
        'boyolali'     => '3309',
        'klaten'       => '3310',
        'sukoharjo'    => '3311',
        'wonogiri'     => '3312',
        'karanganyar'  => '3313',
        'sragen'       => '3314',
        'grobogan'     => '3315',
        'blora'        => '3316',
        'rembang'      => '3317',
        'pati'         => '3318',
        'kudus'        => '3319',
        'jepara'       => '3320',
        'demak'        => '3321',
        'semarang'     => '3322', // kabupaten
        'temanggung'   => '3323',
        'kendal'       => '3324',
        'batang'       => '3325',
        'pekalongan'   => '3326', // kabupaten
        'pemalang'     => '3327',
        'tegal'        => '3328', // kabupaten
        'brebes'       => '3329',
    ];

    /**
     * Pemetaan khusus kota (jenis = 'kota'), kode berbeda dari kabupaten senama.
     */
    private array $kodeMapKota = [
        'magelang'   => '3371',
        'surakarta'  => '3372',
        'solo'       => '3372',
        'salatiga'   => '3373',
        'semarang'   => '3374',
        'pekalongan' => '3375',
        'tegal'      => '3376',
    ];

    public function run(): void
    {
        foreach (Kabupaten::all() as $kabupaten) {
            $nama = $this->normalize($kabupaten->nama_kabupaten);
            $isKota = $kabupaten->jenis === 'kota';

            $kode = $isKota
                ? ($this->kodeMapKota[$nama] ?? null)
                : ($this->kodeMap[$nama] ?? null);

            // Fallback: kalau jenis kota tapi tidak ada di map kota, coba map kabupaten.
            $kode ??= $this->kodeMap[$nama] ?? null;

            if ($kode !== null) {
                $kabupaten->update(['kode' => $kode]);
            } else {
                $this->command?->warn("Kode BPS tidak ditemukan untuk: {$kabupaten->nama_kabupaten} ({$kabupaten->jenis})");
            }
        }
    }

    private function normalize(string $nama): string
    {
        $nama = mb_strtolower(trim($nama));
        // Buang prefix "kota " / "kabupaten " / "kab. " jika ada.
        $nama = preg_replace('/^(kota|kabupaten|kab\.?)\s+/', '', $nama);
        return trim($nama);
    }
}
