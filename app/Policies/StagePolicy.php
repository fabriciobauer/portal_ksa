<?php

namespace App\Policies;

use App\Models\Stage;
use App\Models\User;

class StagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Stage $stage): bool
    {
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Stage $stage): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Stage $stage): bool
    {
        return $user->isAdmin();
    }
}
