<?php

/**
 * Feature Database Tests
 *
 * Tests feature flags database integration including:
 * - Settings table integration
 * - Feature flag consistency
 * - Default feature handling
 */

test('feature function handles non-existent features gracefully', function (): void {
    $featureStatus = feature('features.non.existent.feature');

    expect($featureStatus)->toBeInstanceOf(\App\Helpers\FeatureStatus::class);
    expect($featureStatus->disabled())->toBeTrue();
});

test('feature function returns proper values for existing settings', function (): void {
    // Test that the default settings work
    $loginFeature = feature('features.auth.login');
    $registerFeature = feature('features.auth.register');

    expect($loginFeature->enabled())->toBeTrue();
    expect($registerFeature->enabled())->toBeTrue();
});

test('settings table contains feature flags', function (): void {
    // Test that the application_settings table has our feature flags
    expect(\Schema::hasTable('application_settings'))->toBeTrue();

    // Check that feature settings exist in the database
    $this->assertDatabaseHas('application_settings', [
        'key' => 'features.auth.login'
    ]);

    $this->assertDatabaseHas('application_settings', [
        'key' => 'features.auth.register'
    ]);
});
