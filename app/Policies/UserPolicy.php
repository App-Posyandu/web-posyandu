<?php

namespace App\Policies;

use App\Models\User;
use App\Support\AccessAudit;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return in_array($actor->role, [
            'admin',
            'admin-kabupaten',
            'ketua-timpembina-posyandu',
            'kabid',
            'admin-kecamatan',
            'kades',
            'bu-kades',
            'ketua-posyandu',
            'operator-desa',
            'kader',
        ], true);
    }

    public function view(User $actor, User $subject): Response
    {
        if ($this->canAccess($actor, $subject, true)) {
            return Response::allow();
        }

        $this->deny($actor, $subject, 'view');

        return Response::deny('Akses ditolak.');
    }

    public function update(User $actor, User $subject): Response
    {
        if ($actor->id === $subject->id || $this->canManage($actor, $subject)) {
            return Response::allow();
        }

        $this->deny($actor, $subject, 'update');

        return Response::deny('Akses ditolak.');
    }

    public function delete(User $actor, User $subject): Response
    {
        if ($actor->role === 'admin' && $actor->id !== $subject->id) {
            return Response::allow();
        }

        $this->deny($actor, $subject, 'delete');

        return Response::deny('Akses ditolak.');
    }

    public function verify(User $actor, User $subject): Response
    {
        if ($this->canManage($actor, $subject, true)) {
            return Response::allow();
        }

        $this->deny($actor, $subject, 'verify');

        return Response::deny('Akses ditolak.');
    }

    public function resetPassword(User $actor, User $subject): Response
    {
        if ($this->canResetPassword($actor, $subject)) {
            return Response::allow();
        }

        $this->deny($actor, $subject, 'reset_password');

        return Response::deny('Akses ditolak.');
    }

    private function canAccess(User $actor, User $subject, bool $includeAdminKabupaten = true): bool
    {
        if ($actor->role === 'admin') {
            return true;
        }

        if ($actor->id === $subject->id) {
            return true;
        }

        return match ($actor->role) {
            'admin-kabupaten' => true,
            'kabid' => $this->sameKabupaten($actor, $subject),
            'admin-kecamatan' => $this->sameKecamatan($actor, $subject),
            'kades', 'bu-kades' => $this->sameDesa($actor, $subject),
            'operator-desa' => $this->sameDesa($actor, $subject) && in_array($subject->role, ['kades', 'bu-kades', 'ketua-posyandu', 'kader', 'masyarakat'], true),
            'ketua-posyandu' => $this->samePosyandu($actor, $subject) && in_array($subject->role, ['kader', 'masyarakat'], true),
            'kader' => $this->samePosyandu($actor, $subject) && $subject->role === 'masyarakat',
            'ketua-timpembina-posyandu' => true,
            default => false,
        };
    }

    private function canManage(User $actor, User $subject, bool $allowVerification = false): bool
    {
        if ($actor->role === 'admin') {
            return true;
        }

        return match ($actor->role) {
            'admin-kabupaten' => $this->sameKabupaten($actor, $subject) && in_array($subject->role, [
                'kabid', 'ketua-timpembina-posyandu', 'admin-kecamatan',
                'kades', 'bu-kades', 'operator-desa',
                'ketua-posyandu', 'kader', 'masyarakat',
            ], true),
            'kabid' => $this->sameKabupaten($actor, $subject) && in_array($subject->role, ['admin-kecamatan', 'ketua-posyandu', 'operator-desa', 'kader', 'masyarakat'], true),
            'admin-kecamatan' => $this->sameKecamatan($actor, $subject) && in_array($subject->role, ['ketua-posyandu', 'operator-desa', 'kader', 'masyarakat'], true),
            'kades', 'bu-kades' => $this->sameDesa($actor, $subject) && in_array($subject->role, ['ketua-posyandu', 'operator-desa', 'kader', 'masyarakat'], true),
            'operator-desa' => $this->sameDesa($actor, $subject) && in_array($subject->role, ['kades', 'bu-kades', 'ketua-posyandu', 'kader', 'masyarakat'], true),
            'ketua-posyandu' => $this->samePosyandu($actor, $subject) && in_array($subject->role, ['kader', 'masyarakat'], true),
            'kader' => $this->samePosyandu($actor, $subject) && $subject->role === 'masyarakat' && $allowVerification,
            default => false,
        };
    }

    private function canResetPassword(User $actor, User $subject): bool
    {
        if ($actor->role === 'admin') {
            return true;
        }

        if ($actor->role === 'operator-desa') {
            return $this->sameDesa($actor, $subject) && in_array($subject->role, ['kades', 'bu-kades', 'kader', 'ketua-posyandu'], true);
        }

        if ($actor->role === 'admin-kabupaten') {
            return $this->sameKabupaten($actor, $subject) && in_array($subject->role, [
                'kabid', 'admin-kecamatan', 'kades', 'bu-kades',
                'operator-desa', 'ketua-posyandu', 'kader', 'masyarakat',
            ], true);
        }

        if ($actor->role === 'ketua-posyandu') {
            return $this->samePosyandu($actor, $subject) && $subject->role === 'kader';
        }

        return false;
    }

    private function sameKabupaten(User $actor, User $subject): bool
    {
        if ($actor->kabupaten_id && $subject->kabupaten_id) {
            return (string) $actor->kabupaten_id === (string) $subject->kabupaten_id;
        }
        // Fallback: cocokkan nama kabupaten, OR cocokkan via kabupaten_id salah satu
        if ($actor->kabupaten_id) {
            return (string) $actor->kabupaten === (string) $subject->kabupaten;
        }
        return $actor->kabupaten && (string) $actor->kabupaten === (string) $subject->kabupaten;
    }

    private function sameKecamatan(User $actor, User $subject): bool
    {
        if ($actor->kecamatan_id && $subject->kecamatan_id) {
            return (string) $actor->kecamatan_id === (string) $subject->kecamatan_id;
        }
        if ($actor->kecamatan_id) {
            return (string) $actor->kecamatan === (string) $subject->kecamatan;
        }
        return $actor->kecamatan && (string) $actor->kecamatan === (string) $subject->kecamatan;
    }

    private function sameDesa(User $actor, User $subject): bool
    {
        return (string) $actor->desa === (string) $subject->desa;
    }

    private function samePosyandu(User $actor, User $subject): bool
    {
        return $actor->posyandu_id && $subject->posyandu_id
            && (string) $actor->posyandu_id === (string) $subject->posyandu_id;
    }

    private function deny(User $actor, User $subject, string $action): void
    {
        AccessAudit::record(request(), $actor, 'users', $action, false, 403, [
            'target_user_id' => $subject->id,
            'target_role' => $subject->role,
        ]);
    }
}
