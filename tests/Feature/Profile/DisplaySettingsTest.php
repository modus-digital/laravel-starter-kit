<?php

declare(strict_types=1);

use App\Enums\Settings\UserSettings;
use App\Livewire\User\Profile\Display;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

test('user can update appearance setting', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(Display::class)
        ->set('appearance', 'dark')
        ->call('save')
        ->assertDispatched('reload-page');

    $user->refresh();

    $setting = $user->settings->where('key', UserSettings::DISPLAY)->first();

    expect($setting->value['appearance'])->toBe('dark');
});

test('user can update theme setting', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(Display::class)
        ->set('theme', 'purple')
        ->call('save')
        ->assertDispatched('reload-page');

    $user->refresh();

    $setting = $user->settings->where('key', UserSettings::DISPLAY)->first();

    expect($setting->value['theme'])->toBe('purple');
});

test('user can update both appearance and theme together', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(Display::class)
        ->set('appearance', 'light')
        ->set('theme', 'green')
        ->call('save')
        ->assertDispatched('reload-page');

    $user->refresh();

    $setting = $user->settings->where('key', UserSettings::DISPLAY)->first();

    expect($setting->value['appearance'])->toBe('light')
        ->and($setting->value['theme'])->toBe('green');
});

test('component mounts with existing display settings', function () {
    /** @var User $user */
    $user = User::factory()->create();

    // Update display settings
    $setting = $user->settings->where('key', UserSettings::DISPLAY)->first();
    $setting->updateValueAttribute(null, [
        'appearance' => 'dark',
        'theme' => 'red',
    ]);

    actingAs($user);

    Livewire::test(Display::class)
        ->assertSet('appearance', 'dark')
        ->assertSet('theme', 'red');
});

test('component mounts with default values when no settings exist', function () {
    /** @var User $user */
    $user = User::factory()->create();

    // Clear display settings
    $setting = $user->settings->where('key', UserSettings::DISPLAY)->first();
    $setting->updateValueAttribute(null, []);

    actingAs($user);

    Livewire::test(Display::class)
        ->assertSet('appearance', 'system')
        ->assertSet('theme', 'blue');
});

test('display settings persist correctly to database', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(Display::class)
        ->set('appearance', 'dark')
        ->set('theme', 'orange')
        ->call('save');

    $user->refresh();

    $setting = $user->settings->where('key', UserSettings::DISPLAY)->first();

    expect($setting)->not->toBeNull()
        ->and($setting->key)->toBe(UserSettings::DISPLAY)
        ->and($setting->value)->toHaveKey('appearance', 'dark')
        ->and($setting->value)->toHaveKey('theme', 'orange');
});

test('display settings require authentication', function () {
    Livewire::test(Display::class)
        ->assertUnauthorized();
});

test('dispatches reload page event after save', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(Display::class)
        ->set('appearance', 'system')
        ->call('save')
        ->assertDispatched('reload-page');
});

test('can update appearance to system', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(Display::class)
        ->set('appearance', 'system')
        ->call('save');

    $user->refresh();

    $setting = $user->settings->where('key', UserSettings::DISPLAY)->first();

    expect($setting->value['appearance'])->toBe('system');
});
