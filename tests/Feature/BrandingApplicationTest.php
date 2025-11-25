<?php

declare(strict_types=1);

use App\Enums\ActivityStatus;
use App\Enums\RBAC\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed roles and permissions
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder']);
});

it('injects branding css variables in app blade template', function () {
    $user = \App\Models\User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $content = $response->getContent();

    expect($content)->toContain('--brand-primary');
    expect($content)->toContain('--brand-secondary');
    expect($content)->toContain('--brand-font-sans');
});

it('shares branding data with inertia', function () {
    $user = \App\Models\User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $inertiaProps = $response->viewData('page')['props'] ?? [];

    expect($inertiaProps)->toHaveKey('branding');
    expect($inertiaProps['branding'])->toHaveKeys(['logo', 'primaryColor', 'secondaryColor', 'font']);
});

it('preloads all font options', function () {
    $user = \App\Models\User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $content = $response->getContent();

    expect($content)->toContain('fonts.bunny.net');
    expect($content)->toContain('inter');
    expect($content)->toContain('roboto');
    expect($content)->toContain('poppins');
    expect($content)->toContain('lato');
    expect($content)->toContain('inria-serif');
    expect($content)->toContain('arvo');
});

it('applies custom font via css variable', function () {
    $user = \App\Models\User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $content = $response->getContent();

    // Should contain font-family with CSS variable
    expect($content)->toContain('font-family: var(--brand-font-sans)');
});
