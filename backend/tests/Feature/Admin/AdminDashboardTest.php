<?php

use App\Models\Admin;
use App\Models\Task;
use App\Models\TodoList;
use App\Models\User;

it('requires an admin session for the dashboard', function () {
    $this->getJson('/api/admin/dashboard')
        ->assertUnauthorized()
        ->assertExactJson([
            'code' => 'unauthenticated',
            'message' => 'Authentication is required.',
        ]);
});

it('returns aggregate dashboard counts for the admin', function () {
    $this->travelTo('2026-08-05 12:00:00');

    $activeUser = User::factory()->create(['name' => 'Active User']);
    User::factory()->create();
    User::factory()->blocked()->create();

    $activeList = TodoList::factory()->for($activeUser)->create(['title' => 'Active List']);
    TodoList::factory()->for($activeUser)->create();
    TodoList::factory()->for($activeUser)->archived()->create();

    Task::factory()->for($activeList)->count(2)->create([
        'due_at' => now()->addDay(),
    ]);
    Task::factory()->for($activeList)->overdue()->create();
    $latestTask = Task::factory()->for($activeList)->done()->create([
        'title' => 'Latest Task',
        'due_at' => now()->subDay(),
    ]);
    $latestTask->forceFill(['updated_at' => now()->addMinute()])->saveOrFail();

    $response = $this->actingAs(Admin::factory()->create(), 'admin')
        ->getJson('/api/admin/dashboard')
        ->assertOk()
        ->assertJsonPath('data.users_count', 3)
        ->assertJsonPath('data.blocked_users_count', 1)
        ->assertJsonPath('data.active_lists_count', 2)
        ->assertJsonPath('data.archived_lists_count', 1)
        ->assertJsonPath('data.tasks_count', 4)
        ->assertJsonPath('data.overdue_tasks_count', 1)
        ->assertJsonCount(10, 'data.recent_changes')
        ->assertJsonPath('data.recent_changes.0.type', 'task')
        ->assertJsonPath('data.recent_changes.0.id', $latestTask->id)
        ->assertJsonPath('data.recent_changes.0.label', 'Latest Task');

    expect($response->json('data.recent_changes.0.updated_at'))->toBeString();
});
