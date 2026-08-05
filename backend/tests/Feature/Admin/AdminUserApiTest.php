<?php

use App\Models\Admin;
use App\Models\Task;
use App\Models\TodoList;
use App\Models\User;

it('requires an admin session for user endpoints', function (string $method, string $uri) {
    $user = User::factory()->create();

    $this->json($method, str_replace('{user}', (string) $user->id, $uri))
        ->assertUnauthorized()
        ->assertExactJson([
            'code' => 'unauthenticated',
            'message' => 'Authentication is required.',
        ]);
})->with([
    'index' => ['GET', '/api/admin/users'],
    'show' => ['GET', '/api/admin/users/{user}'],
    'block' => ['PATCH', '/api/admin/users/{user}/block'],
    'unblock' => ['PATCH', '/api/admin/users/{user}/unblock'],
]);

it('lists users with search filter and pagination', function () {
    $admin = Admin::factory()->create();
    $anna = User::factory()->create([
        'name' => 'Анна Смирнова',
        'normalized_name' => 'анна смирнова',
    ]);
    $blocked = User::factory()->blocked()->create([
        'name' => 'Борис Иванов',
        'normalized_name' => 'борис иванов',
    ]);
    User::factory()->create([
        'name' => 'Виктор Орлов',
        'normalized_name' => 'виктор орлов',
    ]);

    $this->actingAs($admin, 'admin')
        ->getJson('/api/admin/users?search=анна')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $anna->id)
        ->assertJsonMissingPath('data.0.todo_lists');

    $this->actingAs($admin, 'admin')
        ->getJson('/api/admin/users?filter=blocked')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $blocked->id);

    $this->actingAs($admin, 'admin')
        ->getJson('/api/admin/users?filter=active')
        ->assertOk()
        ->assertJsonCount(2, 'data');

    $this->actingAs($admin, 'admin')
        ->getJson('/api/admin/users?per_page=1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.total', 3);
});

it('shows a user with todo lists and without nested tasks', function () {
    $user = User::factory()->create();
    $firstList = TodoList::factory()->for($user)->create(['title' => 'Первый']);
    $secondList = TodoList::factory()->for($user)->create(['title' => 'Второй']);
    Task::factory()->for($firstList)->create();

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->getJson("/api/admin/users/{$user->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonCount(2, 'data.todo_lists')
        ->assertJsonPath('data.todo_lists.0.id', $firstList->id)
        ->assertJsonPath('data.todo_lists.1.id', $secondList->id)
        ->assertJsonMissingPath('data.todo_lists.0.tasks');
});

it('returns 404 for an unknown admin user', function () {
    $this->actingAs(Admin::factory()->create(), 'admin')
        ->getJson('/api/admin/users/999999')
        ->assertNotFound();
});

it('blocks and unblocks users idempotently', function () {
    $admin = Admin::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($admin, 'admin')
        ->patchJson("/api/admin/users/{$user->id}/block")
        ->assertOk()
        ->assertJsonPath('data.is_blocked', true);

    $blockedAt = $user->fresh()->blocked_at;
    $this->travel(1)->minute();

    $this->actingAs($admin, 'admin')
        ->patchJson("/api/admin/users/{$user->id}/block")
        ->assertOk()
        ->assertJsonPath('data.is_blocked', true);

    expect($user->fresh()->blocked_at->equalTo($blockedAt))->toBeTrue();

    $this->actingAs($admin, 'admin')
        ->patchJson("/api/admin/users/{$user->id}/unblock")
        ->assertOk()
        ->assertJsonPath('data.is_blocked', false)
        ->assertJsonPath('data.blocked_at', null);

    $this->actingAs($admin, 'admin')
        ->patchJson("/api/admin/users/{$user->id}/unblock")
        ->assertOk()
        ->assertJsonPath('data.is_blocked', false);
});

it('rejects unknown user list query parameters', function (array $query) {
    $this->actingAs(Admin::factory()->create(), 'admin')
        ->getJson('/api/admin/users?'.http_build_query($query))
        ->assertUnprocessable();
})->with([
    'invalid filter' => [['filter' => 'deleted']],
    'invalid per_page' => [['per_page' => 0]],
    'search too long' => [['search' => str_repeat('a', 81)]],
]);
