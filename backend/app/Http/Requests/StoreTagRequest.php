<?php

namespace App\Http\Requests;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

final class StoreTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User
            && Gate::forUser($user)->allows('create', Tag::class);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
        ];
    }

    public function normalizedName(): string
    {
        return Str::lower($this->string('name')->toString());
    }

    /**
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $user = $this->route('user');

                if ($validator->errors()->has('name') || ! $user instanceof User) {
                    return;
                }

                $exists = $user->tags()
                    ->where('normalized_name', $this->normalizedName())
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('name', 'A tag with this name already exists.');
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $name = $this->input('name');

        if (! is_string($name)) {
            return;
        }

        $this->merge([
            'name' => Str::squish($name),
        ]);
    }
}
