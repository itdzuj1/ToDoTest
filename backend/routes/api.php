<?php

use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TodoListController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'name' => 'ToDoTest API',
]));

Route::get('users', [UserController::class, 'index'])->name('users.index');
Route::post('users', [UserController::class, 'store'])->name('users.store');
Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');

Route::prefix('users/{user}/tags')
    ->name('users.tags.')
    ->scopeBindings()
    ->group(function (): void {
        Route::get('/', [TagController::class, 'index'])->name('index');
        Route::post('/', [TagController::class, 'store'])->name('store');
        Route::get('{tag}', [TagController::class, 'show'])->name('show');
        Route::patch('{tag}', [TagController::class, 'update'])->name('update');
        Route::delete('{tag}', [TagController::class, 'destroy'])->name('destroy');
    });

Route::prefix('users/{user}/todo-lists')
    ->name('users.todo-lists.')
    ->scopeBindings()
    ->group(function (): void {
        Route::get('/', [TodoListController::class, 'index'])->name('index');
        Route::post('/', [TodoListController::class, 'store'])->name('store');
        Route::get('{todoList}', [TodoListController::class, 'show'])->name('show');
        Route::patch('{todoList}', [TodoListController::class, 'update'])->name('update');
        Route::patch('{todoList}/archive', [TodoListController::class, 'archive'])->name('archive');
        Route::patch('{todoList}/restore', [TodoListController::class, 'restore'])->name('restore');

        Route::get('{todoList}/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::post('{todoList}/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::get('{todoList}/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
        Route::patch('{todoList}/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('{todoList}/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    });
