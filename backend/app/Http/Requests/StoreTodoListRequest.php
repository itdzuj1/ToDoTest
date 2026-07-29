<?php

namespace App\Http\Requests;

use App\Models\TodoList;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

final class StoreTodoListRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User
            && Gate::forUser($user)->allows('create', TodoList::class);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
        ];
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
