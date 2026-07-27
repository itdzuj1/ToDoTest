<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Done = 'done';

    public function is(self $status): bool
    {
        return $this === $status;
    }

    public function isNot(self $status): bool
    {
        return ! $this->is($status);
    }

    /**
     * @param  array<self>  $statuses
     */
    public function inArray(array $statuses): bool
    {
        return in_array($this, $statuses, true);
    }
}
