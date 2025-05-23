<?php

/**
 * Authentication Pages Tests
 *
 * Tests authentication page rendering including:
 * - Login page display
 * - Registration page display
 * - Page content verification
 */

use App\Models\User;

test('auth login page displays correctly', function (): void {
    $response = $this->get('/auth/login');

    $response->assertStatus(200);
    $response->assertSee('Sign in');
    $response->assertSee('email');
    $response->assertSee('password');
});

test('auth register page displays correctly', function (): void {
    $response = $this->get('/auth/register');

    $response->assertStatus(200);
    $response->assertSee('Create an account');
    $response->assertSee('name');
    $response->assertSee('email');
});

test('guest users are redirected to login', function (): void {
    // Test that protected routes redirect guests
    $response = $this->get('/');

    $response->assertStatus(302);
    $response->assertRedirect('/auth/login');
});
