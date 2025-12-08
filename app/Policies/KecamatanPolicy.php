<?php

namespace App\Policies;

use App\Models\Kecamatan;
use App\Models\User;

class KecamatanPolicy
{
    /**
     * Siapa yang boleh melihat daftar kecamatan?
     * Admin dan Kabid.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'kabid']);
    }

    /**
     * Siapa yang boleh melihat detail kecamatan?
     * Admin, Kabid, dan Admin Kecamatan (untuk wilayahnya sendiri - logika filter ada di controller).
     */
    public function view(User $user, Kecamatan $kecamatan): bool
    {
        return in_array($user->role, ['admin', 'kabid', 'admin-kecamatan']);
    }

    /**
     * Siapa yang boleh membuat kecamatan baru?
     * HANYA Admin dan Kabid.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'kabid']);
    }

    /**
     * Siapa yang boleh mengedit kecamatan?
     * HANYA Admin dan Kabid.
     */
    public function update(User $user, Kecamatan $kecamatan): bool
    {
        return in_array($user->role, ['admin', 'kabid']);
    }

    /**
     * Siapa yang boleh menghapus kecamatan?
     * HANYA Admin dan Kabid.
     */
    public function delete(User $user, Kecamatan $kecamatan): bool
    {
        return in_array($user->role, ['admin', 'kabid']);
    }
}
