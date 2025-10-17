<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use ModusDigital\SocialAuthentication\Enums\AuthenticationProvider;
use ModusDigital\SocialAuthentication\Models\SocialiteProvider;

use function Pest\Laravel\get;
use function Pest\Laravel\seed;

uses()->group('socialite');

beforeEach(function () {
    seed(ModusDigital\SocialAuthentication\Database\Seeders\SocialiteProvidersSeeder::class);
});

test('can redirect to socialite provider', function () {
    $provider = SocialiteProvider::where('provider', AuthenticationProvider::GOOGLE)->first();
    $provider->update([
        'is_enabled' => true,
        'client_id' => 'test-client-id',
        'client_secret' => 'test-secret',
        'redirect_uri' => 'https://example.com/callback',
    ]);

    Socialite::shouldReceive('driver')
        ->once()
        ->with('google')
        ->andReturnSelf();

    Socialite::shouldReceive('redirect')
        ->once()
        ->andReturn(redirect('https://google.com/oauth'));

    get(route('auth.socialite.redirect', 'google'))
        ->assertRedirect();
});

test('cannot redirect to disabled provider', function () {
    $provider = SocialiteProvider::where('provider', AuthenticationProvider::GOOGLE)->first();
    $provider->update(['is_enabled' => false]);

    get(route('auth.socialite.redirect', 'google'))
        ->assertNotFound();
});

test('cannot redirect to unconfigured provider', function () {
    $provider = SocialiteProvider::where('provider', AuthenticationProvider::GOOGLE)->first();
    $provider->update([
        'is_enabled' => true,
        'client_id' => null,
    ]);

    get(route('auth.socialite.redirect', 'google'))
        ->assertNotFound();
});

test('can authenticate with existing user', function () {
    $provider = SocialiteProvider::where('provider', AuthenticationProvider::GOOGLE)->first();
    $provider->update([
        'is_enabled' => true,
        'client_id' => 'test-client-id',
        'client_secret' => 'test-secret',
        'redirect_uri' => 'https://example.com/callback',
    ]);

    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('google-123');
    $socialiteUser->shouldReceive('getEmail')->andReturn('test@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('Test User');
    $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');
    $socialiteUser->token = 'token-123';
    $socialiteUser->refreshToken = 'refresh-token-123';

    Socialite::shouldReceive('driver')
        ->with('google')
        ->andReturnSelf();

    Socialite::shouldReceive('user')
        ->andReturn($socialiteUser);

    get(route('auth.socialite.callback', 'google'))
        ->assertRedirect(route('app.home'));

    expect(Auth::id())->toBe($user->id);

    $user->refresh();
    expect($user->provider)->toBe('google');
});

test('can create new user from socialite', function () {
    $provider = SocialiteProvider::where('provider', AuthenticationProvider::GOOGLE)->first();
    $provider->update([
        'is_enabled' => true,
        'client_id' => 'test-client-id',
        'client_secret' => 'test-secret',
        'redirect_uri' => 'https://example.com/callback',
    ]);

    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('google-456');
    $socialiteUser->shouldReceive('getEmail')->andReturn('newuser@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('New User');
    $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');
    $socialiteUser->token = 'token-456';
    $socialiteUser->refreshToken = 'refresh-token-456';

    Socialite::shouldReceive('driver')
        ->with('google')
        ->andReturnSelf();

    Socialite::shouldReceive('user')
        ->andReturn($socialiteUser);

    get(route('auth.socialite.callback', 'google'))
        ->assertRedirect(route('app.home'));

    $user = User::where('email', 'newuser@example.com')->first();
    expect($user)
        ->not->toBeNull()
        ->provider->toBe('google');

    expect(Auth::id())->toBe($user->id);
});

test('handles socialite failure gracefully', function () {
    $provider = SocialiteProvider::where('provider', AuthenticationProvider::GOOGLE)->first();
    $provider->update([
        'is_enabled' => true,
        'client_id' => 'test-client-id',
        'client_secret' => 'test-secret',
        'redirect_uri' => 'https://example.com/callback',
    ]);

    Socialite::shouldReceive('driver')
        ->with('google')
        ->andReturnSelf();

    Socialite::shouldReceive('user')
        ->andThrow(new Exception('OAuth failed'));

    get(route('auth.socialite.callback', 'google'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('error');
});

test('links socialite provider to existing user without provider', function () {
    $provider = SocialiteProvider::where('provider', AuthenticationProvider::GOOGLE)->first();
    $provider->update([
        'is_enabled' => true,
        'client_id' => 'test-client-id',
        'client_secret' => 'test-secret',
        'redirect_uri' => 'https://example.com/callback',
    ]);

    $user = User::factory()->create([
        'provider' => null,
    ]);

    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('google-789');
    $socialiteUser->shouldReceive('getEmail')->andReturn($user->email);
    $socialiteUser->shouldReceive('getName')->andReturn($user->name);
    $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');
    $socialiteUser->token = 'new-token';
    $socialiteUser->refreshToken = 'new-refresh-token';

    Socialite::shouldReceive('driver')
        ->with('google')
        ->andReturnSelf();

    Socialite::shouldReceive('user')
        ->andReturn($socialiteUser);

    get(route('auth.socialite.callback', 'google'))
        ->assertRedirect(route('app.home'));

    expect(Auth::id())->toBe($user->id);

    $user->refresh();
    expect($user->provider)->toBe('google');
});

test('only enabled providers are shown in buttons', function () {
    // Enable only Google
    SocialiteProvider::where('provider', AuthenticationProvider::GOOGLE)->update([
        'is_enabled' => true,
        'client_id' => 'test',
    ]);

    SocialiteProvider::where('provider', AuthenticationProvider::GITHUB)->update([
        'is_enabled' => false,
    ]);

    $response = get(route('login'));

    $response->assertSee('Google');
    $response->assertDontSee('GitHub');
});
