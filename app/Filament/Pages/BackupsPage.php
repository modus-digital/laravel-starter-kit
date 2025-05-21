<?php

namespace App\Filament\Pages;

use Illuminate\Contracts\Support\Htmlable;
use Override;
use ShuvroRoy\FilamentSpatieLaravelBackup\Pages\Backups as BaseBackups;

class BackupsPage extends BaseBackups
{
    public static ?int $navigationSort = 2;

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
        return __('admin.navigation.pages.backups');
    }

    #[Override]
    public function getHeading(): string
    {
        return __('admin.navigation.pages.backups');
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.applicatie-info');
    }
}
