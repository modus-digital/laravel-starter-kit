<?php

declare(strict_types=1);

use App\Enums\NotificationDeliveryMethod;
use App\Events\Security\NewDeviceLogin;
use App\Events\Security\PasswordChanged;
use App\Events\Security\TwoFactorStatusChanged;
use App\Models\User;
use App\Notifications\Security\NewDeviceLoginNotification;
use App\Notifications\Security\PasswordChangedNotification;
use App\Notifications\Security\TwoFactorStatusNotification;
use Database\Seeders\BootstrapApplicationSeeder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(BootstrapApplicationSeeder::class);
});

test('password changed event sends notification', function () {
    Notification::fake();

    $user = User::factory()->create();
    $user->setPreference('notifications.security_alerts', NotificationDeliveryMethod::EMAIL->value)->save();

    Event::dispatch(new PasswordChanged(
        user: $user,
        ipAddress: '192.168.1.1',
        userAgent: 'Mozilla/5.0',
    ));

    Notification::assertSentTo($user, PasswordChangedNotification::class);
    Notification::assertCount(1);
});

test('two factor status changed event sends notification', function () {
    Notification::fake();

    $user = User::factory()->create();
    $user->setPreference('notifications.security_alerts', NotificationDeliveryMethod::EMAIL->value)->save();

    Event::dispatch(new TwoFactorStatusChanged(
        user: $user,
        enabled: true,
        ipAddress: '192.168.1.1',
        userAgent: 'Mozilla/5.0',
    ));

    Notification::assertSentTo($user, TwoFactorStatusNotification::class);
    Notification::assertCount(1);
});

test('new device login event sends notification', function () {
    Notification::fake();

    $user = User::factory()->create();
    $user->setPreference('notifications.security_alerts', NotificationDeliveryMethod::EMAIL->value)->save();

    Event::dispatch(new NewDeviceLogin(
        user: $user,
        ipAddress: '192.168.1.1',
        userAgent: 'Mozilla/5.0',
        location: null,
    ));

    Notification::assertSentTo($user, NewDeviceLoginNotification::class);
    Notification::assertCount(1);
});

test('security notification respects user preferences', function () {
    Notification::fake();

    $user = User::factory()->create();
    $user->setPreference('notifications.security_alerts', NotificationDeliveryMethod::NONE->value)->save();

    Event::dispatch(new PasswordChanged(
        user: $user,
        ipAddress: '192.168.1.1',
        userAgent: 'Mozilla/5.0',
    ));

    Notification::assertNothingSent();
});
