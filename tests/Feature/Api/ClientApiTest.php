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
            Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission->value]);
        }
    }
});

it('can list clients', function () {
    Client::factory()->count(5)->create();

    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->getJson('/api/v1/admin/clients');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'contact_name',
                    'contact_email',
                    'contact_phone',
                    'address',
                    'postal_code',
                    'city',
                    'country',
                    'status',
                    'created_at',
                    'updated_at',
                    'deleted_at',
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

it('can create a client', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::CreateClients);
    $token = $user->createToken('test-token')->plainTextToken;

    $clientData = [
        'name' => 'Acme Corporation',
        'contact_name' => 'John Doe',
        'contact_email' => 'john@acme.com',
        'contact_phone' => '+1234567890',
        'address' => '123 Main St',
        'postal_code' => '12345',
        'city' => 'New York',
        'country' => 'USA',
        'status' => 'active',
    ];

    $response = $this->withToken($token)->postJson('/api/v1/admin/clients', $clientData);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'contact_name',
                'contact_email',
                'contact_phone',
                'address',
                'postal_code',
                'city',
                'country',
                'status',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
        ]);

    $this->assertDatabaseHas('clients', $clientData);
});

it('can show a client', function () {
    $client = Client::factory()->create();

    $user = User::factory()->create();
    $user->givePermissionTo(Permission::ViewClients);
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->getJson("/api/v1/admin/clients/{$client->id}");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'contact_name',
                'contact_email',
                'contact_phone',
                'address',
                'postal_code',
                'city',
                'country',
                'status',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
        ])
        ->assertJson([
            'data' => [
                'id' => $client->id,
                'name' => $client->name,
            ],
        ]);
});

it('can update a client', function () {
    $client = Client::factory()->create();

    $user = User::factory()->create();
    $user->givePermissionTo(Permission::UpdateClients);
    $token = $user->createToken('test-token')->plainTextToken;

    $updatedData = [
        'name' => 'Updated Corporation',
        'contact_name' => 'Jane Smith',
        'contact_email' => 'jane@updated.com',
    ];

    $response = $this->withToken($token)->putJson("/api/v1/admin/clients/{$client->id}", $updatedData);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'contact_name',
                'contact_email',
                'contact_phone',
                'address',
                'postal_code',
                'city',
                'country',
                'status',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
        ]);

    $this->assertDatabaseHas('clients', [
        'id' => $client->id,
        'name' => 'Updated Corporation',
        'contact_name' => 'Jane Smith',
        'contact_email' => 'jane@updated.com',
    ]);
});

it('can delete a client', function () {
    $client = Client::factory()->create();

    $user = User::factory()->create();
    $user->givePermissionTo(Permission::DeleteClients);
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->deleteJson("/api/v1/admin/clients/{$client->id}");

    $response->assertSuccessful()
        ->assertJson([
            'message' => 'Client deleted successfully.',
        ]);

    $this->assertSoftDeleted('clients', [
        'id' => $client->id,
    ]);
});

it('can restore a deleted client', function () {
    $client = Client::factory()->create();
    $client->delete();

    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->patchJson("/api/v1/admin/clients/{$client->id}/restore");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'contact_name',
                'contact_email',
                'contact_phone',
                'address',
                'postal_code',
                'city',
                'country',
                'status',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
        ]);

    $this->assertDatabaseHas('clients', [
        'id' => $client->id,
        'deleted_at' => null,
    ]);
});

it('can permanently delete a client', function () {
    $client = Client::factory()->create();
    $client->delete();

    $user = User::factory()->create();
    $user->givePermissionTo(Permission::ForceDeleteClients);
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->deleteJson("/api/v1/admin/clients/{$client->id}/force-delete");

    $response->assertSuccessful()
        ->assertJson([
            'message' => 'Client permanently deleted.',
        ]);

    $this->assertDatabaseMissing('clients', [
        'id' => $client->id,
    ]);
});

it('validates client creation', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::CreateClients);
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->postJson('/api/v1/admin/clients', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'status']);
});

it('validates client update', function () {
    $client = Client::factory()->create();
    Client::factory()->create(['name' => 'Existing Client']);

    $user = User::factory()->create();
    $user->givePermissionTo(Permission::UpdateClients);
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->putJson("/api/v1/admin/clients/{$client->id}", [
        'name' => 'Existing Client', // Duplicate name
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

it('requires authentication', function () {
    $response = $this->getJson('/api/v1/admin/clients');

    $response->assertUnauthorized();
});
