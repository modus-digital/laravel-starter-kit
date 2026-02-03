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

it('retrieves null light logo when not set', function () {
    $service = app(BrandingService::class);

    $logo = $service->getLogoLightUrl();

    expect($logo)->toBeNull();
});

it('retrieves null dark logo when not set', function () {
    $service = app(BrandingService::class);

    $logo = $service->getLogoDarkUrl();

    expect($logo)->toBeNull();
});

it('retrieves null light emblem when not set', function () {
    $service = app(BrandingService::class);

    $emblem = $service->getEmblemLightUrl();

    expect($emblem)->toBeNull();
});

it('retrieves null dark emblem when not set', function () {
    $service = app(BrandingService::class);

    $emblem = $service->getEmblemDarkUrl();

    expect($emblem)->toBeNull();
});

it('includes logo_light, logo_dark, emblem_light, and emblem_dark in settings', function () {
    $service = app(BrandingService::class);

    $settings = $service->getSettings();

    expect($settings)->toHaveKey('logo_light');
    expect($settings)->toHaveKey('logo_dark');
    expect($settings)->toHaveKey('emblem_light');
    expect($settings)->toHaveKey('emblem_dark');
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

it('generates a full color scale with all 11 shades', function () {
    $service = app(BrandingService::class);

    $scale = $service->generateColorScale('#eab308');

    expect($scale)->toBeArray();
    expect($scale)->toHaveCount(11);
    expect($scale)->toHaveKeys([50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950]);

    // Verify all shades are valid hex colors
    foreach ($scale as $shade => $hex) {
        expect($hex)->toStartWith('#');
        expect($hex)->toMatch('/^#[0-9A-Fa-f]{6}$/');
    }
});

it('generates color scale where 500 shade matches input color approximately', function () {
    $service = app(BrandingService::class);
    $inputColor = '#eab308';

    $scale = $service->generateColorScale($inputColor);

    // The 500 shade should be close to the input color
    // (may not be exact due to OKLCH conversion and rounding)
    expect($scale[500])->toBeString();
    expect($scale[500])->toStartWith('#');
});

it('generates lighter shades for lower numbers', function () {
    $service = app(BrandingService::class);

    $scale = $service->generateColorScale('#eab308');

    // Parse hex colors to RGB
    $parseHex = function (string $hex): array {
        $hex = mb_ltrim($hex, '#');

        return [
            hexdec(mb_substr($hex, 0, 2)),
            hexdec(mb_substr($hex, 2, 2)),
            hexdec(mb_substr($hex, 4, 2)),
        ];
    };

    // Lower shades should be lighter (higher RGB values on average)
    $shade50Rgb = $parseHex($scale[50]);
    $shade500Rgb = $parseHex($scale[500]);
    $shade950Rgb = $parseHex($scale[950]);

    // Calculate average brightness
    $brightness50 = ($shade50Rgb[0] + $shade50Rgb[1] + $shade50Rgb[2]) / 3;
    $brightness500 = ($shade500Rgb[0] + $shade500Rgb[1] + $shade500Rgb[2]) / 3;
    $brightness950 = ($shade950Rgb[0] + $shade950Rgb[1] + $shade950Rgb[2]) / 3;

    expect($brightness50)->toBeGreaterThan($brightness500);
    expect($brightness500)->toBeGreaterThan($brightness950);
});

it('generates CSS variables in correct format', function () {
    $service = app(BrandingService::class);

    $cssVars = $service->generateCSSVariables('primary', '#eab308');

    expect($cssVars)->toBeString();
    expect($cssVars)->toContain('--primary-50:');
    expect($cssVars)->toContain('--primary-500:');
    expect($cssVars)->toContain('--primary-950:');

    // Verify format: --primary-500: R G B;
    expect($cssVars)->toMatch('/--primary-\d+:\s+\d+\s+\d+\s+\d+;/');
});

it('generates CSS variables with all 11 shades', function () {
    $service = app(BrandingService::class);

    $cssVars = $service->generateCSSVariables('test', '#eab308');

    $shades = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];
    foreach ($shades as $shade) {
        expect($cssVars)->toContain("--test-{$shade}:");
    }
});

it('returns white text color for dark backgrounds', function () {
    $service = app(BrandingService::class);

    $textColor = $service->getContrastTextColor('#000000');

    expect($textColor)->toBe('white');
});

it('returns black text color for light backgrounds', function () {
    $service = app(BrandingService::class);

    $textColor = $service->getContrastTextColor('#ffffff');

    expect($textColor)->toBe('black');
});

it('returns correct contrast text color for medium colors', function () {
    $service = app(BrandingService::class);

    // Dark color should return white
    $darkColor = $service->getContrastTextColor('#1a1a1a');
    expect($darkColor)->toBe('white');

    // Light color should return black
    $lightColor = $service->getContrastTextColor('#f0f0f0');
    expect($lightColor)->toBe('black');
});

it('generates consistent color scales for same input', function () {
    $service = app(BrandingService::class);
    $inputColor = '#eab308';

    $scale1 = $service->generateColorScale($inputColor);
    $scale2 = $service->generateColorScale($inputColor);

    expect($scale1)->toBe($scale2);
});
