<?php

declare(strict_types=1);

use App\Services\BrandingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
});

it('retrieves default primary color when not set', function () {
    $service = app(BrandingService::class);

    $color = $service->getPrimaryColor();

    expect($color)->toStartWith('#');
    expect($color)->toBe('#f59e0b');
});

it('retrieves default secondary color when not set', function () {
    $service = app(BrandingService::class);

    $color = $service->getSecondaryColor();

    expect($color)->toStartWith('#');
    expect($color)->toBe('#6b7280');
});

it('retrieves default font when not set', function () {
    $service = app(BrandingService::class);

    $font = $service->getFont();

    expect($font)->toBe('inter');
});

it('retrieves null logo when not set', function () {
    $service = app(BrandingService::class);

    $logo = $service->getLogoUrl();

    expect($logo)->toBeNull();
});

it('returns hex color format', function () {
    $service = app(BrandingService::class);

    $result = $service->getPrimaryColor();

    expect($result)->toStartWith('#');
    expect($result)->toMatch('/^#[0-9A-Fa-f]{6}$/');
});

it('caches branding settings', function () {
    $service = app(BrandingService::class);

    // First call
    $settings1 = $service->getSettings();

    // Second call should use cache
    $settings2 = $service->getSettings();

    expect($settings1)->toBe($settings2);
    expect(Cache::has('branding_settings'))->toBeTrue();
});

it('clears cache when requested', function () {
    $service = app(BrandingService::class);

    // Populate cache
    $service->getSettings();
    expect(Cache::has('branding_settings'))->toBeTrue();

    // Clear cache
    $service->clearCache();
    expect(Cache::has('branding_settings'))->toBeFalse();
});

it('returns rgb array for primary color', function () {
    $service = app(BrandingService::class);

    $rgb = $service->getPrimaryColorRgb();

    expect($rgb)->toBeArray();
    expect($rgb)->toHaveCount(3);
    expect($rgb[0])->toBeInt();
    expect($rgb[1])->toBeInt();
    expect($rgb[2])->toBeInt();
});

it('returns rgb array for secondary color', function () {
    $service = app(BrandingService::class);

    $rgb = $service->getSecondaryColorRgb();

    expect($rgb)->toBeArray();
    expect($rgb)->toHaveCount(3);
    expect($rgb[0])->toBeInt();
    expect($rgb[1])->toBeInt();
    expect($rgb[2])->toBeInt();
});
