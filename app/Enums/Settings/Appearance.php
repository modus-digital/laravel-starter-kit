<?php

namespace App\Enums\Settings;

enum Appearance: string
{

    case LIGHT = 'light';
    case DARK = 'dark';
    case SYSTEM = 'system';

    public function description(): string
    {
        return match ($this) {
            self::LIGHT => 'Light theme',
            self::DARK => 'Dark theme',
            self::SYSTEM => 'System theme',
        };
    }
}
