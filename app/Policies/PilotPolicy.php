<?php

namespace App\Policies;

use App\Models\Pilot;
use App\Models\User;

class PilotPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Pilot $pilot): bool
    {
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Pilot $pilot): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Pilot $pilot): bool
    {
        return $user->isAdmin();
    }
}
