<?php

test('feature helper function exists', function () {
    expect(function_exists('feature'))->toBeTrue();
});

test('download backup codes helper function exists', function () {
    expect(function_exists('download_backup_codes'))->toBeTrue();
});

test('local date helper function exists', function () {
    expect(function_exists('local_date'))->toBeTrue();
});
