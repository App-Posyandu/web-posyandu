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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('bidang_id')->nullable();
            $table->foreignUuid('posyandu_id')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('no_telepon', 20)->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['masyarakat', 'kader', 'kabid', 'ketua-kader', 'admin'])->default('masyarakat');

            // Kolom Tambahan dari Form Registrasi
            $table->string('nik', 16)->unique()->nullable();
            $table->text('alamat')->nullable();
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('jenis_kelamin');
            $table->timestamp('verified_at')->nullable();
            $table->uuid('verified_by')->nullable();

            // Kolom untuk file Base64
            $table->longText('ktp')->nullable();
            $table->longText('kk')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('bidang_id')
                ->references('id')
                ->on('bidang_pengajuans')
                ->nullOnDelete();

            $table->foreign('posyandu_id')
                ->references('id')
                ->on('posyandus')
                ->nullOnDelete();

            $table->foreign('verified_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();

            $table->foreignUuid('user_id')->nullable()->constrained('users', 'id')->nullOnDelete();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};