<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Waktu operator-desa terakhir menyelesaikan sinkronisasi (dibandingkan ke sync_directives.activated_at).
            $table->timestamp('sync_done_at')->nullable()->after('deactivation_reason');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('sync_done_at');
        });
    }
};
