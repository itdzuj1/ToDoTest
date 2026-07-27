<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Tag;
use App\Models\User;

final class TagPolicy
{
    public function before(User|Admin $actor, string $ability): ?bool
    {
        if ($actor instanceof Admin) {
            return true;
        }

        return $actor->isBlocked() ? false : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Tag $tag): bool
    {
        return $tag->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Tag $tag): bool
    {
        return $tag->user_id === $user->id;
    }

    public function delete(User $user, Tag $tag): bool
    {
        return $tag->user_id === $user->id;
    }
}
