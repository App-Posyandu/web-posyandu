<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kecamatans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_kecamatan');
            $table->foreignUuid('kabupaten_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kecamatans');
    }
};