<?php

declare(strict_types=1);

namespace App\Enums\Settings;

use App\Traits\Enums\HasValues;
use Filament\Support\Contracts\HasLabel;

enum Appearance: string implements HasLabel
{
    use HasValues;

    case LIGHT = 'light';
    case DARK = 'dark';
    case SYSTEM = 'system';

    public function getLabel(): string
    {
        return match ($this) {
            self::LIGHT => __('enums.settings.appearance.light'),
            self::DARK => __('enums.settings.appearance.dark'),
            self::SYSTEM => __('enums.settings.appearance.system'),
        };
    }
}
