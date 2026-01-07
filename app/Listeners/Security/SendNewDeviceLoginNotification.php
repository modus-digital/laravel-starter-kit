<?php

declare(strict_types=1);

namespace App\Listeners\Security;

use App\Enums\NotificationDeliveryMethod;
use App\Events\Security\NewDeviceLogin;
use App\Notifications\Security\NewDeviceLoginNotification;
use App\Services\NotificationChannelResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendNewDeviceLoginNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly NotificationChannelResolver $channelResolver,
    ) {}

    public function handle(NewDeviceLogin $event): void
    {
        $user = $event->user;

        $preference = NotificationDeliveryMethod::tryFrom(
            (string) $user->getPreference('notifications.security_alerts', NotificationDeliveryMethod::EMAIL->value)
        ) ?? NotificationDeliveryMethod::EMAIL;

        $channels = $this->channelResolver->resolve($preference);

        if (empty($channels)) {
            return;
        }

        $user->notify(new NewDeviceLoginNotification(
            ipAddress: $event->ipAddress,
            userAgent: $event->userAgent,
            location: $event->location,
            channels: $channels,
        ));
    }
}
