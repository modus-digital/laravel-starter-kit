<?php

declare(strict_types=1);

use App\Enums\ActivityStatus;
use App\Enums\RBAC\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use ModusDigital\Clients\Filament\Resources\ClientResource\RelationManagers\UserRelationManager;
use ModusDigital\Clients\Models\Client;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->admin = User::factory()->create()->assignRole(Role::SUPER_ADMIN);
    $this->actingAs($this->admin);
});

test('admin can view users table for a client with users', function () {
    $client = Client::factory()->create();
    $users = User::factory()->count(3)->create();

    $client->users()->attach($users->pluck('id'));

    Livewire::test(UserRelationManager::class, [
        'ownerRecord' => $client,
        'pageClass' => 'edit',
    ])
        ->assertCanSeeTableRecords($users)
        ->assertCountTableRecords(3);
});

test('admin can attach existing user to client', function () {
    $client = Client::factory()->create();
    $user = User::factory()->create(['name' => 'John Doe']);

    expect($client->users()->count())->toBe(0);

    Livewire::test(UserRelationManager::class, [
        'ownerRecord' => $client,
        'pageClass' => 'edit',
    ])
        ->callAction(TestAction::make('attachUser')->table(), data: [
            'users' => [$user->id],
        ])
        ->assertHasNoErrors()
        ->assertNotified();

    expect($client->fresh()->users()->count())->toBe(1)
        ->and($client->users->first()->id)->toBe($user->id);
});

test('admin can attach multiple existing users to client', function () {
    $client = Client::factory()->create();
    $users = User::factory()->count(3)->create();

    expect($client->users()->count())->toBe(0);

    Livewire::test(UserRelationManager::class, [
        'ownerRecord' => $client,
        'pageClass' => 'edit',
    ])
        ->callAction(TestAction::make('attachUser')->table(), data: [
            'users' => $users->pluck('id')->toArray(),
        ])
        ->assertHasNoErrors()
        ->assertNotified();

    expect($client->fresh()->users()->count())->toBe(3);
});

test('admin can create new user and auto-attach to client', function () {
    $client = Client::factory()->create();

    expect($client->users()->count())->toBe(0);

    Livewire::test(UserRelationManager::class, [
        'ownerRecord' => $client,
        'pageClass' => 'edit',
    ])
        ->callAction(TestAction::make('createUser')->table(), data: [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'role' => Role::USER->value,
            'status' => ActivityStatus::ACTIVE->value,
        ])
        ->assertHasNoErrors()
        ->assertNotified();

    $user = User::where('email', 'jane@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Jane Smith')
        ->and($client->fresh()->users()->count())->toBe(1)
        ->and($client->users->first()->id)->toBe($user->id);
});

test('created user has correct role assigned', function () {
    $client = Client::factory()->create();

    Livewire::test(UserRelationManager::class, [
        'ownerRecord' => $client,
        'pageClass' => 'edit',
    ])
        ->callAction(TestAction::make('createUser')->table(), data: [
            'name' => 'Bob Johnson',
            'email' => 'bob@example.com',
            'password' => 'password123',
            'role' => Role::USER->value,
            'status' => ActivityStatus::ACTIVE->value,
        ])
        ->assertHasNoErrors();

    $user = User::where('email', 'bob@example.com')->first();

    expect($user->hasRole(Role::USER->value))->toBeTrue();
});

test('admin can detach user from client', function () {
    $client = Client::factory()->create();
    $user = User::factory()->create(['name' => 'Alice Cooper']);

    $client->users()->attach($user->id);

    expect($client->users()->count())->toBe(1);

    Livewire::test(UserRelationManager::class, [
        'ownerRecord' => $client,
        'pageClass' => 'edit',
    ])
        ->callAction(TestAction::make('detachUser')->table($user))
        ->assertHasNoErrors()
        ->assertNotified();

    expect($client->fresh()->users()->count())->toBe(0);
});

test('detaching user does not delete the user', function () {
    $client = Client::factory()->create();
    $user = User::factory()->create(['name' => 'Charlie Brown']);

    $client->users()->attach($user->id);

    expect(User::where('id', $user->id)->exists())->toBeTrue();

    Livewire::test(UserRelationManager::class, [
        'ownerRecord' => $client,
        'pageClass' => 'edit',
    ])
        ->callAction(TestAction::make('detachUser')->table($user));

    expect(User::where('id', $user->id)->exists())->toBeTrue()
        ->and($client->fresh()->users()->count())->toBe(0);
});

test('attach action only shows users not already attached to client', function () {
    $client = Client::factory()->create();
    $attachedUser = User::factory()->create(['name' => 'Already Attached']);
    $notAttachedUser = User::factory()->create(['name' => 'Not Attached']);

    $client->users()->attach($attachedUser->id);

    // Verify the attached user is in the client's users
    expect($client->users()->pluck('users.id')->toArray())->toContain($attachedUser->id)
        ->and($client->users()->pluck('users.id')->toArray())->not->toContain($notAttachedUser->id);
});

test('user can be attached to multiple clients', function () {
    $client1 = Client::factory()->create(['name' => 'Client 1']);
    $client2 = Client::factory()->create(['name' => 'Client 2']);
    $user = User::factory()->create(['name' => 'Shared User']);

    $client1->users()->attach($user->id);
    $client2->users()->attach($user->id);

    expect($client1->fresh()->users()->count())->toBe(1)
        ->and($client2->fresh()->users()->count())->toBe(1)
        ->and($user->fresh()->clients()->count())->toBe(2);
});

test('email must be unique when creating new user', function () {
    $client = Client::factory()->create();
    $existingUser = User::factory()->create(['email' => 'existing@example.com']);

    Livewire::test(UserRelationManager::class, [
        'ownerRecord' => $client,
        'pageClass' => 'edit',
    ])
        ->callAction(TestAction::make('createUser')->table(), data: [
            'name' => 'Duplicate Email',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'role' => Role::USER->value,
            'status' => ActivityStatus::ACTIVE->value,
        ])
        ->assertHasErrors(['mountedActions.0.data.email']);
});

test('password is required when creating new user', function () {
    $client = Client::factory()->create();

    Livewire::test(UserRelationManager::class, [
        'ownerRecord' => $client,
        'pageClass' => 'edit',
    ])
        ->callAction(TestAction::make('createUser')->table(), data: [
            'name' => 'No Password User',
            'email' => 'nopass@example.com',
            'password' => '',
            'role' => Role::USER->value,
            'status' => ActivityStatus::ACTIVE->value,
        ])
        ->assertHasErrors(['mountedActions.0.data.password']);
});

test('users table displays correct status badges', function () {
    $client = Client::factory()->create();
    $activeUser = User::factory()->create([
        'name' => 'Active User',
        'status' => ActivityStatus::ACTIVE,
    ]);
    $inactiveUser = User::factory()->create([
        'name' => 'Inactive User',
        'status' => ActivityStatus::INACTIVE,
    ]);

    $client->users()->attach([$activeUser->id, $inactiveUser->id]);

    Livewire::test(UserRelationManager::class, [
        'ownerRecord' => $client,
        'pageClass' => 'edit',
    ])
        ->assertCanSeeTableRecords([$activeUser, $inactiveUser])
        ->assertCountTableRecords(2);
});
