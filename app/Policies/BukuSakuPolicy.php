<?php

namespace App\Policies;

use App\Models\BukuSaku;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BukuSakuPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function viewBukuSaku(User $user, BukuSaku $bukuSaku): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['kabid', 'admin', 'ketua-posyandu']);
    }

    public function update(User $user, BukuSaku $bukuSaku): bool
    {
        return in_array($user->role, ['kabid', 'admin', 'ketua-posyandu']);
    }

    public function delete(User $user, BukuSaku $bukuSaku): bool
    {
        return in_array($user->role, ['kabid', 'admin', 'ketua-posyandu']);
    }

    public function restore(User $user, BukuSaku $bukuSaku): bool
    {
        return false;
    }

    public function forceDelete(User $user, BukuSaku $bukuSaku): bool
    {
        return false;
    }
}