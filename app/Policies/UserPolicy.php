<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function manage(User $actor): bool
    {
        return $actor->isAdmin();
    }

    public function updateRole(User $actor, User $target): bool
    {
        return $actor->isAdmin() && $actor->id !== $target->id;
    }

    public function updateStatus(User $actor, User $target): bool
    {
        return $actor->isAdmin() && $actor->id !== $target->id;
    }
}
