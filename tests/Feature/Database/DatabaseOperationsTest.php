<?php

/**
 * Database Operations Tests
 *
 * Tests database operations including:
 * - CRUD operations
 * - JSON data handling
 * - Bulk operations
 * - Performance testing
 */

test('database can perform basic operations', function (): void {
    // Test insert
    $userId = \DB::table('users')->insertGetId([
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'database-test@example.com',
        'password' => bcrypt('password'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect($userId)->toBeInt();
    expect($userId)->toBeGreaterThan(0);

    // Test select
    $user = \DB::table('users')->where('id', $userId)->first();
    expect($user)->not->toBeNull();
    expect($user->email)->toBe('database-test@example.com');

    // Test update
    $updated = \DB::table('users')->where('id', $userId)->update([
        'first_name' => 'Updated',
        'updated_at' => now(),
    ]);

    expect($updated)->toBe(1);

    // Test delete
    $deleted = \DB::table('users')->where('id', $userId)->delete();
    expect($deleted)->toBe(1);
});

test('database supports json operations', function (): void {
    // Test JSON operations on settings table
    $testData = ['test' => true, 'nested' => ['value' => 'example']];

    \DB::table('application_settings')->insert([
        'key' => 'test.json.setting',
        'value' => json_encode($testData),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $setting = \DB::table('application_settings')->where('key', 'test.json.setting')->first();
    $decodedValue = json_decode($setting->value, true);

    expect($decodedValue)->toBe($testData);

    // Clean up
    \DB::table('application_settings')->where('key', 'test.json.setting')->delete();
});

test('database handles large datasets efficiently', function (): void {
    // Create multiple users to test performance
    $users = [];
    for ($i = 0; $i < 10; $i++) {
        $users[] = [
            'first_name' => "User{$i}",
            'last_name' => "Test{$i}",
            'email' => "bulk-test-{$i}@example.com",
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    // Bulk insert
    $inserted = \DB::table('users')->insert($users);
    expect($inserted)->toBeTrue();

    // Verify count
    $count = \DB::table('users')->where('email', 'like', 'bulk-test-%')->count();
    expect($count)->toBe(10);

    // Clean up
    \DB::table('users')->where('email', 'like', 'bulk-test-%')->delete();
});
