<?php

namespace App\Http\Requests\Admin;

use App\Models\Admin;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListUsersRequest extends FormRequest
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
            'search' => ['sometimes', 'nullable', 'string', 'max:80'],
            'filter' => ['sometimes', 'required', Rule::in(['active', 'blocked', 'all'])],
            'per_page' => ['sometimes', 'required', 'integer', 'between:1,100'],
            'page' => ['sometimes', 'required', 'integer', 'min:1'],
        ];
    }

    public function search(): ?string
    {
        $search = $this->validated('search');

        return is_string($search) && $search !== '' ? $search : null;
    }

    public function filter(): string
    {
        $filter = $this->validated('filter');

        return is_string($filter) ? $filter : 'all';
    }

    public function perPage(): int
    {
        $perPage = $this->validated('per_page');

        return is_int($perPage) || is_string($perPage) ? (int) $perPage : 20;
    }
}
