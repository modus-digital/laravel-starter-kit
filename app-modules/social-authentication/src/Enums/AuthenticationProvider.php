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
            self::EMAIL => __('social-authentication::social-authentication.providers.email'),
            self::GOOGLE => __('social-authentication::social-authentication.providers.google'),
            self::GITHUB => __('social-authentication::social-authentication.providers.github'),
            self::FACEBOOK => __('social-authentication::social-authentication.providers.facebook'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::EMAIL => 'heroicon-o-envelope',
            self::GOOGLE => 'bi-google',
            self::GITHUB => 'bi-github',
            self::FACEBOOK => 'bi-facebook',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::EMAIL => 'primary',
            self::GOOGLE => 'danger',
            self::GITHUB => 'success',
            self::FACEBOOK => 'info',
        };
    }
}
