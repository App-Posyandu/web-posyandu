<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('histories', function (Blueprint $table) {
            $table->string('pilih_keputusan')->nullable()->after('status');
        });

        DB::table('histories')->where('status', 'Menunggu Kunjungan')->update(['pilih_keputusan' => 'lanjut']);
        DB::table('histories')->where('status', 'Revisi Diminta')->update(['pilih_keputusan' => 'revisi']);
        DB::table('histories')->where('status', 'Ditolak')->update(['pilih_keputusan' => 'tolak']);
        DB::table('histories')->where('status', 'Disetujui Ketua Posyandu')->update(['pilih_keputusan' => 'ditindaklanjuti']);
        DB::table('histories')->where('status', 'Ditolak Ketua Posyandu')->update(['pilih_keputusan' => 'tidak-ditindaklanjuti']);
        DB::table('histories')->where('status', 'Disetujui Kades')->update(['pilih_keputusan' => 'ditindaklanjuti']);
        DB::table('histories')->where('status', 'Ditolak Kades')->update(['pilih_keputusan' => 'tidak-ditindaklanjuti']);
    }

    public function down(): void
    {
        Schema::table('histories', function (Blueprint $table) {
            $table->dropColumn('pilih_keputusan');
        });
    }
};
