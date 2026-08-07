<?php

use App\Models\Task;
use App\Models\TodoList;
use App\Models\User;

it('creates a todo list for the selected profile', function () {
    $user = User::factory()->create();

    $this->postJson("/api/users/{$user->id}/todo-lists", [
        'title' => '  Рабочие   задачи  ',
    ])
        ->assertCreated()
        ->assertJsonPath('data.user_id', $user->id)
        ->assertJsonPath('data.title', 'Рабочие задачи')
        ->assertJsonPath('data.is_archived', false)
        ->assertJsonPath('data.tasks_count', 0)
        ->assertJsonPath('data.completed_tasks_count', 0);

    $this->assertDatabaseHas('todo_lists', [
        'user_id' => $user->id,
        'title' => 'Рабочие задачи',
        'archived_at' => null,
    ]);
});

it('does not resolve a todo list through another profile', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $todoList = TodoList::factory()->for($otherUser)->create();

    $this->getJson("/api/users/{$user->id}/todo-lists/{$todoList->id}")
        ->assertNotFound();
});

it('keeps blocked profiles read-only', function () {
    $user = User::factory()->blocked()->create();
    $todoList = TodoList::factory()->for($user)->create();
    $archivedList = TodoList::factory()->for($user)->archived()->create();

    $this->getJson("/api/users/{$user->id}/todo-lists")
        ->assertOk();

    $this->getJson("/api/users/{$user->id}/todo-lists/{$todoList->id}")
        ->assertOk();

    $this->postJson("/api/users/{$user->id}/todo-lists", [
        'title' => 'Новый список',
    ])->assertForbidden();

    $this->patchJson("/api/users/{$user->id}/todo-lists/{$todoList->id}", [
        'title' => 'Новое название',
    ])->assertForbidden();

    $this->patchJson("/api/users/{$user->id}/todo-lists/{$todoList->id}/archive")
        ->assertForbidden();

    $this->patchJson("/api/users/{$user->id}/todo-lists/{$archivedList->id}/restore")
        ->assertForbidden();
});

it('prevents renaming an archived list until it is restored', function () {
    $user = User::factory()->create();
    $todoList = TodoList::factory()->for($user)->archived()->create([
        'title' => 'Архив',
    ]);

    $this->patchJson("/api/users/{$user->id}/todo-lists/{$todoList->id}", [
        'title' => 'Новое название',
    ])->assertForbidden();

    $this->patchJson("/api/users/{$user->id}/todo-lists/{$todoList->id}/restore")
        ->assertOk()
        ->assertJsonPath('data.is_archived', false);

    $this->patchJson("/api/users/{$user->id}/todo-lists/{$todoList->id}", [
        'title' => 'Новое название',
    ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Новое название');
});

it('keeps archive and restore transitions idempotent', function () {
    $user = User::factory()->create();
    $todoList = TodoList::factory()->for($user)->create();
    $url = "/api/users/{$user->id}/todo-lists/{$todoList->id}";

    $this->patchJson("{$url}/archive")
        ->assertOk()
        ->assertJsonPath('data.is_archived', true);

    $archivedList = $todoList->fresh();
    $this->travel(1)->minute();

    $this->patchJson("{$url}/archive")
        ->assertOk()
        ->assertJsonPath('data.is_archived', true);

    $archiveRetry = $todoList->fresh();

    expect($archiveRetry->archived_at->equalTo($archivedList->archived_at))->toBeTrue()
        ->and($archiveRetry->updated_at->equalTo($archivedList->updated_at))->toBeTrue();

    $this->patchJson("{$url}/restore")
        ->assertOk()
        ->assertJsonPath('data.is_archived', false);

    $restoredList = $todoList->fresh();
    $this->travel(1)->minute();

    $this->patchJson("{$url}/restore")
        ->assertOk()
        ->assertJsonPath('data.is_archived', false);

    $restoreRetry = $todoList->fresh();

    expect($restoreRetry->archived_at)->toBeNull()
        ->and($restoreRetry->updated_at->equalTo($restoredList->updated_at))->toBeTrue();
});

it('includes task counts on todo list responses', function () {
    $user = User::factory()->create();
    $todoList = TodoList::factory()->for($user)->create();
    Task::factory()->for($todoList)->count(2)->create();
    Task::factory()->for($todoList)->done()->create();

    $this->getJson("/api/users/{$user->id}/todo-lists")
        ->assertOk()
        ->assertJsonPath('data.0.tasks_count', 3)
        ->assertJsonPath('data.0.completed_tasks_count', 1);

    $this->getJson("/api/users/{$user->id}/todo-lists/{$todoList->id}")
        ->assertOk()
        ->assertJsonPath('data.tasks_count', 3)
        ->assertJsonPath('data.completed_tasks_count', 1);
});

it('filters active and archived todo lists', function () {
    $user = User::factory()->create();
    $activeList = TodoList::factory()->for($user)->create();
    $archivedList = TodoList::factory()->for($user)->archived()->create();

    $this->getJson("/api/users/{$user->id}/todo-lists")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $activeList->id);

    $this->getJson("/api/users/{$user->id}/todo-lists?filter=archived")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $archivedList->id);

    $this->getJson("/api/users/{$user->id}/todo-lists?filter=all")
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['id' => $activeList->id])
        ->assertJsonFragment(['id' => $archivedList->id]);
});

it('rejects an unsupported todo list filter', function () {
    $user = User::factory()->create();

    $this->getJson("/api/users/{$user->id}/todo-lists?filter=deleted")
        ->assertUnprocessable()
        ->assertJsonValidationErrors('filter');
});

it('validates a todo list title when creating', function (array $payload) {
    $user = User::factory()->create();

    $this->postJson("/api/users/{$user->id}/todo-lists", $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('title');
})->with([
    'missing title' => [[]],
    'blank title' => [['title' => '   ']],
    'non-string title' => [['title' => ['Список']]],
    'title over 120 characters' => [['title' => str_repeat('a', 121)]],
]);

it('normalizes a title when renaming and ignores fields outside the contract', function () {
    $user = User::factory()->create();
    $todoList = TodoList::factory()->for($user)->create();

    $this->patchJson("/api/users/{$user->id}/todo-lists/{$todoList->id}", [
        'title' => '  Домашние   дела  ',
        'archived_at' => now()->toISOString(),
    ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Домашние дела')
        ->assertJsonPath('data.is_archived', false);

    $this->assertDatabaseHas('todo_lists', [
        'id' => $todoList->id,
        'title' => 'Домашние дела',
        'archived_at' => null,
    ]);
});

it('validates a todo list title when renaming', function (array $payload) {
    $user = User::factory()->create();
    $todoList = TodoList::factory()->for($user)->create([
        'title' => 'Исходное название',
    ]);

    $this->patchJson(
        "/api/users/{$user->id}/todo-lists/{$todoList->id}",
        $payload,
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('title');

    $this->assertDatabaseHas('todo_lists', [
        'id' => $todoList->id,
        'title' => 'Исходное название',
    ]);
})->with([
    'missing title' => [[]],
    'blank title' => [['title' => '   ']],
    'non-string title' => [['title' => ['Список']]],
    'title over 120 characters' => [['title' => str_repeat('a', 121)]],
]);

it('allows duplicate todo list titles for one profile', function () {
    $user = User::factory()->create();
    $url = "/api/users/{$user->id}/todo-lists";

    $this->postJson($url, ['title' => 'Личное'])->assertCreated();
    $this->postJson($url, ['title' => 'Личное'])->assertCreated();

    expect($user->todoLists()->where('title', 'Личное')->count())->toBe(2);
});

it('returns not found for an unknown profile', function () {
    $this->getJson('/api/users/999999/todo-lists')
        ->assertNotFound();
});

it('does not mutate a todo list through another profile', function (
    string $method,
    string $suffix,
    array $payload,
) {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $todoList = TodoList::factory()->for($otherUser)->create();

    $this->json(
        $method,
        "/api/users/{$user->id}/todo-lists/{$todoList->id}{$suffix}",
        $payload,
    )->assertNotFound();
})->with([
    'rename' => ['PATCH', '', ['title' => 'Чужое название']],
    'archive' => ['PATCH', '/archive', []],
    'restore' => ['PATCH', '/restore', []],
]);
