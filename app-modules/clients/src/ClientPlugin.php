<?php

declare(strict_types=1);

namespace ModusDigital\Clients;

use Filament\Contracts\Plugin;
use Filament\Panel;

final class ClientPlugin implements Plugin
{
    public function getId(): string
    {
        return 'clients';
    }

    public function register(Panel $panel): void
    {
        $panel->discoverResources(
            in: __DIR__.'/Filament/Resources',
            for: 'ModusDigital\\Clients\\Filament\\Resources'
        );
    }

    public function boot(Panel $panel): void {}
}
