<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('bidang_id')->constrained('bidang_pengajuans')->cascadeOnDelete();
            $table->text('deskripsi_pengajuan');
            $table->enum('status_pengajuan', [
                'Diproses',
                'Disetujui',
                'Ditolak'
            ])->default('Diproses');
            $table->boolean('sudah_verifikasi')->default(false);
            $table->boolean('kunjungan_lapangan')->default(false);
            $table->json('formulir_items')->nullable();
            $table->json('administrasi_items')->nullable();
            $table->boolean('ttd_kader')->default(false);
            $table->json('verified_formulir_items')->nullable();
            $table->json('verified_administrasi_items')->nullable();

            $table->timestamp('tanggal_permohonan')->nullable();
            $table->text('tindak_lanjut')->nullable();

            $table->boolean('submitted_to_desa')->default(false);
            $table->timestamp('submitted_to_desa_at')->nullable();
            $table->boolean('approved_by_kades')->default(false);
            $table->uuid('approved_by_kades_id')->nullable();
            $table->timestamp('approved_by_kades_at')->nullable();

            $table->boolean('approved_by_ketua')->default(false);
            $table->uuid('approved_by_ketua_id')->nullable();
            $table->timestamp('approved_by_ketua_at')->nullable();

            $table->json('foto_kunjungan')->nullable();

            $table->timestamp('revision_requested_at')->nullable();
            $table->integer('revision_count')->default(0);
            $table->boolean('auto_rejected')->default(false);
            $table->string('tracking_code', 20)->unique()->after('id');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuans');
    }
};
