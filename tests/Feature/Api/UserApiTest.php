<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can get current user info', function () {
    $user = User::factory()->create();

    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->getJson('/api/v1/me');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'user' => [
                'id',
                'name',
                'email',
                'status',
                'created_at',
            ],
            'token' => [
                'id',
                'name',
                'abilities',
                'created_at',
                'expires_at',
                'is_expired',
            ],
        ]);
});

it('can list users', function () {
    User::factory()->count(5)->create();

    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->getJson('/api/v1/users');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'status',
                ],
            ],
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);
});

it('can create a user', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'status' => 'active',
    ];

    $response = $this->withToken($token)->postJson('/api/v1/users', $userData);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'status',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);
});

it('can show a user', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $targetUser = User::factory()->create();

    $response = $this->withToken($token)->getJson("/api/v1/users/{$targetUser->id}");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'status',
            ],
        ]);
});

it('can update a user', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $targetUser = User::factory()->create();

    $updateData = [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ];

    $response = $this->withToken($token)->putJson("/api/v1/users/{$targetUser->id}", $updateData);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'status',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $targetUser->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);
});

it('can delete a user', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $targetUser = User::factory()->create();

    $response = $this->withToken($token)->deleteJson("/api/v1/users/{$targetUser->id}");

    $response->assertSuccessful()
        ->assertJson([
            'message' => 'User deleted successfully.',
        ]);

    $this->assertSoftDeleted($targetUser);
});

it('can restore a deleted user', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $targetUser = User::factory()->create();
    $targetUser->delete();

    $response = $this->withToken($token)->patchJson("/api/v1/users/{$targetUser->id}/restore");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'status',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $targetUser->id,
        'deleted_at' => null,
    ]);
});

it('can permanently delete a user', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $targetUser = User::factory()->create();
    $targetUser->delete();

    $response = $this->withToken($token)->deleteJson("/api/v1/users/{$targetUser->id}/force-delete");

    $response->assertSuccessful()
        ->assertJson([
            'message' => 'User permanently deleted.',
        ]);

    $this->assertDatabaseMissing('users', [
        'id' => $targetUser->id,
    ]);
});
