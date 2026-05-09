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
        $role = strtolower(trim($user->role));

        $allowedRoles = [
            'admin',
            'admin-kabupaten',
            'kabid',
            'admin-kecamatan',
            'ketua-timpembina-posyandu',
            'operator-desa',
            'kades',
            'bu-kades',
            'ketua-posyandu',
            'ketua-kader',
            'kader',
            'masyarakat',
        ];

        return in_array($role, $allowedRoles, true);
    }

    public function viewAjuan(User $user, Pengajuan $ajuan): Response
    {
        if (in_array($user->role, ['admin', 'ketua-timpembina-posyandu'], true)) {
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

            'operator-desa' => $this->sameDesa($user, $ajuanUser),

            'ketua-posyandu' => (string) $user->posyandu_id === (string) $ajuanUser->posyandu_id,

            'kader' => (string) $user->posyandu_id === (string) $ajuanUser->posyandu_id
                && (string) $user->bidang_id === (string) $ajuan->bidang_id,

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
        // Must be in "Diproses" status
        if ($ajuan->status_pengajuan !== 'Diproses') {
            $this->deny($user, $ajuan, 'verify');
            return Response::deny('Hanya pengajuan dengan status Diproses yang dapat diverifikasi.');
        }

        if ($user->role === 'kader') {
            if ((string) $user->bidang_id === (string) $ajuan->bidang_id) {
                return Response::allow();
            }

            $this->deny($user, $ajuan, 'verify');
            return Response::deny('Kader hanya dapat memverifikasi pengajuan di bidangnya sendiri.');
        }

        if (in_array($user->role, ['ketua-posyandu', 'ketua-timpembina-posyandu'], true)) {
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

    public function takeover(User $user, Pengajuan $ajuan): Response
    {
        if ($user->role !== 'ketua-kader') {
            return Response::deny('Hanya Ketua Kader yang dapat mengambil alih pengajuan.');
        }

        $ajuanUser = $ajuan->relationLoaded('user') ? $ajuan->user : $ajuan->user()->first();

        if (!$ajuanUser) {
            return Response::deny('Data pengajuan tidak valid.');
        }

        // Hanya bisa takeover di posyandu yang sama
        if ((string) $user->posyandu_id !== (string) $ajuanUser->posyandu_id) {
            return Response::deny('Anda hanya dapat mengambil alih pengajuan di posyandu Anda.');
        }

        // Hanya bisa takeover pengajuan yang masih dalam proses
        if ($ajuan->status_pengajuan !== 'Diproses') {
            return Response::deny('Hanya pengajuan dengan status Diproses yang dapat diambil alih.');
        }

        return Response::allow();
    }

    public function requestRevision(User $user, Pengajuan $ajuan): Response
    {
        if (in_array($user->role, ['admin', 'ketua-posyandu', 'kades', 'ketua-kader'], true)) {
            return Response::allow();
        }

        // Kader hanya bisa minta revisi di bidangnya
        if ($user->role === 'kader') {
            if ((string) $user->bidang_id === (string) $ajuan->bidang_id) {
                return Response::allow();
            }

            return Response::deny('Kader hanya dapat meminta revisi untuk bidangnya sendiri.');
        }

        return Response::deny('Akses ditolak.');
    }

    private function deny(User $actor, Pengajuan $subject, string $action): void
    {
        AccessAudit::record(request(), $actor, 'pengajuan', $action, false, 403, [
            'target_pengajuan_id' => $subject->id,
            'target_owner_id' => $subject->user_id,
        ]);
    }
}
