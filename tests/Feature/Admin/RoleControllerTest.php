<?php

declare(strict_types=1);

use App\Enums\RBAC\Permission;
use App\Models\Permission as PermissionModel;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Ensure required permissions exist in the database
    foreach (Permission::cases() as $permission) {
        if ($permission->shouldSync()) {
            Spatie\Permission\Models\Permission::firstOrCreate(
                ['name' => $permission->value, 'guard_name' => 'web']
            );
        }
    }

    // Create an admin role and assign to user for testing
    $adminRole = Role::withInternal()->firstOrCreate(
        ['name' => Role::ADMIN, 'guard_name' => 'web'],
        ['internal' => true]
    );

    $this->user = User::factory()->create();
    $this->user->assignRole($adminRole);

    // Give necessary permissions for role management
    $this->user->givePermissionTo([
        Permission::AccessControlPanel,
        Permission::ViewAnyRoles,
        Permission::CreateRoles,
        Permission::ViewRoles,
        Permission::UpdateRoles,
        Permission::DeleteRoles,
    ]);
});

it('can list roles', function () {
    Role::factory()->count(5)->create();

    $response = $this->actingAs($this->user)->get('/admin/roles');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('core/admin/roles/index')
            ->has('roles', 6) // 5 external + 1 internal (admin from beforeEach)
        );
});

it('can show create role page', function () {
    $response = $this->actingAs($this->user)->get('/admin/roles/create');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('core/admin/roles/create')
            ->has('permissions')
        );
});

it('can create a role', function () {
    $permission = PermissionModel::where('name', Permission::AccessControlPanel->value)->first();

    $roleData = [
        'name' => 'test_role',
        'guard_name' => 'web',
        'icon' => 'heroicon-o-user',
        'color' => '#3b82f6',
        'permissions' => [$permission->name],
    ];

    $response = $this->actingAs($this->user)->post('/admin/roles', $roleData);

    $response->assertRedirect();

    $this->assertDatabaseHas('roles', [
        'name' => 'test_role',
        'guard_name' => 'web',
    ]);

    $role = Role::where('name', 'test_role')->first();
    expect($role->hasPermissionTo($permission->name))->toBeTrue();
});

it('can show a role', function () {
    $role = Role::factory()->create();

    $response = $this->actingAs($this->user)->get("/admin/roles/{$role->id}");

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('core/admin/roles/show')
            ->where('role.id', $role->id)
            ->has('activities')
        );
});

it('can show edit role page', function () {
    $role = Role::factory()->create();

    $response = $this->actingAs($this->user)->get("/admin/roles/{$role->id}/edit");

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('core/admin/roles/edit')
            ->where('role.id', $role->id)
            ->has('permissions')
        );
});

it('can update a role', function () {
    $role = Role::factory()->create(['name' => 'old_name']);

    $updateData = [
        'name' => 'new_name',
        'icon' => 'heroicon-o-star',
        'color' => '#8b5cf6',
        'permissions' => [],
    ];

    $response = $this->actingAs($this->user)->put("/admin/roles/{$role->id}", $updateData);

    $response->assertRedirect("/admin/roles/{$role->id}");

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'new_name',
    ]);
});

it('cannot update system roles', function () {
    $role = Role::withInternal()->where('name', Role::ADMIN)->first();

    // Verify the role is internal
    expect($role)->not->toBeNull();
    expect($role->internal)->toBeTrue();
    expect($role->isInternal())->toBeTrue();

    $updateData = [
        'name' => 'hacked_role',
        'permissions' => [],
    ];

    $response = $this->actingAs($this->user)->put("/admin/roles/{$role->id}", $updateData);

    // Should be redirected with error OR validation error
    expect($response->status())->toBeIn([302, 422]);

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => Role::ADMIN, // Name should not change
    ]);
});

it('can delete a role', function () {
    $role = Role::factory()->create(['name' => 'deletable_role']);

    $response = $this->actingAs($this->user)->delete("/admin/roles/{$role->id}");

    $response->assertRedirect('/admin/roles');

    $this->assertDatabaseMissing('roles', [
        'id' => $role->id,
    ]);
});

it('cannot delete system roles', function () {
    $role = Role::withInternal()->where('name', Role::ADMIN)->first();

    $response = $this->actingAs($this->user)->delete("/admin/roles/{$role->id}");

    $response->assertRedirect('/admin/roles');

    // Role should still exist
    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => Role::ADMIN,
    ]);
});

it('requires access control panel permission to access admin roles', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/roles');

    $response->assertForbidden();
});
