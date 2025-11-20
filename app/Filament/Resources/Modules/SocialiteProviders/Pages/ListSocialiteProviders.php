<?php

declare(strict_types=1);

namespace App\Filament\Resources\Modules\SocialiteProviders\Pages;

use App\Filament\Resources\Modules\SocialiteProviders\SocialiteProviderResource;
use Filament\Resources\Pages\ListRecords;

final class ListSocialiteProviders extends ListRecords
{
    protected static string $resource = SocialiteProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
