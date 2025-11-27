<?php

declare(strict_types=1);

use App\Enums\ActivityStatus;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;

it('casts status to activity status enum on user', function (): void {
    $user = new User([
        'name' => 'Test User',
        'email' => 'user@example.com',
        'status' => ActivityStatus::ACTIVE,
    ]);

    expect($user->status)
        ->toBeInstanceOf(ActivityStatus::class)
        ->and($user->status)->toBe(ActivityStatus::ACTIVE);
});

it('has activities morphMany relation on user', function (): void {
    $user = new User();

    expect($user->activities())
        ->toBeInstanceOf(MorphMany::class)
        ->and($user->activities()->getRelated())->toBeInstanceOf(Activity::class);
});


