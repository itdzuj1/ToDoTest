<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\TodoListFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property CarbonImmutable|null $archived_at
 */
#[Fillable(['user_id', 'title'])]
class TodoList extends Model
{
    /** @use HasFactory<TodoListFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Task, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function archive(): void
    {
        if ($this->isArchived()) {
            return;
        }

        $this->forceFill(['archived_at' => now()])->saveOrFail();
    }

    public function restore(): void
    {
        if (! $this->isArchived()) {
            return;
        }

        $this->forceFill(['archived_at' => null])->saveOrFail();
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'archived_at' => 'immutable_datetime',
        ];
    }
}
