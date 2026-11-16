<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posyandus', function (Blueprint $table) {
            // Kode wilayah resmi (BPS/Kemendagri) kabupaten/kota posyandu ini.
            // 4 digit: 2 digit provinsi + 2 digit kabupaten (sama seperti awalan NIK). Mis. Kota Cimahi = 3277.
            $table->string('kode_kabupaten', 10)->nullable()->after('kabupaten');
        });
    }

    public function down(): void
    {
        Schema::table('posyandus', function (Blueprint $table) {
            $table->dropColumn('kode_kabupaten');
        });
    }
};
