<?php

declare(strict_types=1);

namespace App\Enums\Settings;

use App\Traits\Enums\HasValues;
use Filament\Support\Contracts\HasLabel;

enum Language: string implements HasLabel
{
    use HasValues;

    case ENGLISH = 'en';
    case SPANISH = 'es';
    case FRENCH = 'fr';
    case GERMAN = 'de';
    case ITALIAN = 'it';
    case PORTUGUESE = 'pt';
    case DUTCH = 'nl';

    public function getLabel(): string
    {
        return match ($this) {
            self::ENGLISH => __('enums.settings.language.english'),
            self::SPANISH => __('enums.settings.language.spanish'),
            self::FRENCH => __('enums.settings.language.french'),
            self::GERMAN => __('enums.settings.language.german'),
            self::ITALIAN => __('enums.settings.language.italian'),
            self::PORTUGUESE => __('enums.settings.language.portuguese'),
            self::DUTCH => __('enums.settings.language.dutch'),
        };
    }
}
