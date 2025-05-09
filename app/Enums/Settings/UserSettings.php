<?php

namespace App\Enums\Settings;

enum UserSettings: string
{
    case LOCALIZATION = 'localization';
    case SECURITY = 'security';
    case DISPLAY = 'display';
    case NOTIFICATIONS = 'notifications';

    public function description(): string
    {
        return match ($this) {
            self::LOCALIZATION => 'Localization settings',
            self::SECURITY => 'Security settings',
            self::DISPLAY => 'Display settings',
            self::NOTIFICATIONS => 'Notifications settings',
        };
    }
}
