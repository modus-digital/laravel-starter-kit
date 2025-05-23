<?php

use App\Helpers\FeatureStatus;

test('feature status class exists', function () {
    expect(class_exists(FeatureStatus::class))->toBeTrue();
});

test('feature status can be instantiated', function () {
    $featureStatus = new FeatureStatus(true);
    expect($featureStatus)->toBeInstanceOf(FeatureStatus::class);
});
