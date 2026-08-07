<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListTodoListsRequest;
use App\Http\Requests\StoreTodoListRequest;
use App\Http\Requests\UpdateTodoListRequest;
use App\Http\Resources\TodoListResource;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class TodoListController extends Controller
{
    public function index(ListTodoListsRequest $request, User $user): AnonymousResourceCollection
    {
        $query = $user->todoLists()->latest();
        $filter = $request->filter();

        if ($filter === 'active') {
            $query->whereNull('archived_at');
        } elseif ($filter === 'archived') {
            $query->whereNotNull('archived_at');
        }

        return TodoListResource::collection(
            $query->withCount(TodoList::resourceCountRelations())->get(),
        );
    }

    public function store(StoreTodoListRequest $request, User $user): JsonResponse
    {
        $todoList = $user->todoLists()->create($request->validated());
        $todoList->loadCount(TodoList::resourceCountRelations());

        return (new TodoListResource($todoList))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(User $user, TodoList $todoList): TodoListResource
    {
        Gate::forUser($user)->authorize('view', $todoList);

        $todoList->loadCount(TodoList::resourceCountRelations());

        return new TodoListResource($todoList);
    }

    public function update(
        UpdateTodoListRequest $request,
        User $user,
        TodoList $todoList,
    ): TodoListResource {
        $todoList->fill($request->validated())->saveOrFail();
        $todoList->loadCount(TodoList::resourceCountRelations());

        return new TodoListResource($todoList);
    }

    public function archive(User $user, TodoList $todoList): TodoListResource
    {
        Gate::forUser($user)->authorize('archive', $todoList);

        $todoList->archive();
        $todoList->loadCount(TodoList::resourceCountRelations());

        return new TodoListResource($todoList);
    }

    public function restore(User $user, TodoList $todoList): TodoListResource
    {
        Gate::forUser($user)->authorize('restore', $todoList);

        $todoList->restore();
        $todoList->loadCount(TodoList::resourceCountRelations());

        return new TodoListResource($todoList);
    }
}
