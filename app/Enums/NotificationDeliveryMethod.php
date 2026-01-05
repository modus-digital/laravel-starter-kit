<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationDeliveryMethod: string
{
    case NONE = 'none';
    case EMAIL = 'email';
    case PUSH = 'push';
    case EMAIL_PUSH = 'email_push';

    public function label(): string
    {
        return match ($this) {
            self::NONE => 'None',
            self::EMAIL => 'Email',
            self::PUSH => 'Push',
            self::EMAIL_PUSH => 'Email + Push',
        };
    }
}
