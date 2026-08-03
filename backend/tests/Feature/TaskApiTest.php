<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Tag;
use App\Models\Task;
use App\Models\TodoList;
use App\Models\User;

it('creates a task with normalized title and default values', function () {
    $user = User::factory()->create();
    $todoList = TodoList::factory()->for($user)->create();
    $otherList = TodoList::factory()->for($user)->create();

    $this->postJson("/api/users/{$user->id}/todo-lists/{$todoList->id}/tasks", [
        'title' => '  Купить   продукты ',
        'todo_list_id' => $otherList->id,
        'completed_at' => now()->toISOString(),
    ])
        ->assertCreated()
        ->assertJsonPath('data.todo_list_id', $todoList->id)
        ->assertJsonPath('data.title', 'Купить продукты')
        ->assertJsonPath('data.description', null)
        ->assertJsonPath('data.status', TaskStatus::Todo->value)
        ->assertJsonPath('data.priority', TaskPriority::Medium->value)
        ->assertJsonPath('data.due_at', null)
        ->assertJsonPath('data.completed_at', null)
        ->assertJsonPath('data.tags', []);

    $this->assertDatabaseHas('tasks', [
        'todo_list_id' => $todoList->id,
        'title' => 'Купить продукты',
        'status' => TaskStatus::Todo,
        'priority' => TaskPriority::Medium,
        'completed_at' => null,
    ]);
});

it('creates a task with status priority description and due date', function () {
    $user = User::factory()->create();
    $todoList = TodoList::factory()->for($user)->create();

    $this->postJson("/api/users/{$user->id}/todo-lists/{$todoList->id}/tasks", [
        'title' => 'Опубликовать отчёт',
        'description' => 'Проверить итоговые цифры',
        'status' => TaskStatus::Done->value,
        'priority' => TaskPriority::High->value,
        'due_at' => '2030-08-10T15:30:00+03:00',
    ])
        ->assertCreated()
        ->assertJsonPath('data.description', 'Проверить итоговые цифры')
        ->assertJsonPath('data.status', TaskStatus::Done->value)
        ->assertJsonPath('data.priority', TaskPriority::High->value)
        ->assertJsonPath('data.due_at', '2030-08-10T12:30:00.000000Z')
        ->assertJsonPath('data.is_overdue', false)
        ->assertJsonPath('data.completed_at', fn (mixed $value) => is_string($value));
});

it('lists and shows only tasks from the selected list with tags loaded', function () {
    $user = User::factory()->create();
    $todoList = TodoList::factory()->for($user)->create();
    $firstTask = Task::factory()->for($todoList)->create();
    $secondTask = Task::factory()->for($todoList)->create();
    $tag = Tag::factory()->for($user)->create();
    $secondTask->tags()->attach($tag);
    Task::factory()->create();

    $this->getJson("/api/users/{$user->id}/todo-lists/{$todoList->id}/tasks")
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $secondTask->id)
        ->assertJsonPath('data.0.tags.0.id', $tag->id)
        ->assertJsonPath('data.1.id', $firstTask->id);

    $this->getJson("/api/users/{$user->id}/todo-lists/{$todoList->id}/tasks/{$secondTask->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $secondTask->id)
        ->assertJsonPath('data.tags.0.id', $tag->id);
});

it('updates task fields and keeps completed_at consistent with status', function () {
    $user = User::factory()->create();
    $todoList = TodoList::factory()->for($user)->create();
    $task = Task::factory()->for($todoList)->create([
        'status' => TaskStatus::Todo,
        'completed_at' => null,
    ]);

    $this->patchJson("/api/users/{$user->id}/todo-lists/{$todoList->id}/tasks/{$task->id}", [
        'title' => '  Готовая   задача ',
        'description' => null,
        'status' => TaskStatus::Done->value,
        'priority' => TaskPriority::Low->value,
        'due_at' => '2030-08-10T12:00:00Z',
    ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Готовая задача')
        ->assertJsonPath('data.description', null)
        ->assertJsonPath('data.status', TaskStatus::Done->value)
        ->assertJsonPath('data.priority', TaskPriority::Low->value)
        ->assertJsonPath('data.completed_at', fn (mixed $value) => is_string($value));

    $completedAt = $task->fresh()->completed_at;
    $this->travel(1)->minute();

    $this->patchJson("/api/users/{$user->id}/todo-lists/{$todoList->id}/tasks/{$task->id}", [
        'status' => TaskStatus::Done->value,
    ])->assertOk();

    expect($task->fresh()->completed_at->equalTo($completedAt))->toBeTrue();

    $this->patchJson("/api/users/{$user->id}/todo-lists/{$todoList->id}/tasks/{$task->id}", [
        'status' => TaskStatus::InProgress->value,
    ])
        ->assertOk()
        ->assertJsonPath('data.completed_at', null);
});

it('updates overdue state when a task is completed', function () {
    $user = User::factory()->create();
    $todoList = TodoList::factory()->for($user)->create();
    $task = Task::factory()->for($todoList)->overdue()->create();

    $this->getJson("/api/users/{$user->id}/todo-lists/{$todoList->id}/tasks/{$task->id}")
        ->assertOk()
        ->assertJsonPath('data.is_overdue', true);

    $this->patchJson("/api/users/{$user->id}/todo-lists/{$todoList->id}/tasks/{$task->id}", [
        'status' => TaskStatus::Done->value,
    ])
        ->assertOk()
        ->assertJsonPath('data.is_overdue', false);
});

it('deletes a task', function () {
    $user = User::factory()->create();
    $todoList = TodoList::factory()->for($user)->create();
    $task = Task::factory()->for($todoList)->create();

    $this->deleteJson("/api/users/{$user->id}/todo-lists/{$todoList->id}/tasks/{$task->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});

it('validates task fields when creating', function (array $payload, string $field) {
    $user = User::factory()->create();
    $todoList = TodoList::factory()->for($user)->create();

    $this->postJson(
        "/api/users/{$user->id}/todo-lists/{$todoList->id}/tasks",
        $payload,
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);
})->with([
    'missing title' => [[], 'title'],
    'blank title' => [['title' => '   '], 'title'],
    'non-string title' => [['title' => ['Задача']], 'title'],
    'title over 120 characters' => [['title' => str_repeat('a', 121)], 'title'],
    'non-string description' => [['title' => 'Задача', 'description' => []], 'description'],
    'description over 5000 characters' => [
        ['title' => 'Задача', 'description' => str_repeat('a', 5001)],
        'description',
    ],
    'invalid status' => [['title' => 'Задача', 'status' => 'closed'], 'status'],
    'invalid priority' => [['title' => 'Задача', 'priority' => 'urgent'], 'priority'],
    'invalid due date' => [['title' => 'Задача', 'due_at' => 'not-a-date'], 'due_at'],
    'due date without timezone' => [['title' => 'Задача', 'due_at' => '2030-08-10T12:00:00'], 'due_at'],
]);

it('validates task fields when updating', function (array $payload, string $field) {
    $user = User::factory()->create();
    $todoList = TodoList::factory()->for($user)->create();
    $task = Task::factory()->for($todoList)->create();

    $this->patchJson(
        "/api/users/{$user->id}/todo-lists/{$todoList->id}/tasks/{$task->id}",
        $payload,
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);
})->with([
    'blank title' => [['title' => '   '], 'title'],
    'null title' => [['title' => null], 'title'],
    'invalid status' => [['status' => 'closed'], 'status'],
    'invalid priority' => [['priority' => 'urgent'], 'priority'],
    'invalid due date' => [['due_at' => '2030-08-10'], 'due_at'],
]);

it('keeps blocked profiles tasks read-only', function () {
    $user = User::factory()->blocked()->create();
    $todoList = TodoList::factory()->for($user)->create();
    $task = Task::factory()->for($todoList)->create();

    $this->getJson("/api/users/{$user->id}/todo-lists/{$todoList->id}/tasks")
        ->assertOk();

    $this->getJson("/api/users/{$user->id}/todo-lists/{$todoList->id}/tasks/{$task->id}")
        ->assertOk();

    $this->postJson("/api/users/{$user->id}/todo-lists/{$todoList->id}/tasks", [
        'title' => 'Новая задача',
    ])->assertForbidden();

    $this->patchJson("/api/users/{$user->id}/todo-lists/{$todoList->id}/tasks/{$task->id}", [
        'title' => 'Новое название',
    ])->assertForbidden();

    $this->deleteJson("/api/users/{$user->id}/todo-lists/{$todoList->id}/tasks/{$task->id}")
        ->assertForbidden();
});

it('keeps archived lists tasks read-only', function () {
    $user = User::factory()->create();
    $todoList = TodoList::factory()->for($user)->archived()->create();
    $task = Task::factory()->for($todoList)->create();

    $this->getJson("/api/users/{$user->id}/todo-lists/{$todoList->id}/tasks")
        ->assertOk();

    $this->getJson("/api/users/{$user->id}/todo-lists/{$todoList->id}/tasks/{$task->id}")
        ->assertOk();

    $this->postJson("/api/users/{$user->id}/todo-lists/{$todoList->id}/tasks", [
        'title' => 'Новая задача',
    ])->assertForbidden();

    $this->patchJson("/api/users/{$user->id}/todo-lists/{$todoList->id}/tasks/{$task->id}", [
        'title' => 'Новое название',
    ])->assertForbidden();

    $this->deleteJson("/api/users/{$user->id}/todo-lists/{$todoList->id}/tasks/{$task->id}")
        ->assertForbidden();
});

it('does not resolve a task through another list', function (
    string $method,
    array $payload,
) {
    $user = User::factory()->create();
    $todoList = TodoList::factory()->for($user)->create();
    $otherList = TodoList::factory()->for($user)->create();
    $task = Task::factory()->for($otherList)->create();

    $this->json(
        $method,
        "/api/users/{$user->id}/todo-lists/{$todoList->id}/tasks/{$task->id}",
        $payload,
    )->assertNotFound();
})->with([
    'show' => ['GET', []],
    'update' => ['PATCH', ['title' => 'Чужая задача']],
    'delete' => ['DELETE', []],
]);

it('does not resolve a todo list through another profile for task endpoints', function () {
    $user = User::factory()->create();
    $todoList = TodoList::factory()->create();

    $this->getJson("/api/users/{$user->id}/todo-lists/{$todoList->id}/tasks")
        ->assertNotFound();
});
