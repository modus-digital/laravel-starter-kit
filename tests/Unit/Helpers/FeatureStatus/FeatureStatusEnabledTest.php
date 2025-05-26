<?php

use App\Helpers\FeatureStatus;

test('feature status enabled returns true for true value', function () {
    $featureStatus = new FeatureStatus(true);
    expect($featureStatus->enabled())->toBeTrue();
});

test('feature status enabled returns false for false value', function () {
    $featureStatus = new FeatureStatus(false);
    expect($featureStatus->enabled())->toBeFalse();
});

test('feature status enabled returns false for null value', function () {
    $featureStatus = new FeatureStatus(null);
    expect($featureStatus->enabled())->toBeFalse();
});
