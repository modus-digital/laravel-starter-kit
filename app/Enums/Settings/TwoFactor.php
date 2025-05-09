<?php

namespace App\Enums\Settings;

enum TwoFactor: string
{
    case ENABLED = 'enabled';
    case DISABLED = 'disabled';

    public function description(): string
    {
        return match ($this) {
            self::ENABLED => 'Two-factor authentication is enabled',
            self::DISABLED => 'Two-factor authentication is disabled',
        };
    }
}
