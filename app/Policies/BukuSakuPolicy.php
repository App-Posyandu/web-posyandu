<?php

namespace App\Policies;

use App\Models\BukuSaku;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BukuSakuPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function viewBukuSaku(User $user, BukuSaku $bukuSaku): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['kabid', 'admin', 'ketua-posyandu']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BukuSaku $bukuSaku): bool
    {
        return in_array($user->role, ['kabid', 'admin', 'ketua-posyandu']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BukuSaku $bukuSaku): bool
    {
        return in_array($user->role, ['kabid', 'admin', 'ketua-posyandu']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BukuSaku $bukuSaku): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BukuSaku $bukuSaku): bool
    {
        return false;
    }
}