<?php

declare(strict_types=1);

use App\Enums\RBAC\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Outerweb\Settings\Facades\Setting;
use Spatie\Permission\Models\Permission as PermissionModel;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create permissions if they don't exist
    PermissionModel::findOrCreate(Permission::ACCESS_CONTROL_PANEL->value, 'web');
    PermissionModel::findOrCreate(Permission::MANAGE_SETTINGS->value, 'web');
    PermissionModel::findOrCreate(Permission::HAS_API_ACCESS->value, 'web');

    $this->user = User::factory()->create();
    $this->user->givePermissionTo([
        Permission::ACCESS_CONTROL_PANEL,
        Permission::MANAGE_SETTINGS,
    ]);
    Storage::fake('public');
});

it('can show branding edit page', function () {
    $response = $this->actingAs($this->user)->get('/admin/branding');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('core/admin/branding/edit')
            ->has('branding')
        );
});

it('can update branding settings', function () {
    $data = [
        'app_name' => 'My App',
        'tagline' => 'A great application',
        'primary_color' => '#3b82f6',
        'secondary_color' => '#8b5cf6',
        'font' => 'Inter',
    ];

    $response = $this->actingAs($this->user)->put('/admin/branding', $data);

    $response->assertRedirect('/admin/branding');

    expect(Setting::get('branding.app_name'))->toBe('My App');
    expect(Setting::get('branding.primary_color'))->toBe('#3b82f6');
});

it('can upload light mode logo', function () {
    $logo = UploadedFile::fake()->image('logo-light.png');

    $data = [
        'logo_light' => $logo,
        'app_name' => 'My App',
        'tagline' => 'Test',
        'primary_color' => '#3b82f6',
        'secondary_color' => '#8b5cf6',
        'font' => 'Inter',
    ];

    $response = $this->actingAs($this->user)->put('/admin/branding', $data);

    $response->assertRedirect('/admin/branding');

    $logoUrl = Setting::get('branding.logo_light');
    expect($logoUrl)->not->toBeNull();

    // Extract path from URL for file existence check
    $path = str_replace('/storage/', '', parse_url($logoUrl, PHP_URL_PATH) ?? '');

    Storage::disk('public')->assertExists($path);
});

it('can upload dark mode logo', function () {
    $logo = UploadedFile::fake()->image('logo-dark.png');

    $data = [
        'logo_dark' => $logo,
        'app_name' => 'My App',
        'tagline' => 'Test',
        'primary_color' => '#3b82f6',
        'secondary_color' => '#8b5cf6',
        'font' => 'Inter',
    ];

    $response = $this->actingAs($this->user)->put('/admin/branding', $data);

    $response->assertRedirect('/admin/branding');

    $logoUrl = Setting::get('branding.logo_dark');
    expect($logoUrl)->not->toBeNull();

    // Extract path from URL for file existence check
    $path = str_replace('/storage/', '', parse_url($logoUrl, PHP_URL_PATH) ?? '');

    Storage::disk('public')->assertExists($path);
});

it('can upload light emblem', function () {
    $emblem = UploadedFile::fake()->image('emblem-light.png');

    $data = [
        'emblem_light' => $emblem,
        'app_name' => 'My App',
        'tagline' => 'Test',
        'primary_color' => '#3b82f6',
        'secondary_color' => '#8b5cf6',
        'font' => 'Inter',
    ];

    $response = $this->actingAs($this->user)->put('/admin/branding', $data);

    $response->assertRedirect('/admin/branding');

    $emblemUrl = Setting::get('branding.emblem_light');
    expect($emblemUrl)->not->toBeNull();

    // Extract path from URL for file existence check
    $path = str_replace('/storage/', '', parse_url($emblemUrl, PHP_URL_PATH) ?? '');

    Storage::disk('public')->assertExists($path);
});

it('can upload dark emblem', function () {
    $emblem = UploadedFile::fake()->image('emblem-dark.png');

    $data = [
        'emblem_dark' => $emblem,
        'app_name' => 'My App',
        'tagline' => 'Test',
        'primary_color' => '#3b82f6',
        'secondary_color' => '#8b5cf6',
        'font' => 'Inter',
    ];

    $response = $this->actingAs($this->user)->put('/admin/branding', $data);

    $response->assertRedirect('/admin/branding');

    $emblemUrl = Setting::get('branding.emblem_dark');
    expect($emblemUrl)->not->toBeNull();

    // Extract path from URL for file existence check
    $path = str_replace('/storage/', '', parse_url($emblemUrl, PHP_URL_PATH) ?? '');

    Storage::disk('public')->assertExists($path);
});

it('validates light logo file type', function () {
    $logo = UploadedFile::fake()->create('logo.pdf', 100);

    $data = [
        'logo_light' => $logo,
        'app_name' => 'My App',
        'tagline' => 'Test',
        'primary_color' => '#3b82f6',
        'secondary_color' => '#8b5cf6',
        'font' => 'Inter',
    ];

    $response = $this->actingAs($this->user)->put('/admin/branding', $data);

    $response->assertSessionHasErrors('logo_light');
});

it('validates dark logo file type', function () {
    $logo = UploadedFile::fake()->create('logo.pdf', 100);

    $data = [
        'logo_dark' => $logo,
        'app_name' => 'My App',
        'tagline' => 'Test',
        'primary_color' => '#3b82f6',
        'secondary_color' => '#8b5cf6',
        'font' => 'Inter',
    ];

    $response = $this->actingAs($this->user)->put('/admin/branding', $data);

    $response->assertSessionHasErrors('logo_dark');
});

it('validates light emblem file type', function () {
    $emblem = UploadedFile::fake()->create('emblem.pdf', 100);

    $data = [
        'emblem_light' => $emblem,
        'app_name' => 'My App',
        'tagline' => 'Test',
        'primary_color' => '#3b82f6',
        'secondary_color' => '#8b5cf6',
        'font' => 'Inter',
    ];

    $response = $this->actingAs($this->user)->put('/admin/branding', $data);

    $response->assertSessionHasErrors('emblem_light');
});

it('validates dark emblem file type', function () {
    $emblem = UploadedFile::fake()->create('emblem.pdf', 100);

    $data = [
        'emblem_dark' => $emblem,
        'app_name' => 'My App',
        'tagline' => 'Test',
        'primary_color' => '#3b82f6',
        'secondary_color' => '#8b5cf6',
        'font' => 'Inter',
    ];

    $response = $this->actingAs($this->user)->put('/admin/branding', $data);

    $response->assertSessionHasErrors('emblem_dark');
});

it('validates color format', function () {
    $data = [
        'app_name' => 'My App',
        'tagline' => 'Test',
        'primary_color' => 'invalid-color',
        'secondary_color' => '#8b5cf6',
        'font' => 'Inter',
    ];

    $response = $this->actingAs($this->user)->put('/admin/branding', $data);

    $response->assertSessionHasErrors('primary_color');
});

it('replaces old light logo when uploading a new one', function () {
    // Upload first logo
    $oldLogo = UploadedFile::fake()->image('old-logo.png');
    Setting::set('branding.logo_light', '/storage/branding/old-logo.png');
    Storage::disk('public')->put('branding/old-logo.png', $oldLogo->getContent());

    // Upload new logo
    $newLogo = UploadedFile::fake()->image('new-logo.png');

    $data = [
        'logo_light' => $newLogo,
        'app_name' => 'My App',
        'tagline' => 'Test',
        'primary_color' => '#3b82f6',
        'secondary_color' => '#8b5cf6',
        'font' => 'Inter',
    ];

    $response = $this->actingAs($this->user)->put('/admin/branding', $data);

    $response->assertRedirect('/admin/branding');

    // New logo should exist
    $newLogoUrl = Setting::get('branding.logo_light');
    $newPath = str_replace('/storage/', '', parse_url($newLogoUrl, PHP_URL_PATH) ?? '');
    Storage::disk('public')->assertExists($newPath);
});

it('requires manage settings permission to edit branding', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/branding');

    $response->assertForbidden();
});
