<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'id' => Str::uuid(),
                'key' => 'auto_reject_days',
                'value' => '5',
                'type' => 'integer',
                'category' => 'revision',
                'label' => 'Batas Waktu Revisi (Hari Kerja)',
                'description' => 'Jumlah hari kerja yang diberikan untuk masyarakat merevisi pengajuan sebelum otomatis ditolak',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'key' => 'revision_debug_mode',
                'value' => 'false',
                'type' => 'boolean',
                'category' => 'revision',
                'label' => 'Mode Debug Revisi',
                'description' => 'Aktifkan mode debug untuk testing (menggunakan menit instead of hari)',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'key' => 'revision_debug_minutes',
                'value' => '5',
                'type' => 'integer',
                'category' => 'revision',
                'label' => 'Batas Waktu Debug (Menit)',
                'description' => 'Jumlah menit untuk mode debug (hanya aktif jika debug mode ON)',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'key' => 'max_revision_count',
                'value' => '3',
                'type' => 'integer',
                'category' => 'revision',
                'label' => 'Maksimal Jumlah Revisi',
                'description' => 'Jumlah maksimal revisi yang diperbolehkan sebelum pengajuan otomatis ditolak',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'key' => 'enable_auto_reject',
                'value' => 'true',
                'type' => 'boolean',
                'category' => 'revision',
                'label' => 'Aktifkan Auto-Reject',
                'description' => 'Mengaktifkan fitur auto-reject jika masyarakat tidak merevisi dalam batas waktu',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('system_settings')->insert($setting);
        }
    }
}