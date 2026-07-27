<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Carbon\CarbonImmutable;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property TaskStatus $status
 * @property TaskPriority $priority
 * @property CarbonImmutable|null $due_at
 * @property CarbonImmutable|null $completed_at
 */
#[Fillable(['todo_list_id', 'title', 'description', 'priority', 'due_at'])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<TodoList, $this>
     */
    public function todoList(): BelongsTo
    {
        return $this->belongsTo(TodoList::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'task_tag');
    }

    public function changeStatus(TaskStatus $status): void
    {
        $this->forceFill([
            'status' => $status,
            'completed_at' => $status->is(TaskStatus::Done) ? ($this->completed_at ?? now()) : null,
        ])->save();
    }

    public function isOverdue(): bool
    {
        return $this->due_at !== null
            && $this->due_at->isPast()
            && $this->status->isNot(TaskStatus::Done);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
            'due_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
        ];
    }
}
