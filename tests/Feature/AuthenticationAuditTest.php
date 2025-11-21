<?php

declare(strict_types=1);

use App\Listeners\LogFailedLogin;
use App\Listeners\LogLogout;
use App\Listeners\LogSuccessfulLogin;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Queue;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
});

test('successful login listener creates audit log', function () {
    Queue::fake();

    $event = new Login('web', $this->user, false);
    $listener = app(LogSuccessfulLogin::class);
    $listener->handle($event);

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'authentication',
        'causer_type' => User::class,
        'causer_id' => $this->user->id,
    ]);

    $activity = Activity::where('log_name', 'authentication')
        ->where('causer_id', $this->user->id)
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->get('ip_address'))->not->toBeNull()
        ->and($activity->properties->get('user_agent'))->not->toBeNull()
        ->and($activity->properties->get('guard'))->toBe('web')
        ->and($activity->properties->get('remember'))->toBe(false);
});

test('failed login listener creates audit log with user', function () {
    Queue::fake();

    $event = new Failed('web', $this->user, ['email' => 'test@example.com', 'password' => 'wrong-password']);
    $listener = app(LogFailedLogin::class);
    $listener->handle($event);

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'authentication',
        'causer_type' => User::class,
        'causer_id' => $this->user->id,
    ]);

    $activity = Activity::where('log_name', 'authentication')
        ->where('causer_id', $this->user->id)
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->get('event'))->toBe('login_failed')
        ->and($activity->properties->get('credentials')['email'])->toBe('test@example.com')
        ->and($activity->properties->get('guard'))->toBe('web');
});

test('failed login listener creates audit log without subject for non-existent user', function () {
    Queue::fake();

    $event = new Failed('web', null, ['email' => 'nonexistent@example.com', 'password' => 'password']);
    $listener = app(LogFailedLogin::class);
    $listener->handle($event);

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'authentication',
        'causer_type' => null,
        'causer_id' => null,
    ]);

    $activity = Activity::where('log_name', 'authentication')
        ->whereNull('causer_id')
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->get('event'))->toBe('login_failed')
        ->and($activity->properties->get('credentials')['email'])->toBe('nonexistent@example.com');
});

test('logout listener creates audit log', function () {
    Queue::fake();

    $event = new Logout('web', $this->user);
    $listener = app(LogLogout::class);
    $listener->handle($event);

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'authentication',
        'causer_type' => User::class,
        'causer_id' => $this->user->id,
    ]);

    $activity = Activity::where('log_name', 'authentication')
        ->where('causer_id', $this->user->id)
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->get('guard'))->toBe('web');
});
