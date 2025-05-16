<?php

namespace App\Enums\Settings;

enum TwoFactor: string
{
    case ENABLED = 'enabled';
    case DISABLED = 'disabled';

    /**
     * Get the description for the two-factor authentication
     */
    public function description(): string
    {
        return match ($this) {
            self::ENABLED => __('settings.security.two_factor.enabled'),
            self::DISABLED => __('settings.security.two_factor.disabled'),
        };
    }

    /**
     * Get all possible enum values as an array
     *
     * @return string[]
     */
    public static function values(): array
    {
        return array_map(fn (TwoFactor $twoFactor) => $twoFactor->value, self::cases());
    }
}
