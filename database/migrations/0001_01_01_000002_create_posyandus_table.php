<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posyandus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_posyandu');
            $table->string('desa');
            $table->string('kecamatan');
            $table->string('kabupaten');
            $table->foreignUuid('kabupaten_id')
                ->nullable();
            $table->foreignUuid('kecamatan_id')
                ->nullable();
            $table->json('rw_list')->nullable();
            $table->json('rt_mapping')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('posyandus');
    }
};