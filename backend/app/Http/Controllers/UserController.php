<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

final class UserController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $users = User::query()
            ->whereNull('blocked_at')
            ->withCount(User::resourceCountRelations())
            ->orderBy('normalized_name')
            ->orderBy('id')
            ->get();

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $user = User::query()->create([
                ...$request->validated(),
                'normalized_name' => $request->normalizedName(),
            ]);
        } catch (QueryException $exception) {
            if (! in_array((string) $exception->getCode(), ['23000', '23505'], true)) {
                throw $exception;
            }

            throw ValidationException::withMessages([
                'name' => ['A profile with this name already exists.'],
            ]);
        }

        $user->loadCount(User::resourceCountRelations());

        return (new UserResource($user))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(User $user): UserResource
    {
        $user->loadCount(User::resourceCountRelations());

        return new UserResource($user);
    }
}
