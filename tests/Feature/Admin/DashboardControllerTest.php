<?php

declare(strict_types=1);

use App\Enums\RBAC\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can view admin dashboard', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::ACCESS_CONTROL_PANEL);

    $response = $this->actingAs($user)->get('/admin');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/index')
            ->has('stats')
            ->has('recentActivities')
            ->has('roleDistribution')
        );
});

it('requires access control panel permission to view dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin');

    $response->assertForbidden();
});

it('displays correct stats on dashboard', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::ACCESS_CONTROL_PANEL);

    User::factory()->count(5)->create();

    $response = $this->actingAs($user)->get('/admin');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('stats.total_users', 6) // 5 created + 1 authenticated user
        );
});
