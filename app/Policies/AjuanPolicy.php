<?php

namespace App\Policies;

use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AjuanPolicy
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
    public function view(User $user, Pengajuan $ajuan): bool
    {
        Log::info('ViewAjuan Policy Check:', [
            'user_id' => $user->id,
            'ajuan_user_id' => $ajuan->user_id,
            'user_role' => $user->role,
            'result' => $user->id === $ajuan->user_id || $user->role !== 'masyarakat'
        ]);
        return $user->id === $ajuan->user_id || $user->role !== 'masyarakat';
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

    public function verify(User $user, Pengajuan $ajuan): bool
    {
        // Hanya user dengan role 'kader' DAN
        // ajuan yang statusnya masih 'Diproses'
        // yang boleh melakukan verifikasi.
        return $user->role === 'kader' && $ajuan->status_pengajuan === 'Diproses';
    }
}
