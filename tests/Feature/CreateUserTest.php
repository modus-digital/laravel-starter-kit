<?php

declare(strict_types=1);

use App\Enums\ActivityStatus;
use App\Enums\RBAC\Role;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\User;
use App\Notifications\Auth\AccountCreated;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->actingAs(User::factory()->create()->assignRole(Role::SUPER_ADMIN));
});

test('admin can create a new user through filament', function () {
    Notification::fake();

    $roleId = Spatie\Permission\Models\Role::where('name', Role::USER->value)->first()->id;

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => $roleId,
            'status' => ActivityStatus::ACTIVE->value,
        ])
        ->call('create')
        ->assertHasNoErrors();

    expect(User::where('email', 'john@example.com')->exists())->toBeTrue();
});

test('password is automatically generated when creating user', function () {
    Notification::fake();

    $roleId = Spatie\Permission\Models\Role::where('name', Role::USER->value)->first()->id;

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'role' => $roleId,
            'status' => ActivityStatus::ACTIVE->value,
        ])
        ->call('create')
        ->assertHasNoErrors();

    $user = User::where('email', 'jane@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->password)->not->toBeNull()
        ->and($user->password)->not->toBeEmpty();
});

test('notification is sent with generated password after user creation', function () {
    Notification::fake();

    $roleId = Spatie\Permission\Models\Role::where('name', Role::USER->value)->first()->id;

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Bob Johnson',
            'email' => 'bob@example.com',
            'role' => $roleId,
            'status' => ActivityStatus::ACTIVE->value,
        ])
        ->call('create')
        ->assertHasNoErrors();

    $user = User::where('email', 'bob@example.com')->first();

    Notification::assertSentTo(
        $user,
        AccountCreated::class,
        function ($notification) {
            return mb_strlen($notification->password) === 18;
        }
    );
});

test('user can login with generated password', function () {
    Notification::fake();

    $roleId = Spatie\Permission\Models\Role::where('name', Role::USER->value)->first()->id;

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Alice Cooper',
            'email' => 'alice@example.com',
            'role' => $roleId,
            'status' => ActivityStatus::ACTIVE->value,
        ])
        ->call('create')
        ->assertHasNoErrors();

    $user = User::where('email', 'alice@example.com')->first();

    Notification::assertSentTo(
        $user,
        AccountCreated::class,
        function ($notification) use ($user) {
            // Verify the password in the notification works for login
            return Hash::check($notification->password, $user->password);
        }
    );
});
