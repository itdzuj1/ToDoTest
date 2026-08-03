<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Task;
use App\Models\TodoList;
use App\Models\User;

final class TaskPolicy
{
    public function before(User|Admin $actor, string $ability): ?bool
    {
        if ($actor instanceof Admin) {
            return true;
        }

        if ($actor->isBlocked() && ! in_array($ability, ['viewAny', 'view'], true)) {
            return false;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        return $task->todoList->user_id === $user->id;
    }

    public function create(User $user, TodoList $todoList): bool
    {
        return $todoList->user_id === $user->id && ! $todoList->isArchived();
    }

    public function update(User $user, Task $task): bool
    {
        return $this->ownerHasActiveList($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->ownerHasActiveList($user, $task);
    }

    private function ownerHasActiveList(User $user, Task $task): bool
    {
        return $task->todoList()
            ->where('user_id', $user->id)
            ->whereNull('archived_at')
            ->exists();
    }
}
