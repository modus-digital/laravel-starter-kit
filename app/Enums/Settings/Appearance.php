<?php

namespace App\Enums\Settings;

enum Appearance: string
{
    case LIGHT = 'light';
    case DARK = 'dark';
    case SYSTEM = 'system';

    /**
     * Get the description for the appearance mode
     *
     * @return string
     */
    public function description(): string
    {
        return match ($this) {
            self::LIGHT => __('settings.appearance.modes.light'),
            self::DARK => __('settings.appearance.modes.dark'),
            self::SYSTEM => __('settings.appearance.modes.system'),
        };
    }

    /**
     * Get the label for the appearance mode
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::LIGHT => __('settings.appearance.labels.light'),
            self::DARK => __('settings.appearance.labels.dark'),
            self::SYSTEM => __('settings.appearance.labels.system'),
        };
    }

    /**
     * Get all possible enum values as an array
     *
     * @return string[]
     */
    public static function values(): array
    {
        return array_map(fn(Appearance $appearance) => $appearance->value, self::cases());
    }
}
