<?php

declare(strict_types=1);

use App\Enums\RBAC\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission as PermissionModel;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create the permissions in the database that are needed by the middleware
    foreach (Permission::cases() as $permission) {
        if ($permission->shouldSync()) {
            PermissionModel::findOrCreate($permission->value, 'web');
        }
    }
});

it('can view v2 admin dashboard with layout', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::ACCESS_CONTROL_PANEL);

    $response = $this->actingAs($user)->get('/admin/dashboard');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('core/admin/dashboard/index')
            ->has('layout')
            ->has('availableWidgets')
        );
});

it('returns default layout when user has no saved layout', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::ACCESS_CONTROL_PANEL);

    $response = $this->actingAs($user)->get('/admin/dashboard');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('layout.0.i', 'stats')
            ->where('layout.1.i', 'activities')
            ->where('layout.2.i', 'clients')
            ->where('layout.3.i', 'email')
            ->where('layout.4.i', 'activity-chart')
        );
});

it('can save dashboard layout', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::ACCESS_CONTROL_PANEL);

    $newLayout = [
        ['i' => 'stats', 'x' => 0, 'y' => 0, 'w' => 12, 'h' => 3],
        ['i' => 'activities', 'x' => 0, 'y' => 3, 'w' => 12, 'h' => 4],
    ];

    $response = $this->actingAs($user)->putJson('/admin/dashboard/layout', [
        'layout' => $newLayout,
    ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    $user->refresh();
    expect($user->getPreference('dashboard.layout'))->toBe($newLayout);
});

it('persists layout between page loads', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::ACCESS_CONTROL_PANEL);

    $customLayout = [
        ['i' => 'activities', 'x' => 0, 'y' => 0, 'w' => 12, 'h' => 4],
        ['i' => 'stats', 'x' => 0, 'y' => 4, 'w' => 6, 'h' => 2],
    ];

    $user->setPreference('dashboard.layout', $customLayout)->save();

    $response = $this->actingAs($user)->get('/admin/dashboard');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('layout.0.i', 'activities')
            ->where('layout.0.w', 12)
            ->where('layout.1.i', 'stats')
            ->where('layout.1.w', 6)
        );
});

it('validates layout structure when saving', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::ACCESS_CONTROL_PANEL);

    $response = $this->actingAs($user)->putJson('/admin/dashboard/layout', [
        'layout' => [
            ['i' => 'stats'], // Missing required fields
        ],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['layout.0.x', 'layout.0.y', 'layout.0.w', 'layout.0.h']);
});

it('requires authentication to save layout', function () {
    $response = $this->putJson('/admin/dashboard/layout', [
        'layout' => [
            ['i' => 'stats', 'x' => 0, 'y' => 0, 'w' => 12, 'h' => 2],
        ],
    ]);

    $response->assertUnauthorized();
});

it('requires permission to save layout', function () {
    $user = User::factory()->create();
    // User without ACCESS_CONTROL_PANEL permission

    $response = $this->actingAs($user)->putJson('/admin/dashboard/layout', [
        'layout' => [
            ['i' => 'stats', 'x' => 0, 'y' => 0, 'w' => 12, 'h' => 2],
        ],
    ]);

    $response->assertForbidden();
});

it('returns widget data with deferred props', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::ACCESS_CONTROL_PANEL);

    // Create some test data
    User::factory()->count(3)->create();

    $response = $this->actingAs($user)->get('/admin/dashboard');

    $response->assertSuccessful();

    // Deferred props should be loaded via separate request
    // Initial page should have layout and availableWidgets
    $response->assertInertia(fn ($page) => $page
        ->has('layout')
        ->has('availableWidgets')
    );
});
