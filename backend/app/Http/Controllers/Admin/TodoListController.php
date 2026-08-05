<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TodoListResource;
use App\Models\TodoList;

final class TodoListController extends Controller
{
    public function archive(TodoList $todoList): TodoListResource
    {
        $todoList->archive();

        return new TodoListResource($todoList->refresh());
    }

    public function restore(TodoList $todoList): TodoListResource
    {
        $todoList->restore();

        return new TodoListResource($todoList->refresh());
    }
}
