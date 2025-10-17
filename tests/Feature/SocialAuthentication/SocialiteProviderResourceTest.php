<?php

declare(strict_types=1);

use App\Enums\RBAC\Permission;
use App\Models\User;
use Livewire\Livewire;
use ModusDigital\SocialAuthentication\Enums\AuthenticationProvider;
use ModusDigital\SocialAuthentication\Filament\Resources\SocialiteProviderResource\Pages\EditSocialiteProvider;
use ModusDigital\SocialAuthentication\Filament\Resources\SocialiteProviderResource\Pages\ListSocialiteProviders;
use ModusDigital\SocialAuthentication\Filament\Resources\SocialiteProviderResource\SocialiteProviderResource;
use ModusDigital\SocialAuthentication\Models\SocialiteProvider;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

uses()->group('socialite');

beforeEach(function () {
    seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    seed(ModusDigital\SocialAuthentication\Database\Seeders\SocialiteProvidersSeeder::class);

    $this->user = User::factory()->create();
    $this->user->givePermissionTo(Permission::ACCESS_CONTROL_PANEL->value);
    $this->user->givePermissionTo(Permission::MANAGE_OAUTH_PROVIDERS->value);

    actingAs($this->user);
    Filament\Facades\Filament::setCurrentPanel('control');
});

test('can list socialite providers', function () {
    $providers = SocialiteProvider::all();

    Livewire::test(ListSocialiteProviders::class)
        ->assertCanSeeTableRecords($providers);
});

test('can edit socialite provider credentials', function () {
    $provider = SocialiteProvider::where('provider', AuthenticationProvider::GOOGLE)->first();

    Livewire::test(EditSocialiteProvider::class, ['record' => $provider->id])
        ->fillForm([
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'redirect_uri' => 'https://example.com/auth/google/callback',
            'is_enabled' => true,
        ])
        ->call('save')
        ->assertHasNoErrors()
        ->assertNotified();

    $provider->refresh();

    expect($provider)
        ->client_id->toBe('test-client-id')
        ->client_secret->toBe('test-client-secret')
        ->redirect_uri->toBe('https://example.com/auth/google/callback')
        ->is_enabled->toBeTrue();
});

test('can enable/disable providers', function () {
    $provider = SocialiteProvider::where('provider', AuthenticationProvider::GOOGLE)->first();

    expect($provider->is_enabled)->toBeFalse();

    Livewire::test(EditSocialiteProvider::class, ['record' => $provider->id])
        ->fillForm([
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'redirect_uri' => 'https://example.com/callback',
            'is_enabled' => true,
        ])
        ->call('save')
        ->assertHasNoErrors();

    expect($provider->fresh()->is_enabled)->toBeTrue();
});

test('cannot create new providers', function () {
    expect(SocialiteProviderResource::canCreate())->toBeFalse();
});

test('cannot delete providers', function () {
    $provider = SocialiteProvider::first();

    expect(SocialiteProviderResource::canDelete($provider))->toBeFalse();
});

test('provider name and type are read-only', function () {
    $provider = SocialiteProvider::where('provider', AuthenticationProvider::GOOGLE)->first();
    $originalName = $provider->name;
    $originalProvider = $provider->provider;

    Livewire::test(EditSocialiteProvider::class, ['record' => $provider->id])
        ->fillForm([
            'name' => 'Changed Name',
            'client_id' => 'test-id',
            'client_secret' => 'test-secret',
            'redirect_uri' => 'https://example.com/callback',
        ])
        ->call('save');

    $provider->refresh();

    expect($provider)
        ->name->toBe($originalName)
        ->provider->toBe($originalProvider)
        ->client_id->toBe('test-id');
});

test('cannot access without permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::ACCESS_CONTROL_PANEL->value);

    actingAs($user);

    expect(SocialiteProviderResource::canAccess())->toBeFalse();
});

test('can filter by enabled status', function () {
    $enabledProvider = SocialiteProvider::first();
    $enabledProvider->update(['is_enabled' => true, 'client_id' => 'test']);

    $disabledProvider = SocialiteProvider::skip(1)->first();
    $disabledProvider->update(['is_enabled' => false]);

    Livewire::test(ListSocialiteProviders::class)
        ->filterTable('is_enabled', true)
        ->assertCanSeeTableRecords([$enabledProvider])
        ->assertCanNotSeeTableRecords([$disabledProvider]);
});

test('can filter by configured status', function () {
    $configuredProvider = SocialiteProvider::first();
    $configuredProvider->update(['client_id' => 'test-client-id']);

    $unconfiguredProvider = SocialiteProvider::skip(1)->first();
    $unconfiguredProvider->update(['client_id' => null]);

    Livewire::test(ListSocialiteProviders::class)
        ->filterTable('configured', true)
        ->assertCanSeeTableRecords([$configuredProvider])
        ->assertCanNotSeeTableRecords([$unconfiguredProvider]);
});

test('all providers are seeded', function () {
    expect(SocialiteProvider::count())->toBe(3);

    expect(SocialiteProvider::where('provider', AuthenticationProvider::GOOGLE)->exists())->toBeTrue();
    expect(SocialiteProvider::where('provider', AuthenticationProvider::GITHUB)->exists())->toBeTrue();
    expect(SocialiteProvider::where('provider', AuthenticationProvider::FACEBOOK)->exists())->toBeTrue();
});
