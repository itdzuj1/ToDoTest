<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->name();

        return [
            'name' => $name,
            'normalized_name' => Str::of($name)->squish()->lower()->toString(),
            'blocked_at' => null,
        ];
    }

    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'blocked_at' => now(),
        ]);
    }
}
