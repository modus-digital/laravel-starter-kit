<?php

namespace App\Filament\Resources\Modules\SocialiteProviders\Pages;

use App\Filament\Resources\Modules\SocialiteProviders\SocialiteProviderResource;
use Filament\Resources\Pages\ListRecords;

class ListSocialiteProviders extends ListRecords
{
    protected static string $resource = SocialiteProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
