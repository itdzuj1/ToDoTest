<?php

namespace App\Http\Requests\Admin;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Admin;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListAllTasksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') instanceof Admin;
    }

    /**
     * @return array<string, list<ValidationRule|string>>
     */
    public function rules(): array
    {
        $userId = $this->input('user_id');

        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:120'],
            'user_id' => ['sometimes', 'required', 'integer', Rule::exists(User::class, 'id')],
            'status' => ['sometimes', 'required', Rule::enum(TaskStatus::class)],
            'priority' => ['sometimes', 'required', Rule::enum(TaskPriority::class)],
            'tag_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists(Tag::class, 'id')->where(
                    function (Builder $query) use ($userId): void {
                        if (is_int($userId) || (is_string($userId) && $userId !== '')) {
                            $query->where('user_id', (int) $userId);
                        }
                    },
                ),
            ],
            'due' => ['sometimes', 'required', Rule::in(['overdue', 'today', 'upcoming', 'none'])],
            'list_archived' => ['sometimes', 'required', 'boolean'],
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

    public function userId(): ?int
    {
        $userId = $this->validated('user_id');

        return is_int($userId) || is_string($userId) ? (int) $userId : null;
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

    public function listArchived(): ?bool
    {
        if (! array_key_exists('list_archived', $this->validated())) {
            return null;
        }

        return (bool) $this->validated('list_archived');
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
}
