<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\ListTasksRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

final class TaskController extends Controller
{
    public function index(
        ListTasksRequest $request,
        User $user,
        TodoList $todoList,
    ): AnonymousResourceCollection {
        $query = $todoList->tasks()->with('tags');
        $search = $request->search();

        if ($search !== null) {
            $query->where(fn (Builder $query) => $query
                ->whereLike('title', "%{$search}%")
                ->orWhereLike('description', "%{$search}%"));
        }

        $status = $request->status();

        if ($status !== null) {
            $query->where('status', $status);
        }

        $priority = $request->priority();

        if ($priority !== null) {
            $query->where('priority', $priority);
        }

        $tagId = $request->tagId();

        if ($tagId !== null) {
            $query->whereRelation('tags', 'tags.id', $tagId);
        }

        $due = $request->due();
        $now = now();

        if ($due === 'overdue') {
            $query
                ->whereNotNull('due_at')
                ->where('due_at', '<', $now)
                ->where('status', '!=', TaskStatus::Done);
        } elseif ($due === 'today') {
            $query->whereBetween('due_at', [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
            ]);
        } elseif ($due === 'upcoming') {
            $query->where('due_at', '>', $now->copy()->endOfDay());
        } elseif ($due === 'none') {
            $query->whereNull('due_at');
        }

        $direction = $request->direction();
        $sort = $request->sort();

        if ($sort === 'due_at') {
            // можно вынести из index в private-методы
            $query
                ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END ASC')
                ->orderBy('due_at', $direction);
        } elseif ($sort === 'priority') {
            // можно вынести из index в private-методы
            $query->orderByRaw($direction === 'asc'
                ? "CASE priority WHEN 'low' THEN 1 WHEN 'medium' THEN 2 WHEN 'high' THEN 3 ELSE 4 END ASC"
                : "CASE priority WHEN 'low' THEN 1 WHEN 'medium' THEN 2 WHEN 'high' THEN 3 ELSE 0 END DESC");
        } else {
            $query->orderBy('created_at', $direction);
        }

        $tasks = $query
            ->orderBy('id', $direction)
            ->paginate($request->perPage())
            ->withQueryString();

        return TaskResource::collection($tasks);
    }

    public function store(
        StoreTaskRequest $request,
        User $user,
        TodoList $todoList,
    ): JsonResponse {
        $task = DB::transaction(function () use ($request, $todoList): Task {
            $attributes = $request->validated();
            unset($attributes['status'], $attributes['priority'], $attributes['tag_ids']);

            if (array_key_exists('due_at', $attributes)) {
                $attributes['due_at'] = $request->dueAt();
            }

            $attributes['priority'] = $request->priority() ?? TaskPriority::Medium;
            $task = $todoList->tasks()->create($attributes);
            $task->changeStatus($request->status() ?? TaskStatus::Todo);

            $tagIds = $request->tagIds();

            if ($tagIds !== null) {
                $task->tags()->sync($tagIds);
            }

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
            unset($attributes['status'], $attributes['priority'], $attributes['tag_ids']);

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
            } else {
                $task->saveOrFail();
            }

            $tagIds = $request->tagIds();

            if ($tagIds !== null) {
                $task->tags()->sync($tagIds);
            }
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
