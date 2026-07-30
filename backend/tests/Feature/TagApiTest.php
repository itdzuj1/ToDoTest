<?php

use App\Models\Tag;
use App\Models\Task;
use App\Models\User;

it('lists profile tags in normalized name order', function () {
    $user = User::factory()->create();
    $secondTag = Tag::factory()->for($user)->create([
        'name' => 'Работа',
        'normalized_name' => 'работа',
    ]);
    $firstTag = Tag::factory()->for($user)->create([
        'name' => 'Дом',
        'normalized_name' => 'дом',
    ]);
    Tag::factory()->create();

    $this->getJson("/api/users/{$user->id}/tags")
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $firstTag->id)
        ->assertJsonPath('data.1.id', $secondTag->id);
});

it('creates, renames and deletes a tag', function () {
    $user = User::factory()->create();

    $createdResponse = $this->postJson("/api/users/{$user->id}/tags", [
        'name' => '  Важные   дела  ',
        'user_id' => User::factory()->create()->id,
        'normalized_name' => 'injected',
    ])
        ->assertCreated()
        ->assertJsonPath('data.user_id', $user->id)
        ->assertJsonPath('data.name', 'Важные дела');

    $tagId = $createdResponse->json('data.id');

    $this->assertDatabaseHas('tags', [
        'id' => $tagId,
        'user_id' => $user->id,
        'name' => 'Важные дела',
        'normalized_name' => 'важные дела',
    ]);

    $this->patchJson("/api/users/{$user->id}/tags/{$tagId}", [
        'name' => '  Срочные   дела ',
        'user_id' => User::factory()->create()->id,
        'normalized_name' => 'injected',
    ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Срочные дела');

    $this->deleteJson("/api/users/{$user->id}/tags/{$tagId}")
        ->assertNoContent();

    $this->assertDatabaseMissing('tags', ['id' => $tagId]);
});

it('returns a tag owned by the selected profile', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->for($user)->create();

    $this->getJson("/api/users/{$user->id}/tags/{$tag->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $tag->id)
        ->assertJsonPath('data.user_id', $user->id);
});

it('validates a tag name when creating', function (array $payload) {
    $user = User::factory()->create();

    $this->postJson("/api/users/{$user->id}/tags", $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
})->with([
    'missing name' => [[]],
    'blank name' => [['name' => '   ']],
    'non-string name' => [['name' => ['Дом']]],
    'name over 50 characters' => [['name' => str_repeat('a', 51)]],
]);

it('validates a tag name when renaming', function (array $payload) {
    $user = User::factory()->create();
    $tag = Tag::factory()->for($user)->create();

    $this->patchJson("/api/users/{$user->id}/tags/{$tag->id}", $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
})->with([
    'missing name' => [[]],
    'blank name' => [['name' => '   ']],
    'non-string name' => [['name' => ['Дом']]],
    'name over 50 characters' => [['name' => str_repeat('a', 51)]],
]);

it('rejects duplicate tag names within one profile', function () {
    $user = User::factory()->create();
    $existingTag = Tag::factory()->for($user)->create([
        'name' => 'Важное',
        'normalized_name' => 'важное',
    ]);
    $otherTag = Tag::factory()->for($user)->create([
        'name' => 'Дом',
        'normalized_name' => 'дом',
    ]);

    $this->postJson("/api/users/{$user->id}/tags", [
        'name' => '  ВАЖНОЕ ',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');

    $this->patchJson("/api/users/{$user->id}/tags/{$otherTag->id}", [
        'name' => '  ВАЖНОЕ ',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');

    $this->patchJson("/api/users/{$user->id}/tags/{$existingTag->id}", [
        'name' => ' Важное ',
    ])->assertOk();
});

it('keeps blocked profiles tags read-only', function () {
    $user = User::factory()->blocked()->create();
    $tag = Tag::factory()->for($user)->create();

    $this->getJson("/api/users/{$user->id}/tags")
        ->assertOk();

    $this->getJson("/api/users/{$user->id}/tags/{$tag->id}")
        ->assertOk();

    $this->postJson("/api/users/{$user->id}/tags", ['name' => 'Новый'])
        ->assertForbidden();

    $this->patchJson("/api/users/{$user->id}/tags/{$tag->id}", ['name' => 'Новое имя'])
        ->assertForbidden();

    $this->deleteJson("/api/users/{$user->id}/tags/{$tag->id}")
        ->assertForbidden();
});

it('does not resolve another profiles tag through a nested url', function (
    string $method,
    array $payload,
) {
    $user = User::factory()->create();
    $tag = Tag::factory()->create();

    $this->json(
        $method,
        "/api/users/{$user->id}/tags/{$tag->id}",
        $payload,
    )->assertNotFound();
})->with([
    'show' => ['GET', []],
    'rename' => ['PATCH', ['name' => 'Чужой тег']],
    'delete' => ['DELETE', []],
]);

it('deletes tag assignments without deleting tasks', function () {
    $tag = Tag::factory()->create();
    $task = Task::factory()->create();
    $task->tags()->attach($tag);

    $this->deleteJson("/api/users/{$tag->user_id}/tags/{$tag->id}")
        ->assertNoContent();

    $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    $this->assertDatabaseMissing('task_tag', [
        'task_id' => $task->id,
        'tag_id' => $tag->id,
    ]);
});
