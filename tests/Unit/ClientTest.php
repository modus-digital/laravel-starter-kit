<?php

declare(strict_types=1);

use App\Enums\ActivityStatus;
use App\Models\Activity;
use App\Models\Modules\Clients\Client;
use Illuminate\Database\Eloquent\Relations\MorphMany;

it('casts status to activity status enum', function (): void {
    $client = new Client([
        'name' => 'Test Client',
        'status' => ActivityStatus::ACTIVE,
    ]);

    expect($client->status)
        ->toBeInstanceOf(ActivityStatus::class)
        ->and($client->status)->toBe(ActivityStatus::ACTIVE);
});

it('has activities morphMany relation', function (): void {
    $client = new Client();

    expect($client->activities())
        ->toBeInstanceOf(MorphMany::class)
        ->and($client->activities()->getRelated())->toBeInstanceOf(Activity::class);
});
