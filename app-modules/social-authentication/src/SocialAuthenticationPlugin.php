<?php

declare(strict_types=1);

namespace ModusDigital\SocialAuthentication;

use Filament\Contracts\Plugin;
use Filament\Panel;

final class SocialAuthenticationPlugin implements Plugin
{
    public function getId(): string
    {
        return 'social-authentication';
    }

    public function register(Panel $panel): void
    {
        $panel->discoverResources(
            in: __DIR__.'/Filament/Resources',
            for: 'ModusDigital\\SocialAuthentication\\Filament\\Resources'
        );
    }

    public function boot(Panel $panel): void {}
}
