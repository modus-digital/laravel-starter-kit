<?php

declare(strict_types=1);

use App\Enums\ActivityStatus;
use App\Enums\RBAC\Permission;
use App\Enums\RBAC\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Outerweb\Settings\Facades\Setting;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create required permissions in the database
    foreach (Permission::cases() as $permission) {
        if ($permission->shouldSync()) {
            Spatie\Permission\Models\Permission::create(['name' => $permission->value]);
        }
    }

    // Create required roles
    foreach (Role::cases() as $role) {
        Spatie\Permission\Models\Role::create(['name' => $role->value]);
    }

    // Enable registration
    config(['modules.registration.enabled' => true]);

    // Enable Google provider in config
    config(['modules.socialite.providers.google' => true]);
});

it('redirects to provider when oauth is configured', function () {
    // Configure OAuth settings
    Setting::set('integrations.oauth.google.enabled', true);
    Setting::set('integrations.oauth.google.client_id', 'test_client_id');
    Setting::set('integrations.oauth.google.client_secret', encrypt('test_client_secret'));

    // Mock Socialite
    Socialite::shouldReceive('driver')
        ->with('google')
        ->once()
        ->andReturnSelf();

    Socialite::shouldReceive('redirect')
        ->once()
        ->andReturn(redirect('https://google.com/oauth'));

    $response = $this->get('/auth/google');

    $response->assertRedirect();
});

it('redirects to login when provider is not enabled in config', function () {
    // Disable provider in config
    config(['modules.socialite.providers.google' => false]);

    Setting::set('integrations.oauth.google.enabled', true);
    Setting::set('integrations.oauth.google.client_id', 'test_client_id');
    Setting::set('integrations.oauth.google.client_secret', encrypt('test_client_secret'));

    $response = $this->get('/auth/google');

    $response->assertRedirect(route('login'))
        ->assertSessionHas('error');
});

it('redirects to login when provider is not enabled in settings', function () {
    Setting::set('integrations.oauth.google.enabled', false);
    Setting::set('integrations.oauth.google.client_id', 'test_client_id');
    Setting::set('integrations.oauth.google.client_secret', encrypt('test_client_secret'));

    $response = $this->get('/auth/google');

    $response->assertRedirect(route('login'))
        ->assertSessionHas('error');
});

it('redirects to login when client id is missing', function () {
    Setting::set('integrations.oauth.google.enabled', true);
    Setting::set('integrations.oauth.google.client_id', null);
    Setting::set('integrations.oauth.google.client_secret', encrypt('test_client_secret'));

    $response = $this->get('/auth/google');

    $response->assertRedirect(route('login'))
        ->assertSessionHas('error');
});

it('redirects to login when client secret is missing', function () {
    Setting::set('integrations.oauth.google.enabled', true);
    Setting::set('integrations.oauth.google.client_id', 'test_client_id');
    Setting::set('integrations.oauth.google.client_secret', null);

    $response = $this->get('/auth/google');

    $response->assertRedirect(route('login'))
        ->assertSessionHas('error');
});

it('creates new user on callback when user does not exist', function () {
    // Configure OAuth settings
    Setting::set('integrations.oauth.google.enabled', true);
    Setting::set('integrations.oauth.google.client_id', 'test_client_id');
    Setting::set('integrations.oauth.google.client_secret', encrypt('test_client_secret'));

    // Mock Socialite user
    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getEmail')->andReturn('test@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('Test User');

    Socialite::shouldReceive('driver')
        ->with('google')
        ->once()
        ->andReturnSelf();

    Socialite::shouldReceive('user')
        ->once()
        ->andReturn($socialiteUser);

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect(route('dashboard'));

    // Verify user was created
    $user = User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('Test User');
    expect($user->provider)->toBe('google');
    expect($user->status)->toBe(ActivityStatus::ACTIVE);
    expect($user->hasRole(Role::USER))->toBeTrue();
    expect($user->hasVerifiedEmail())->toBeTrue();

    // Verify user is authenticated
    expect(Auth::check())->toBeTrue();
    expect(Auth::user()->email)->toBe('test@example.com');
});

it('logs in existing user on callback', function () {
    // Create existing user
    $existingUser = User::factory()->create([
        'email' => 'existing@example.com',
        'provider' => null,
    ]);

    // Configure OAuth settings
    Setting::set('integrations.oauth.google.enabled', true);
    Setting::set('integrations.oauth.google.client_id', 'test_client_id');
    Setting::set('integrations.oauth.google.client_secret', encrypt('test_client_secret'));

    // Mock Socialite user
    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getEmail')->andReturn('existing@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('Existing User');

    Socialite::shouldReceive('driver')
        ->with('google')
        ->once()
        ->andReturnSelf();

    Socialite::shouldReceive('user')
        ->once()
        ->andReturn($socialiteUser);

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect(route('dashboard'));

    // Verify user is authenticated
    expect(Auth::check())->toBeTrue();
    expect(Auth::user()->email)->toBe('existing@example.com');

    // Verify provider was linked
    $existingUser->refresh();
    expect($existingUser->provider)->toBe('google');
});

it('links provider to existing user with different provider', function () {
    // Create existing user with GitHub provider
    $existingUser = User::factory()->create([
        'email' => 'test@example.com',
        'provider' => 'github',
    ]);

    // Configure OAuth settings for Google
    Setting::set('integrations.oauth.google.enabled', true);
    Setting::set('integrations.oauth.google.client_id', 'test_client_id');
    Setting::set('integrations.oauth.google.client_secret', encrypt('test_client_secret'));

    // Mock Socialite user
    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getEmail')->andReturn('test@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('Test User');

    Socialite::shouldReceive('driver')
        ->with('google')
        ->once()
        ->andReturnSelf();

    Socialite::shouldReceive('user')
        ->once()
        ->andReturn($socialiteUser);

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect(route('dashboard'));

    // Verify provider was updated
    $existingUser->refresh();
    expect($existingUser->provider)->toBe('google');
});

it('redirects to login when registration is disabled', function () {
    // Disable registration
    config(['modules.registration.enabled' => false]);

    // Configure OAuth settings
    Setting::set('integrations.oauth.google.enabled', true);
    Setting::set('integrations.oauth.google.client_id', 'test_client_id');
    Setting::set('integrations.oauth.google.client_secret', encrypt('test_client_secret'));

    // Mock Socialite user
    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getEmail')->andReturn('newuser@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('New User');

    Socialite::shouldReceive('driver')
        ->with('google')
        ->once()
        ->andReturnSelf();

    Socialite::shouldReceive('user')
        ->once()
        ->andReturn($socialiteUser);

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect(route('login'))
        ->assertSessionHas('error');

    // Verify user was not created
    expect(User::where('email', 'newuser@example.com')->exists())->toBeFalse();
});

it('redirects to login when socialite authentication fails', function () {
    // Configure OAuth settings
    Setting::set('integrations.oauth.google.enabled', true);
    Setting::set('integrations.oauth.google.client_id', 'test_client_id');
    Setting::set('integrations.oauth.google.client_secret', encrypt('test_client_secret'));

    Socialite::shouldReceive('driver')
        ->with('google')
        ->once()
        ->andReturnSelf();

    Socialite::shouldReceive('user')
        ->once()
        ->andThrow(new Exception('OAuth failed'));

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect(route('login'))
        ->assertSessionHas('error');
});

it('configures provider with correct redirect url', function () {
    Setting::set('integrations.oauth.google.enabled', true);
    Setting::set('integrations.oauth.google.client_id', 'test_client_id');
    Setting::set('integrations.oauth.google.client_secret', encrypt('test_client_secret'));

    Socialite::shouldReceive('driver')
        ->with('google')
        ->once()
        ->andReturnSelf();

    Socialite::shouldReceive('redirect')
        ->once()
        ->andReturn(redirect('https://google.com/oauth'));

    $this->get('/auth/google');

    // Verify the configuration was set correctly
    $config = config('services.google');
    expect($config['client_id'])->toBe('test_client_id');
    expect($config['client_secret'])->toBe('test_client_secret');
    expect($config['redirect'])->toBe(route('oauth.callback', 'google'));
});
