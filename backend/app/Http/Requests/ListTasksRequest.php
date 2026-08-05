<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

final class ListTasksRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User
            && Gate::forUser($user)->allows('viewAny', Task::class);
    }

    /**
     * @return array<string, list<ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:120'],
            'status' => ['sometimes', 'required', Rule::enum(TaskStatus::class)],
            'priority' => ['sometimes', 'required', Rule::enum(TaskPriority::class)],
            'tag_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists(Tag::class, 'id')->where(
                    fn (Builder $query) => $query->where('user_id', $this->routeUserId()),
                ),
            ],
            'due' => ['sometimes', 'required', Rule::in(['overdue', 'today', 'upcoming', 'none'])],
            'sort' => ['sometimes', 'required', Rule::in(['created_at', 'due_at', 'priority'])],
            'direction' => ['sometimes', 'required', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'required', 'integer', 'between:1,100'],
            'page' => ['sometimes', 'required', 'integer', 'min:1'],
        ];
    }

    public function search(): ?string
    {
        $search = $this->validated('search');

        return is_string($search) && $search !== '' ? $search : null;
    }

    public function status(): ?TaskStatus
    {
        $status = $this->validated('status');

        return is_string($status) ? TaskStatus::from($status) : null;
    }

    public function priority(): ?TaskPriority
    {
        $priority = $this->validated('priority');

        return is_string($priority) ? TaskPriority::from($priority) : null;
    }

    public function tagId(): ?int
    {
        $tagId = $this->validated('tag_id');

        return is_int($tagId) || is_string($tagId) ? (int) $tagId : null;
    }

    public function due(): ?string
    {
        $due = $this->validated('due');

        return is_string($due) ? $due : null;
    }

    public function sort(): string
    {
        $sort = $this->validated('sort');

        return is_string($sort) ? $sort : 'created_at';
    }

    public function direction(): string
    {
        $direction = $this->validated('direction');

        return is_string($direction) ? $direction : 'desc';
    }

    public function perPage(): int
    {
        $perPage = $this->validated('per_page');

        return is_int($perPage) || is_string($perPage) ? (int) $perPage : 20;
    }

    private function routeUserId(): int
    {
        $user = $this->route('user');

        return $user instanceof User ? $user->id : 0;
    }
}
