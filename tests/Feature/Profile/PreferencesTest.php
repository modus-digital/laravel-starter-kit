<?php

declare(strict_types=1);

use App\Enums\Settings\UserSettings;
use App\Livewire\User\Profile\Preferences;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

test('user can update locale setting', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(Preferences::class)
        ->set('locale', 'es')
        ->call('save');

    $user->refresh();

    $setting = $user->settings->where('key', UserSettings::LOCALIZATION)->first();

    expect($setting->value['locale'])->toBe('es');
});

test('user can update timezone setting', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(Preferences::class)
        ->set('timezone', 'America/New_York')
        ->call('save');

    $user->refresh();

    $setting = $user->settings->where('key', UserSettings::LOCALIZATION)->first();

    expect($setting->value['timezone'])->toBe('America/New_York');
});

test('user can update date format setting', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(Preferences::class)
        ->set('date_format', 'd/m/Y')
        ->call('save');

    $user->refresh();

    $setting = $user->settings->where('key', UserSettings::LOCALIZATION)->first();

    expect($setting->value['date_format'])->toBe('d/m/Y');
});

test('user can update time format setting', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(Preferences::class)
        ->set('time_format', 'h:i A')
        ->call('save');

    $user->refresh();

    $setting = $user->settings->where('key', UserSettings::LOCALIZATION)->first();

    expect($setting->value['time_format'])->toBe('h:i A');
});

test('user can update all preferences together', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(Preferences::class)
        ->set('locale', 'fr')
        ->set('timezone', 'Europe/Paris')
        ->set('date_format', 'd/m/Y')
        ->set('time_format', 'H:i')
        ->call('save');

    $user->refresh();

    $setting = $user->settings->where('key', UserSettings::LOCALIZATION)->first();

    expect($setting->value['locale'])->toBe('fr')
        ->and($setting->value['timezone'])->toBe('Europe/Paris')
        ->and($setting->value['date_format'])->toBe('d/m/Y')
        ->and($setting->value['time_format'])->toBe('H:i');
});

test('component mounts with existing preferences', function () {
    /** @var User $user */
    $user = User::factory()->create();

    // Update preferences
    $setting = $user->settings->where('key', UserSettings::LOCALIZATION)->first();
    $setting->updateValueAttribute(null, [
        'locale' => 'es',
        'timezone' => 'America/Los_Angeles',
        'date_format' => 'm/d/Y',
        'time_format' => 'h:i A',
    ]);

    actingAs($user);

    Livewire::test(Preferences::class)
        ->assertSet('locale', 'es')
        ->assertSet('timezone', 'America/Los_Angeles')
        ->assertSet('date_format', 'm/d/Y')
        ->assertSet('time_format', 'h:i A');
});

test('component mounts with default values when no settings exist', function () {
    /** @var User $user */
    $user = User::factory()->create();

    // Clear localization settings
    $setting = $user->settings->where('key', UserSettings::LOCALIZATION)->first();
    $setting->updateValueAttribute(null, []);

    actingAs($user);

    Livewire::test(Preferences::class)
        ->assertSet('locale', 'en')
        ->assertSet('timezone', 'UTC')
        ->assertSet('date_format', 'Y-m-d')
        ->assertSet('time_format', 'H:i');
});

test('settings persist to LOCALIZATION key', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(Preferences::class)
        ->set('locale', 'de')
        ->set('timezone', 'Europe/Berlin')
        ->call('save');

    $user->refresh();

    $setting = $user->settings->where('key', UserSettings::LOCALIZATION)->first();

    expect($setting)->not->toBeNull()
        ->and($setting->key)->toBe(UserSettings::LOCALIZATION);
});

test('preferences require authentication', function () {
    Livewire::test(Preferences::class)
        ->assertUnauthorized();
});

test('date and time format arrays are populated on mount', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    $component = Livewire::test(Preferences::class);

    expect($component->get('dateFormats'))->toBeArray()
        ->toHaveKeys(['m/d/Y', 'd/m/Y', 'Y-m-d'])
        ->and($component->get('timeFormats'))->toBeArray()
        ->toHaveKeys(['h:i A', 'H:i']);
});

test('can save preferences with default values', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(Preferences::class)
        ->call('save');

    $user->refresh();

    $setting = $user->settings->where('key', UserSettings::LOCALIZATION)->first();

    expect($setting->value)->toHaveKey('locale')
        ->toHaveKey('timezone')
        ->toHaveKey('date_format')
        ->toHaveKey('time_format');
});
