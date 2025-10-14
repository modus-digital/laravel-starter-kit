<?php

declare(strict_types=1);

namespace App\Enums\Settings;

use App\Traits\Enums\HasValues;
use Filament\Support\Contracts\HasLabel;

enum Theme: string implements HasLabel
{
    use HasValues;

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
    public function getLabel(): string
    {
        return match ($this) {
            self::BLUE => __('enums.settings.theme.blue'),
            self::INDIGO => __('enums.settings.theme.indigo'),
            self::RED => __('enums.settings.theme.red'),
            self::ORANGE => __('enums.settings.theme.orange'),
            self::YELLOW => __('enums.settings.theme.yellow'),
            self::GREEN => __('enums.settings.theme.green'),
            self::TEAL => __('enums.settings.theme.teal'),
            self::CYAN => __('enums.settings.theme.cyan'),
            self::PURPLE => __('enums.settings.theme.purple'),
        };
    }
}
