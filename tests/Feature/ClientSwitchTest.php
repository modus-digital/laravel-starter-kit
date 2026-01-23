<?php

declare(strict_types=1);

use App\Models\Modules\Clients\Client;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->create();
});

it('can switch to a client the user belongs to', function (): void {
    $client = Client::factory()->create();
    $this->user->clients()->attach($client);

    $response = $this->actingAs($this->user)
        ->post("/clients/{$client->id}/switch");

    $response->assertRedirect();
    expect(session('current_client_id'))->toBe($client->id);
});

it('cannot switch to a client the user does not belong to', function (): void {
    $client = Client::factory()->create();

    $response = $this->actingAs($this->user)
        ->post("/clients/{$client->id}/switch");

    $response->assertForbidden();
    expect(session('current_client_id'))->toBeNull();
});

it('can switch between multiple clients', function (): void {
    $client1 = Client::factory()->create(['name' => 'Client 1']);
    $client2 = Client::factory()->create(['name' => 'Client 2']);
    $this->user->clients()->attach([$client1->id, $client2->id]);

    // Switch to client 1
    $this->actingAs($this->user)
        ->post("/clients/{$client1->id}/switch")
        ->assertRedirect();

    expect(session('current_client_id'))->toBe($client1->id);

    // Switch to client 2
    $this->actingAs($this->user)
        ->post("/clients/{$client2->id}/switch")
        ->assertRedirect();

    expect(session('current_client_id'))->toBe($client2->id);
});

it('requires authentication', function (): void {
    $client = Client::factory()->create();

    $response = $this->post("/clients/{$client->id}/switch");

    $response->assertRedirect('/login');
});
