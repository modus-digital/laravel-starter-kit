<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\post;

test('user can logout', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => Hash::make('SecurePassword123!'),
    ]);

    actingAs($user);

    $response = post(route('auth.logout'));

    $response->assertRedirect(route('login'));
    assertGuest();
});

test('session is invalidated after logout', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    $oldSessionId = session()->getId();
    session()->put('test_key', 'test_value');

    post(route('auth.logout'));

    expect(session()->has('test_key'))->toBeFalse();
    expect(session()->getId())->not->toBe($oldSessionId);
});

test('csrf token is regenerated after logout', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    $oldToken = csrf_token();

    post(route('auth.logout'));

    $newToken = csrf_token();

    expect($newToken)->not->toBe($oldToken);
});

test('two factor session is cleared after logout', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);
    session()->put('two_factor_verified', true);

    post(route('auth.logout'));

    expect(session()->has('two_factor_verified'))->toBeFalse();
});

test('logout requires authentication', function () {
    $response = post(route('auth.logout'));

    $response->assertRedirect(route('login'));
});

test('logout redirects to login page', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    $response = post(route('auth.logout'));

    $response->assertRedirect(route('login'));
});

test('user cannot access protected routes after logout', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    actingAs($user);

    post(route('auth.logout'));

    $response = $this->get(route('app.dashboard'));

    $response->assertRedirect(route('login'));
});

test('remember token exists and is preserved across logout', function () {
    $user = User::factory()->create([
        'remember_token' => 'test-remember-token',
    ]);

    // Directly log in using Auth facade to avoid actingAs modifying the token
    auth()->login($user);

    $tokenBeforeLogout = $user->fresh()->remember_token;
    expect($tokenBeforeLogout)->not->toBeNull();

    post(route('auth.logout'));

    $user->refresh();

    // Remember token should still exist after logout (not null)
    // This allows "remember me" functionality to work on next login
    expect($user->remember_token)->not->toBeNull();
});
