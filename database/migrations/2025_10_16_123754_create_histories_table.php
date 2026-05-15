<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pengajuan_id')->constrained('pengajuans')->cascadeOnDelete();
            $table->string('status');
            $table->string('pilih_keputusan')->nullable()->after('status');
            $table->text('catatan')->nullable();
            $table->foreignUuid('diubah_oleh')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->enum('action_by_role', [
                'kader',
                'ketua-posyandu',
                'ketua-timpembina-posyandu',
                'kades',
                'system'
            ])->nullable();
            $table->timestamp('created_at');
        });

        // Backfill `pilih_keputusan` for any pre-existing history rows (no-op on fresh installs)
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
        Schema::dropIfExists('histories');
    }
};