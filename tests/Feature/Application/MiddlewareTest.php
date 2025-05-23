<?php

/**
 * Application Middleware Tests
 *
 * Tests middleware functionality including:
 * - Public page access
 * - Route protection
 * - Authentication middleware
 */

test('middleware allows access to public pages', function (): void {
    // Test that public authentication pages load without middleware issues
    $loginResponse = $this->get('/auth/login');
    $registerResponse = $this->get('/auth/register');

    $loginResponse->assertStatus(200);
    $registerResponse->assertStatus(200);
});

test('routes are properly configured', function (): void {
    // Test that basic routing is working
    $homeResponse = $this->get('/');
    expect($homeResponse->status())->toBe(302); // Should redirect

    // Test that auth routes exist
    $loginResponse = $this->get('/auth/login');
    expect($loginResponse->status())->toBe(200);
});
