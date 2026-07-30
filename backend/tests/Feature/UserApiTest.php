<?php

use App\Models\User;

it('lists only active profiles in normalized name order', function () {
    $secondUser = User::factory()->create([
        'name' => 'Beta',
        'normalized_name' => 'beta',
    ]);
    $firstUser = User::factory()->create([
        'name' => 'Alpha',
        'normalized_name' => 'alpha',
    ]);
    User::factory()->blocked()->create([
        'name' => 'Blocked',
        'normalized_name' => 'blocked',
    ]);

    $this->getJson('/api/users')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $firstUser->id)
        ->assertJsonPath('data.1.id', $secondUser->id)
        ->assertJsonMissingPath('data.0.todo_lists');
});

it('returns a blocked profile by its id', function () {
    $user = User::factory()->blocked()->create();

    $this->getJson("/api/users/{$user->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.is_blocked', true)
        ->assertJsonMissingPath('data.todo_lists');
});

it('creates and normalizes a demo profile', function () {
    $this->postJson('/api/users', [
        'name' => '  Анна   Смирнова  ',
        'normalized_name' => 'injected',
        'blocked_at' => now()->toISOString(),
    ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Анна Смирнова')
        ->assertJsonPath('data.is_blocked', false);

    $this->assertDatabaseHas('users', [
        'name' => 'Анна Смирнова',
        'normalized_name' => 'анна смирнова',
        'blocked_at' => null,
    ]);
});

it('rejects a duplicate profile name without regard to case or spacing', function () {
    User::factory()->create([
        'name' => 'Анна Смирнова',
        'normalized_name' => 'анна смирнова',
    ]);

    $this->postJson('/api/users', [
        'name' => '  АННА   СМИРНОВА ',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');

    expect(User::query()->count())->toBe(1);
});

it('validates a demo profile name', function (array $payload) {
    $this->postJson('/api/users', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
})->with([
    'missing name' => [[]],
    'blank name' => [['name' => '   ']],
    'non-string name' => [['name' => ['Анна']]],
    'name over 80 characters' => [['name' => str_repeat('a', 81)]],
]);

it('returns not found for an unknown profile id', function () {
    $this->getJson('/api/users/999999')
        ->assertNotFound();
});
