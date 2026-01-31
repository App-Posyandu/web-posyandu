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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique()->comment('Setting key (e.g., auto_reject_days)');
            $table->text('value')->comment('Setting value (can be JSON)');
            $table->string('type')->default('string')->comment('Data type: string, integer, boolean, json');
            $table->string('category')->nullable()->comment('Setting category (e.g., revision, email)');
            $table->string('label')->nullable()->comment('Human-readable label');
            $table->text('description')->nullable()->comment('Setting description');
            $table->boolean('is_public')->default(false)->comment('Visible to non-admin users');
            $table->uuid('updated_by')->nullable()->comment('User who last updated');
            $table->timestamps();

            // Foreign key
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            // Index
            $table->index('key');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
