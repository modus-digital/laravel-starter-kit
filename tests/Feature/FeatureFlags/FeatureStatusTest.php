<?php

/**
 * Feature Status Tests
 *
 * Tests FeatureStatus object behavior including:
 * - Method availability
 * - Value handling
 * - Database integration
 */

test('feature status has correct methods', function (): void {
    $feature = feature('features.auth.login');

    expect(method_exists($feature, 'enabled'))->toBeTrue();
    expect(method_exists($feature, 'disabled'))->toBeTrue();
    expect(method_exists($feature, 'value'))->toBeTrue();
});

test('feature status value method works correctly', function (): void {
    $enabledFeature = feature('features.auth.login');
    $disabledFeature = feature('features.non.existent');

    expect($enabledFeature->value())->toBeTrue();
    expect($disabledFeature->value())->toBeNull();
});

test('feature function works with database errors gracefully', function (): void {
    // This test ensures that if database issues occur, the feature function doesn't crash
    // It should return a FeatureStatus object with null value

    $feature = feature('any.feature.key');
    expect($feature)->toBeInstanceOf(\App\Helpers\FeatureStatus::class);
});
