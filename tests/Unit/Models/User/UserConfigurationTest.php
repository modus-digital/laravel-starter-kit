<?php

use App\Models\User;

test('user has correct fillable attributes', function () {
    $fillable = [
        'first_name',
        'last_name_prefix',
        'last_name',
        'email',
        'phone',
        'password',
        'last_login_at',
    ];

    $user = new User();
    expect($user->getFillable())->toBe($fillable);
});

test('user has correct hidden attributes', function () {
    $user = new User();
    $hidden = $user->getHidden();

    expect($hidden)
        ->toContain('password')
        ->toContain('remember_token');
});

test('user has correct casts and table configuration', function () {
    $user = new User();
    $casts = $user->getCasts();

    expect($casts)
        ->toHaveKey('email_verified_at', 'datetime')
        ->toHaveKey('last_login_at', 'datetime')
        ->toHaveKey('password', 'hashed');

    expect($user->getTable())->toBe('users');
});
