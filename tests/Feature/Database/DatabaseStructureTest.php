<?php

/**
 * Database Structure Tests
 *
 * Tests database table structure including:
 * - Required tables existence
 * - Column structure validation
 * - Table relationships
 */
test('all required tables exist', function (): void {
    $requiredTables = [
        'users',
        'application_settings',
        'password_reset_tokens',
        'sessions',
        'migrations',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
    ];

    foreach ($requiredTables as $table) {
        expect(\Schema::hasTable($table))->toBeTrue("Table {$table} should exist");
    }
});

test('users table has correct columns', function (): void {
    $columns = [
        'id',
        'first_name',
        'last_name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'created_at',
        'updated_at',
    ];

    foreach ($columns as $column) {
        expect(\Schema::hasColumn('users', $column))->toBeTrue("Users table should have {$column} column");
    }
});

test('application_settings table has correct columns', function (): void {
    $columns = [
        'id',
        'key',
        'value',
        'created_at',
        'updated_at',
    ];

    foreach ($columns as $column) {
        expect(\Schema::hasColumn('application_settings', $column))->toBeTrue("Settings table should have {$column} column");
    }
});
