<?php

/**
 * User Management Tests
 *
 * Tests user creation and management including:
 * - User creation with valid data
 * - User authentication flows
 * - User model functionality
 */

use App\Models\User;

test('user can be created with valid data', function (): void {
    $userData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'SecurePassword123!',
    ];

    $user = User::factory()->create($userData);

    expect($user->first_name)->toBe('John');
    expect($user->last_name)->toBe('Doe');
    expect($user->email)->toBe('john.doe@example.com');

    $this->assertDatabaseHas('users', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
    ]);
});

test('user authentication works correctly', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // Test that user can be authenticated
    $this->actingAs($user);

    expect(auth()->check())->toBeTrue();
    expect(auth()->user())->toBeInstanceOf(User::class);
    expect(auth()->user()->email)->toBe('test@example.com');
});

test('user full name accessor works', function (): void {
    $user = User::factory()->create([
        'first_name' => 'Jane',
        'last_name' => 'Smith',
    ]);

    expect($user->name)->toBe('Jane  Smith');
});
