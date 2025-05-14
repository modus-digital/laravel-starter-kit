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
            self::LIGHT => __('settings.appearance.modes.light'),
            self::DARK => __('settings.appearance.modes.dark'),
            self::SYSTEM => __('settings.appearance.modes.system'),
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::LIGHT => __('settings.appearance.labels.light'),
            self::DARK => __('settings.appearance.labels.dark'),
            self::SYSTEM => __('settings.appearance.labels.system'),
        };
    }
    public static function values(): array
    {
        return array_map(fn(Appearance $appearance) => $appearance->value, self::cases());
    }
}
