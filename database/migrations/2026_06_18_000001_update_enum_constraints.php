<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // Fix users.role — initial migration only had 5 roles; new roles were added in code
        // but the DB CHECK constraint was never updated, causing 500 on insert.
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        DB::statement("
            ALTER TABLE users
            ADD CONSTRAINT users_role_check
            CHECK (role::text = ANY (ARRAY[
                'masyarakat'::text,
                'kader'::text,
                'ketua-posyandu'::text,
                'kades'::text,
                'bu-kades'::text,
                'operator-desa'::text,
                'admin-kecamatan'::text,
                'kabid'::text,
                'ketua-timpembina-posyandu'::text,
                'admin-kabupaten'::text,
                'admin'::text
            ]))
        ");

        // Fix users.jenis_wilayah — ensure it matches current enum values
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_jenis_wilayah_check');
        DB::statement("
            ALTER TABLE users
            ADD CONSTRAINT users_jenis_wilayah_check
            CHECK (jenis_wilayah::text = ANY (ARRAY['kabupaten'::text, 'kota'::text]))
        ");

        // Fix histories.action_by_role — 'admin' was added after initial deploy
        DB::statement('ALTER TABLE histories DROP CONSTRAINT IF EXISTS histories_action_by_role_check');
        DB::statement("
            ALTER TABLE histories
            ADD CONSTRAINT histories_action_by_role_check
            CHECK (action_by_role::text = ANY (ARRAY[
                'kader'::text,
                'ketua-posyandu'::text,
                'ketua-timpembina-posyandu'::text,
                'kades'::text,
                'system'::text,
                'admin'::text
            ]))
        ");

        // Fix user_histories.action_type — ensure it matches the enum in the model
        DB::statement('ALTER TABLE user_histories DROP CONSTRAINT IF EXISTS user_histories_action_type_check');
        DB::statement("
            ALTER TABLE user_histories
            ADD CONSTRAINT user_histories_action_type_check
            CHECK (action_type::text = ANY (ARRAY[
                'created'::text,
                'updated'::text,
                'activated'::text,
                'deactivated'::text,
                'role_changed'::text,
                'verified'::text
            ]))
        ");
    }

    public function down(): void
    {
        // No rollback — the original constraints were already wrong/outdated.
    }
};
