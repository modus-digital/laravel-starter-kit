<?php

declare(strict_types=1);

namespace App\Filament\Resources\Modules\SocialiteProviders\Pages;

use App\Filament\Resources\Modules\SocialiteProviders\SocialiteProviderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

final class EditSocialiteProvider extends EditRecord
{
    protected static string $resource = SocialiteProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
