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
            self::LOCALIZATION => __('settings.categories.localization'),
            self::SECURITY => __('settings.categories.security'),
            self::DISPLAY => __('settings.categories.display'),
            self::NOTIFICATIONS => __('settings.categories.notifications'),
        };
    }
}
