<?php

declare(strict_types=1);

namespace App\Enums\Settings;

use App\Traits\Enums\HasValues;
use Filament\Support\Contracts\HasLabel;

enum TwoFactor: string implements HasLabel
{
    use HasValues;

    case ENABLED = 'enabled';
    case DISABLED = 'disabled';

    public function getLabel(): string
    {
        return match ($this) {
            self::ENABLED => __('enums.settings.two_factor.label.enabled'),
            self::DISABLED => __('enums.settings.two_factor.label.disabled'),
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::ENABLED => __('enums.settings.two_factor.description.enabled'),
            self::DISABLED => __('enums.settings.two_factor.description.disabled'),
        };
    }
}
