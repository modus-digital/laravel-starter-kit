<?php

declare(strict_types=1);

use App\Enums\RBAC\Permission;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission as SpatiePermission;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create required permissions in the database
    foreach (Permission::cases() as $permission) {
        if ($permission->shouldSync()) {
            SpatiePermission::create(['name' => $permission->value]);
        }
    }

    $this->user = User::factory()->create();
    $this->user->givePermissionTo(Permission::ACCESS_ACTIVITY_LOGS);
    $this->user->givePermissionTo(Permission::ACCESS_CONTROL_PANEL);
});

it('can list activities', function () {
    // Create activities - excluding tasks/comments log names as they are filtered
    Activity::factory()->count(5)->create([
        'log_name' => 'administration',
    ]);

    $response = $this->actingAs($this->user)->get('/admin/activities');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/activities/index')
            ->has('activities')
            ->where('activities', fn ($activities) => count($activities) >= 1)
            ->has('logNames')
        );
});

it('can filter activities by log name', function () {
    Activity::factory()->create(['log_name' => 'test_log']);
    Activity::factory()->create(['log_name' => 'other_log']);

    $response = $this->actingAs($this->user)->get('/admin/activities?log_name=test_log');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/activities/index')
            ->has('activities', 1)
        );
});

it('can filter activities by event', function () {
    Activity::factory()->create(['event' => 'created']);
    Activity::factory()->create(['event' => 'updated']);

    $response = $this->actingAs($this->user)->get('/admin/activities?event=created');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/activities/index')
            ->has('activities', 1)
        );
});

it('can filter activities by date range', function () {
    Activity::factory()->create(['created_at' => now()->subDays(5)]);
    Activity::factory()->create(['created_at' => now()]);

    $dateFrom = now()->subDays(1)->format('Y-m-d');

    $response = $this->actingAs($this->user)->get("/admin/activities?date_from={$dateFrom}");

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/activities/index')
            ->has('activities', 1)
        );
});

// Note: Activity details are shown in a sheet/drawer on the index page, not a separate page

it('requires access activity logs permission to view activities', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/activities');

    $response->assertForbidden();
});
