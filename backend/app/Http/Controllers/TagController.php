<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

final class TagController extends Controller
{
    public function index(User $user): AnonymousResourceCollection
    {
        Gate::forUser($user)->authorize('viewAny', Tag::class);

        $tags = $user->tags()
            ->orderBy('normalized_name')
            ->orderBy('id')
            ->get();

        return TagResource::collection($tags);
    }

    public function store(StoreTagRequest $request, User $user): JsonResponse
    {
        try {
            $tag = $user->tags()->create([
                ...$request->validated(),
                'normalized_name' => $request->normalizedName(),
            ]);
        } catch (QueryException $exception) {
            $this->throwTagNameConflict($exception);
        }

        return (new TagResource($tag))
            ->response()
            ->setStatusCode(HttpResponse::HTTP_CREATED);
    }

    public function show(User $user, Tag $tag): TagResource
    {
        Gate::forUser($user)->authorize('view', $tag);

        return new TagResource($tag);
    }

    public function update(
        UpdateTagRequest $request,
        User $user,
        Tag $tag,
    ): TagResource {
        try {
            $tag->fill([
                ...$request->validated(),
                'normalized_name' => $request->normalizedName(),
            ])->saveOrFail();
        } catch (QueryException $exception) {
            $this->throwTagNameConflict($exception);
        }

        return new TagResource($tag);
    }

    public function destroy(User $user, Tag $tag): Response
    {
        Gate::forUser($user)->authorize('delete', $tag);

        $tag->deleteOrFail();

        return response()->noContent();
    }

    private function throwTagNameConflict(QueryException $exception): never
    {
        if (! in_array((string) $exception->getCode(), ['23000', '23505'], true)) {
            throw $exception;
        }

        throw ValidationException::withMessages([
            'name' => ['A tag with this name already exists.'],
        ]);
    }
}
