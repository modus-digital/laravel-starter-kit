<?php

namespace App\Enums;

use App\Traits\Enums\HasOptions;

enum AuthenticationProvider: string
{
    use HasOptions;

    case EMAIL = 'email';
    case GOOGLE = 'google';
    case GITHUB = 'github';
    case MICROSOFT = 'microsoft';

    public function getLabel(): string
    {
        return match ($this) {
            self::EMAIL => 'Email',
            self::GOOGLE => 'Google',
            self::GITHUB => 'GitHub',
            self::MICROSOFT => 'Microsoft',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::EMAIL => 'heroicon-o-envelope',
            self::GOOGLE => 'bi-google',
            self::GITHUB => 'bi-github',
            self::MICROSOFT => 'bi-microsoft',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::EMAIL => 'primary',
            self::GOOGLE => 'danger',
            self::GITHUB => 'success',
            self::MICROSOFT => 'info',
        };
    }
}
