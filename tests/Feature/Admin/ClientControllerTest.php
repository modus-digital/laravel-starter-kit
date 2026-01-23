<?php

declare(strict_types=1);

use App\Enums\ActivityStatus;
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

    $this->user = User::factory()->create();
    $this->user->givePermissionTo(Permission::READ_CLIENTS);
    $this->user->givePermissionTo(Permission::CREATE_CLIENTS);
    $this->user->givePermissionTo(Permission::UPDATE_CLIENTS);
    $this->user->givePermissionTo(Permission::DELETE_CLIENTS);
    $this->user->givePermissionTo(Permission::RESTORE_CLIENTS);
});

it('can list clients', function () {
    Client::factory()->count(5)->create();

    $response = $this->actingAs($this->user)->get('/admin/clients');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/clients/index')
            ->has('clients', 5)
        );
});

it('can filter clients by search', function () {
    Client::factory()->create(['name' => 'Acme Corp', 'contact_name' => 'John Doe']);
    Client::factory()->create(['name' => 'Tech Inc', 'contact_name' => 'Jane Smith']);

    $response = $this->actingAs($this->user)->get('/admin/clients?search=Acme');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/clients/index')
            ->has('clients', 1)
            ->where('clients.0.name', 'Acme Corp')
        );
});

it('can filter clients by status', function () {
    Client::factory()->create(['status' => ActivityStatus::ACTIVE]);
    Client::factory()->create(['status' => ActivityStatus::INACTIVE]);

    $response = $this->actingAs($this->user)->get('/admin/clients?status=active');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/clients/index')
            ->has('clients')
        );
});

it('can show create client page', function () {
    $response = $this->actingAs($this->user)->get('/admin/clients/create');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/clients/create')
            ->has('statuses')
        );
});

it('can create a client', function () {
    $clientData = [
        'name' => 'Test Client',
        'contact_name' => 'John Doe',
        'contact_email' => 'john@test.com',
        'status' => ActivityStatus::ACTIVE->value,
    ];

    $response = $this->actingAs($this->user)->post('/admin/clients', $clientData);

    $response->assertRedirect('/admin/clients/'.Client::where('name', 'Test Client')->first()->id);

    $this->assertDatabaseHas('clients', [
        'name' => 'Test Client',
        'contact_name' => 'John Doe',
        'contact_email' => 'john@test.com',
    ]);
});

it('can show a client', function () {
    $client = Client::factory()->create();

    $response = $this->actingAs($this->user)->get("/admin/clients/{$client->id}");

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/clients/show')
            ->where('client.id', $client->id)
            ->has('users')
            ->has('activities')
        );
});

it('can show edit client page', function () {
    $client = Client::factory()->create();

    $response = $this->actingAs($this->user)->get("/admin/clients/{$client->id}/edit");

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/clients/edit')
            ->where('client.id', $client->id)
            ->has('statuses')
        );
});

it('can update a client', function () {
    $client = Client::factory()->create();

    $updateData = [
        'name' => 'Updated Client',
        'contact_name' => 'Jane Smith',
    ];

    $response = $this->actingAs($this->user)->put("/admin/clients/{$client->id}", $updateData);

    $response->assertRedirect("/admin/clients/{$client->id}");

    $this->assertDatabaseHas('clients', [
        'id' => $client->id,
        'name' => 'Updated Client',
        'contact_name' => 'Jane Smith',
    ]);
});

it('can delete a client', function () {
    $client = Client::factory()->create();

    $response = $this->actingAs($this->user)->delete("/admin/clients/{$client->id}");

    $response->assertRedirect('/admin/clients');

    $this->assertSoftDeleted($client);
});

it('can restore a deleted client', function () {
    $client = Client::factory()->create();
    $client->delete();

    $response = $this->actingAs($this->user)->post("/admin/clients/{$client->id}/restore");

    $response->assertRedirect("/admin/clients/{$client->id}");

    $this->assertDatabaseHas('clients', [
        'id' => $client->id,
        'deleted_at' => null,
    ]);
});

it('can permanently delete a client', function () {
    $client = Client::factory()->create();
    $client->delete();

    $response = $this->actingAs($this->user)->delete("/admin/clients/{$client->id}/force");

    $response->assertRedirect('/admin/clients');

    $this->assertDatabaseMissing('clients', [
        'id' => $client->id,
    ]);
});

it('requires read permission to list clients', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/clients');

    $response->assertForbidden();
});

it('requires create permission to create clients', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::READ_CLIENTS);

    $response = $this->actingAs($user)->get('/admin/clients/create');

    $response->assertForbidden();
});

it('requires update permission to update clients', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::READ_CLIENTS);
    $client = Client::factory()->create();

    $response = $this->actingAs($user)->get("/admin/clients/{$client->id}/edit");

    $response->assertForbidden();
});

it('requires delete permission to delete clients', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::READ_CLIENTS);
    $client = Client::factory()->create();

    $response = $this->actingAs($user)->delete("/admin/clients/{$client->id}");

    $response->assertForbidden();
});
