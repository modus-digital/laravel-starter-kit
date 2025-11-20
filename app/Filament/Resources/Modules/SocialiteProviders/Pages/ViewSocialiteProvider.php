<?php

declare(strict_types=1);

namespace App\Filament\Resources\Modules\SocialiteProviders\Pages;

use App\Filament\Resources\Modules\SocialiteProviders\SocialiteProviderResource;
use App\Models\Modules\SocialiteProvider;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewSocialiteProvider extends ViewRecord
{
    protected static string $resource = SocialiteProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn (SocialiteProvider $record): bool => config("modules.socialite.providers.{$record->name}", false)),
        ];
    }
}
