<?php

namespace App\Policies;

use App\Models\Pengajuan;
use App\Models\User;
use App\Support\AccessAudit;
use Illuminate\Auth\Access\Response;

class AjuanPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            'admin',
            'admin-kabupaten',
            'kabid',
            'admin-kecamatan',
            'ketua-timpembina-posyandu',
            'operator-desa',
            'kades',
            'bu-kades',
            'ketua-posyandu',
            'kader',
            'masyarakat',
        ], true);
    }

    public function viewAjuan(User $user, Pengajuan $ajuan): Response
    {
        if ($user->role === 'admin' || $user->role === 'ketua-timpembina-posyandu') {
            return Response::allow();
        }

        if ((string) $user->id === (string) $ajuan->user_id) {
            return Response::allow();
        }

        $ajuanUser = $ajuan->relationLoaded('user') ? $ajuan->user : $ajuan->user()->first();

        if (!$ajuanUser) {
            $this->deny($user, $ajuan, 'view');
            return Response::deny('Akses ditolak.');
        }

        $allowed = match ($user->role) {
            'admin-kabupaten', 'kabid' => $this->sameKabupaten($user, $ajuanUser),
            'admin-kecamatan' => $this->sameKecamatan($user, $ajuanUser),
            'kades', 'bu-kades' => $this->sameDesa($user, $ajuanUser),
            'operator-desa', 'ketua-posyandu', 'kader' => (string) $user->posyandu_id === (string) $ajuanUser->posyandu_id,
            'masyarakat' => false,
            default => false,
        };

        if ($allowed) {
            return Response::allow();
        }

        $this->deny($user, $ajuan, 'view');

        return Response::deny('Akses ditolak.');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Pengajuan $pengajuan): Response
    {
        if ((string) $user->id === (string) $pengajuan->user_id || $user->role === 'admin') {
            return Response::allow();
        }

        $this->deny($user, $pengajuan, 'update');

        return Response::deny('Akses ditolak.');
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

    public function verify(User $user, Pengajuan $ajuan): Response
    {
        if (
            in_array($user->role, ['kader', 'ketua-posyandu', 'ketua-timpembina-posyandu'], true)
            && $ajuan->status_pengajuan === 'Diproses'
        ) {
            return Response::allow();
        }

        $this->deny($user, $ajuan, 'verify');

        return Response::deny('Akses ditolak.');
    }

    private function sameKabupaten(User $actor, User $subject): bool
    {
        return $actor->kabupaten_id && $subject->kabupaten_id
            ? (string) $actor->kabupaten_id === (string) $subject->kabupaten_id
            : (string) $actor->kabupaten === (string) $subject->kabupaten;
    }

    private function sameKecamatan(User $actor, User $subject): bool
    {
        return $actor->kecamatan_id && $subject->kecamatan_id
            ? (string) $actor->kecamatan_id === (string) $subject->kecamatan_id
            : (string) $actor->kecamatan === (string) $subject->kecamatan;
    }

    private function sameDesa(User $actor, User $subject): bool
    {
        if ($actor->posyandu_id && $subject->posyandu_id) {
            if ((string) $actor->posyandu_id === (string) $subject->posyandu_id) {
                return true;
            }
        }

        $actorDesa = mb_strtolower(trim((string) $actor->desa));
        if ($actorDesa === '') {
            return false;
        }

        $subjectDesa = mb_strtolower(trim((string) $subject->desa));
        if ($subjectDesa !== '' && str_contains($subjectDesa, $actorDesa)) {
            return true;
        }

        $subjectAlamat = mb_strtolower(trim((string) $subject->alamat));
        if ($subjectAlamat !== '' && str_contains($subjectAlamat, $actorDesa)) {
            return true;
        }

        $subjectPosyanduDesa = mb_strtolower(trim((string) optional($subject->posyandu)->desa));
        if ($subjectPosyanduDesa !== '' && str_contains($subjectPosyanduDesa, $actorDesa)) {
            return true;
        }

        return false;
    }

    private function deny(User $actor, Pengajuan $subject, string $action): void
    {
        AccessAudit::record(request(), $actor, 'pengajuan', $action, false, 403, [
            'target_pengajuan_id' => $subject->id,
            'target_owner_id' => $subject->user_id,
        ]);
    }
}
