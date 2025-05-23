<?php

/**
 * User Factory Tests
 *
 * Tests user factory functionality including:
 * - User creation via factory
 * - Unique email generation
 * - Database integration
 */

use App\Models\User;

test('user factory creates users correctly', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'first_name' => 'Test',
        'last_name' => 'User',
    ]);

    expect($user->email)->toBe('test@example.com');
    expect($user->first_name)->toBe('Test');
    expect($user->last_name)->toBe('User');

    // Check user was created in database
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'first_name' => 'Test',
        'last_name' => 'User',
    ]);
});

test('user factory creates unique emails', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    expect($user1->email)->not->toBe($user2->email);
});

test('users table has correct structure', function (): void {
    expect(\Schema::hasColumn('users', 'id'))->toBeTrue();
    expect(\Schema::hasColumn('users', 'first_name'))->toBeTrue();
    expect(\Schema::hasColumn('users', 'last_name'))->toBeTrue();
    expect(\Schema::hasColumn('users', 'email'))->toBeTrue();
    expect(\Schema::hasColumn('users', 'password'))->toBeTrue();
    expect(\Schema::hasColumn('users', 'email_verified_at'))->toBeTrue();
    expect(\Schema::hasColumn('users', 'created_at'))->toBeTrue();
    expect(\Schema::hasColumn('users', 'updated_at'))->toBeTrue();
});
