<?php

namespace App\Filament\Pages;

use Illuminate\Contracts\Support\Htmlable;
use Override;
use ShuvroRoy\FilamentSpatieLaravelBackup\Pages\Backups as BaseBackups;

class BackupsPage extends BaseBackups
{
    #[Override]
    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function getSlug(): string
    {
        return '/core/backups';
    }

    #[Override]
    public static function getNavigationIcon(): string|Htmlable|null
    {
        return 'heroicon-o-inbox-arrow-down';
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return 'Backups';
    }

    #[Override]
    public function getHeading(): string
    {
        return 'Backups';
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return 'Applicatie-info';
    }
}
