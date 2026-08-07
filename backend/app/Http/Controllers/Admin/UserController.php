<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListUsersRequest;
use App\Http\Resources\UserResource;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class UserController extends Controller
{
    public function index(ListUsersRequest $request): AnonymousResourceCollection
    {
        $query = User::query();
        $search = $request->search();

        if ($search !== null) {
            $normalizedSearch = mb_strtolower($search);

            $query->where(fn (Builder $query) => $query
                ->whereLike('name', "%{$search}%")
                ->orWhereLike('normalized_name', "%{$normalizedSearch}%"));
        }

        $filter = $request->filter();

        if ($filter === 'active') {
            $query->whereNull('blocked_at');
        } elseif ($filter === 'blocked') {
            $query->whereNotNull('blocked_at');
        }

        $users = $query
            ->withCount(User::resourceCountRelations())
            ->orderBy('normalized_name')
            ->orderBy('id')
            ->paginate($request->perPage())
            ->withQueryString();

        return UserResource::collection($users);
    }

    public function show(User $user): UserResource
    {
        $user->loadCount(User::resourceCountRelations());
        $user->load([
            'todoLists' => fn ($query) => $query
                ->orderBy('id')
                ->withCount(TodoList::resourceCountRelations()),
        ]);

        return new UserResource($user);
    }

    public function block(User $user): UserResource
    {
        $user->block();
        $user->loadCount(User::resourceCountRelations());

        return new UserResource($user);
    }

    public function unblock(User $user): UserResource
    {
        $user->unblock();
        $user->loadCount(User::resourceCountRelations());

        return new UserResource($user);
    }
}
