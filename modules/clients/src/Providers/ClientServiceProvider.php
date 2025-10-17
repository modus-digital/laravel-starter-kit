<?php

declare(strict_types=1);

namespace ModusDigital\Clients\Providers;

use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use ModusDigital\Clients\ClientPlugin;

final class ClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Panel::configureUsing(
            modifyUsing: fn (Panel $panel) => (
                $panel->getId() !== 'control' || $panel->plugin(new ClientPlugin())
            )
        );
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'clients');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'clients');
    }
}
