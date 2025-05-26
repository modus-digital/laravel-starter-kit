<?php

use App\Models\User;

test('user name accessor combines first and last name', function () {
    $user = new User([
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    expect($user->name)->toBe('John  Doe');
});

test('user name accessor handles empty names', function () {
    $user = new User([
        'first_name' => '',
        'last_name' => '',
    ]);

    expect($user->name)->toBe('  ');
});

test('user name accessor includes last name prefix', function () {
    $user = new User([
        'first_name' => 'John',
        'last_name_prefix' => 'van',
        'last_name' => 'Doe',
    ]);

    expect($user->name)->toBe('John van Doe');
});
