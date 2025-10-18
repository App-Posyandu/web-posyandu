<?php

namespace Database\Factories;

use App\Models\BidangPengajuan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pengajuan>
 */
class PengajuanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::inRandomOrder()->first();
        $bidang = BidangPengajuan::inRandomOrder()->first();

        // Ambil definisi formulir dari bidang yang terpilih
        $formulirItems = $bidang->formulir_items ?? [];
        $administrasiItems = $bidang->administrasi_items ?? [];

        // Buat data jawaban palsu
        $checklistData = collect($formulirItems)->random(rand(1, count($formulirItems)))->values();

        $dokumenData = [];
        foreach (array_keys($administrasiItems) as $key) {
            $dokumenData[$key] = 'dokumen/' . fake()->word() . '.pdf';
        }

        return [
            'user_id' => $user->uuid, // Ambil UUID dari user yang terpilih
            'bidang_id' => $bidang->id, // Ambil ID dari bidang yang terpilih
            'status' => fake()->randomElement(['Diproses', 'Disetujui', 'Ditolak']),

            'formulir_items' => $checklistData,
            'administrasi_items' => $dokumenData,
        ];
    }
}
