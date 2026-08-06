<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListTasksRequest;
use App\Http\Requests\Admin\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\TodoList;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

final class TaskController extends Controller
{
    public function index(ListTasksRequest $request, TodoList $todoList): AnonymousResourceCollection
    {
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
            $query
                ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END ASC')
                ->orderBy('due_at', $direction);
        } elseif ($sort === 'priority') {
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

    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        DB::transaction(function () use ($request, $task): void {
            $attributes = $request->validated();
            unset($attributes['status'], $attributes['priority'], $attributes['tag_ids']);

            //часто повторяется. Выносить отдельно?
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

    public function destroy(Task $task): Response
    {
        $task->deleteOrFail();

        return response()->noContent();
    }
}
