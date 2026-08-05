<?php

namespace App\Http\Requests\Admin;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Admin;
use App\Models\Tag;
use App\Models\Task;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class UpdateTaskRequest extends FormRequest
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
        return [
            'title' => ['sometimes', 'required', 'string', 'max:120'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'status' => ['sometimes', 'required', Rule::enum(TaskStatus::class)],
            'priority' => ['sometimes', 'required', Rule::enum(TaskPriority::class)],
            'due_at' => [
                'sometimes',
                'nullable',
                'date',
                'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/',
            ],
            'tag_ids' => ['sometimes', 'nullable', 'array', 'list'],
            'tag_ids.*' => [
                'integer',
                'distinct:strict',
                Rule::exists(Tag::class, 'id')->where(
                    fn (Builder $query) => $query->where('user_id', $this->taskOwnerId()),
                ),
            ],
        ];
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

    public function dueAt(): ?CarbonImmutable
    {
        $dueAt = $this->validated('due_at');

        return is_string($dueAt) ? CarbonImmutable::parse($dueAt)->utc() : null;
    }

    /**
     * @return list<int>|null
     */
    public function tagIds(): ?array
    {
        $validated = $this->validated();

        if (! array_key_exists('tag_ids', $validated)) {
            return null;
        }

        if (! is_array($validated['tag_ids'])) {
            return [];
        }

        return array_values(array_map(
            fn (mixed $tagId): int => (int) $tagId,
            $validated['tag_ids'],
        ));
    }

    protected function prepareForValidation(): void
    {
        $title = $this->input('title');

        if (! is_string($title)) {
            return;
        }

        $this->merge([
            'title' => Str::squish($title),
        ]);
    }

    private function taskOwnerId(): int
    {
        $task = $this->route('task');

        if (! $task instanceof Task) {
            return 0;
        }

        $task->loadMissing('todoList');

        return $task->todoList->user_id;
    }
}
