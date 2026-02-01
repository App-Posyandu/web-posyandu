<?php

namespace App\Policies;

use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PosyanduPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Posyandu $posyandu): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Posyandu $posyandu): bool
    {
        return false;
    }

    public function delete(User $user, Posyandu $posyandu): bool
    {
        return false;
    }

    public function restore(User $user, Posyandu $posyandu): bool
    {
        return false;
    }

    public function forceDelete(User $user, Posyandu $posyandu): bool
    {
        return false;
    }
}
