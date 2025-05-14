<?php

namespace App\Enums\Settings;

enum Theme: string
{
    case BLUE = 'blue';
    case INDIGO = 'indigo';

    public function description(): string
    {
        return match ($this) {
            self::BLUE => __('settings.theme.options.blue'),
            self::INDIGO => __('settings.theme.options.indigo'),
        };
    }

    public static function values(): array
    {
        return array_map(fn(Theme $theme) => $theme->value, self::cases());
    }
}
