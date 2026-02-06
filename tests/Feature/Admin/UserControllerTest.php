<?php

declare(strict_types=1);

use App\Enums\ActivityStatus;
use App\Enums\RBAC\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create required permissions in the database
    foreach (Permission::cases() as $permission) {
        if ($permission->shouldSync()) {
            Spatie\Permission\Models\Permission::create(['name' => $permission->value]);
        }
    }

    $this->user = User::factory()->create();
    $this->user->givePermissionTo(Permission::AccessControlPanel);
});

it('can list users', function () {
    User::factory()->count(5)->create();

    $response = $this->actingAs($this->user)->get('/admin/users');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/users/index')
            ->has('users', 6) // 5 created + 1 authenticated user
        );
});

it('can filter users by search', function () {
    User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

    $response = $this->actingAs($this->user)->get('/admin/users?search=John');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/users/index')
            ->has('users', 1)
            ->where('users.0.name', 'John Doe')
        );
});

it('can filter users by status', function () {
    User::factory()->create(['status' => ActivityStatus::ACTIVE]);
    User::factory()->create(['status' => ActivityStatus::INACTIVE]);

    $response = $this->actingAs($this->user)->get('/admin/users?status=active');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/users/index')
            ->has('users')
        );
});

it('can show create user page', function () {
    $response = $this->actingAs($this->user)->get('/admin/users/create');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/users/create')
            ->has('roles')
            ->has('statuses')
        );
});

it('can create a user', function () {
    $role = Role::factory()->create(['name' => 'user']);

    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'status' => ActivityStatus::ACTIVE->value,
        'role' => $role->name,
    ];

    $response = $this->actingAs($this->user)->post('/admin/users', $userData);

    $response->assertRedirect('/admin/users/'.User::where('email', 'test@example.com')->first()->id);

    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);
});

it('can show a user', function () {
    $targetUser = User::factory()->create();

    $response = $this->actingAs($this->user)->get("/admin/users/{$targetUser->id}");

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/users/show')
            ->where('user.id', $targetUser->id)
            ->has('activities')
        );
});

it('can show edit user page', function () {
    $targetUser = User::factory()->create();

    $response = $this->actingAs($this->user)->get("/admin/users/{$targetUser->id}/edit");

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/users/edit')
            ->where('user.id', $targetUser->id)
            ->has('roles')
            ->has('statuses')
        );
});

it('can update a user', function () {
    $targetUser = User::factory()->create();

    $updateData = [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ];

    $response = $this->actingAs($this->user)->put("/admin/users/{$targetUser->id}", $updateData);

    $response->assertRedirect("/admin/users/{$targetUser->id}");

    $this->assertDatabaseHas('users', [
        'id' => $targetUser->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);
});

it('can delete a user', function () {
    $targetUser = User::factory()->create();

    $response = $this->actingAs($this->user)->delete("/admin/users/{$targetUser->id}");

    $response->assertRedirect('/admin/users');

    $this->assertSoftDeleted($targetUser);
});

it('can restore a deleted user', function () {
    $targetUser = User::factory()->create();
    $targetUser->delete();

    $response = $this->actingAs($this->user)->post("/admin/users/{$targetUser->id}/restore");

    $response->assertRedirect("/admin/users/{$targetUser->id}");

    $this->assertDatabaseHas('users', [
        'id' => $targetUser->id,
        'deleted_at' => null,
    ]);
});

it('can permanently delete a user', function () {
    $targetUser = User::factory()->create();
    $targetUser->delete();

    $response = $this->actingAs($this->user)->delete("/admin/users/{$targetUser->id}/force");

    $response->assertRedirect('/admin/users');

    $this->assertDatabaseMissing('users', [
        'id' => $targetUser->id,
    ]);
});

it('requires access control panel permission to access admin users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/users');

    $response->assertForbidden();
});
