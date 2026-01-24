<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pengajuans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('bidang_id')->constrained('bidang_pengajuans')->cascadeOnDelete();
            $table->text('deskripsi_pengajuan');
            $table->enum('status_pengajuan', [
                'Diproses',           // Sedang diproses Kader
                'Sesuai',             // Belum dikirim ke Pemdes (setelah approval Ketua)
                'Diajukan ke Desa',   // Sudah dikirim ke Pemdes
                'Disetujui',          // Disetujui Kades
                'Ditolak'
            ])->default('Diproses');
            $table->boolean('sudah_verifikasi')->default(false);
            $table->boolean('kunjungan_lapangan')->default(false);
            $table->json('formulir_items')->nullable();
            $table->json('administrasi_items')->nullable();
            $table->boolean('ttd_kader')->default(false);
            $table->json('verified_formulir_items')->nullable();
            $table->json('verified_administrasi_items')->nullable();

            $table->timestamp('tanggal_permohonan')->nullable()->comment('Tanggal saat ajuan dibuat');
            $table->text('tindak_lanjut')->nullable()->comment('Deskripsi tindak lanjut (sama seperti deskripsi)');
            $table->boolean('approved_by_ketua')->default(false)->comment('Apakah sudah diapprove Ketua Posyandu');
            $table->uuid('approved_by_ketua_id')->nullable()->comment('ID Ketua yang approve');
            $table->timestamp('approved_by_ketua_at')->nullable();
            $table->boolean('approved_by_kades')->default(false)->comment('Apakah sudah diapprove Kades');
            $table->uuid('approved_by_kades_id')->nullable();
            $table->timestamp('approved_by_kades_at')->nullable();

            $table->json('foto_kunjungan')->nullable()->comment('Array foto saat kunjungan lapangan (non-required)');

            $table->timestamp('revision_requested_at')->nullable()->comment('Waktu revisi diminta');
            $table->integer('revision_count')->default(0)->comment('Jumlah revisi yang dilakukan');
            $table->boolean('auto_rejected')->default(false)->comment('Auto reject jika > 5 hari kerja');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuans');
    }
};