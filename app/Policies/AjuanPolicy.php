<?php

namespace App\Policies;

use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AjuanPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function viewAjuan(User $user, Pengajuan $ajuan): bool
    {
        return $user->id === $ajuan->user_id || $user->role !== 'masyarakat';
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

    public function verify(User $user, Pengajuan $ajuan): bool
    {
        return $user->role === 'kader' && $ajuan->status_pengajuan === 'Diproses';
    }
}
