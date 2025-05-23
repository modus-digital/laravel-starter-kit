<?php

use App\Models\UserSetting;
use App\Enums\Settings\UserSettings;

test('user setting casts key to user settings enum', function () {
    $userSetting = new UserSetting();
    $casts = $userSetting->getCasts();

    expect($casts)
        ->toHaveKey('key', UserSettings::class);
});

test('user setting casts value to array', function () {
    $userSetting = new UserSetting();
    $casts = $userSetting->getCasts();

    expect($casts)
        ->toHaveKey('value', 'array');
});

test('user setting casts timestamps to datetime', function () {
    $userSetting = new UserSetting();
    $casts = $userSetting->getCasts();

    expect($casts)
        ->toHaveKey('created_at', 'datetime')
        ->toHaveKey('updated_at', 'datetime');
});
