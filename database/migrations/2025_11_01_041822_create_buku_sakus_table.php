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
        Schema::create('buku_sakus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->comment('User yang mengupload (Kabid)')->constrained('users', 'id')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buku_sakus');
    }
};
