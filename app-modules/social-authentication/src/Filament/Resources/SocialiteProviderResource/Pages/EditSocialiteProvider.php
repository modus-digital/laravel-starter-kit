<?php

declare(strict_types=1);

namespace ModusDigital\SocialAuthentication\Filament\Resources\SocialiteProviderResource\Pages;

use Filament\Resources\Pages\EditRecord;
use ModusDigital\SocialAuthentication\Filament\Resources\SocialiteProviderResource\SocialiteProviderResource;

final class EditSocialiteProvider extends EditRecord
{
    protected static string $resource = SocialiteProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('social-authentication::social-authentication.notifications.saved');
    }
}
