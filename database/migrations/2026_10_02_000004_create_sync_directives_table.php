<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_directives', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Satu directive per kabupaten — diatur oleh admin-kabupaten.
            $table->string('kabupaten')->unique();
            $table->boolean('is_active')->default(false);
            $table->text('message')->nullable();
            // Waktu terakhir diaktifkan/di-broadcast; jadi acuan "versi" sinkronisasi.
            $table->timestamp('activated_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_directives');
    }
};
