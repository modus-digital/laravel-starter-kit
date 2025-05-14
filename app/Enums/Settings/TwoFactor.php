<?php

namespace App\Enums\Settings;

enum TwoFactor: string
{
    case ENABLED = 'enabled';
    case DISABLED = 'disabled';

    public function description(): string
    {
        return match ($this) {
            self::ENABLED => __('settings.security.two_factor.enabled'),
            self::DISABLED => __('settings.security.two_factor.disabled'),
        };
    }

    public static function values(): array
    {
        return array_map(fn(TwoFactor $twoFactor) => $twoFactor->value, self::cases());
    }
}
