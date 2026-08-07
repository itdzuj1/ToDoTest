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
        $todoList->loadCount(TodoList::resourceCountRelations());

        return new TodoListResource($todoList);
    }

    public function restore(TodoList $todoList): TodoListResource
    {
        $todoList->restore();
        $todoList->loadCount(TodoList::resourceCountRelations());

        return new TodoListResource($todoList);
    }
}
