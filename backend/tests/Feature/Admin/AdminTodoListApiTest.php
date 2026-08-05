<?php

use App\Models\Admin;
use App\Models\TodoList;
use App\Models\User;

it('requires an admin session for list archive endpoints', function (string $uri) {
    $todoList = TodoList::factory()->create();

    $this->patchJson(str_replace('{todoList}', (string) $todoList->id, $uri))
        ->assertUnauthorized()
        ->assertExactJson([
            'code' => 'unauthenticated',
            'message' => 'Authentication is required.',
        ]);
})->with([
    'archive' => ['/api/admin/todo-lists/{todoList}/archive'],
    'restore' => ['/api/admin/todo-lists/{todoList}/restore'],
]);

it('archives and restores todo lists idempotently for admins', function () {
    $admin = Admin::factory()->create();
    $todoList = TodoList::factory()->create();

    $this->actingAs($admin, 'admin')
        ->patchJson("/api/admin/todo-lists/{$todoList->id}/archive")
        ->assertOk()
        ->assertJsonPath('data.is_archived', true);

    $archivedAt = $todoList->fresh()->archived_at;
    $this->travel(1)->minute();

    $this->actingAs($admin, 'admin')
        ->patchJson("/api/admin/todo-lists/{$todoList->id}/archive")
        ->assertOk()
        ->assertJsonPath('data.is_archived', true);

    expect($todoList->fresh()->archived_at->equalTo($archivedAt))->toBeTrue();

    $this->actingAs($admin, 'admin')
        ->patchJson("/api/admin/todo-lists/{$todoList->id}/restore")
        ->assertOk()
        ->assertJsonPath('data.is_archived', false)
        ->assertJsonPath('data.archived_at', null);

    $this->actingAs($admin, 'admin')
        ->patchJson("/api/admin/todo-lists/{$todoList->id}/restore")
        ->assertOk()
        ->assertJsonPath('data.is_archived', false);
});

it('returns 404 for an unknown admin todo list', function () {
    $this->actingAs(Admin::factory()->create(), 'admin')
        ->patchJson('/api/admin/todo-lists/999999/archive')
        ->assertNotFound();
});

it('allows an admin to archive a list of a blocked user', function () {
    $user = User::factory()->blocked()->create();
    $todoList = TodoList::factory()->for($user)->create();

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->patchJson("/api/admin/todo-lists/{$todoList->id}/archive")
        ->assertOk()
        ->assertJsonPath('data.is_archived', true);
});
