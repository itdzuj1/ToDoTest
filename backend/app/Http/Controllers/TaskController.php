<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

final class TaskController extends Controller
{
    public function index(User $user, TodoList $todoList): AnonymousResourceCollection
    {
        Gate::forUser($user)->authorize('viewAny', Task::class);

        $tasks = $todoList->tasks()
            ->with('tags')
            ->latest('id')
            ->get();

        return TaskResource::collection($tasks);
    }

    public function store(
        StoreTaskRequest $request,
        User $user,
        TodoList $todoList,
    ): JsonResponse {
        $task = DB::transaction(function () use ($request, $todoList): Task {
            $attributes = $request->validated();
            unset($attributes['status'], $attributes['priority']);

            if (array_key_exists('due_at', $attributes)) {
                $attributes['due_at'] = $request->dueAt();
            }

            $attributes['priority'] = $request->priority() ?? TaskPriority::Medium;
            $task = $todoList->tasks()->create($attributes);
            $task->changeStatus($request->status() ?? TaskStatus::Todo);

            return $task;
        });

        return (new TaskResource($task->load('tags')))
            ->response()
            ->setStatusCode(HttpResponse::HTTP_CREATED);
    }

    public function show(User $user, TodoList $todoList, Task $task): TaskResource
    {
        Gate::forUser($user)->authorize('view', $task);

        return new TaskResource($task->load('tags'));
    }

    public function update(
        UpdateTaskRequest $request,
        User $user,
        TodoList $todoList,
        Task $task,
    ): TaskResource {
        DB::transaction(function () use ($request, $task): void {
            $attributes = $request->validated();
            unset($attributes['status'], $attributes['priority']);

            if (array_key_exists('due_at', $attributes)) {
                $attributes['due_at'] = $request->dueAt();
            }

            $priority = $request->priority();

            if ($priority !== null) {
                $attributes['priority'] = $priority;
            }

            $task->fill($attributes);
            $status = $request->status();

            if ($status !== null) {
                $task->changeStatus($status);

                return;
            }

            $task->saveOrFail();
        });

        return new TaskResource($task->load('tags'));
    }

    public function destroy(User $user, TodoList $todoList, Task $task): Response
    {
        Gate::forUser($user)->authorize('delete', $task);

        $task->deleteOrFail();

        return response()->noContent();
    }
}
