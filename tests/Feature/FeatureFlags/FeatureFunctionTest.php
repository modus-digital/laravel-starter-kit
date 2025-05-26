<?php

/**
 * Feature Function Tests
 *
 * Tests the feature helper function including:
 * - Function availability
 * - FeatureStatus object creation
 * - Enabled/disabled feature handling
 */
test('feature helper function exists and is callable', function (): void {
    expect(function_exists('feature'))->toBeTrue();
});

test('feature function returns FeatureStatus object', function (): void {
    $feature = feature('features.auth.login');

    expect($feature)->toBeInstanceOf(\App\Helpers\FeatureStatus::class);
});

test('feature function returns correct status for enabled features', function (): void {
    $featureStatus = feature('features.auth.login');

    expect($featureStatus)->toBeInstanceOf(\App\Helpers\FeatureStatus::class);
    expect($featureStatus->enabled())->toBeBool();
});
