<?php

use App\Helpers\FeatureStatus;

test('feature status value returns original value', function () {
    $featureStatus1 = new FeatureStatus(true);
    $featureStatus2 = new FeatureStatus(false);
    $featureStatus3 = new FeatureStatus(null);

    expect($featureStatus1->value())->toBeTrue();
    expect($featureStatus2->value())->toBeFalse();
    expect($featureStatus3->value())->toBeNull();
});
