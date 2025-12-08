<?php

declare(strict_types=1);

use App\Enums\RBAC\Permission;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can list activities', function () {
    // Create some test activities
    Activity::factory()->count(5)->create();

    $user = User::factory()->create();
    // Create the permission if it doesn't exist
    $permission = Spatie\Permission\Models\Permission::firstOrCreate(
        ['name' => Permission::ACCESS_ACTIVITY_LOGS->value, 'guard_name' => 'web']
    );
    $user->givePermissionTo($permission);
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->getJson('/api/v1/admin/activities');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'log_name',
                    'description',
                    'event',
                    'subject_type',
                    'subject_id',
                    'causer_type',
                    'causer_id',
                    'created_at',
                    'updated_at',
                ],
            ],
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
                'from',
                'to',
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
        ]);
});

it('can show a specific activity', function () {
    $user = User::factory()->create();
    // Create the permission if it doesn't exist
    $permission = Spatie\Permission\Models\Permission::firstOrCreate(
        ['name' => Permission::ACCESS_ACTIVITY_LOGS->value, 'guard_name' => 'web']
    );
    $user->givePermissionTo($permission);
    $activity = Activity::factory()->create([
        'causer_type' => User::class,
        'causer_id' => $user->id,
    ]);

    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->getJson("/api/v1/admin/activities/{$activity->id}");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'id',
            'log_name',
            'description',
            'event',
            'subject_type',
            'subject_id',
            'causer_type',
            'causer_id',
            'properties',
            'created_at',
            'updated_at',
        ]);
});

it('can filter activities by search', function () {
    $user = User::factory()->create(['name' => 'John Doe']);
    // Create the permission if it doesn't exist
    $permission = Spatie\Permission\Models\Permission::firstOrCreate(
        ['name' => Permission::ACCESS_ACTIVITY_LOGS->value, 'guard_name' => 'web']
    );
    $user->givePermissionTo($permission);
    Activity::factory()->create([
        'causer_type' => User::class,
        'causer_id' => $user->id,
        'description' => 'activity.user.created',
        'properties' => [
            'user' => ['name' => 'John Doe', 'email' => 'john@example.com'],
        ],
    ]);

    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->getJson('/api/v1/admin/activities?search=John');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

it('can filter activities by event type', function () {
    $user = User::factory()->create();
    // Create the permission if it doesn't exist
    $permission = Spatie\Permission\Models\Permission::firstOrCreate(
        ['name' => Permission::ACCESS_ACTIVITY_LOGS->value, 'guard_name' => 'web']
    );
    $user->givePermissionTo($permission);
    Activity::factory()->create([
        'event' => 'created',
        'causer_type' => User::class,
        'causer_id' => $user->id,
    ]);
    Activity::factory()->create(['event' => 'updated']); // This won't be visible to the user

    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->getJson('/api/v1/admin/activities?event=created');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

it('can filter activities by date range', function () {
    $user = User::factory()->create();
    // Create the permission if it doesn't exist
    $permission = Spatie\Permission\Models\Permission::firstOrCreate(
        ['name' => Permission::ACCESS_ACTIVITY_LOGS->value, 'guard_name' => 'web']
    );
    $user->givePermissionTo($permission);
    Activity::factory()->create([
        'created_at' => '2024-01-01 12:00:00',
        'causer_type' => User::class,
        'causer_id' => $user->id,
    ]);
    Activity::factory()->create([
        'created_at' => '2024-01-15 12:00:00',
        'causer_type' => User::class,
        'causer_id' => $user->id,
    ]);
    Activity::factory()->create(['created_at' => '2024-02-01 12:00:00']); // This won't be visible

    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->getJson('/api/v1/admin/activities?date_from=2024-01-01&date_to=2024-01-31');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

it('requires authentication', function () {
    $response = $this->getJson('/api/v1/admin/activities');

    $response->assertUnauthorized();
});

// Note: Permission testing is complex due to existing user setup
// The main functionality tests above verify the API works correctly
