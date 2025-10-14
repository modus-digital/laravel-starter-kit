<?php

declare(strict_types=1);

use App\Enums\Settings\TwoFactor;
use App\Enums\Settings\TwoFactorProvider;
use App\Enums\Settings\UserSettings;
use App\Models\User;
use App\Notifications\Auth\TwoFactorVerification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Volt;
use PragmaRX\Google2FA\Google2FA;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    RateLimiter::clear('two-factor-email:1');
});

test('email 2fa code is sent when user has email 2fa enabled', function () {
    Notification::fake();

    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => Hash::make('SecurePassword123!'),
    ]);

    // Update the existing SECURITY setting created by HasSettings trait
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

    Volt::test('auth.two-factor.verify');

    Notification::assertSentTo($user, TwoFactorVerification::class);
});

test('valid email 2fa code allows access', function () {
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

    $code = '123456';
    Cache::put("two_factor:login:email:code:{$user->id}", $code, now()->addMinutes(10));
    Cache::put("two_factor:login:email:attempts:{$user->id}", 0, now()->addMinutes(10));

    actingAs($user);

    $response = Volt::test('auth.two-factor.verify')
        ->set('code', $code)
        ->call('verify');

    $response->assertRedirect(route('app.dashboard'));
    expect(session('two_factor_verified'))->toBeTrue();
});

test('invalid email 2fa code is rejected', function () {
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

    $code = '123456';
    Cache::put("two_factor:login:email:code:{$user->id}", $code, now()->addMinutes(10));
    Cache::put("two_factor:login:email:attempts:{$user->id}", 0, now()->addMinutes(10));

    actingAs($user);

    $response = Volt::test('auth.two-factor.verify')
        ->set('code', '999999')
        ->call('verify');

    $response->assertHasErrors(['code']);
    expect(session('two_factor_verified'))->toBeFalsy();
});

test('expired email 2fa code is rejected', function () {
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

    $response = Volt::test('auth.two-factor.verify')
        ->set('code', '123456')
        ->call('verify');

    $response->assertHasErrors(['code']);
    expect($response->errors()->first('code'))->toContain('invalid');
});

test('email 2fa enforces rate limiting after 3 failed attempts', function () {
    RateLimiter::clear('two-factor-email:1');

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

    $code = '123456';
    Cache::put("two_factor:login:email:code:{$user->id}", $code, now()->addMinutes(10));
    Cache::put("two_factor:login:email:attempts:{$user->id}", 0, now()->addMinutes(10));

    actingAs($user);

    for ($i = 0; $i < 3; $i++) {
        Volt::test('auth.two-factor.verify')
            ->set('code', '999999')
            ->call('verify');
    }

    $response = Volt::test('auth.two-factor.verify')
        ->set('code', $code)
        ->call('verify');

    $response->assertHasErrors(['code']);
    expect($response->errors()->first('code'))->toContain('Too many');

    RateLimiter::clear('two-factor-email:1');
});

test('email 2fa resend functionality works', function () {
    Notification::fake();

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

    Volt::test('auth.two-factor.verify')
        ->call('resend');

    Notification::assertSentTo($user, TwoFactorVerification::class);
});

test('authenticator 2fa with valid totp code allows access', function () {
    $google2FA = new Google2FA();
    $secret = $google2FA->generateSecretKey();
    $validCode = $google2FA->getCurrentOtp($secret);

    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
    ]);

    $setting = $user->settings()->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute(null, [
        'password_last_changed_at' => null,
        'two_factor' => [
            'status' => TwoFactor::ENABLED->value,
            'provider' => TwoFactorProvider::AUTHENTICATOR->value,
            'secret' => $secret,
            'confirmed_at' => now()->toISOString(),
            'recovery_codes' => ['dummy-code-1', 'dummy-code-2'],
        ],
    ]);

    actingAs($user);

    $response = Volt::test('auth.two-factor.verify')
        ->set('code', $validCode)
        ->call('verify');

    $response->assertRedirect(route('app.dashboard'));
    expect(session('two_factor_verified'))->toBeTrue();
});

test('authenticator 2fa with invalid code is rejected', function () {
    $google2FA = new Google2FA();
    $secret = $google2FA->generateSecretKey();

    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
    ]);

    $setting = $user->settings()->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute(null, [
        'password_last_changed_at' => null,
        'two_factor' => [
            'status' => TwoFactor::ENABLED->value,
            'provider' => TwoFactorProvider::AUTHENTICATOR->value,
            'secret' => $secret,
            'confirmed_at' => now()->toISOString(),
            'recovery_codes' => ['dummy-code-1', 'dummy-code-2'],
        ],
    ]);

    actingAs($user);

    $response = Volt::test('auth.two-factor.verify')
        ->set('code', '000000')
        ->call('verify');

    $response->assertHasErrors(['code']);
    expect(session('two_factor_verified'))->toBeFalsy();
});

test('authenticator 2fa enforces time-based rate limiting', function () {
    $google2FA = new Google2FA();
    $secret = $google2FA->generateSecretKey();

    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
    ]);

    $setting = $user->settings()->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute(null, [
        'password_last_changed_at' => null,
        'two_factor' => [
            'status' => TwoFactor::ENABLED->value,
            'provider' => TwoFactorProvider::AUTHENTICATOR->value,
            'secret' => $secret,
            'confirmed_at' => now()->toISOString(),
            'recovery_codes' => ['dummy-code-1', 'dummy-code-2'],
        ],
    ]);

    actingAs($user);

    $component = Volt::test('auth.two-factor.verify')
        ->set('code', '000000')
        ->call('verify');

    $component->assertHasErrors(['code']);

    // Try again immediately (within 15 seconds)
    $component = Volt::test('auth.two-factor.verify')
        ->set('code', '000000')
        ->call('verify');

    $component->assertHasErrors(['code']);
    expect($component->errors()->first('code'))->toContain('invalid');
});

test('2fa redirects to login if user is not authenticated', function () {
    $response = Volt::test('auth.two-factor.verify');

    $response->assertRedirect(route('login'));
});

test('2fa redirects to dashboard if 2fa is not enabled', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
    ]);

    $setting = $user->settings()->where('key', UserSettings::SECURITY)->first();
    $setting->updateValueAttribute(null, [
        'password_last_changed_at' => null,
        'two_factor' => [
            'status' => TwoFactor::DISABLED->value,
            'provider' => null,
            'secret' => null,
            'confirmed_at' => null,
            'recovery_codes' => ['dummy-code-1', 'dummy-code-2'],
        ],
    ]);

    actingAs($user);

    $response = Volt::test('auth.two-factor.verify');

    $response->assertRedirect(route('app.dashboard'));
});
