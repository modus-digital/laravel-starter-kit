<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    // The resource class this page belongs to.
    protected static string $resource = UserResource::class;

    // The actions to display in the header.
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
