<?php

namespace App\Http\Resources;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Task */
final class TaskResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'todo_list_id' => $this->todo_list_id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'priority' => $this->priority->value,
            'due_at' => $this->due_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'is_overdue' => $this->isOverdue(),
            'is_list_archived' => $this->when(
                $this->relationLoaded('todoList'),
                fn (): bool => $this->todoList->isArchived(),
            ),
            'user_id' => $this->when(
                $this->relationLoaded('todoList'),
                fn (): int => $this->todoList->user_id,
            ),
            'user_name' => $this->when(
                $this->relationLoaded('todoList') && $this->todoList->relationLoaded('user'),
                fn (): string => $this->todoList->user->name,
            ),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
