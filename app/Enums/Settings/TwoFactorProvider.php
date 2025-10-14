<?php

declare(strict_types=1);

namespace App\Enums\Settings;

enum TwoFactorProvider: string
{
    case EMAIL = 'email';
    case AUTHENTICATOR = 'authenticator';

    public function getLabel(): string
    {
        return match ($this) {
            self::EMAIL => __('enums.settings.two_factor.providers.email'),
            self::AUTHENTICATOR => __('enums.settings.two_factor.providers.authenticator')
        };
    }
}
