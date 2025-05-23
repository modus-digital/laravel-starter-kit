<?php

use App\Models\UserSetting;

test('user setting uses correct table', function () {
    $userSetting = new UserSetting();
    expect($userSetting->getTable())->toBe('user_settings');
});

test('user setting does not use auto incrementing id', function () {
    $userSetting = new UserSetting();
    expect($userSetting->incrementing)->toBeFalse();
});

test('user setting has correct fillable attributes', function () {
    $expectedFillable = [
        'user_id',
        'key',
        'value',
    ];

    $userSetting = new UserSetting();
    expect($userSetting->getFillable())->toBe($expectedFillable);
});
