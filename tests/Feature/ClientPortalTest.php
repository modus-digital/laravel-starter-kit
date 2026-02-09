<?php

declare(strict_types=1);

use App\Enums\RBAC\Permission;
use App\Models\Modules\Clients\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Enable the clients module
    config(['modules.clients.enabled' => true]);

    // Create required permissions in the database
    foreach (Permission::cases() as $permission) {
        if ($permission->shouldSync()) {
            Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission->value]);
        }
    }

    $this->user = User::factory()->create();
    $this->user->givePermissionTo(Permission::ViewClients);
    $this->client = Client::factory()->create();
    $this->client->users()->attach($this->user->id);

    // Set current client in session
    session(['current_client_id' => $this->client->id]);
});

it('can view client portal dashboard', function () {
    $response = $this->actingAs($this->user)
        ->get('/portal/');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('modules/app/clients/show')
            ->has('client')
            ->where('client.id', $this->client->id)
        );
});

it('cannot view dashboard without a client in session', function () {
    session()->forget('current_client_id');

    $response = $this->actingAs($this->user)
        ->get('/portal');

    $response->assertNotFound();
});

it('can view users management page', function () {
    $response = $this->actingAs($this->user)
        ->get('/portal/manage/users');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('modules/app/clients/users')
            ->has('client')
            ->where('client.id', $this->client->id)
            ->has('users')
        );
});

it('can view client settings page', function () {
    $response = $this->actingAs($this->user)
        ->get('/portal/manage/settings');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('modules/app/clients/settings')
            ->has('client')
            ->where('client.id', $this->client->id)
        );
});

it('can update client settings', function () {
    $this->user->givePermissionTo(Permission::UpdateClients);

    $updateData = [
        'name' => 'Updated Client Name',
        'contact_name' => 'John Doe',
        'contact_email' => 'john@example.com',
        'contact_phone' => '+1234567890',
        'address' => '123 Test St',
        'postal_code' => '12345',
        'city' => 'Test City',
        'country' => 'Test Country',
    ];

    $response = $this->actingAs($this->user)
        ->put('/portal/manage/settings', $updateData);

    $response->assertRedirect('/portal/manage/settings');

    $this->assertDatabaseHas('clients', [
        'id' => $this->client->id,
        'name' => 'Updated Client Name',
        'contact_email' => 'john@example.com',
    ]);
});

it('cannot access client portal without client in session', function () {
    session()->forget('current_client_id');

    $response = $this->actingAs($this->user)
        ->get('/portal/manage/settings');

    $response->assertNotFound();
});

it('can view client activities page', function () {
    $response = $this->actingAs($this->user)
        ->get('/portal/activities');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('modules/app/clients/activities')
            ->has('client')
            ->has('activities')
        );
});

it('validates required client settings fields', function () {
    $this->user->givePermissionTo(Permission::UpdateClients);

    $response = $this->actingAs($this->user)
        ->put('/portal/manage/settings', [
            'name' => '', // Required field
        ]);

    $response->assertSessionHasErrors(['name']);
});
