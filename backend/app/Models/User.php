<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property CarbonImmutable|null $blocked_at
 */
#[Fillable(['name', 'normalized_name'])]
class User extends Model
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    /**
     * @return HasMany<TodoList, $this>
     */
    public function todoLists(): HasMany
    {
        return $this->hasMany(TodoList::class);
    }

    /**
     * @return HasManyThrough<Task, TodoList, $this>
     */
    public function tasks(): HasManyThrough
    {
        return $this->hasManyThrough(Task::class, TodoList::class);
    }

    /**
     * @return HasMany<Tag, $this>
     */
    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    /**
     * @return list<string>
     */
    public static function resourceCountRelations(): array
    {
        return ['todoLists', 'tasks'];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'blocked_at' => 'immutable_datetime',
        ];
    }

    public function isBlocked(): bool
    {
        return $this->blocked_at !== null;
    }

    public function block(): void
    {
        if ($this->isBlocked()) {
            return;
        }

        $this->forceFill(['blocked_at' => now()])->saveOrFail();
    }

    public function unblock(): void
    {
        if (! $this->isBlocked()) {
            return;
        }

        $this->forceFill(['blocked_at' => null])->saveOrFail();
    }
}
