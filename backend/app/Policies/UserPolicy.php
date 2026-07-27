<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;

final class UserPolicy
{
    public function before(User|Admin $actor, string $ability): ?bool
    {
        if ($actor instanceof Admin) {
            return true;
        }

        return $actor->isBlocked() ? false : null;
    }

    public function view(User $actor, User $user): bool
    {
        return $actor->is($user);
    }

    public function update(User $actor, User $user): bool
    {
        return $actor->is($user);
    }
}
