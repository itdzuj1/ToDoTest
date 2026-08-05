<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final class DashboardController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'data' => [
                'users_count' => User::query()->count(),
                'active_lists_count' => TodoList::query()->whereNull('archived_at')->count(),
                'archived_lists_count' => TodoList::query()->whereNotNull('archived_at')->count(),
                'tasks_count' => Task::query()->count(),
                'overdue_tasks_count' => Task::query()
                    ->whereNotNull('due_at')
                    ->where('due_at', '<', now())
                    ->where('status', '!=', TaskStatus::Done)
                    ->count(),
            ],
        ]);
    }
}
