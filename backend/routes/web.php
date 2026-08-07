<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TaskController as AdminTaskController;
use App\Http\Controllers\Admin\TodoListController as AdminTodoListController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'name' => 'ToDoTest API',
]));

Route::prefix('api/admin')->name('api.admin.')->group(function (): void {
    Route::get('csrf-cookie', [AuthController::class, 'csrfCookie'])->name('csrf-cookie');
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:admin-login')
        ->name('login');

    Route::middleware('auth:admin')->group(function (): void {
        Route::get('session', [AuthController::class, 'show'])->name('session.show');
        Route::delete('session', [AuthController::class, 'logout'])->name('session.destroy');
        Route::get('dashboard', [DashboardController::class, 'show'])->name('dashboard.show');
        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::patch('users/{user}/block', [AdminUserController::class, 'block'])->name('users.block');
        Route::patch('users/{user}/unblock', [AdminUserController::class, 'unblock'])->name('users.unblock');
        Route::patch('todo-lists/{todoList}/archive', [AdminTodoListController::class, 'archive'])
            ->name('todo-lists.archive');
        Route::patch('todo-lists/{todoList}/restore', [AdminTodoListController::class, 'restore'])
            ->name('todo-lists.restore');
        Route::get('todo-lists/{todoList}/tasks', [AdminTaskController::class, 'index'])
            ->name('todo-lists.tasks.index');
        Route::get('tasks', [AdminTaskController::class, 'indexAll'])->name('tasks.index');
        Route::patch('tasks/{task}', [AdminTaskController::class, 'update'])->name('tasks.update');
        Route::delete('tasks/{task}', [AdminTaskController::class, 'destroy'])->name('tasks.destroy');
    });
});
