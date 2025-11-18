<?php

namespace App\Filament\Resources\Modules\Clients\Pages;

use App\Filament\Resources\Modules\Clients\ClientResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
