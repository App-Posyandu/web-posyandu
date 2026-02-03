<?php

namespace App\Policies;

use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PengajuanPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Pengajuan $pengajuan): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Pengajuan $pengajuan): bool
    {
        return false;
    }

    public function delete(User $user, Pengajuan $pengajuan): bool
    {
        return false;
    }

    public function restore(User $user, Pengajuan $pengajuan): bool
    {
        return false;
    }

    public function forceDelete(User $user, Pengajuan $pengajuan): bool
    {
        return false;
    }

    public function verify(User $user, Pengajuan $pengajuan)
    {
        if ($user->role === 'kader') {
            return $pengajuan->user->posyandu_id === $user->posyandu_id;
        }

        if ($user->role === 'ketua-posyandu') {
            return $pengajuan->user->posyandu_id === $user->posyandu_id
                && $pengajuan->kunjungan_lapangan === true;
        }

        if ($user->role === 'kades') {
            return $pengajuan->submitted_to_desa === true
                && $pengajuan->status_pengajuan === 'Diproses';
        }

        return false;
    }
}
