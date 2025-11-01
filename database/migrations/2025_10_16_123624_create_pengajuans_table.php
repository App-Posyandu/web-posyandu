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
            $table->enum('status_pengajuan', ['Diproses', 'Disetujui', 'Ditolak'])->default('Diproses');
            $table->boolean('sudah_verifikasi')->default(false);
            $table->boolean('kunjungan_lapangan')->default(false);
            $table->json('formulir_items')->nullable();
            $table->json('administrasi_items')->nullable();
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
