<?php

/**
 * Application Routing Tests
 *
 * Tests basic routing functionality including:
 * - Home page redirects
 * - Authentication page loading
 * - Route configuration
 */

test('home page redirects to login', function (): void {
    $response = $this->get('/');

    $response->assertStatus(302);
    $response->assertRedirect('/auth/login');
});

test('login page loads successfully', function (): void {
    $response = $this->get('/auth/login');

    $response->assertStatus(200);
    $response->assertSee('Sign in'); // Actual text on the page
});

test('register page loads successfully', function (): void {
    $response = $this->get('/auth/register');

    $response->assertStatus(200);
    $response->assertSee('Create an account'); // Actual text on the page
});
