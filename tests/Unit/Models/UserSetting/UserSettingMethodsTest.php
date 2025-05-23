<?php

use App\Models\UserSetting;

test('user setting has parsed value accessor', function () {
    $userSetting = new UserSetting();
    expect(method_exists($userSetting, 'parsedValue'))->toBeTrue();
});

test('user setting has update value attribute method', function () {
    $userSetting = new UserSetting();
    expect(method_exists($userSetting, 'updateValueAttribute'))->toBeTrue();
});

test('user setting has retrieve method', function () {
    $userSetting = new UserSetting();
    expect(method_exists($userSetting, 'retrieve'))->toBeTrue();
});
