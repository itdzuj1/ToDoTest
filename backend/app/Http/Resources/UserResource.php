<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
final class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_blocked' => $this->isBlocked(),
            'blocked_at' => $this->blocked_at?->toISOString(),
            'todo_lists_count' => (int) ($this->todo_lists_count ?? 0),
            'tasks_count' => (int) ($this->tasks_count ?? 0),
            'todo_lists' => TodoListResource::collection($this->whenLoaded('todoLists')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
