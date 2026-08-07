<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Admin;
use App\Models\Tag;
use App\Models\Task;
use App\Models\TodoList;
use App\Models\User;

it('requires an admin session for task endpoints', function (string $method, string $uri) {
    $todoList = TodoList::factory()->create();
    $task = Task::factory()->for($todoList)->create();

    $resolvedUri = str_replace(
        ['{todoList}', '{task}'],
        [(string) $todoList->id, (string) $task->id],
        $uri,
    );

    $this->json($method, $resolvedUri)
        ->assertUnauthorized()
        ->assertExactJson([
            'code' => 'unauthenticated',
            'message' => 'Authentication is required.',
        ]);
})->with([
    'index' => ['GET', '/api/admin/todo-lists/{todoList}/tasks'],
    'index-all' => ['GET', '/api/admin/tasks'],
    'update' => ['PATCH', '/api/admin/tasks/{task}'],
    'destroy' => ['DELETE', '/api/admin/tasks/{task}'],
]);

it('lists all tasks across todo lists with owner and archive metadata', function () {
    $admin = Admin::factory()->create();
    $user = User::factory()->create(['name' => 'Анна']);
    $otherUser = User::factory()->create(['name' => 'Борис']);
    $activeList = TodoList::factory()->for($user)->create();
    $archivedList = TodoList::factory()->for($user)->archived()->create();
    $otherList = TodoList::factory()->for($otherUser)->create();

    $activeTask = Task::factory()->for($activeList)->create([
        'title' => 'Active owner task',
        'status' => TaskStatus::Todo,
    ]);
    $archivedTask = Task::factory()->for($archivedList)->create([
        'title' => 'Archived list task',
        'status' => TaskStatus::InProgress,
    ]);
    Task::factory()->for($otherList)->create([
        'title' => 'Other user task',
    ]);

    $this->actingAs($admin, 'admin')
        ->getJson('/api/admin/tasks?user_id='.$user->id.'&per_page=10')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.user_id', $user->id)
        ->assertJsonPath('data.0.user_name', 'Анна')
        ->assertJsonFragment(['id' => $activeTask->id, 'is_list_archived' => false])
        ->assertJsonFragment(['id' => $archivedTask->id, 'is_list_archived' => true]);

    $this->actingAs($admin, 'admin')
        ->getJson('/api/admin/tasks?user_id='.$user->id.'&list_archived=1&status=in_progress')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $archivedTask->id)
        ->assertJsonPath('data.0.is_list_archived', true);

    $this->actingAs($admin, 'admin')
        ->getJson('/api/admin/tasks?search=Active&list_archived=0')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $activeTask->id);
});

it('lists tasks for a todo list with filters and pagination', function () {
    $this->travelTo('2026-08-05 12:00:00');

    $admin = Admin::factory()->create();
    $user = User::factory()->create();
    $todoList = TodoList::factory()->for($user)->create();
    $tag = Tag::factory()->for($user)->create();
    $matchingTask = Task::factory()->for($todoList)->create([
        'title' => 'Prepare REPORT',
        'status' => TaskStatus::InProgress,
        'priority' => TaskPriority::High,
        'due_at' => now()->subHour(),
    ]);
    $matchingTask->tags()->attach($tag);
    Task::factory()->for($todoList)->create([
        'title' => 'Buy groceries',
        'status' => TaskStatus::Todo,
        'priority' => TaskPriority::Low,
        'due_at' => null,
    ]);
    Task::factory()->create();

    $this->actingAs($admin, 'admin')
        ->getJson("/api/admin/todo-lists/{$todoList->id}/tasks?search=report&status=in_progress&priority=high&tag_id={$tag->id}&due=overdue&per_page=1")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matchingTask->id)
        ->assertJsonPath('data.0.tags.0.id', $tag->id)
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.total', 1);
});

it('updates a task including tags and completed_at consistency', function () {
    $admin = Admin::factory()->create();
    $user = User::factory()->create();
    $todoList = TodoList::factory()->for($user)->create();
    $tag = Tag::factory()->for($user)->create();
    $otherTag = Tag::factory()->for($user)->create();
    $task = Task::factory()->for($todoList)->create([
        'status' => TaskStatus::Todo,
        'completed_at' => null,
    ]);
    $task->tags()->attach($tag);

    $this->actingAs($admin, 'admin')
        ->patchJson("/api/admin/tasks/{$task->id}", [
            'title' => '  Готовая   задача ',
            'description' => null,
            'status' => TaskStatus::Done->value,
            'priority' => TaskPriority::Low->value,
            'due_at' => '2030-08-10T12:00:00Z',
            'tag_ids' => [$otherTag->id],
            'completed_at' => now()->toISOString(),
            'todo_list_id' => TodoList::factory()->create()->id,
        ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Готовая задача')
        ->assertJsonPath('data.description', null)
        ->assertJsonPath('data.status', TaskStatus::Done->value)
        ->assertJsonPath('data.priority', TaskPriority::Low->value)
        ->assertJsonPath('data.due_at', '2030-08-10T12:00:00.000000Z')
        ->assertJsonCount(1, 'data.tags')
        ->assertJsonPath('data.tags.0.id', $otherTag->id)
        ->assertJsonPath('data.completed_at', fn (mixed $value) => is_string($value));

    $completedAt = $task->fresh()->completed_at;
    $this->travel(1)->minute();

    $this->actingAs($admin, 'admin')
        ->patchJson("/api/admin/tasks/{$task->id}", [
            'status' => TaskStatus::Done->value,
        ])->assertOk();

    expect($task->fresh()->completed_at->equalTo($completedAt))->toBeTrue();

    $this->actingAs($admin, 'admin')
        ->patchJson("/api/admin/tasks/{$task->id}", [
            'status' => TaskStatus::InProgress->value,
            'tag_ids' => [],
        ])
        ->assertOk()
        ->assertJsonPath('data.completed_at', null)
        ->assertJsonPath('data.tags', []);
});

it('allows an admin to update and delete tasks in archived lists and for blocked users', function () {
    $admin = Admin::factory()->create();
    $user = User::factory()->blocked()->create();
    $todoList = TodoList::factory()->for($user)->archived()->create();
    $task = Task::factory()->for($todoList)->create([
        'title' => 'Старое название',
    ]);

    $this->actingAs($admin, 'admin')
        ->patchJson("/api/admin/tasks/{$task->id}", [
            'title' => 'Новое название',
        ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Новое название');

    $this->actingAs($admin, 'admin')
        ->deleteJson("/api/admin/tasks/{$task->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('tasks', [
        'id' => $task->id,
    ]);
});

it('rejects foreign tags and invalid update payloads', function (array $payload, string $errorKey) {
    $user = User::factory()->create();
    $todoList = TodoList::factory()->for($user)->create();
    $task = Task::factory()->for($todoList)->create();
    $foreignTag = Tag::factory()->create();

    if (($payload['tag_ids'][0] ?? null) === 'foreign') {
        $payload['tag_ids'] = [$foreignTag->id];
    }

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->patchJson("/api/admin/tasks/{$task->id}", $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors($errorKey);
})->with([
    'empty title' => [['title' => ''], 'title'],
    'invalid status' => [['status' => 'archived'], 'status'],
    'invalid priority' => [['priority' => 'urgent'], 'priority'],
    'invalid due_at' => [['due_at' => '2026-08-05'], 'due_at'],
    'foreign tag' => [['tag_ids' => ['foreign']], 'tag_ids.0'],
]);

it('rejects unknown task list query parameters', function (array $query) {
    $todoList = TodoList::factory()->create();

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->getJson('/api/admin/todo-lists/'.$todoList->id.'/tasks?'.http_build_query($query))
        ->assertUnprocessable();
})->with([
    'invalid status' => [['status' => 'archived']],
    'invalid due' => [['due' => 'yesterday']],
    'invalid sort' => [['sort' => 'title']],
    'invalid direction' => [['direction' => 'sideways']],
    'foreign tag' => [['tag_id' => 999999]],
]);

it('returns 404 for unknown admin task resources', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')
        ->getJson('/api/admin/todo-lists/999999/tasks')
        ->assertNotFound();

    $this->actingAs($admin, 'admin')
        ->patchJson('/api/admin/tasks/999999', ['title' => 'X'])
        ->assertNotFound();

    $this->actingAs($admin, 'admin')
        ->deleteJson('/api/admin/tasks/999999')
        ->assertNotFound();
});
