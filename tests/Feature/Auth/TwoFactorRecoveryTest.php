<?php

declare(strict_types=1);

use App\Enums\Settings\TwoFactor;
use App\Enums\Settings\TwoFactorProvider;
use App\Enums\Settings\UserSettings;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Volt;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    RateLimiter::clear('two-factor-recovery:1');
});

test('valid recovery code allows access', function () {
    /** @var User $user */
    $user = User::factory()->create(['email' => 'john@example.com']);

    $recoveryCodes = ['1234-5678', '8765-4321'];

    $setting = $user->settings()->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute(null, [
        'password_last_changed_at' => null,
        'two_factor' => [
            'status' => TwoFactor::ENABLED->value,
            'provider' => TwoFactorProvider::EMAIL->value,
            'secret' => null,
            'confirmed_at' => now()->toISOString(),
            'recovery_codes' => $recoveryCodes,
        ],
    ]);

    actingAs($user);

    $response = Volt::test('auth.two-factor.recover')
        ->set('recoveryCode', '1234-5678')
        ->call('recover');

    $response->assertRedirect(route('app.dashboard'));
    expect(session('two_factor_verified'))->toBeTrue();
});

test('recovery code is removed after use', function () {
    /** @var User $user */
    $user = User::factory()->create(['email' => 'john@example.com']);

    $recoveryCodes = ['1234-5678', '8765-4321'];

    $setting = $user->settings()->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute(null, [
        'password_last_changed_at' => null,
        'two_factor' => [
            'status' => TwoFactor::ENABLED->value,
            'provider' => TwoFactorProvider::EMAIL->value,
            'secret' => null,
            'confirmed_at' => now()->toISOString(),
            'recovery_codes' => $recoveryCodes,
        ],
    ]);

    actingAs($user);

    Volt::test('auth.two-factor.recover')
        ->set('recoveryCode', '1234-5678')
        ->call('recover');

    $user->refresh();
    $settings = $user->settings()->where('key', UserSettings::SECURITY)->first();
    $twoFactorSettings = $settings->retrieve(UserSettings::SECURITY, 'two_factor');

    expect($twoFactorSettings['recovery_codes'])->not->toContain('1234-5678');
    expect($twoFactorSettings['recovery_codes'])->toContain('8765-4321');
});

test('invalid recovery code is rejected', function () {
    /** @var User $user */
    $user = User::factory()->create(['email' => 'john@example.com']);

    $setting = $user->settings()->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute(null, [
        'password_last_changed_at' => null,
        'two_factor' => [
            'status' => TwoFactor::ENABLED->value,
            'provider' => TwoFactorProvider::EMAIL->value,
            'secret' => null,
            'confirmed_at' => now()->toISOString(),
            'recovery_codes' => ['1234-5678', '8765-4321'],
        ],
    ]);

    actingAs($user);

    $response = Volt::test('auth.two-factor.recover')
        ->set('recoveryCode', '9999-9999')
        ->call('recover');

    $response->assertHasErrors(['recoveryCode']);
    expect(session('two_factor_verified'))->toBeFalsy();
});

test('recovery code with dash formatting works', function () {
    /** @var User $user */
    $user = User::factory()->create(['email' => 'john@example.com']);

    $setting = $user->settings()->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute(null, [
        'password_last_changed_at' => null,
        'two_factor' => [
            'status' => TwoFactor::ENABLED->value,
            'provider' => TwoFactorProvider::EMAIL->value,
            'secret' => null,
            'confirmed_at' => now()->toISOString(),
            'recovery_codes' => ['1234-5678', '8765-4321'], // Multiple codes to avoid empty array
        ],
    ]);

    actingAs($user);

    $response = Volt::test('auth.two-factor.recover')
        ->set('recoveryCode', '1234-5678')
        ->call('recover');

    $response->assertRedirect(route('app.dashboard'));
});

test('recovery code without dash works and is normalized', function () {
    /** @var User $user */
    $user = User::factory()->create(['email' => 'john@example.com']);

    $setting = $user->settings()->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute(null, [
        'password_last_changed_at' => null,
        'two_factor' => [
            'status' => TwoFactor::ENABLED->value,
            'provider' => TwoFactorProvider::EMAIL->value,
            'secret' => null,
            'confirmed_at' => now()->toISOString(),
            'recovery_codes' => ['1234-5678', '8765-4321'], // Multiple codes to avoid empty array
        ],
    ]);

    actingAs($user);

    $response = Volt::test('auth.two-factor.recover')
        ->set('recoveryCode', '12345678')
        ->call('recover');

    $response->assertRedirect(route('app.dashboard'));
});

test('recovery enforces rate limiting after 3 failed attempts', function () {
    RateLimiter::clear('two-factor-recovery:1');

    /** @var User $user */
    $user = User::factory()->create(['email' => 'john@example.com']);

    $setting = $user->settings()->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute(null, [
        'password_last_changed_at' => null,
        'two_factor' => [
            'status' => TwoFactor::ENABLED->value,
            'provider' => TwoFactorProvider::EMAIL->value,
            'secret' => null,
            'confirmed_at' => now()->toISOString(),
            'recovery_codes' => ['1234-5678'],
        ],
    ]);

    actingAs($user);

    for ($i = 0; $i < 3; $i++) {
        Volt::test('auth.two-factor.recover')
            ->set('recoveryCode', '9999-9999')
            ->call('recover');
    }

    $response = Volt::test('auth.two-factor.recover')
        ->set('recoveryCode', '1234-5678')
        ->call('recover');

    $response->assertHasErrors(['recoveryCode']);
    expect($response->errors()->first('recoveryCode'))->toContain('Too many');

    RateLimiter::clear('two-factor-recovery:1');
});

test('recovery handles missing settings gracefully', function () {
    /** @var User $user */
    $user = User::factory()->create(['email' => 'john@example.com']);

    actingAs($user);

    $response = Volt::test('auth.two-factor.recover')
        ->set('recoveryCode', '1234-5678')
        ->call('recover');

    $response->assertHasErrors(['recoveryCode']);
});

test('recovery requires recovery code field', function () {
    /** @var User $user */
    $user = User::factory()->create(['email' => 'john@example.com']);

    $setting = $user->settings()->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute(null, [
        'password_last_changed_at' => null,
        'two_factor' => [
            'status' => TwoFactor::ENABLED->value,
            'provider' => TwoFactorProvider::EMAIL->value,
            'secret' => null,
            'confirmed_at' => now()->toISOString(),
            'recovery_codes' => ['1234-5678'],
        ],
    ]);

    actingAs($user);

    $response = Volt::test('auth.two-factor.recover')
        ->set('recoveryCode', '')
        ->call('recover');

    $response->assertHasErrors(['recoveryCode']);
});

test('recovery redirects to login if user is not authenticated', function () {
    $response = Volt::test('auth.two-factor.recover')
        ->set('recoveryCode', '1234-5678')
        ->call('recover');

    $response->assertRedirect(route('login'));
});

test('rate limit is cleared after successful recovery', function () {
    RateLimiter::clear('two-factor-recovery:1');

    /** @var User $user */
    $user = User::factory()->create(['email' => 'john@example.com']);

    $setting = $user->settings()->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute(null, [
        'password_last_changed_at' => null,
        'two_factor' => [
            'status' => TwoFactor::ENABLED->value,
            'provider' => TwoFactorProvider::EMAIL->value,
            'secret' => null,
            'confirmed_at' => now()->toISOString(),
            'recovery_codes' => ['1234-5678', '8765-4321'],
        ],
    ]);

    actingAs($user);

    // Make 2 failed attempts
    for ($i = 0; $i < 2; $i++) {
        Volt::test('auth.two-factor.recover')
            ->set('recoveryCode', '9999-9999')
            ->call('recover');
    }

    // Successful recovery should clear rate limiter
    Volt::test('auth.two-factor.recover')
        ->set('recoveryCode', '1234-5678')
        ->call('recover');

    expect(RateLimiter::attempts('two-factor-recovery:1'))->toBe(0);

    RateLimiter::clear('two-factor-recovery:1');
});
