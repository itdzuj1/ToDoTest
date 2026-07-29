<?php

use App\Http\Controllers\TodoListController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'name' => 'ToDoTest API',
]));

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
    });
