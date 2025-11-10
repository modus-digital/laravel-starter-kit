<?php

declare(strict_types=1);

namespace ModusDigital\SocialAuthentication\Filament\Resources\SocialiteProviderResource\Pages;

use Filament\Resources\Pages\ListRecords;
use ModusDigital\SocialAuthentication\Filament\Resources\SocialiteProviderResource\SocialiteProviderResource;

final class ListSocialiteProviders extends ListRecords
{
    protected static string $resource = SocialiteProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
