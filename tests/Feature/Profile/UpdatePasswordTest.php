<?php

declare(strict_types=1);

use App\Enums\Settings\UserSettings;
use App\Livewire\User\Profile\UpdatePassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

test('user can update password with valid inputs', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('OldPassword123!'),
    ]);

    actingAs($user);

    Livewire::test(UpdatePassword::class)
        ->set('current_password', 'OldPassword123!')
        ->set('new_password', 'NewPassword123!')
        ->set('new_password_confirmation', 'NewPassword123!')
        ->call('save')
        ->assertHasNoErrors();

    $user->refresh();

    expect(Hash::check('NewPassword123!', $user->password))->toBeTrue();
});

test('current password is required', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(UpdatePassword::class)
        ->set('current_password', '')
        ->set('new_password', 'NewPassword123!')
        ->set('new_password_confirmation', 'NewPassword123!')
        ->call('save')
        ->assertHasErrors(['current_password' => 'required']);
});

test('current password must match', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('CorrectPassword123!'),
    ]);

    actingAs($user);

    Livewire::test(UpdatePassword::class)
        ->set('current_password', 'WrongPassword123!')
        ->set('new_password', 'NewPassword123!')
        ->set('new_password_confirmation', 'NewPassword123!')
        ->call('save')
        ->assertHasErrors(['current_password' => 'current_password']);
});

test('new password is required', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!'),
    ]);

    actingAs($user);

    Livewire::test(UpdatePassword::class)
        ->set('current_password', 'CurrentPassword123!')
        ->set('new_password', '')
        ->set('new_password_confirmation', '')
        ->call('save')
        ->assertHasErrors(['new_password' => 'required']);
});

test('new password must be confirmed', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!'),
    ]);

    actingAs($user);

    Livewire::test(UpdatePassword::class)
        ->set('current_password', 'CurrentPassword123!')
        ->set('new_password', 'NewPassword123!')
        ->set('new_password_confirmation', 'DifferentPassword123!')
        ->call('save')
        ->assertHasErrors(['new_password' => 'confirmed']);
});

test('new password must meet requirements', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!'),
    ]);

    actingAs($user);

    Livewire::test(UpdatePassword::class)
        ->set('current_password', 'CurrentPassword123!')
        ->set('new_password', 'weak')
        ->set('new_password_confirmation', 'weak')
        ->call('save')
        ->assertHasErrors(['new_password']);
});

test('password is properly hashed', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!'),
    ]);

    actingAs($user);

    Livewire::test(UpdatePassword::class)
        ->set('current_password', 'CurrentPassword123!')
        ->set('new_password', 'NewPassword123!')
        ->set('new_password_confirmation', 'NewPassword123!')
        ->call('save');

    $user->refresh();

    expect($user->password)->not->toBe('NewPassword123!')
        ->and(Hash::check('NewPassword123!', $user->password))->toBeTrue();
});

test('updates password_last_changed_at timestamp', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!'),
    ]);

    actingAs($user);

    Livewire::test(UpdatePassword::class)
        ->set('current_password', 'CurrentPassword123!')
        ->set('new_password', 'NewPassword123!')
        ->set('new_password_confirmation', 'NewPassword123!')
        ->call('save');

    $user->refresh();

    $setting = $user->settings->where('key', UserSettings::SECURITY)->first();

    expect($setting->value['password_last_changed_at'])->not->toBeNull();
});

test('form fields reset after successful save', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!'),
    ]);

    actingAs($user);

    Livewire::test(UpdatePassword::class)
        ->set('current_password', 'CurrentPassword123!')
        ->set('new_password', 'NewPassword123!')
        ->set('new_password_confirmation', 'NewPassword123!')
        ->call('save')
        ->assertSet('current_password', '')
        ->assertSet('new_password', '')
        ->assertSet('new_password_confirmation', '');
});

test('update password requires authentication', function () {
    Livewire::test(UpdatePassword::class)
        ->assertUnauthorized();
});

test('old password cannot be used after update', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('OldPassword123!'),
    ]);

    actingAs($user);

    Livewire::test(UpdatePassword::class)
        ->set('current_password', 'OldPassword123!')
        ->set('new_password', 'NewPassword123!')
        ->set('new_password_confirmation', 'NewPassword123!')
        ->call('save');

    $user->refresh();

    expect(Hash::check('OldPassword123!', $user->password))->toBeFalse()
        ->and(Hash::check('NewPassword123!', $user->password))->toBeTrue();
});

test('password last changed timestamp is ISO format', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!'),
    ]);

    actingAs($user);

    Livewire::test(UpdatePassword::class)
        ->set('current_password', 'CurrentPassword123!')
        ->set('new_password', 'NewPassword123!')
        ->set('new_password_confirmation', 'NewPassword123!')
        ->call('save');

    $user->refresh();

    $setting = $user->settings->where('key', UserSettings::SECURITY)->first();
    $timestamp = $setting->value['password_last_changed_at'];

    expect($timestamp)->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d+Z$/');
});

