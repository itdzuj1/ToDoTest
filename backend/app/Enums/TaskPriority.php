<?php

namespace App\Enums;

enum TaskPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';

    public function is(self $priority): bool
    {
        return $this === $priority;
    }

    public function isNot(self $priority): bool
    {
        return ! $this->is($priority);
    }

    /**
     * @param  array<self>  $priorities
     */
    public function inArray(array $priorities): bool
    {
        return in_array($this, $priorities, true);
    }
}
