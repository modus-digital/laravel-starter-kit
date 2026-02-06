<?php

declare(strict_types=1);

use App\Enums\RBAC\Permission;
use App\Models\Modules\Clients\Client;
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

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo(Permission::AccessControlPanel);
});

it('can remove a user from a client', function () {
    $client = Client::factory()->create();
    $user = User::factory()->create();

    // Attach user to client
    $client->users()->attach($user->id);

    expect($client->users()->count())->toBe(1);

    $response = $this->actingAs($this->admin)
        ->delete("/admin/clients/{$client->id}/users/{$user->id}");

    $response->assertRedirect("/admin/clients/{$client->id}");

    expect($client->fresh()->users()->count())->toBe(0);
});

it('cannot remove a user that does not belong to the client', function () {
    $client = Client::factory()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($this->admin)
        ->delete("/admin/clients/{$client->id}/users/{$user->id}");

    $response->assertNotFound();
});
