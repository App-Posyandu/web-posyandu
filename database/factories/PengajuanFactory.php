<?php

namespace Database\Factories;

use App\Models\BidangPengajuan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PengajuanFactory extends Factory
{
    public function definition(): array
    {
        $user = User::inRandomOrder()->first();
        $bidang = BidangPengajuan::inRandomOrder()->first();

        $formulirItems = $bidang->formulir_items ?? [];
        $administrasiItems = $bidang->administrasi_items ?? [];

        $checklistData = collect($formulirItems)->random(rand(1, count($formulirItems)))->values();

        $dokumenData = [];
        foreach (array_keys($administrasiItems) as $key) {
            $dokumenData[$key] = 'dokumen/' . fake()->word() . '.pdf';
        }

        return [
            'user_id' => $user->uuid,
            'bidang_id' => $bidang->id,
            'status' => fake()->randomElement(['Diproses', 'Disetujui', 'Ditolak']),

            'formulir_items' => $checklistData,
            'administrasi_items' => $dokumenData,
        ];
    }
}
