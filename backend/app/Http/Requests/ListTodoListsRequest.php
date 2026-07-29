<?php

namespace App\Http\Requests;

use App\Models\TodoList;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

final class ListTodoListsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User
            && Gate::forUser($user)->allows('viewAny', TodoList::class);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'filter' => ['sometimes', 'string', Rule::in(['active', 'archived', 'all'])],
        ];
    }

    public function filter(): string
    {
        return $this->has('filter')
            ? $this->string('filter')->toString()
            : 'active';
    }
}
