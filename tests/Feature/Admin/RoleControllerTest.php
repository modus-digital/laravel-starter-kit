<?php

declare(strict_types=1);

use App\Enums\RBAC\Permission;
use App\Enums\RBAC\Role as RoleEnum;
use App\Models\Permission as PermissionModel;
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
    $this->user->givePermissionTo(Permission::READ_ROLES);
    $this->user->givePermissionTo(Permission::CREATE_ROLES);
    $this->user->givePermissionTo(Permission::UPDATE_ROLES);
    $this->user->givePermissionTo(Permission::DELETE_ROLES);
});

it('can list roles', function () {
    Role::factory()->count(5)->create();

    $response = $this->actingAs($this->user)->get('/admin/roles');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/roles/index')
            ->has('roles', 5)
        );
});

it('can show create role page', function () {
    $response = $this->actingAs($this->user)->get('/admin/roles/create');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/roles/create')
            ->has('permissions')
        );
});

it('can create a role', function () {
    $permission = PermissionModel::factory()->create(['name' => Permission::READ_USERS->value]);

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
            ->component('admin/roles/show')
            ->where('role.id', $role->id)
            ->has('activities')
        );
});

it('can show edit role page', function () {
    $role = Role::factory()->create();

    $response = $this->actingAs($this->user)->get("/admin/roles/{$role->id}/edit");

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/roles/edit')
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
    $role = Role::factory()->create(['name' => RoleEnum::ADMIN->value]);

    $updateData = [
        'name' => 'hacked_role',
        'permissions' => [],
    ];

    $response = $this->actingAs($this->user)->put("/admin/roles/{$role->id}", $updateData);

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => RoleEnum::ADMIN->value, // Name should not change
    ]);
});

it('can delete a role', function () {
    $role = Role::factory()->create(['name' => 'deletable_role']);

    $response = $this->actingAs($this->user)->delete("/admin/roles/{$role->id}");

    $response->assertRedirect('/admin/roles');

    $this->assertSoftDeleted($role);
});

it('cannot delete system roles', function () {
    $role = Role::factory()->create(['name' => RoleEnum::ADMIN->value]);

    $response = $this->actingAs($this->user)->delete("/admin/roles/{$role->id}");

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'deleted_at' => null, // Should not be deleted
    ]);
});

it('requires read permission to list roles', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/roles');

    $response->assertForbidden();
});

it('requires create permission to create roles', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::READ_ROLES);

    $response = $this->actingAs($user)->get('/admin/roles/create');

    $response->assertForbidden();
});
