<?php

use App\Enums\Settings\TwoFactor;

test('two factor enum exists and has enabled case', function () {
    expect(enum_exists(TwoFactor::class))->toBeTrue();
    expect(TwoFactor::ENABLED->value)->toBe('enabled');
});

test('two factor enum has disabled case', function () {
    expect(TwoFactor::DISABLED->value)->toBe('disabled');
});

test('two factor enum cases are correct', function () {
    $cases = TwoFactor::cases();

    expect($cases)
        ->toHaveCount(2)
        ->toContain(TwoFactor::ENABLED)
        ->toContain(TwoFactor::DISABLED);
});
