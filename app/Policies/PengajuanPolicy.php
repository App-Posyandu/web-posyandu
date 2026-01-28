<?php

namespace App\Policies;

use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PengajuanPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Pengajuan $pengajuan): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Pengajuan $pengajuan): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pengajuan $pengajuan): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Pengajuan $pengajuan): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Pengajuan $pengajuan): bool
    {
        return false;
    }

    public function verify(User $user, Pengajuan $pengajuan)
    {
        // Kader: hanya step 1-2
        if ($user->role === 'kader') {
            return $pengajuan->user->posyandu_id === $user->posyandu_id;
        }

        // Ketua Posyandu: step 3
        if ($user->role === 'ketua-posyandu') {
            return $pengajuan->user->posyandu_id === $user->posyandu_id
                && $pengajuan->kunjungan_lapangan === true;
        }

        // Kades: approval final
        if ($user->role === 'kades') {
            return $pengajuan->status_pengajuan === 'Diajukan ke Desa';
        }

        return false;
    }
}
