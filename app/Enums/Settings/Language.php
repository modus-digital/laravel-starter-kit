<?php

namespace App\Enums\Settings;

enum Language: string
{
    case ENGLISH = 'en';
    case SPANISH = 'es';
    case FRENCH = 'fr';
    case GERMAN = 'de';
    case ITALIAN = 'it';
    case PORTUGUESE = 'pt';
    case DUTCH = 'nl';

    /**
     * Get the display name for the language
     *
     * @return string
     */
    public function displayName(): string
    {
        return match ($this) {
            self::ENGLISH => __('settings.language.options.en'),
            self::SPANISH => __('settings.language.options.es'),
            self::FRENCH => __('settings.language.options.fr'),
            self::GERMAN => __('settings.language.options.de'),
            self::ITALIAN => __('settings.language.options.it'),
            self::PORTUGUESE => __('settings.language.options.pt'),
            self::DUTCH => __('settings.language.options.nl'),
        };
    }

    /**
     * Get all possible enum values as an array
     *
     * @return string[]
     */
    public static function values(): array
    {
        return array_map(fn(Language $language) => $language->value, self::cases());
    }
}
