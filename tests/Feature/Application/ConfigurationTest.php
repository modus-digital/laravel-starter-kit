<?php

/**
 * Application Configuration Tests
 *
 * Tests application configuration including:
 * - Configuration loading
 * - Environment setup
 * - Helper function availability
 */
test('configuration is loaded correctly', function (): void {
    expect(config('app.name'))->toBeString();
    expect(config('settings.database_table_name'))->toBe('application_settings');
    expect(config('app.env'))->toBe('testing');
});

test('application environment is properly set for testing', function (): void {
    expect(app()->environment('testing'))->toBeTrue();
    expect(config('database.default'))->toBe('sqlite');
});

test('helper functions are available', function (): void {
    expect(function_exists('feature'))->toBeTrue();
    expect(function_exists('local_date'))->toBeTrue();
    expect(function_exists('download_backup_codes'))->toBeTrue();
});
