<?php

declare(strict_types=1);

use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Spatie\Activitylog\Facades\Activity as ActivityFacade;

beforeEach(function () {
    $this->user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
});

test('activity description is transformed to translation key based on event', function () {
    Queue::fake();

    ActivityFacade::inLog('authentication')
        ->event('auth.login')
        ->causedBy($this->user)
        ->log('');

    $activity = Activity::where('log_name', 'authentication')
        ->where('causer_id', $this->user->id)
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->description)->toBe('activity.auth.login');
});

test('issuer details are automatically added to properties', function () {
    Queue::fake();

    ActivityFacade::inLog('authentication')
        ->event('auth.login')
        ->causedBy($this->user)
        ->log('');

    $activity = Activity::where('log_name', 'authentication')
        ->where('causer_id', $this->user->id)
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->get('issuer'))->toBeArray()
        ->and($activity->properties->get('issuer')['name'])->toBe('John Doe')
        ->and($activity->properties->get('issuer')['email'])->toBe('john@example.com')
        ->and($activity->properties->get('issuer')['ip_address'])->not->toBeNull()
        ->and($activity->properties->get('issuer')['user_agent'])->not->toBeNull();
});

test('issuer name defaults to System when no causer', function () {
    Queue::fake();

    ActivityFacade::inLog('authentication')
        ->event('auth.login.failed')
        ->log('');

    $activity = Activity::where('log_name', 'authentication')
        ->whereNull('causer_id')
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->get('issuer')['name'])->toBe('System');
});

test('custom properties are preserved and merged with issuer', function () {
    Queue::fake();

    ActivityFacade::inLog('impersonation')
        ->event('impersonate.start')
        ->causedBy($this->user)
        ->withProperties([
            'target' => 'Jane Doe',
            'custom_data' => 'value',
        ])
        ->log('');

    $activity = Activity::where('log_name', 'impersonation')
        ->where('causer_id', $this->user->id)
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->get('target'))->toBe('Jane Doe')
        ->and($activity->properties->get('custom_data'))->toBe('value')
        ->and($activity->properties->get('issuer'))->toBeArray()
        ->and($activity->properties->get('issuer')['name'])->toBe('John Doe');
});

test('getTranslatedDescription returns translated text with replacements', function () {
    Queue::fake();

    ActivityFacade::inLog('authentication')
        ->event('auth.login')
        ->causedBy($this->user)
        ->log('');

    $activity = Activity::where('log_name', 'authentication')
        ->where('causer_id', $this->user->id)
        ->latest()
        ->first();

    $translated = $activity->getTranslatedDescription();

    expect($translated)->toBe('John Doe signed in');
});

test('getTranslatedDescription works for impersonation with target', function () {
    Queue::fake();

    ActivityFacade::inLog('impersonation')
        ->event('impersonate.start')
        ->causedBy($this->user)
        ->withProperties([
            'target' => 'Jane Doe',
        ])
        ->log('');

    $activity = Activity::where('log_name', 'impersonation')
        ->where('causer_id', $this->user->id)
        ->latest()
        ->first();

    $translated = $activity->getTranslatedDescription();

    expect($translated)->toBe('John Doe started impersonating Jane Doe');
});

test('getTranslatedDescription works for user management actions', function () {
    Queue::fake();

    $targetUser = User::factory()->create(['name' => 'New User']);

    ActivityFacade::inLog('administration')
        ->event('user.created')
        ->causedBy($this->user)
        ->performedOn($targetUser)
        ->withProperties([
            'target' => $targetUser->name,
        ])
        ->log('');

    $activity = Activity::where('log_name', 'administration')
        ->where('event', 'user.created')
        ->latest()
        ->first();

    $translated = $activity->getTranslatedDescription();

    expect($translated)->toBe('John Doe created user New User');
});

test('getTranslatedDescription falls back to key when translation not found', function () {
    Queue::fake();

    ActivityFacade::inLog('custom')
        ->event('custom.unknown.event')
        ->causedBy($this->user)
        ->log('');

    $activity = Activity::where('log_name', 'custom')
        ->where('causer_id', $this->user->id)
        ->latest()
        ->first();

    $translated = $activity->getTranslatedDescription();

    // When translation is not found, Laravel returns the key itself
    expect($translated)->toBe('activity.custom.unknown.event');
});

test('existing issuer properties are preserved and merged', function () {
    Queue::fake();

    // Simulate passing custom issuer data (e.g., for specific context)
    ActivityFacade::inLog('authentication')
        ->event('auth.login')
        ->causedBy($this->user)
        ->withProperties([
            'issuer' => [
                'custom_field' => 'custom_value',
            ],
        ])
        ->log('');

    $activity = Activity::where('log_name', 'authentication')
        ->where('causer_id', $this->user->id)
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->get('issuer')['custom_field'])->toBe('custom_value')
        ->and($activity->properties->get('issuer')['name'])->toBe('John Doe')
        ->and($activity->properties->get('issuer')['email'])->toBe('john@example.com');
});

test('getTranslatedDescription works for field updates with attribute changes', function () {
    Queue::fake();

    $targetUser = User::factory()->create(['name' => 'Test User']);

    ActivityFacade::inLog('administration')
        ->event('user.updated')
        ->causedBy($this->user)
        ->performedOn($targetUser)
        ->withProperties([
            'user' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'email' => $targetUser->email,
            ],
            'attribute' => 'name',
            'old' => 'Old Name',
            'new' => 'New Name',
        ])
        ->log('');

    $activity = Activity::where('log_name', 'administration')
        ->where('event', 'user.updated')
        ->latest()
        ->first();

    $translated = $activity->getTranslatedDescription();

    expect($translated)->toBe('John Doe updated name on Test User from Old Name to New Name');
});

test('getTranslatedDescription handles null old values as empty', function () {
    Queue::fake();

    $targetUser = User::factory()->create(['name' => 'Test User']);

    ActivityFacade::inLog('administration')
        ->event('user.updated')
        ->causedBy($this->user)
        ->performedOn($targetUser)
        ->withProperties([
            'user' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'email' => $targetUser->email,
            ],
            'attribute' => 'phone',
            'old' => null,
            'new' => '+1234567890',
        ])
        ->log('');

    $activity = Activity::where('log_name', 'administration')
        ->where('event', 'user.updated')
        ->latest()
        ->first();

    $translated = $activity->getTranslatedDescription();

    expect($translated)->toBe('John Doe updated phone on Test User from empty to +1234567890');
});
