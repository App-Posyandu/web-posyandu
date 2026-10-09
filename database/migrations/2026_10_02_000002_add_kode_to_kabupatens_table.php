<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kabupatens', function (Blueprint $table) {
            // Kode wilayah BPS (4 digit, 2 digit awal = kode provinsi). Mis. Kebumen = 3305.
            $table->string('kode', 10)->nullable()->after('nama_kabupaten');
        });
    }

    public function down(): void
    {
        Schema::table('kabupatens', function (Blueprint $table) {
            $table->dropColumn('kode');
        });
    }
};
