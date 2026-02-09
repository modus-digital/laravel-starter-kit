<?php

declare(strict_types=1);

use App\Listeners\LogFailedLogin;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
});

test('failed login listener creates audit log with user', function () {
    Queue::fake();

    $event = new Failed('web', $this->user, ['email' => 'test@example.com', 'password' => 'wrong-password']);
    $listener = app(LogFailedLogin::class);
    $listener->handle($event);

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'authentication',
        'event' => 'auth.login.failed',
        'causer_type' => User::class,
        'causer_id' => $this->user->id,
    ]);

    $activity = Activity::where('log_name', 'authentication')
        ->where('causer_id', $this->user->id)
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->description)->toBe('activity.auth.login_failed')
        ->and($activity->properties->get('credentials')['email'])->toBe('test@example.com')
        ->and($activity->properties->get('guard'))->toBe('web')
        ->and($activity->properties->get('issuer'))->toBeArray()
        ->and($activity->properties->get('issuer')['ip_address'])->not->toBeNull()
        ->and($activity->properties->get('issuer')['user_agent'])->not->toBeNull();
});

test('failed login listener creates audit log without subject for non-existent user', function () {
    Queue::fake();

    $event = new Failed('web', null, ['email' => 'nonexistent@example.com', 'password' => 'password']);
    $listener = app(LogFailedLogin::class);
    $listener->handle($event);

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'authentication',
        'event' => 'auth.login.failed',
        'causer_type' => null,
        'causer_id' => null,
    ]);

    $activity = Activity::where('log_name', 'authentication')
        ->whereNull('causer_id')
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->description)->toBe('activity.auth.login_failed')
        ->and($activity->properties->get('credentials')['email'])->toBe('nonexistent@example.com')
        ->and($activity->properties->get('issuer')['name'])->toBe('System');
});
