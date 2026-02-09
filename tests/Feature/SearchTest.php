<?php

declare(strict_types=1);

use App\Enums\RBAC\Permission;
use App\Models\Modules\Clients\Client;
use App\Models\User;
use Database\Seeders\BootstrapApplicationSeeder;

beforeEach(function (): void {
    $this->seed(BootstrapApplicationSeeder::class);
});

it('requires authentication', function (): void {
    $response = $this->get('/search?q=test');

    $response->assertRedirect('/login');
});

it('returns empty results for empty query', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::AccessControlPanel->value);

    $response = $this->actingAs($user)
        ->get('/search?q=');

    $response->assertSuccessful();
    $response->assertJson([
        'data' => [],
    ]);
});

it('returns empty results when query is only whitespace', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::AccessControlPanel->value);

    $response = $this->actingAs($user)
        ->get('/search?q='.urlencode('   '));

    $response->assertSuccessful();
    $response->assertJson([
        'data' => [],
    ]);
});

it('can search users when user has permission', function (): void {
    $user = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    $user->givePermissionTo([
        Permission::AccessControlPanel->value,
        Permission::ViewAnyUsers->value,
    ]);

    $searchUser = User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

    $response = $this->actingAs($user)
        ->get('/search?q=Jane');

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'type', 'label', 'subtitle', 'icon', 'url'],
        ],
    ]);

    $data = $response->json('data');
    expect($data)->not->toBeEmpty();
    expect($data[0]['type'])->toBe('User');
    expect($data[0]['label'])->toContain('Jane');
    expect($data[0]['url'])->toContain($searchUser->id);
});

it('cannot search users when user lacks permission', function (): void {
    $user = User::factory()->create();
    // User does not have READ_USERS permission

    User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

    $response = $this->actingAs($user)
        ->get('/search?q=Jane');

    $response->assertSuccessful();
    $data = $response->json('data');
    expect($data)->toBeEmpty();
});

it('can search clients when user has permission and module is enabled', function (): void {
    config(['modules.clients.enabled' => true]);

    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::AccessControlPanel->value,
        Permission::ViewAnyClients->value,
    ]);

    $client = Client::factory()->create(['name' => 'Acme Corp', 'contact_email' => 'contact@acme.com']);

    $response = $this->actingAs($user)
        ->get('/search?q=Acme');

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'type', 'label', 'subtitle', 'icon', 'url'],
        ],
    ]);

    $data = $response->json('data');
    expect($data)->not->toBeEmpty();

    $clientResult = collect($data)->firstWhere('type', 'Client');
    expect($clientResult)->not->toBeNull();
    expect($clientResult['label'])->toContain('Acme');
    expect($clientResult['url'])->toContain($client->id);
});

it('cannot search clients when user lacks permission', function (): void {
    config(['modules.clients.enabled' => true]);

    $user = User::factory()->create();
    // User does not have READ_CLIENTS permission

    Client::factory()->create(['name' => 'Acme Corp', 'contact_email' => 'contact@acme.com']);

    $response = $this->actingAs($user)
        ->get('/search?q=Acme');

    $response->assertSuccessful();
    $data = $response->json('data');

    // Should not contain Client results
    $clientResults = collect($data)->where('type', 'Client');
    expect($clientResults)->toBeEmpty();
});

it('can search across multiple model types', function (): void {
    config(['modules.clients.enabled' => true]);

    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::AccessControlPanel->value,
        Permission::ViewAnyUsers->value,
        Permission::ViewAnyClients->value,
    ]);

    $searchUser = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    $client = Client::factory()->create(['name' => 'John Corp', 'contact_email' => 'contact@johncorp.com']);

    $response = $this->actingAs($user)
        ->get('/search?q=John');

    $response->assertSuccessful();
    $data = $response->json('data');

    expect($data)->not->toBeEmpty();

    $userResults = collect($data)->where('type', 'User');
    $clientResults = collect($data)->where('type', 'Client');

    expect($userResults)->not->toBeEmpty();
    expect($clientResults)->not->toBeEmpty();
});

it('respects the limit parameter', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::AccessControlPanel->value);

    // Create multiple users
    User::factory()->count(15)->create(['name' => 'Test User']);

    $response = $this->actingAs($user)
        ->get('/search?q=Test&limit=5');

    $response->assertSuccessful();
    $data = $response->json('data');

    expect(count($data))->toBeLessThanOrEqual(5);
});

it('searches by name and email for users', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::AccessControlPanel->value,
        Permission::ViewAnyUsers->value,
    ]);

    $searchUser = User::factory()->create(['name' => 'John Doe', 'email' => 'john.doe@example.com']);

    // Search by name
    $response = $this->actingAs($user)
        ->get('/search?q=John');

    $response->assertSuccessful();
    $data = $response->json('data');
    expect($data)->not->toBeEmpty();
    expect($data[0]['label'])->toContain('John');

    // Search by email
    $response = $this->actingAs($user)
        ->get('/search?q=john.doe@example.com');

    $response->assertSuccessful();
    $data = $response->json('data');
    expect($data)->not->toBeEmpty();
    expect($data[0]['subtitle'])->toContain('john.doe@example.com');
});

it('searches by name, contact name, and email for clients', function (): void {
    config(['modules.clients.enabled' => true]);

    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::AccessControlPanel->value,
        Permission::ViewAnyClients->value,
    ]);

    $client = Client::factory()->create([
        'name' => 'Acme Corporation',
        'contact_name' => 'Jane Smith',
        'contact_email' => 'jane@acme.com',
    ]);

    // Search by company name
    $response = $this->actingAs($user)
        ->get('/search?q=Acme');

    $response->assertSuccessful();
    $data = $response->json('data');
    expect($data)->not->toBeEmpty();
    expect($data[0]['label'])->toContain('Acme');

    // Search by contact name
    $response = $this->actingAs($user)
        ->get('/search?q=Jane');

    $response->assertSuccessful();
    $data = $response->json('data');
    expect($data)->not->toBeEmpty();

    // Search by contact email
    $response = $this->actingAs($user)
        ->get('/search?q=jane@acme.com');

    $response->assertSuccessful();
    $data = $response->json('data');
    expect($data)->not->toBeEmpty();
    expect($data[0]['subtitle'])->toContain('jane@acme.com');
});

it('returns results with correct structure', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::AccessControlPanel->value,
        Permission::ViewAnyUsers->value,
    ]);

    $searchUser = User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);

    $response = $this->actingAs($user)
        ->get('/search?q=Test');

    $response->assertSuccessful();
    $data = $response->json('data');

    expect($data)->not->toBeEmpty();

    $result = $data[0];
    expect($result)->toHaveKeys(['id', 'type', 'label', 'subtitle', 'icon', 'url']);
    expect($result['id'])->toBe($searchUser->id);
    expect($result['type'])->toBe('User');
    expect($result['label'])->toBe('Test User');
    expect($result['subtitle'])->toBe('test@example.com');
    expect($result['url'])->toContain('/admin/users/');
    expect($result['url'])->toContain($searchUser->id);
});

it('does not return soft deleted models', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::AccessControlPanel->value,
        Permission::ViewAnyUsers->value,
    ]);

    $deletedUser = User::factory()->create(['name' => 'Deleted User', 'email' => 'deleted@example.com']);
    $deletedUser->delete();

    $response = $this->actingAs($user)
        ->get('/search?q=Deleted');

    $response->assertSuccessful();
    $data = $response->json('data');

    // Should not include soft deleted user
    expect($data)->toBeEmpty();
});
