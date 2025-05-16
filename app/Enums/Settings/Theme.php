<?php

namespace App\Enums\Settings;

enum Theme: string
{
    case BLUE = 'blue';
    case INDIGO = 'indigo';
    case RED = 'red';
    case ORANGE = 'orange';
    case YELLOW = 'yellow';
    case GREEN = 'green';
    case TEAL = 'teal';
    case CYAN = 'cyan';
    case PURPLE = 'purple';

    /**
     * Get the description for the theme
     */
    public function description(): string
    {
        return match ($this) {
            self::BLUE => __('settings.theme.options.blue'),
            self::INDIGO => __('settings.theme.options.indigo'),
            self::RED => __('settings.theme.options.red'),
            self::ORANGE => __('settings.theme.options.orange'),
            self::YELLOW => __('settings.theme.options.yellow'),
            self::GREEN => __('settings.theme.options.green'),
            self::TEAL => __('settings.theme.options.teal'),
            self::CYAN => __('settings.theme.options.cyan'),
            self::PURPLE => __('settings.theme.options.purple'),
        };
    }

    /**
     * Get all possible enum values as an array
     *
     * @return string[]
     */
    public static function values(): array
    {
        return array_map(fn (Theme $theme) => $theme->value, self::cases());
    }
}
