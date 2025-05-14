<?php

namespace App\Filament\Pages;

use Illuminate\Contracts\Support\Htmlable;
use ShuvroRoy\FilamentSpatieLaravelBackup\Pages\Backups as BaseBackups;

class BackupsPage extends BaseBackups
{
    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function getSlug(): string
    {
        return '/core/backups';
    }

    public static function getNavigationIcon(): string|Htmlable|null
    {
        return 'heroicon-o-inbox-arrow-down';
    }

    public static function getNavigationLabel(): string
    {
        return 'Backups';
    }

    public function getHeading(): string
    {
        return 'Backups';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Applicatie-info';
    }
}
