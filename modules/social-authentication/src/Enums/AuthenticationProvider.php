<?php

declare(strict_types=1);

namespace ModusDigital\SocialAuthentication\Enums;

enum AuthenticationProvider: string
{
    case EMAIL = 'email';
    case GOOGLE = 'google';
    case GITHUB = 'github';
    case FACEBOOK = 'facebook';

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->getLabel()])
            ->toArray();
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::EMAIL => 'Email',
            self::GOOGLE => 'Google',
            self::GITHUB => 'GitHub',
            self::FACEBOOK => 'Facebook',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::EMAIL => 'heroicon-o-envelope',
            self::GOOGLE => 'heroicon-o-globe-alt',
            self::GITHUB => 'heroicon-o-code-bracket',
            self::FACEBOOK => 'heroicon-o-user-group',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::EMAIL => 'primary',
            self::GOOGLE => 'danger',
            self::GITHUB => 'gray',
            self::FACEBOOK => 'info',
        };
    }
}
