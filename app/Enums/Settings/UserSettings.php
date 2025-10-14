<?php

declare(strict_types=1);

namespace App\Enums\Settings;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

enum UserSettings: string implements HasDescription, HasLabel
{
    case LOCALIZATION = 'localization';
    case SECURITY = 'security';
    case DISPLAY = 'display';
    case NOTIFICATIONS = 'notifications';

    public function getLabel(): string
    {
        return match ($this) {
            self::LOCALIZATION => __('enums.settings.user_settings.label.localization'),
            self::SECURITY => __('enums.settings.user_settings.label.security'),
            self::DISPLAY => __('enums.settings.user_settings.label.display'),
            self::NOTIFICATIONS => __('enums.settings.user_settings.label.notifications'),
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::LOCALIZATION => __('enums.settings.user_settings.description.localization'),
            self::SECURITY => __('enums.settings.user_settings.description.security'),
            self::DISPLAY => __('enums.settings.user_settings.description.display'),
            self::NOTIFICATIONS => __('enums.settings.user_settings.description.notifications'),
        };
    }
}
