<?php

use App\Enums\Settings\TwoFactor;

test('two factor enum is backed by string', function () {
    expect(TwoFactor::ENABLED)
        ->toBeInstanceOf(\BackedEnum::class);

    expect(TwoFactor::ENABLED->value)->toBeString();
    expect(TwoFactor::DISABLED->value)->toBeString();
});

test('two factor enum can be created from value', function () {
    $enabled = TwoFactor::from('enabled');
    $disabled = TwoFactor::from('disabled');

    expect($enabled)->toBe(TwoFactor::ENABLED);
    expect($disabled)->toBe(TwoFactor::DISABLED);
});

test('two factor enum try from returns null for invalid value', function () {
    $result = TwoFactor::tryFrom('invalid');
    expect($result)->toBeNull();
});
