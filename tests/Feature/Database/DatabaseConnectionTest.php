<?php

/**
 * Database Connection Tests
 *
 * Tests database connectivity including:
 * - Database connection verification
 * - Migration status
 * - Basic database operations
 */

test('database connection works correctly', function (): void {
    expect(\DB::connection()->getPdo())->not->toBeNull();
    expect(\DB::connection()->getDatabaseName())->toBeString();
});

test('database connections work correctly', function (): void {
    // Test that we can connect to the database
    expect(\DB::connection()->getPdo())->not->toBeNull();

    // Test that migrations have run
    expect(\Schema::hasTable('users'))->toBeTrue();
    expect(\Schema::hasTable('application_settings'))->toBeTrue();
});

test('migrations have run successfully', function (): void {
    // Check that migrations table exists and has records
    expect(\Schema::hasTable('migrations'))->toBeTrue();

    // Check that we have migration records
    $migrationCount = \DB::table('migrations')->count();
    expect($migrationCount)->toBeGreaterThan(0);
});
