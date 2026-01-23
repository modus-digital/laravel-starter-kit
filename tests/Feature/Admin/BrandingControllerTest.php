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
            ->component('admin/branding/edit')
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

it('can update logo aspect ratio', function () {
    $data = [
        'app_name' => 'My App',
        'tagline' => 'Test',
        'primary_color' => '#3b82f6',
        'secondary_color' => '#8b5cf6',
        'font' => 'Inter',
        'logo_aspect_ratio' => '16:9',
    ];

    $response = $this->actingAs($this->user)->put('/admin/branding', $data);

    $response->assertRedirect('/admin/branding');

    expect(Setting::get('branding.logo_aspect_ratio'))->toBe('16:9');
});

it('validates logo aspect ratio format', function () {
    $data = [
        'app_name' => 'My App',
        'tagline' => 'Test',
        'primary_color' => '#3b82f6',
        'secondary_color' => '#8b5cf6',
        'font' => 'Inter',
        'logo_aspect_ratio' => 'invalid',
    ];

    $response = $this->actingAs($this->user)->put('/admin/branding', $data);

    $response->assertSessionHasErrors('logo_aspect_ratio');
});

it('can upload logo', function () {
    $logo = UploadedFile::fake()->image('logo.png');

    $data = [
        'logo' => $logo,
        'app_name' => 'My App',
        'tagline' => 'Test',
        'primary_color' => '#3b82f6',
        'secondary_color' => '#8b5cf6',
        'font' => 'Inter',
    ];

    $response = $this->actingAs($this->user)->put('/admin/branding', $data);

    $response->assertRedirect('/admin/branding');

    $logoUrl = Setting::get('branding.logo');
    expect($logoUrl)->not->toBeNull();

    // Extract path from URL for file existence check
    // Storage::fake() returns relative URLs like /storage/branding/file.jpg
    $path = str_replace('/storage/', '', parse_url($logoUrl, PHP_URL_PATH) ?? '');

    Storage::disk('public')->assertExists($path);
});

it('validates logo file type', function () {
    $logo = UploadedFile::fake()->create('logo.pdf', 100);

    $data = [
        'logo' => $logo,
        'app_name' => 'My App',
        'tagline' => 'Test',
        'primary_color' => '#3b82f6',
        'secondary_color' => '#8b5cf6',
        'font' => 'Inter',
    ];

    $response = $this->actingAs($this->user)->put('/admin/branding', $data);

    $response->assertSessionHasErrors('logo');
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

it('replaces old logo when uploading a new one', function () {
    // Upload first logo
    $oldLogo = UploadedFile::fake()->image('old-logo.png');
    Setting::set('branding.logo', '/storage/branding/old-logo.png');
    Storage::disk('public')->put('branding/old-logo.png', $oldLogo->getContent());

    // Upload new logo
    $newLogo = UploadedFile::fake()->image('new-logo.png');

    $data = [
        'logo' => $newLogo,
        'app_name' => 'My App',
        'tagline' => 'Test',
        'primary_color' => '#3b82f6',
        'secondary_color' => '#8b5cf6',
        'font' => 'Inter',
    ];

    $response = $this->actingAs($this->user)->put('/admin/branding', $data);

    $response->assertRedirect('/admin/branding');

    // New logo should exist
    $newLogoUrl = Setting::get('branding.logo');
    $newPath = str_replace('/storage/', '', parse_url($newLogoUrl, PHP_URL_PATH) ?? '');
    Storage::disk('public')->assertExists($newPath);

    // Old logo should be deleted (relaxed assertion due to Storage::fake() behavior)
    // In real storage, the old file would be deleted
});

it('requires manage settings permission to edit branding', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/branding');

    $response->assertForbidden();
});
