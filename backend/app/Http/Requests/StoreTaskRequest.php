<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TodoList;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');
        $todoList = $this->route('todoList');

        return $user instanceof User
            && $todoList instanceof TodoList
            && Gate::forUser($user)->allows('create', [Task::class, $todoList]);
    }

    /**
     * @return array<string, list<ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'status' => ['sometimes', 'required', Rule::enum(TaskStatus::class)],
            'priority' => ['sometimes', 'required', Rule::enum(TaskPriority::class)],
            'due_at' => [
                'sometimes',
                'nullable',
                'date',
                'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/',
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
}
