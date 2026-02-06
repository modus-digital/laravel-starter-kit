<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\NotificationDeliveryMethod;

final class NotificationChannelResolver
{
    /**
     * Resolve notification channels from user preference.
     *
     * @return array<int, string>
     */
    public function resolve(NotificationDeliveryMethod $preference): array
    {
        return match ($preference) {
            NotificationDeliveryMethod::NONE => [],
            NotificationDeliveryMethod::EMAIL => ['mail'],
            NotificationDeliveryMethod::PUSH => ['database'],
            NotificationDeliveryMethod::EMAIL_PUSH => ['mail', 'database'],
        };
    }
}
