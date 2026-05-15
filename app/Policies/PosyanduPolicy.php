<?php

namespace App\Policies;

use App\Models\Posyandu;
use App\Models\User;
use App\Support\AccessAudit;
use Illuminate\Auth\Access\Response;

class PosyanduPolicy
{
    public function viewAny(User $actor): bool
    {
        return in_array($actor->role, [
            'admin', 'admin-kabupaten', 'kabid', 'admin-kecamatan',
            'ketua-timpembina-posyandu', 'operator-desa', 'kades', 'bu-kades', 'ketua-posyandu'
        ], true);
    }

    public function view(User $actor, Posyandu $subject): Response
    {
        if ($this->canAccess($actor, $subject)) {
            return Response::allow();
        }

        $this->deny($actor, $subject, 'view');

        return Response::deny('Akses ditolak.');
    }

    public function create(User $actor): Response
    {
        if (in_array($actor->role, ['admin', 'operator-desa'], true)) {
            return Response::allow();
        }

        AccessAudit::record(request(), $actor, 'posyandu', 'create', false, 403);

        return Response::deny('Akses ditolak.');
    }

    public function update(User $actor, Posyandu $subject): Response
    {
        if ($this->canAccess($actor, $subject)) {
            return Response::allow();
        }

        $this->deny($actor, $subject, 'update');

        return Response::deny('Akses ditolak.');
    }

    public function delete(User $actor, Posyandu $subject): Response
    {
        if ($actor->role === 'admin') {
            return Response::allow();
        }

        $this->deny($actor, $subject, 'delete');

        return Response::deny('Akses ditolak.');
    }

    private function canAccess(User $actor, Posyandu $subject): bool
    {
        if ($actor->role === 'admin' || $actor->role === 'ketua-timpembina-posyandu') {
            return true;
        }

        return match ($actor->role) {
            'admin-kabupaten' => $this->sameKabupaten($actor, $subject),
            'kabid' => $this->sameKabupaten($actor, $subject),
            'admin-kecamatan' => $this->sameKecamatan($actor, $subject),
            'kades', 'bu-kades' => $this->sameDesa($actor, $subject),
            'operator-desa' => $this->sameDesa($actor, $subject),
            'ketua-posyandu' => (string) $actor->posyandu_id === (string) $subject->id,
            default => false,
        };
    }

    private function sameKabupaten(User $actor, Posyandu $subject): bool
    {
        return $actor->kabupaten_id && $subject->kabupaten_id
            ? (string) $actor->kabupaten_id === (string) $subject->kabupaten_id
            : (string) $actor->kabupaten === (string) $subject->kabupaten;
    }

    private function sameKecamatan(User $actor, Posyandu $subject): bool
    {
        return $actor->kecamatan_id && $subject->kecamatan_id
            ? (string) $actor->kecamatan_id === (string) $subject->kecamatan_id
            : (string) $actor->kecamatan === (string) $subject->kecamatan;
    }

    private function sameDesa(User $actor, Posyandu $subject): bool
    {
        return (string) $actor->desa === (string) $subject->desa
            && $this->sameKecamatan($actor, $subject)
            && $this->sameKabupaten($actor, $subject);
    }

    private function deny(User $actor, Posyandu $subject, string $action): void
    {
        AccessAudit::record(request(), $actor, 'posyandu', $action, false, 403, [
            'target_posyandu_id' => $subject->id,
        ]);
    }
}
