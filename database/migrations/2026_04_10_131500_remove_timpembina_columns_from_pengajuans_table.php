<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('pengajuans', 'approved_by_timpembina')) {
            $legacyApprovals = DB::table('pengajuans')
                ->select([
                    'id',
                    'approved_by_timpembina',
                    'approved_by_timpembina_id',
                    'approved_by_timpembina_at',
                    'approved_by_ketua',
                    'approved_by_ketua_id',
                    'approved_by_ketua_at',
                ])
                ->where('approved_by_timpembina', true)
                ->get();

            foreach ($legacyApprovals as $row) {
                DB::table('pengajuans')
                    ->where('id', $row->id)
                    ->update([
                        'approved_by_ketua' => true,
                        'approved_by_ketua_id' => $row->approved_by_ketua_id ?: $row->approved_by_timpembina_id,
                        'approved_by_ketua_at' => $row->approved_by_ketua_at ?: $row->approved_by_timpembina_at,
                    ]);
            }
        }

        $hasSubmittedToTimpembina = Schema::hasColumn('pengajuans', 'submitted_to_timpembina');
        $hasSubmittedToTimpembinaAt = Schema::hasColumn('pengajuans', 'submitted_to_timpembina_at');
        $hasApprovedByTimpembina = Schema::hasColumn('pengajuans', 'approved_by_timpembina');
        $hasApprovedByTimpembinaId = Schema::hasColumn('pengajuans', 'approved_by_timpembina_id');
        $hasApprovedByTimpembinaAt = Schema::hasColumn('pengajuans', 'approved_by_timpembina_at');

        Schema::table('pengajuans', function (Blueprint $table) use (
            $hasSubmittedToTimpembina,
            $hasSubmittedToTimpembinaAt,
            $hasApprovedByTimpembina,
            $hasApprovedByTimpembinaId,
            $hasApprovedByTimpembinaAt
        ) {
            if ($hasSubmittedToTimpembina) {
                $table->dropColumn('submitted_to_timpembina');
            }

            if ($hasSubmittedToTimpembinaAt) {
                $table->dropColumn('submitted_to_timpembina_at');
            }

            if ($hasApprovedByTimpembina) {
                $table->dropColumn('approved_by_timpembina');
            }

            if ($hasApprovedByTimpembinaId) {
                $table->dropColumn('approved_by_timpembina_id');
            }

            if ($hasApprovedByTimpembinaAt) {
                $table->dropColumn('approved_by_timpembina_at');
            }
        });
    }

    public function down(): void
    {
        $hasSubmittedToTimpembina = Schema::hasColumn('pengajuans', 'submitted_to_timpembina');
        $hasSubmittedToTimpembinaAt = Schema::hasColumn('pengajuans', 'submitted_to_timpembina_at');
        $hasApprovedByTimpembina = Schema::hasColumn('pengajuans', 'approved_by_timpembina');
        $hasApprovedByTimpembinaId = Schema::hasColumn('pengajuans', 'approved_by_timpembina_id');
        $hasApprovedByTimpembinaAt = Schema::hasColumn('pengajuans', 'approved_by_timpembina_at');

        Schema::table('pengajuans', function (Blueprint $table) use (
            $hasSubmittedToTimpembina,
            $hasSubmittedToTimpembinaAt,
            $hasApprovedByTimpembina,
            $hasApprovedByTimpembinaId,
            $hasApprovedByTimpembinaAt
        ) {
            if (!$hasSubmittedToTimpembina) {
                $table->boolean('submitted_to_timpembina')->default(false);
            }

            if (!$hasSubmittedToTimpembinaAt) {
                $table->timestamp('submitted_to_timpembina_at')->nullable();
            }

            if (!$hasApprovedByTimpembina) {
                $table->boolean('approved_by_timpembina')->default(false);
            }

            if (!$hasApprovedByTimpembinaId) {
                $table->uuid('approved_by_timpembina_id')->nullable();
            }

            if (!$hasApprovedByTimpembinaAt) {
                $table->timestamp('approved_by_timpembina_at')->nullable();
            }
        });
    }
};
