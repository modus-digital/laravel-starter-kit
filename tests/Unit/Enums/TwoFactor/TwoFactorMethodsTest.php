<?php

use App\Enums\Settings\TwoFactor;

test('two factor values method works correctly', function () {
    $values = TwoFactor::values();

    expect($values)
        ->toBeArray()
        ->toHaveCount(2)
        ->toContain('enabled')
        ->toContain('disabled');
});
