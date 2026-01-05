<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\Users\Pages;

use App\Filament\Resources\Core\Users\UserResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
