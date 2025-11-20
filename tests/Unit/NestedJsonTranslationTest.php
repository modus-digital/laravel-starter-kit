<?php

use App\Translation\NestedJsonLoader;

describe('Nested JSON Translation', function () {
    it('uses the custom nested json loader', function () {
        $loader = app('translation.loader');

        expect($loader)->toBeInstanceOf(NestedJsonLoader::class);
    });

    it('translates nested json keys using dot notation', function () {
        expect(__('enums.rbac.role.admin'))->toBe('Admin')
            ->and(__('enums.rbac.role.super_admin'))->toBe('Super Admin')
            ->and(__('enums.activity_status.active'))->toBe('Active');
    });

    it('translates deeply nested json keys', function () {
        expect(__('auth.failed'))->toBe('These credentials do not match our records.')
            ->and(__('auth.password'))->toBe('The provided password is incorrect.')
            ->and(__('auth.throttle'))->toBe('Too many login attempts. Please try again in :seconds seconds.');
    });

    it('translates pagination keys', function () {
        expect(__('pagination.next'))->toBe('Next &raquo;')
            ->and(__('pagination.previous'))->toBe('&laquo; Previous');
    });

    it('translates password reset keys', function () {
        expect(__('passwords.reset'))->toBe('Your password has been reset.')
            ->and(__('passwords.sent'))->toBe('We have emailed your password reset link.');
    });

    it('translates validation keys', function () {
        expect(__('validation.required'))->toBe('The :attribute field is required.')
            ->and(__('validation.email'))->toBe('The :attribute field must be a valid email address.');
    });

    it('translates deeply nested validation keys', function () {
        expect(__('validation.between.array'))->toBe('The :attribute field must have between :min and :max items.')
            ->and(__('validation.password.mixed'))->toBe('The :attribute field must contain at least one uppercase and one lowercase letter.');
    });

    it('returns the key when translation is missing', function () {
        expect(__('enums.missing.key'))->toBe('enums.missing.key');
    });
});
