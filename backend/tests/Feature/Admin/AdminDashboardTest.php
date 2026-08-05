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

    $activeUser = User::factory()->create();
    User::factory()->create();
    User::factory()->blocked()->create();

    $activeList = TodoList::factory()->for($activeUser)->create();
    TodoList::factory()->for($activeUser)->create();
    TodoList::factory()->for($activeUser)->archived()->create();

    Task::factory()->for($activeList)->count(2)->create([
        'due_at' => now()->addDay(),
    ]);
    Task::factory()->for($activeList)->overdue()->create();
    Task::factory()->for($activeList)->done()->create([
        'due_at' => now()->subDay(),
    ]);

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->getJson('/api/admin/dashboard')
        ->assertOk()
        ->assertExactJson([
            'data' => [
                'users_count' => 3,
                'active_lists_count' => 2,
                'archived_lists_count' => 1,
                'tasks_count' => 4,
                'overdue_tasks_count' => 1,
            ],
        ]);
});
