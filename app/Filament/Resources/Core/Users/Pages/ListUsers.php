<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\Users\Pages;

use App\Filament\Resources\Core\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
