<?php

declare(strict_types=1);

use App\Enums\Settings\TwoFactor;
use App\Enums\Settings\TwoFactorProvider;
use App\Enums\Settings\UserSettings;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('redirects to 2fa verify when enabled and not verified', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => now(),
    ]);

    $setting = $user->settings()->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute(null, [
        'password_last_changed_at' => null,
        'two_factor' => [
            'status' => TwoFactor::ENABLED->value,
            'provider' => TwoFactorProvider::EMAIL->value,
            'secret' => null,
            'confirmed_at' => now()->toISOString(),
            'recovery_codes' => ['dummy-code-1', 'dummy-code-2'],
        ],
    ]);

    actingAs($user);

    $response = get(route('app.dashboard'));

    $response->assertRedirect(route('two-factor.verify'));
});

test('allows access when 2fa verified', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => now(),
    ]);

    $setting = $user->settings()->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute(null, [
        'password_last_changed_at' => null,
        'two_factor' => [
            'status' => TwoFactor::ENABLED->value,
            'provider' => TwoFactorProvider::EMAIL->value,
            'secret' => null,
            'confirmed_at' => now()->toISOString(),
            'recovery_codes' => ['dummy-code-1', 'dummy-code-2'],
        ],
    ]);

    actingAs($user);
    session()->put('two_factor_verified', true);

    $response = get(route('app.dashboard'));

    $response->assertOk();
});

test('allows access when 2fa disabled', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => now(),
    ]);

    $setting = $user->settings()->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute(null, [
        'password_last_changed_at' => null,
        'two_factor' => [
            'status' => TwoFactor::DISABLED->value,
            'provider' => null,
            'secret' => null,
            'confirmed_at' => null,
            'recovery_codes' => [],
        ],
    ]);

    actingAs($user);

    $response = get(route('app.dashboard'));

    $response->assertOk();
});

test('allows 2fa routes without verification', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
    ]);

    $setting = $user->settings()->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute(null, [
        'password_last_changed_at' => null,
        'two_factor' => [
            'status' => TwoFactor::ENABLED->value,
            'provider' => TwoFactorProvider::EMAIL->value,
            'secret' => null,
            'confirmed_at' => now()->toISOString(),
            'recovery_codes' => ['dummy-code-1', 'dummy-code-2'],
        ],
    ]);

    actingAs($user);

    $response = get(route('two-factor.verify'));
    $response->assertOk();

    $response = get(route('two-factor.recover'));
    $response->assertOk();
});

test('allows email verification routes without 2fa verification', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => null,
    ]);

    $setting = $user->settings()->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute(null, [
        'password_last_changed_at' => null,
        'two_factor' => [
            'status' => TwoFactor::ENABLED->value,
            'provider' => TwoFactorProvider::EMAIL->value,
            'secret' => null,
            'confirmed_at' => now()->toISOString(),
            'recovery_codes' => ['dummy-code-1', 'dummy-code-2'],
        ],
    ]);

    actingAs($user);

    $response = get(route('verification.notice'));

    $response->assertOk();
});

test('allows logout post request', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => now(),
    ]);

    $setting = $user->settings()->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute(null, [
        'password_last_changed_at' => null,
        'two_factor' => [
            'status' => TwoFactor::ENABLED->value,
            'provider' => TwoFactorProvider::EMAIL->value,
            'secret' => null,
            'confirmed_at' => now()->toISOString(),
            'recovery_codes' => ['dummy-code-1', 'dummy-code-2'],
        ],
    ]);

    actingAs($user);

    $response = $this->post(route('auth.logout'));

    $response->assertRedirect(route('login'));
});

test('bypasses 2fa when impersonating', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => now(),
    ]);

    $setting = $user->settings()->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute(null, [
        'password_last_changed_at' => null,
        'two_factor' => [
            'status' => TwoFactor::ENABLED->value,
            'provider' => TwoFactorProvider::EMAIL->value,
            'secret' => null,
            'confirmed_at' => now()->toISOString(),
            'recovery_codes' => ['dummy-code-1', 'dummy-code-2'],
        ],
    ]);

    actingAs($user);
    session()->put('impersonate', true);

    $response = get(route('app.dashboard'));

    $response->assertOk();
});

test('bypasses 2fa with can_bypass_two_factor session flag', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => now(),
    ]);

    $setting = $user->settings()->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute(null, [
        'password_last_changed_at' => null,
        'two_factor' => [
            'status' => TwoFactor::ENABLED->value,
            'provider' => TwoFactorProvider::EMAIL->value,
            'secret' => null,
            'confirmed_at' => now()->toISOString(),
            'recovery_codes' => ['dummy-code-1', 'dummy-code-2'],
        ],
    ]);

    actingAs($user);
    session()->put('can_bypass_two_factor', true);

    $response = get(route('app.dashboard'));

    $response->assertOk();
});

test('does not bypass random post routes', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => now(),
    ]);

    $setting = $user->settings()->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute(null, [
        'password_last_changed_at' => null,
        'two_factor' => [
            'status' => TwoFactor::ENABLED->value,
            'provider' => TwoFactorProvider::EMAIL->value,
            'secret' => null,
            'confirmed_at' => now()->toISOString(),
            'recovery_codes' => ['dummy-code-1', 'dummy-code-2'],
        ],
    ]);

    actingAs($user);

    // GET request to dashboard should redirect to 2FA
    $response = get(route('app.dashboard'));

    $response->assertRedirect(route('two-factor.verify'));
});

test('allows access when user has no 2fa settings', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => now(),
    ]);

    actingAs($user);

    $response = get(route('app.dashboard'));

    $response->assertOk();
});
