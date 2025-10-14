<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Livewire\Volt\Volt;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    RateLimiter::clear('email-verification:1');
});

test('verification email is sent after registration', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => null,
    ]);

    $user->sendEmailVerificationNotification();

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('user can verify email with valid link', function () {
    Event::fake();

    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => null,
    ]);

    actingAs($user);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = get($verificationUrl);

    $response->assertRedirect(route('app.dashboard'));

    $user->refresh();
    expect($user->email_verified_at)->not->toBeNull();

    Event::assertDispatched(Verified::class);
});

test('verification fails with invalid signature', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => null,
    ]);

    actingAs($user);

    $response = get(route('verification.verify', [
        'id' => $user->id,
        'hash' => 'invalid-hash',
    ]));

    $response->assertStatus(403);

    $user->refresh();
    expect($user->email_verified_at)->toBeNull();
});

test('already verified user is redirected', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => now(),
    ]);

    actingAs($user);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = get($verificationUrl);

    $response->assertRedirect(route('app.dashboard'));
});

test('protected routes require verification', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => null,
    ]);

    actingAs($user);

    $response = get(route('app.dashboard'));

    $response->assertRedirect(route('verification.notice'));
});

test('verified users can access protected routes', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => now(),
    ]);

    actingAs($user);

    $response = get(route('app.dashboard'));

    $response->assertOk();
});

test('resend verification email works', function () {
    Notification::fake();

    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => null,
    ]);

    actingAs($user);

    $response = Volt::test('auth.verify-email')
        ->call('resend');

    // Verify no errors and notification was sent
    $response->assertHasNoErrors();
    Notification::assertSentTo($user, VerifyEmail::class);
});

test('resend enforces rate limiting after 3 attempts', function () {
    RateLimiter::clear('email-verification:1');

    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => null,
    ]);

    actingAs($user);

    for ($i = 0; $i < 3; $i++) {
        Volt::test('auth.verify-email')->call('resend');
    }

    $response = Volt::test('auth.verify-email')->call('resend');

    $response->assertHasErrors(['email']);
    expect($response->errors()->first('email'))->toContain('Too many');

    RateLimiter::clear('email-verification:1');
});

test('verified users are redirected from verification notice', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => now(),
    ]);

    actingAs($user);

    $response = Volt::test('auth.verify-email')->call('resend');

    $response->assertRedirect(route('app.dashboard'));
});

test('email verification url expires', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => null,
    ]);

    actingAs($user);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->subMinutes(10), // Expired
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = get($verificationUrl);

    $response->assertStatus(403);
});

test('verification requires authentication', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'email_verified_at' => null,
    ]);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = get($verificationUrl);

    $response->assertRedirect(route('login'));
});
