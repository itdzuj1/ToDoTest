<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\TodoList;
use App\Models\User;

final class TodoListPolicy
{
    public function before(User|Admin $actor, string $ability): ?bool
    {
        if ($actor instanceof Admin) {
            return true;
        }

        if (! $actor->isBlocked()) {
            return null;
        }

        return match ($ability) {
            'viewAny', 'view' => null,
            default => false,
        };
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TodoList $todoList): bool
    {
        return $todoList->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, TodoList $todoList): bool
    {
        return $todoList->user_id === $user->id && ! $todoList->isArchived();
    }

    public function archive(User $user, TodoList $todoList): bool
    {
        return $todoList->user_id === $user->id;
    }

    public function restore(User $user, TodoList $todoList): bool
    {
        return $todoList->user_id === $user->id;
    }
}
