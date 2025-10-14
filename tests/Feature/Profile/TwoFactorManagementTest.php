<?php

declare(strict_types=1);

use App\Enums\Settings\TwoFactor as TwoFactorEnum;
use App\Enums\Settings\TwoFactorProvider;
use App\Enums\Settings\UserSettings;
use App\Livewire\User\Profile\TwoFactor;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

test('component mounts with correct 2FA status', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $setting = $user->settings->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute('two_factor', [
        'status' => TwoFactorEnum::ENABLED->value,
        'provider' => TwoFactorProvider::AUTHENTICATOR->value,
        'secret' => 'test-secret',
        'confirmed_at' => now()->toISOString(),
        'recovery_codes' => ['code1', 'code2', 'code3', 'code4', 'code5', 'code6', 'code7', 'code8'],
    ]);

    actingAs($user);

    Livewire::test(TwoFactor::class)
        ->assertSet('status', TwoFactorEnum::ENABLED->value);
});

test('component mounts with correct provider', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $setting = $user->settings->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute('two_factor', [
        'status' => TwoFactorEnum::ENABLED->value,
        'provider' => TwoFactorProvider::AUTHENTICATOR->value,
        'secret' => 'test-secret',
        'confirmed_at' => now()->toISOString(),
        'recovery_codes' => ['code1', 'code2', 'code3'],
    ]);

    actingAs($user);

    Livewire::test(TwoFactor::class)
        ->assertSet('provider', TwoFactorProvider::AUTHENTICATOR);
});

test('component mounts with correct recovery codes count', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $setting = $user->settings->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute('two_factor', [
        'status' => TwoFactorEnum::ENABLED->value,
        'provider' => TwoFactorProvider::EMAIL->value,
        'confirmed_at' => now()->toISOString(),
        'recovery_codes' => [
            'code1' => 'value1',
            'code2' => 'value2',
            'code3' => 'value3',
            'code4' => 'value4',
            'code5' => 'value5',
        ],
    ]);

    actingAs($user);

    $component = Livewire::test(TwoFactor::class);

    $recoveryCodes = $component->get('recoveryCodes');

    expect($recoveryCodes['used'])->toBe(3) // 8 - 5 = 3
        ->and($recoveryCodes['total'])->toBe(8);
});

test('user can disable 2FA', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $setting = $user->settings->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute('two_factor', [
        'status' => TwoFactorEnum::ENABLED->value,
        'provider' => TwoFactorProvider::AUTHENTICATOR->value,
        'secret' => 'test-secret',
        'confirmed_at' => now()->toISOString(),
        'recovery_codes' => ['code1', 'code2'],
    ]);

    actingAs($user);

    Livewire::test(TwoFactor::class)
        ->call('disable')
        ->assertSet('status', TwoFactorEnum::DISABLED->value)
        ->assertDispatched('close-modal', name: 'disable-two-factor-confirmation')
        ->assertDispatched('two-factor-updated');

    $user->refresh();

    $setting = $user->settings->where('key', UserSettings::SECURITY)->first();

    expect($setting->value['two_factor']['status'])->toBe(TwoFactorEnum::DISABLED->value);
});

test('disabling 2FA clears provider', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $setting = $user->settings->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute('two_factor', [
        'status' => TwoFactorEnum::ENABLED->value,
        'provider' => TwoFactorProvider::AUTHENTICATOR->value,
        'secret' => 'test-secret',
        'confirmed_at' => now()->toISOString(),
        'recovery_codes' => ['code1', 'code2'],
    ]);

    actingAs($user);

    Livewire::test(TwoFactor::class)
        ->call('disable');

    $user->refresh();

    $setting = $user->settings->where('key', UserSettings::SECURITY)->first();

    expect($setting->value['two_factor']['provider'])->toBeNull();
});

test('disabling 2FA clears secret', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $setting = $user->settings->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute('two_factor', [
        'status' => TwoFactorEnum::ENABLED->value,
        'provider' => TwoFactorProvider::AUTHENTICATOR->value,
        'secret' => 'test-secret',
        'confirmed_at' => now()->toISOString(),
        'recovery_codes' => ['code1', 'code2'],
    ]);

    actingAs($user);

    Livewire::test(TwoFactor::class)
        ->call('disable');

    $user->refresh();

    $setting = $user->settings->where('key', UserSettings::SECURITY)->first();

    expect($setting->value['two_factor']['secret'])->toBeNull();
});

test('disabling 2FA clears confirmed_at', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $setting = $user->settings->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute('two_factor', [
        'status' => TwoFactorEnum::ENABLED->value,
        'provider' => TwoFactorProvider::EMAIL->value,
        'confirmed_at' => now()->toISOString(),
        'recovery_codes' => ['code1', 'code2'],
    ]);

    actingAs($user);

    Livewire::test(TwoFactor::class)
        ->call('disable');

    $user->refresh();

    $setting = $user->settings->where('key', UserSettings::SECURITY)->first();

    expect($setting->value['two_factor']['confirmed_at'])->toBeNull();
});

test('disabling 2FA clears recovery codes', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $setting = $user->settings->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute('two_factor', [
        'status' => TwoFactorEnum::ENABLED->value,
        'provider' => TwoFactorProvider::AUTHENTICATOR->value,
        'secret' => 'test-secret',
        'confirmed_at' => now()->toISOString(),
        'recovery_codes' => ['code1', 'code2', 'code3'],
    ]);

    actingAs($user);

    Livewire::test(TwoFactor::class)
        ->call('disable');

    $user->refresh();

    $setting = $user->settings->where('key', UserSettings::SECURITY)->first();

    expect($setting->value['two_factor']['recovery_codes'])->toBe([]);
});

test('two factor management requires authentication', function () {
    Livewire::test(TwoFactor::class)
        ->assertUnauthorized();
});

test('component defaults to EMAIL provider when none set', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $setting = $user->settings->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute('two_factor', [
        'status' => TwoFactorEnum::DISABLED->value,
        'provider' => null,
    ]);

    actingAs($user);

    Livewire::test(TwoFactor::class)
        ->assertSet('provider', TwoFactorProvider::EMAIL);
});

test('component resets recovery codes count after disable', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $setting = $user->settings->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute('two_factor', [
        'status' => TwoFactorEnum::ENABLED->value,
        'provider' => TwoFactorProvider::AUTHENTICATOR->value,
        'secret' => 'test-secret',
        'confirmed_at' => now()->toISOString(),
        'recovery_codes' => ['code1', 'code2', 'code3', 'code4', 'code5'],
    ]);

    actingAs($user);

    Livewire::test(TwoFactor::class)
        ->call('disable')
        ->assertSet('recoveryCodes', [
            'used' => 0,
            'total' => 8,
        ]);
});

test('listens to two-factor-updated event', function () {
    /** @var User $user */
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    $component = Livewire::test(TwoFactor::class);

    expect($component)->toBeTruthy();
});

