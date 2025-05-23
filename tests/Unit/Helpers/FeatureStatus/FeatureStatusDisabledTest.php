<?php

use App\Helpers\FeatureStatus;

test('feature status disabled returns false for true value', function () {
    $featureStatus = new FeatureStatus(true);
    expect($featureStatus->disabled())->toBeFalse();
});

test('feature status disabled returns true for false value', function () {
    $featureStatus = new FeatureStatus(false);
    expect($featureStatus->disabled())->toBeTrue();
});

test('feature status disabled returns true for null value', function () {
    $featureStatus = new FeatureStatus(null);
    expect($featureStatus->disabled())->toBeTrue();
});
