<?php

namespace App\Policies;

use App\Models\Kecamatan;
use App\Models\User;

class KecamatanPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'kabid']);
    }

    public function view(User $user, Kecamatan $kecamatan): bool
    {
        return in_array($user->role, ['admin', 'kabid', 'admin-kecamatan']);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'kabid']);
    }

    public function update(User $user, Kecamatan $kecamatan): bool
    {
        return in_array($user->role, ['admin', 'kabid']);
    }

    public function delete(User $user, Kecamatan $kecamatan): bool
    {
        return in_array($user->role, ['admin', 'kabid']);
    }
}
