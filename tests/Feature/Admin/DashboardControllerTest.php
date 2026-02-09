<?php

declare(strict_types=1);

use App\Enums\RBAC\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can view admin dashboard', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::AccessControlPanel);

    $response = $this->actingAs($user)->get('/admin');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('core/admin/dashboard/index')
            ->has('layout')
            ->has('availableWidgets')
        );
});

it('requires access control panel permission to view dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin');

    $response->assertForbidden();
});

it('displays correct stats on dashboard', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::AccessControlPanel);

    User::factory()->count(5)->create();

    $response = $this->actingAs($user)->get('/admin');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->has('layout')
            ->has('availableWidgets')
        );
});
