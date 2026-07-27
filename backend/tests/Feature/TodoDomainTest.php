<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Admin;
use App\Models\Tag;
use App\Models\Task;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

it('connects users, lists, tasks and tags', function () {
    $user = User::factory()->create();
    $todoList = TodoList::factory()->for($user)->create();
    $task = Task::factory()->for($todoList)->create();
    $tag = Tag::factory()->for($user)->create();

    $task->tags()->attach($tag);

    expect($user->todoLists->first()->is($todoList))->toBeTrue()
        ->and($todoList->tasks->first()->is($task))->toBeTrue()
        ->and($task->tags->first()->is($tag))->toBeTrue()
        ->and($tag->tasks->first()->is($task))->toBeTrue();
});

it('casts task status and priority to enums', function () {
    $task = Task::factory()->create([
        'status' => TaskStatus::InProgress,
        'priority' => TaskPriority::High,
    ]);

    expect($task->status->is(TaskStatus::InProgress))->toBeTrue()
        ->and($task->priority->is(TaskPriority::High))->toBeTrue();
});

it('keeps completed_at consistent with task status', function () {
    $task = Task::factory()->create();

    $task->changeStatus(TaskStatus::Done);

    expect($task->fresh()->status->is(TaskStatus::Done))->toBeTrue()
        ->and($task->fresh()->completed_at)->not->toBeNull();

    $task->changeStatus(TaskStatus::InProgress);

    expect($task->fresh()->status->is(TaskStatus::InProgress))->toBeTrue()
        ->and($task->fresh()->completed_at)->toBeNull();
});

it('identifies only unfinished past-due tasks as overdue', function () {
    $overdueTask = Task::factory()->overdue()->create();
    $completedTask = Task::factory()->done()->create([
        'due_at' => now()->subDay(),
    ]);

    expect($overdueTask->isOverdue())->toBeTrue()
        ->and($completedTask->isOverdue())->toBeFalse();
});

it('keeps block and archive timestamps consistent', function () {
    $user = User::factory()->create();
    $todoList = TodoList::factory()->for($user)->create();

    $user->block();
    $todoList->archive();

    expect($user->fresh()->isBlocked())->toBeTrue()
        ->and($user->fresh()->blocked_at)->not->toBeNull()
        ->and($todoList->fresh()->isArchived())->toBeTrue()
        ->and($todoList->fresh()->archived_at)->not->toBeNull();

    $user->unblock();
    $todoList->restore();

    expect($user->fresh()->isBlocked())->toBeFalse()
        ->and($user->fresh()->blocked_at)->toBeNull()
        ->and($todoList->fresh()->isArchived())->toBeFalse()
        ->and($todoList->fresh()->archived_at)->toBeNull();
});

it('enforces owner, blocked-user and archived-list policies', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $todoList = TodoList::factory()->for($owner)->create();
    $task = Task::factory()->for($todoList)->create();

    expect(Gate::forUser($owner)->allows('update', $task))->toBeTrue()
        ->and(Gate::forUser($otherUser)->allows('update', $task))->toBeFalse();

    $owner->block();

    expect(Gate::forUser($owner)->allows('update', $task))->toBeFalse();

    $owner->unblock();
    $todoList->archive();

    expect(Gate::forUser($owner)->allows('update', $task))->toBeFalse();

    $admin = Admin::factory()->create();

    expect(Gate::forUser($admin)->allows('update', $task))->toBeTrue();
});
