<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

final class DashboardController extends Controller
{
    private const RecentChangesLimit = 10;

    public function show(): JsonResponse
    {
        return response()->json([
            'data' => [
                'users_count' => User::query()->count(),
                'blocked_users_count' => User::query()->whereNotNull('blocked_at')->count(),
                'active_lists_count' => TodoList::query()->whereNull('archived_at')->count(),
                'archived_lists_count' => TodoList::query()->whereNotNull('archived_at')->count(),
                'tasks_count' => Task::query()->count(),
                'overdue_tasks_count' => Task::query()
                    ->whereNotNull('due_at')
                    ->where('due_at', '<', now())
                    ->where('status', '!=', TaskStatus::Done)
                    ->count(),
                'recent_changes' => $this->recentChanges(),
            ],
        ]);
    }

    /**
     * @return list<array{type: string, id: int, label: string, updated_at: string}>
     */
    private function recentChanges(): array
    {
        $limit = self::RecentChangesLimit;

        /** @var Collection<int, array{type: string, id: int, label: string, updated_at: string, sort_at: string}> $changes */
        $changes = collect()
            ->merge(
                User::query()
                    ->latest('updated_at')
                    ->limit($limit)
                    ->get(['id', 'name', 'updated_at'])
                    ->map(fn (User $user): array => [
                        'type' => 'user',
                        'id' => $user->id,
                        'label' => $user->name,
                        'updated_at' => $user->updated_at?->toISOString() ?? '',
                        'sort_at' => $user->updated_at?->toISOString() ?? '',
                    ]),
            )
            ->merge(
                TodoList::query()
                    ->latest('updated_at')
                    ->limit($limit)
                    ->get(['id', 'title', 'updated_at'])
                    ->map(fn (TodoList $todoList): array => [
                        'type' => 'todo_list',
                        'id' => $todoList->id,
                        'label' => $todoList->title,
                        'updated_at' => $todoList->updated_at?->toISOString() ?? '',
                        'sort_at' => $todoList->updated_at?->toISOString() ?? '',
                    ]),
            )
            ->merge(
                Task::query()
                    ->latest('updated_at')
                    ->limit($limit)
                    ->get(['id', 'title', 'updated_at'])
                    ->map(fn (Task $task): array => [
                        'type' => 'task',
                        'id' => $task->id,
                        'label' => $task->title,
                        'updated_at' => $task->updated_at?->toISOString() ?? '',
                        'sort_at' => $task->updated_at?->toISOString() ?? '',
                    ]),
            )
            ->sortByDesc('sort_at')
            ->take($limit)
            ->values()
            ->map(fn (array $change): array => [
                'type' => $change['type'],
                'id' => $change['id'],
                'label' => $change['label'],
                'updated_at' => $change['updated_at'],
            ]);

        return $changes->all();
    }
}
