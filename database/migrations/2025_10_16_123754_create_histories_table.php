<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pengajuan_id')->constrained('pengajuans')->cascadeOnDelete();
            $table->string('status');
            $table->text('catatan')->nullable();
            $table->foreignUuid('diubah_oleh')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->enum('action_by_role', [
                'kader',
                'ketua-posyandu',
                'kades',
                'system'
            ])->nullable();
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('histories');
    }
};