<?php

declare(strict_types=1);

use Database\Seeders\BootstrapApplicationSeeder;

beforeEach(function () {
    $this->seed(BootstrapApplicationSeeder::class);
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    // User model doesn't implement MustVerifyEmail, so users go straight to dashboard
    $response->assertRedirect(route('dashboard', absolute: false));
});
