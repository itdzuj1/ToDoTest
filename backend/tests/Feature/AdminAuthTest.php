<?php

use App\Models\Admin;
use Database\Seeders\AdminSeeder;

it('issues a CSRF cookie for the admin client', function () {
    $this->get('/api/admin/csrf-cookie')
        ->assertNoContent()
        ->assertCookie('XSRF-TOKEN');
});

it('seeds an admin from explicit configuration', function () {
    config()->set('admin.seed', [
        'name' => 'Test Admin',
        'email' => 'ADMIN@EXAMPLE.TEST',
        'password' => 'configured-password',
    ]);

    $this->seed(AdminSeeder::class);

    $admin = Admin::query()->sole();

    expect($admin->name)->toBe('Test Admin')
        ->and($admin->email)->toBe('admin@example.test');
});

it('authenticates an admin and returns the current resource', function () {
    $admin = Admin::factory()->create([
        'email' => 'admin@example.test',
        'password' => 'correct-password',
    ]);

    $this->postJson('/api/admin/login', [
        'email' => ' ADMIN@EXAMPLE.TEST ',
        'password' => 'correct-password',
    ])
        ->assertOk()
        ->assertJsonPath('data.id', $admin->id)
        ->assertJsonPath('data.email', 'admin@example.test');

    $this->assertAuthenticatedAs($admin, 'admin');

    $this->getJson('/api/admin/session')
        ->assertOk()
        ->assertJsonPath('data.id', $admin->id);
});

it('rejects invalid admin credentials with field errors', function () {
    Admin::factory()->create([
        'email' => 'admin@example.test',
        'password' => 'correct-password',
    ]);

    $this->postJson('/api/admin/login', [
        'email' => 'admin@example.test',
        'password' => 'wrong-password',
    ])
        ->assertUnprocessable()
        ->assertExactJson([
            'errors' => [
                'email' => ['The provided credentials are invalid.'],
            ],
        ]);

    $this->assertGuest('admin');
});

it('validates malformed admin login input without an internal error', function () {
    $this->postJson('/api/admin/login', [
        'email' => ['not-a-string'],
        'password' => 'password',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('requires an admin session for protected endpoints', function () {
    $this->getJson('/api/admin/session')
        ->assertUnauthorized()
        ->assertExactJson([
            'code' => 'unauthenticated',
            'message' => 'Authentication is required.',
        ]);
});

it('logs the admin out and invalidates the session', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')
        ->deleteJson('/api/admin/session')
        ->assertNoContent();

    $this->assertGuest('admin');
});

it('limits repeated admin login attempts', function () {
    Admin::factory()->create([
        'email' => 'admin@example.test',
        'password' => 'correct-password',
    ]);

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/admin/login', [
            'email' => 'admin@example.test',
            'password' => 'wrong-password',
        ])->assertUnprocessable();
    }

    $this->postJson('/api/admin/login', [
        'email' => 'admin@example.test',
        'password' => 'wrong-password',
    ])
        ->assertTooManyRequests()
        ->assertExactJson([
            'code' => 'too_many_requests',
            'message' => 'Too many requests.',
        ]);
});
